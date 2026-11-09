<?php
declare(strict_types=1);

namespace Admin\Orders;

use Admin\{Input, InputError};

final class OrderInput
{
    public const MAX_LINES = 50;
    public const MAX_QUANTITY = 1000000000;

    public static function quantities(mixed $lines): array
    {
        if (!is_array($lines) || !$lines || count($lines) > self::MAX_LINES) {
            throw new InputError('An order needs between 1 and 50 product lines.');
        }
        $quantities = [];
        foreach ($lines as $line) {
            if (!is_array($line)) {
                throw new InputError('Invalid order line.');
            }
            $id = Input::integer($line['product_id'] ?? null, 'Product', 1, PHP_INT_MAX);
            $quantity = Input::integer($line['quantity'] ?? null, 'Quantity', 1, self::MAX_QUANTITY);
            $quantities[$id] = ($quantities[$id] ?? 0) + $quantity;
            if ($quantities[$id] > self::MAX_QUANTITY) {
                throw new InputError('Combined quantity for a product exceeds 1,000,000,000 units.');
            }
        }
        return $quantities;
    }
}
