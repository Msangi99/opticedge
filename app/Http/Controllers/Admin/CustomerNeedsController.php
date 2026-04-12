<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgentCredit;
use App\Models\CustomerNeed;
use App\Models\PendingSale;
use Illuminate\View\View;

class CustomerNeedsController extends Controller
{
    /**
     * Agent app sell flow: instant (pending / sell), credit (Watu), and catalog needs.
     */
    public function index(): View
    {
        $pendingAgentSales = PendingSale::query()
            ->whereNotNull('seller_id')
            ->with(['seller', 'product.category', 'paymentOption'])
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
