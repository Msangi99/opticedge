<?php

namespace App\Support;

use App\Models\Purchase;
use Carbon\CarbonInterface;

class PurchaseInvoiceNumber
{
    /**
     * Full distributor name as a URL-safe slug (letters, digits, hyphens between words).
     */
    public static function slugifyDistributor(?string $distributorName): string
    {
        $s = trim((string) $distributorName);
        if ($s === '') {
            return 'UNKNOWN';
        }
        $s = preg_replace('/\s+/', '-', $s);
        $s = preg_replace('/[^a-zA-Z0-9\-]/', '', $s);
        $s = preg_replace('/-+/', '-', $s);
        $s = trim($s, '-');

        return $s !== '' ? $s : 'UNKNOWN';
    }

    /**
     * Base invoice: {DistributorSlug}-{Y-m-d}-{H-i} (time from "now" when generated).
     * Truncates slug so total length stays within $maxLength (DB column limit).
     */
    public static function baseName(?string $distributorName, string $dateYmd, ?CarbonInterface $at = null, int $maxLength = 255): string
    {
        $at = $at ?? now();
        $datePart = \Carbon\Carbon::parse($dateYmd)->format('Y-m-d');
        $timePart = $at->format('H-i');
        $suffix = '-' . $datePart . '-' . $timePart;
        $maxSlug = max(8, $maxLength - strlen($suffix) - 4);
        $slug = self::slugifyDistributor($distributorName);
        if (strlen($slug) > $maxSlug) {
            $slug = substr($slug, 0, $maxSlug);
            $slug = rtrim($slug, '-');
            if ($slug === '') {
                $slug = 'UNKNOWN';
            }
        }

        return $slug . $suffix;
    }

    /**
     * Unique invoice name; appends -2, -3, ... if base already exists (stays within DB string length).
     */
    public static function unique(?string $distributorName, string $dateYmd, ?CarbonInterface $at = null): string
    {
        $maxLen = 255;
        $base = self::baseName($distributorName, $dateYmd, $at);
        if (strlen($base) > $maxLen) {
            $base = rtrim(substr($base, 0, $maxLen), '-');
        }
        $name = $base;
        $n = 2;
        while (Purchase::where('name', $name)->exists()) {
            $suffix = '-' . $n;
            $trunc = substr($base, 0, max(1, $maxLen - strlen($suffix)));
            $name = $trunc . $suffix;
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
