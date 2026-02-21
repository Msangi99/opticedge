<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Selcompay extends Model
{
    protected $fillable = [
        'transid',
        'order_id',
        'phone_number',
        'amount',
        'payment_status',
        'local_order_id',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'local_order_id');
    }
}
