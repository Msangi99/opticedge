<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgentCredit;
use App\Models\CustomerNeed;
use App\Models\PendingSale;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class CustomerNeedsController extends Controller
{
    /**
     * Agent app sell flow: instant (pending / sell), credit (Watu), and catalog needs.
     */
    public function index(): View
    {
        $pendingEager = ['product.category', 'paymentOption'];
        if (Schema::hasColumn('pending_sales', 'seller_id')) {
            array_unshift($pendingEager, 'seller');
        }

        $pendingQuery = PendingSale::query()->with($pendingEager);

        if (Schema::hasColumn('pending_sales', 'seller_id')) {
            $pendingQuery->whereNotNull('seller_id');
        } else {
            // Older DBs without seller_id: agent instant sales still link product_list.pending_sale_id.
            $pendingQuery->whereExists(function ($sub) {
                $sub->selectRaw('1')
                    ->from('product_list')
                    ->whereColumn('product_list.pending_sale_id', 'pending_sales.id');
            });
        }

        $pendingAgentSales = $pendingQuery
            ->latest('date')
            ->latest('id')
            ->limit(200)
            ->get();

        $agentCredits = AgentCredit::query()
            ->with(['agent', 'product.category', 'productListItem', 'paymentOption'])
            ->latest('date')
            ->latest('id')
            ->limit(200)
            ->get();

        $customerNeeds = CustomerNeed::query()
            ->with(['agent', 'category', 'product'])
            ->latest('id')
            ->limit(200)
            ->get();

        return view('admin.customer-needs.index', compact(
            'pendingAgentSales',
            'agentCredits',
            'customerNeeds'
        ));
    }
}
