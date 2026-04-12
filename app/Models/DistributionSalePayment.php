<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DistributionSalePayment extends Model
{
    protected $fillable = [
        'distribution_sale_id',
        'payment_option_id',
        'amount',
        'paid_date',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_date' => 'date',
    ];

    public function distributionSale()
    {
        return $this->belongsTo(DistributionSale::class);
    }

    public function paymentOption()
    {
        return $this->belongsTo(PaymentOption::class);
    }
}
