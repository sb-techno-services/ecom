<?php
declare(strict_types=1);

namespace Admin\Http;

final class Csrf
{
    public static function token(): string
    {
        return $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
    }

    public static function accepts(mixed $candidate): bool
    {
        return is_string($candidate) && isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $candidate);
    }
}
