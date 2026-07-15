<?php

namespace App\Support;

/**
 * Formats numbers using the Indian numbering system (lakh/crore grouping:
 * last 3 digits together, then every 2 digits) instead of the international
 * 3-digit grouping, e.g. 1,00,00,000.00 instead of 10,000,000.00.
 */
class IndianNumber
{
    public static function format($value, int $decimals = 2): string
    {
        $number = number_format((float) $value, $decimals, '.', '');
        [$integer, $decimal] = array_pad(explode('.', $number), 2, str_repeat('0', $decimals));

        $negative = str_starts_with($integer, '-');
        $integer = ltrim($integer, '-');

        $lastThree = substr($integer, -3);
        $remaining = substr($integer, 0, -3);

        if ($remaining !== '') {
            $remaining = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $remaining);
            $lastThree = ',' . $lastThree;
        }

        $formatted = $remaining . $lastThree;

        return ($negative ? '-' : '') . $formatted . ($decimals > 0 ? '.' . $decimal : '');
    }
}
