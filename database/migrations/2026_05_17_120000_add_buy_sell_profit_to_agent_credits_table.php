<?php

use App\Models\AgentCredit;
use App\Services\DistributionSaleService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('agent_credits')) {
            return;
        }

        Schema::table('agent_credits', function (Blueprint $table) {
            if (! Schema::hasColumn('agent_credits', 'purchase_price')) {
                $table->decimal('purchase_price', 15, 2)->nullable()->after('total_amount');
            }
            if (! Schema::hasColumn('agent_credits', 'selling_price')) {
                $table->decimal('selling_price', 15, 2)->nullable()->after('purchase_price');
            }
            if (! Schema::hasColumn('agent_credits', 'profit')) {
                $table->decimal('profit', 15, 2)->nullable()->after('selling_price');
            }
        });

        if (! Schema::hasColumn('agent_credits', 'purchase_price')) {
            return;
        }

        $service = app(DistributionSaleService::class);

        AgentCredit::query()
            ->orderBy('id')
            ->chunkById(100, function ($credits) use ($service) {
                foreach ($credits as $credit) {
                    if ($credit->selling_price !== null && $credit->purchase_price !== null && $credit->profit !== null) {
                        continue;
                    }

                    $sell = (float) ($credit->selling_price ?? $credit->total_amount ?? 0);
                    $buy = (float) ($credit->purchase_price ?? $service->getBuyPriceForProduct((int) $credit->product_id));

                    $credit->update([
                        'purchase_price' => $buy,
                        'selling_price' => $sell,
                        'profit' => $sell - $buy,
                    ]);
                }
            });
    }

    public function down(): void
    {
        if (! Schema::hasTable('agent_credits')) {
            return;
        }

        Schema::table('agent_credits', function (Blueprint $table) {
            if (Schema::hasColumn('agent_credits', 'profit')) {
                $table->dropColumn('profit');
            }
            if (Schema::hasColumn('agent_credits', 'selling_price')) {
                $table->dropColumn('selling_price');
            }
            if (Schema::hasColumn('agent_credits', 'purchase_price')) {
                $table->dropColumn('purchase_price');
            }
        });
    }
};
