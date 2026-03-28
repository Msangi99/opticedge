<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentOption;

class PaymentOptionController extends Controller
{
    /**
     * List payment options (channels) for admin. Includes hidden for full admin view.
     */
    public function index()
    {
        $options = PaymentOption::orderBy('name')->get()->map(function ($opt) {
            return [
                'id' => $opt->id,
                'name' => $opt->name,
                'type' => $opt->type,
                'balance' => (float) $opt->balance,
                'opening_balance' => (float) $opt->opening_balance,
                'is_hidden' => (bool) $opt->is_hidden,
            ];
        });

        return response()->json(['data' => $options]);
    }

    /**
     * Visible channels only (for agents: down payments on credit sales).
     */
    public function indexVisible()
    {
        $options = PaymentOption::visible()->orderBy('name')->get()->map(function ($opt) {
            return [
                'id' => $opt->id,
                'name' => $opt->name,
                'type' => $opt->type,
                'balance' => (float) $opt->balance,
            ];
        });

        return response()->json(['data' => $options]);
    }
}
