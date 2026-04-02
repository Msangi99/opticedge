<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BranchTransferLog;
use App\Services\BranchTransferService;
use Illuminate\Http\Request;

class AdminBranchTransferApiController extends Controller
{
    public function items(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'nullable',
            'product_id' => 'nullable|exists:products,id',
        ]);

        $service = app(BranchTransferService::class);

        if ($request->boolean('unassigned')) {
            $items = $service->queryUnassignedItems(
                isset($validated['product_id']) ? (int) $validated['product_id'] : null
            )->get();
        } else {
            $request->validate([
                'branch_id' => 'required|exists:branches,id',
            ]);
            $items = $service->queryItemsForBranch(
                (int) $validated['branch_id'],
                isset($validated['product_id']) ? (int) $validated['product_id'] : null
            )->get();
        }

        return response()->json([
            'data' => $items->map(fn ($i) => [
                'id' => $i->id,
                'imei_number' => $i->imei_number,
                'model' => $i->model,
                'text' => $i->imei_number.($i->model ? ' – '.$i->model : '').
                    ($i->product ? ' ('.($i->product->category->name ?? '').' – '.$i->product->name.')' : ''),
            ])->values()->all(),
        ]);
    }

    public function store(Request $request)
    {
        $unassigned = $request->boolean('unassigned');

        $validated = $request->validate([
            'from_branch_id' => 'nullable|exists:branches,id',
            'to_branch_id' => 'required|exists:branches,id',
            'product_list_ids' => 'required|array|min:1',
            'product_list_ids.*' => 'distinct|integer|exists:product_list,id',
        ]);

        $fromBranchId = $unassigned ? null : (isset($validated['from_branch_id']) ? (int) $validated['from_branch_id'] : null);
        if (! $unassigned && $fromBranchId === null) {
            return response()->json(['message' => 'Select source branch or set unassigned to true.'], 422);
        }

        try {
            app(BranchTransferService::class)->transferItems(
                $validated['product_list_ids'],
                $fromBranchId,
                (int) $validated['to_branch_id'],
                $request->user()
            );
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['message' => 'Devices moved.'], 201);
    }

    public function logs(Request $request)
    {
        $page = BranchTransferLog::with([
            'productListItem.product.category',
            'fromBranch',
            'toBranch',
            'admin',
        ])->latest()->paginate($request->integer('per_page', 40));

        return response()->json([
            'data' => $page->getCollection()->map(fn ($log) => [
                'id' => $log->id,
                'created_at' => $log->created_at?->toIso8601String(),
                'imei_number' => $log->productListItem?->imei_number,
                'product_name' => $log->productListItem?->product?->name,
                'from_branch' => $log->fromBranch?->name,
                'to_branch' => $log->toBranch?->name,
                'admin' => $log->admin ? ['id' => $log->admin->id, 'name' => $log->admin->name] : null,
            ])->values()->all(),
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'per_page' => $page->perPage(),
                'total' => $page->total(),
            ],
        ]);
    }
}
