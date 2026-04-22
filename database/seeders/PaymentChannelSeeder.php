<?php

namespace Database\Seeders;

use App\Models\PaymentOption;
use Illuminate\Database\Seeder;

class PaymentChannelSeeder extends Seeder
{
    /**
     * Seed required payment channels.
     */
    public function run(): void
    {
        PaymentOption::firstOrCreate(
            ['name' => 'I&M'],
            [
                'type' => PaymentOption::TYPE_BANK,
                'balance' => 0,
                'opening_balance' => 0,
                'is_hidden' => false,
            ]
        );
    }
}
