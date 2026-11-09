<?php
declare(strict_types=1);

namespace Admin\Orders;

final class OrderDisplay
{
    public static function number(int $id): string
    {
        return sprintf('ORD-%06d', $id);
    }

    public static function status(array $order): string
    {
        return $order['cancelled_at'] === null ? 'Open' : 'Cancelled';
    }
}
