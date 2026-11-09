<?php
declare(strict_types=1);

namespace Admin;

final class Money
{
    public static function parse(mixed $value): int
    {
        if (!is_string($value) || !preg_match('/\A([0-9]{1,6})(?:\.([0-9]{1,2}))?\z/', trim($value), $parts)) {
            throw new InputError('Enter a GBP price from 0 to 999999.99 with up to two decimal places.');
        }
        return (int) $parts[1] * 100 + (int) str_pad($parts[2] ?? '', 2, '0');
    }

    public static function decimal(int $pence): string
    {
        return sprintf('%d.%02d', intdiv($pence, 100), $pence % 100);
    }

    public static function format(int $pence): string
    {
        return '£' . self::decimal($pence);
    }
}
