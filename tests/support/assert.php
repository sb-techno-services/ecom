<?php
declare(strict_types=1);

function same(mixed $expected, mixed $actual): void
{
    if ($expected !== $actual) {
        throw new RuntimeException('Expected ' . var_export($expected, true) . ', got ' . var_export($actual, true));
    }
}

function contains(string $needle, string $actual): void
{
    if (strpos($actual, $needle) === false) {
        throw new RuntimeException("Response did not contain: $needle");
    }
}

function rejects(string $type, callable $operation): Throwable
{
    try {
        $operation();
    } catch (Throwable $exception) {
        if ($exception instanceof $type) {
            return $exception;
        }
        throw $exception;
    }
    throw new RuntimeException("Expected $type, but the operation succeeded.");
}
