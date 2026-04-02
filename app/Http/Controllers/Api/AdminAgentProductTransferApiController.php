<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AgentProductTransfer;
use App\Services\AgentProductTransferService;
use Illuminate\Http\Request;

class AdminAgentProductTransferApiController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $q = AgentProductTransfer::query()
            ->with(['fromAgent', 'toAgent', 'items'])
            ->latest();

        if ($status && in_array($status, ['pending', 'approved', 'rejected', 'cancelled'], true)) {
            $q->where('status', $status);
        }

        $page = $q->paginate($request->integer('per_page', 25));

        return response()->json([
            'data' => $page->getCollection()->map(fn ($t) => $this->summary($t))->values()->all(),
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'per_page' => $page->perPage(),
                'total' => $page->total(),
            ],
        ]);
    }

    public function show(AgentProductTransfer $agent_product_transfer)
    {
        $agent_product_transfer->load([
            'fromAgent',
            'toAgent',
            'decidedByUser',
            'items.productListItem.product.category',
            'items.productListItem.purchase.branch',
            'items.productListItem.stock',
            'items.productListItem.branch',
        ]);

        return response()->json(['data' => $this->detail($agent_product_transfer)]);
    }

    public function approve(Request $request, AgentProductTransfer $agent_product_transfer)
    {
        $validated = $request->validate([
            'admin_note' => 'nullable|string|max:2000',
        ]);

        try {
            app(AgentProductTransferService::class)->approve(
                $agent_product_transfer,
                $request->user(),
                $validated['admin_note'] ?? null
            );
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Approved.',
            'data' => $this->detail($agent_product_transfer->fresh([
                'fromAgent', 'toAgent', 'decidedByUser', 'items.productListItem.product.category',
                'items.productListItem.purchase.branch', 'items.productListItem.stock', 'items.productListItem.branch',
            ])),
        ]);
    }

    public function reject(Request $request, AgentProductTransfer $agent_product_transfer)
    {
        $validated = $request->validate([
            'admin_note' => 'nullable|string|max:2000',
        ]);

        try {
            app(AgentProductTransferService::class)->reject(
                $agent_product_transfer,
                $request->user(),
                $validated['admin_note'] ?? null
            );
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Rejected.',
            'data' => $this->detail($agent_product_transfer->fresh([
                'fromAgent', 'toAgent', 'decidedByUser', 'items',
            ])),
        ]);
    }

    private function summary(AgentProductTransfer $t): array
    {
        return [
            'id' => $t->id,
            'status' => $t->status,
            'created_at' => $t->created_at?->toIso8601String(),
            'from_agent' => $t->fromAgent ? ['id' => $t->fromAgent->id, 'name' => $t->fromAgent->name, 'email' => $t->fromAgent->email] : null,
            'to_agent' => $t->toAgent ? ['id' => $t->toAgent->id, 'name' => $t->toAgent->name, 'email' => $t->toAgent->email] : null,
            'items_count' => $t->items->count(),
        ];
    }

    private function detail(AgentProductTransfer $t): array
    {
        $base = $this->summary($t);
        $base['message'] = $t->message;
        $base['admin_note'] = $t->admin_note;
        $base['decided_at'] = $t->decided_at?->toIso8601String();
        $base['decided_by'] = $t->decidedByUser ? ['id' => $t->decidedByUser->id, 'name' => $t->decidedByUser->name] : null;
        $base['items'] = $t->items->map(function ($ti) {
            $i = $ti->productListItem;
            if (! $i) {
                return ['product_list_id' => $ti->product_list_id];
            }
            $bid = $i->effectiveBranchId();

            return [
                'product_list_id' => $i->id,
                'imei_number' => $i->imei_number,
                'model' => $i->model,
                'product' => $i->product ? [
                    'id' => $i->product->id,
                    'name' => $i->product->name,
                    'category' => $i->product->category?->name,
                ] : null,
                'stock' => $i->stock ? ['id' => $i->stock->id, 'name' => $i->stock->name] : null,
                'purchase' => $i->purchase ? [
                    'id' => $i->purchase->id,
                    'name' => $i->purchase->name,
                    'date' => $i->purchase->date,
                    'branch' => $i->purchase->branch?->name,
                ] : null,
                'effective_branch_id' => $bid,
                'effective_branch_name' => $i->branch?->name ?? $i->purchase?->branch?->name,
            ];
        })->values()->all();

        return $base;
    }
}
