<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentOption extends Model
{
    protected $fillable = [
        'type',
        'name',
        'balance',
        'opening_balance',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'opening_balance' => 'decimal:2',
    ];

    public const TYPE_MOBILE = 'mobile';
    public const TYPE_BANK = 'bank';
    public const TYPE_CASH = 'cash';

    public static function types(): array
    {
        return [
            self::TYPE_MOBILE => 'Mobile',
            self::TYPE_BANK => 'Bank',
            self::TYPE_CASH => 'Cash',
        ];
    }

    public function pendingSales()
    {
        return $this->hasMany(PendingSale::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }
}
