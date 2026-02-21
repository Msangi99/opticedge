<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        'activity',
        'amount',
        'cash_used',
        'date',
    ];

    public const CASH_SYSTEM = 'system';
    public const CASH_CASH = 'cash';

    public static function cashOptions(): array
    {
        return [
            self::CASH_SYSTEM => 'System',
            self::CASH_CASH => 'Cash',
        ];
    }
}
