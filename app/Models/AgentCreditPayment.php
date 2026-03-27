<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentCreditPayment extends Model
{
    protected $fillable = [
        'agent_credit_id',
        'payment_option_id',
        'amount',
        'paid_date',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_date' => 'date',
    ];

    public function agentCredit()
    {
        return $this->belongsTo(AgentCredit::class);
    }

    public function paymentOption()
    {
        return $this->belongsTo(PaymentOption::class);
    }
}
