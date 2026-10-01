<?php

namespace App\Support;

class Rut
{
    public static function normalize(string $rut): string
    {
        $clean = strtoupper((string) preg_replace('/[^0-9Kk]/', '', $rut));

        if (strlen($clean) < 2) {
            return $clean;
        }

        return substr($clean, 0, -1).'-'.substr($clean, -1);
    }

    public static function isValid(string $rut): bool
    {
        $clean = strtoupper((string) preg_replace('/[^0-9Kk]/', '', $rut));

        if (! preg_match('/^[0-9]{7,8}[0-9K]$/', $clean)) {
            return false;
        }

        $body = substr($clean, 0, -1);
        $provided = substr($clean, -1);
        $sum = 0;
        $factor = 2;

        for ($index = strlen($body) - 1; $index >= 0; $index--) {
            $sum += ((int) $body[$index]) * $factor;
            $factor = $factor === 7 ? 2 : $factor + 1;
        }

        $result = 11 - ($sum % 11);
        $expected = match ($result) {
            11 => '0',
            10 => 'K',
            default => (string) $result,
        };

        return $provided === $expected;
    }
}
