<?php

namespace App\Support;

use App\Models\Purchase;
use Carbon\CarbonInterface;

class PurchaseInvoiceNumber
{
    /**
     * 4-character alphanumeric prefix from distributor name (padded with X).
     */
    public static function prefixFromDistributor(?string $distributorName): string
    {
        $s = preg_replace('/[^a-zA-Z0-9]/', '', (string) $distributorName);
        $s = strtoupper(substr($s, 0, 4));

        return str_pad($s, 4, 'X');
    }

    /**
     * Base invoice: PREFIX-YYYY-MM-DD (no uniqueness suffix).
     */
    public static function baseName(?string $distributorName, string $dateYmd): string
    {
        return self::prefixFromDistributor($distributorName) . '-' . $dateYmd;
    }

    /**
     * Unique invoice name; appends -2, -3, ... if base already exists.
     */
    public static function unique(?string $distributorName, string $dateYmd): string
    {
        $base = self::baseName($distributorName, $dateYmd);
        $name = $base;
        $n = 2;
        while (Purchase::where('name', $name)->exists()) {
            $name = $base . '-' . $n;
            $n++;
        }

        return $name;
    }

    public static function dateString(CarbonInterface|string $date): string
    {
        if ($date instanceof CarbonInterface) {
            return $date->format('Y-m-d');
        }

        return \Carbon\Carbon::parse($date)->format('Y-m-d');
    }
}
