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

    /**
     * Agent sale configuration:
     *  - regular_channels: all visible non-Watu channels (for the Sell tab picker)
     *  - watu_channel: admin-configured default Watu channel (auto-used in the Watu tab)
     */
    public function agentSaleConfig()
    {
        $allVisible = PaymentOption::visible()->orderBy('name')->get();

        $regularChannels = $allVisible
            ->filter(fn ($opt) => ! $opt->isWatuAgentCreditChannel())
            ->map(fn ($opt) => ['id' => $opt->id, 'name' => $opt->name, 'type' => $opt->type])
            ->values();

        // Admin-configured Watu default
        $watuIdRaw = Setting::query()->where('key', 'default_watu_channel_id')->value('value');
        $watuId = is_numeric($watuIdRaw) ? (int) $watuIdRaw : null;
        $watuChannel = $watuId ? $allVisible->firstWhere('id', $watuId) : null;

        // Fallback: first visible channel whose name contains "watu"
        if (! $watuChannel) {
            $watuChannel = $allVisible->first(fn ($opt) => $opt->isWatuAgentCreditChannel());
        }

        return response()->json([
            'data' => [
                'regular_channels' => $regularChannels,
                'watu_channel' => $watuChannel
                    ? ['id' => $watuChannel->id, 'name' => $watuChannel->name]
                    : null,
            ],
        ]);
    }
}
