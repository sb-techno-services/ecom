<?php
declare(strict_types=1);

function e(string|int|null $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $page, ?int $id = null): string
{
    return '/?' . http_build_query($id === null ? ['page' => $page] : ['page' => $page, 'id' => $id]);
}

function utc(string $date): string
{
    return $date . ' UTC';
}
