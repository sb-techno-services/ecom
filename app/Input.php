<?php
declare(strict_types=1);

namespace Admin;

final class Input
{
    public static function text(mixed $value, string $label, int $max, bool $required = true): string
    {
        if (!is_string($value)) {
            throw new InputError("$label must be text.");
        }
        $value = trim($value);
        if (($required && $value === '') || strlen($value) > $max ||
            strpos($value, "\0") !== false || preg_match('//u', $value) !== 1) {
            throw new InputError("$label must be valid text within $max bytes" . ($required ? ' and is required.' : '.'));
        }
        return $value;
    }

    public static function integer(mixed $value, string $label, int $min, int $max): int
    {
        if (is_int($value) || (is_string($value) && preg_match('/\A[0-9]{1,19}\z/', $value))) {
            $digits = ltrim((string) $value, '0');
            $maximum = (string) $max;
            $fits = strlen($digits) < strlen($maximum) ||
                (strlen($digits) === strlen($maximum) && strcmp($digits, $maximum) <= 0);
            if ($fits && (int) $value >= $min) {
                return (int) $value;
            }
        }
        throw new InputError("$label must be a whole number from $min to $max.");
    }

    public static function id(mixed $value): int
    {
        return self::integer($value, 'ID', 1, PHP_INT_MAX);
    }
}
