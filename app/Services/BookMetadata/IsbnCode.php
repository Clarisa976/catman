<?php

namespace App\Services\BookMetadata;

use InvalidArgumentException;

class IsbnCode
{
    public static function normalize(string $value): string
    {
        return strtoupper(preg_replace('/[\s-]+/', '', trim($value)) ?? '');
    }

    public static function normalizeAndValidate(string $value): string
    {
        $code = self::normalize($value);

        if (self::isValidIsbn10($code) || self::isValidIsbn13($code) || self::isValidEan13($code)) {
            return $code;
        }

        throw new InvalidArgumentException('ISBN/EAN must be a valid ISBN-10, ISBN-13, or EAN-13 code.');
    }

    public static function isValidIsbn10(string $code): bool
    {
        if (! preg_match('/^\d{9}[\dX]$/', $code)) {
            return false;
        }

        $sum = 0;

        for ($i = 0; $i < 10; $i++) {
            $digit = $code[$i] === 'X' ? 10 : (int) $code[$i];
            $sum += $digit * (10 - $i);
        }

        return $sum % 11 === 0;
    }

    public static function isValidIsbn13(string $code): bool
    {
        return preg_match('/^97[89]\d{10}$/', $code) === 1 && self::hasValidEan13Checksum($code);
    }

    public static function isValidEan13(string $code): bool
    {
        return preg_match('/^\d{13}$/', $code) === 1 && self::hasValidEan13Checksum($code);
    }

    private static function hasValidEan13Checksum(string $code): bool
    {
        $sum = 0;

        for ($i = 0; $i < 12; $i++) {
            $sum += (int) $code[$i] * ($i % 2 === 0 ? 1 : 3);
        }

        $check = (10 - ($sum % 10)) % 10;

        return $check === (int) $code[12];
    }
}
