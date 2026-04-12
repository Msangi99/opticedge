<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerNeed;
use Illuminate\View\View;

class CustomerNeedsController extends Controller
{
    /**
     * Agent app → Sell → Needed: category + model requests.
     */
    public function index(): View
    {
        $customerNeeds = CustomerNeed::query()
            ->with(['agent', 'category', 'product'])
            ->latest('id')
            ->limit(500)
            ->get();

        return view('admin.customer-needs.index', compact('customerNeeds'));
    }
}
