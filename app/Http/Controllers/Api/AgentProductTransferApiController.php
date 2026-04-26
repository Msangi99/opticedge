<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AgentProductListAssignment;
use App\Models\AgentProductTransfer;
use App\Models\User;
use App\Services\AgentProductTransferService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgentProductTransferApiController extends Controller
{
    /**
     * Active agents other than the authenticated user (same rules as web AgentController::transferCreate).
     */
    public function transferRecipients()
    {
        $agents = User::query()
            ->where('role', 'agent')
            ->where('status', 'active')
            ->where('id', '!=', Auth::id())
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return response()->json([
            'data' => $agents->map(fn (User $u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
            ])->values()->all(),
        ]);
    }

    public function transferableImeis(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:models,id',
        ]);

        $service = app(AgentProductTransferService::class);
        $locked = $service->productListIdsInPendingOutgoingTransfer(Auth::id());

        $rows = AgentProductListAssignment::query()
            ->where('agent_id', Auth::id())
            ->whereHas('productListItem', function ($q) use ($validated) {
                $q->where('product_id', (int) $validated['product_id'])->whereNull('sold_at');
            })
            ->with('productListItem')
            ->get()
            ->pluck('productListItem')
            ->filter(fn ($item) => $item && ! $locked->contains($item->id));

        return response()->json([
            'data' => $rows->map(fn ($i) => [
                'id' => $i->id,
                'imei_number' => $i->imei_number,
                'model' => $i->model,
                'text' => $i->imei_number.($i->model ? ' – '.$i->model : ''),
            ])->values()->all(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'to_agent_id' => 'required|exists:users,id',
            'product_list_ids' => 'required|array|min:1',
            'product_list_ids.*' => 'distinct|integer|exists:product_list,id',
            'message' => 'nullable|string|max:2000',
        ]);

        try {
            $transfer = app(AgentProductTransferService::class)->createTransfer(
                Auth::user(),
                (int) $validated['to_agent_id'],
                $validated['product_list_ids'],
                $validated['message'] ?? null
            );
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Transfer request submitted.',
            'data' => $this->transferSummary($transfer),
        ], 201);
    }

    public function index(Request $request)
    {
        $transfers = AgentProductTransfer::query()
            ->where(function ($q) {
                $q->where('from_agent_id', Auth::id())
                    ->orWhere('to_agent_id', Auth::id());
            })
            ->with(['fromAgent', 'toAgent', 'items'])
            ->latest()
            ->paginate($request->integer('per_page', 20));

        return response()->json([
            'data' => $transfers->getCollection()->map(fn ($t) => $this->transferSummary($t))->values()->all(),
            'meta' => [
                'current_page' => $transfers->currentPage(),
                'last_page' => $transfers->lastPage(),
                'per_page' => $transfers->perPage(),
                'total' => $transfers->total(),
            ],
        ]);
    }

    public function cancel(AgentProductTransfer $agent_product_transfer)
    {
        if ((int) $agent_product_transfer->from_agent_id !== (int) Auth::id()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }
        try {
            app(AgentProductTransferService::class)->cancelOwn($agent_product_transfer, Auth::user());
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['message' => 'Cancelled.', 'data' => $this->transferSummary($agent_product_transfer->fresh())]);
    }

    private function transferSummary(AgentProductTransfer $t): array
    {
        $t->loadMissing(['fromAgent', 'toAgent', 'items']);

        return [
            'id' => $t->id,
            'status' => $t->status,
            'message' => $t->message,
            'admin_note' => $t->admin_note,
            'created_at' => $t->created_at?->toIso8601String(),
            'decided_at' => $t->decided_at?->toIso8601String(),
            'from_agent' => $t->fromAgent ? ['id' => $t->fromAgent->id, 'name' => $t->fromAgent->name, 'email' => $t->fromAgent->email] : null,
            'to_agent' => $t->toAgent ? ['id' => $t->toAgent->id, 'name' => $t->toAgent->name, 'email' => $t->toAgent->email] : null,
            'items_count' => $t->items->count(),
        ];
    }
}
