<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentOption;
use App\Models\Setting;

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
        $defaultIdRaw = Setting::query()
            ->where('key', 'default_agent_sale_channel_id')
            ->value('value');
        $defaultId = is_numeric($defaultIdRaw) ? (int) $defaultIdRaw : null;

        $query = PaymentOption::visible()->orderBy('name');
        if ($defaultId !== null) {
            $query->where('id', $defaultId);
        }

        $options = $query->get()->map(function ($opt) {
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
