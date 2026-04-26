<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentCredit extends Model
{
    protected $fillable = [
        'agent_id',
        'customer_name',
        'customer_phone',
        'kin_name',
        'kin_phone',
        'product_list_id',
        'product_id',
        'total_amount',
        'paid_amount',
        'commission_paid',
        'payment_status',
        'payment_option_id',
        'installment_count',
        'installment_amount',
        'installment_interval_days',
        'first_due_date',
        'installment_notes',
        'date',
        'paid_date',
    ];

    protected $casts = [
        'date' => 'date',
        'paid_date' => 'date',
        'first_due_date' => 'date',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'commission_paid' => 'decimal:2',
        'installment_amount' => 'decimal:2',
        'installment_interval_days' => 'integer',
    ];

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function productListItem()
    {
        return $this->belongsTo(ProductListItem::class, 'product_list_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function paymentOption()
    {
        return $this->belongsTo(PaymentOption::class);
    }

    public function payments()
    {
        return $this->hasMany(AgentCreditPayment::class)
            ->orderByDesc('paid_date')
            ->orderByDesc('id');
    }
}
