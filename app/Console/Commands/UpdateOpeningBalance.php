<?php

namespace App\Console\Commands;

use App\Models\PaymentOption;
use Illuminate\Console\Command;

class UpdateOpeningBalance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payment-options:update-opening-balance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update opening balance for all payment options (runs daily at 6:00 PM)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating opening balance for all payment options...');
        
        $paymentOptions = PaymentOption::all();
        $updated = 0;
        
        foreach ($paymentOptions as $option) {
            $currentBalance = $option->balance ?? 0;
            $oldOpeningBalance = $option->opening_balance ?? 0;
            
            // Update opening balance to current balance
            $option->update([
                'opening_balance' => $currentBalance
            ]);
            
            $updated++;
            $this->line("  {$option->name}: Opening balance updated from " . number_format($oldOpeningBalance, 0) . " to " . number_format($currentBalance, 0) . " TZS");
        }
        
        $this->info("Done. Updated opening balance for {$updated} payment option(s).");
        
        return Command::SUCCESS;
    }
}
