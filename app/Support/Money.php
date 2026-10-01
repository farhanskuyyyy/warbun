<?php

namespace App\Support;

use Illuminate\Validation\ValidationException;

final class Money
{
    public static function cents(mixed $value): int
    {
        $value = (string) $value;
        if (! preg_match('/^([0-9]{1,12})(?:\.([0-9]{1,2}))?$/D', $value, $m)) {
            throw ValidationException::withMessages(['amount' => __('Invalid monetary amount.')]);
        }

        return (int) $m[1] * 100 + (int) str_pad($m[2] ?? '', 2, '0');
    }

    public static function decimal(int $cents): string
    {
        if (abs($cents) > 99999999999999) {
            throw ValidationException::withMessages(['amount' => __('Invalid monetary amount.')]);
        }

        return ($cents < 0 ? '-' : '').intdiv(abs($cents), 100).'.'.str_pad((string) (abs($cents) % 100), 2, '0', STR_PAD_LEFT);
    }

    public static function proportion(int $value, int $numerator, int $denominator): int
    {
        // Decimal long division avoids float and multiplication overflow.
        if ($denominator < 1 || $numerator < 0 || $numerator > $denominator || $value < 0) {
            throw ValidationException::withMessages(['amount' => __('Invalid monetary amount.')]);
        }
        $result = 0;
        $remainder = 0;
        foreach (str_split((string) $value) as $digit) {
            $partial = $remainder * 10 + (int) $digit * $numerator;
            $result = $result * 10 + intdiv($partial, $denominator);
            $remainder = $partial % $denominator;
        }

        return $result;
    }

    public static function multiply(int $cents, int $quantity): int
    {
        if ($quantity < 1 || $cents > intdiv(99999999999999, $quantity)) {
            throw ValidationException::withMessages(['amount' => __('Invalid monetary amount.')]);
        }

        return $cents * $quantity;
    }

    public static function format(mixed $value): string
    {
        $negative = str_starts_with((string) $value, '-');
        $cents = self::cents(ltrim((string) $value, '-'));
        $en = app()->getLocale() === 'en';

        return ($negative ? '-' : '').'Rp '.number_format(intdiv($cents, 100), 0, $en ? '.' : ',', $en ? ',' : '.').($cents % 100 ? ($en ? '.' : ',').str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT) : '');
    }
}
