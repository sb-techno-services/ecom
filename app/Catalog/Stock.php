<?php
declare(strict_types=1);

namespace Admin\Catalog;

use Admin\{Input, InputError};
use Admin\Database\Write;
use PDO;

final class Stock
{
    public function __construct(private PDO $db, private Products $products) {}

    public function adjust(int $id, array $input): void
    {
        $units = Input::integer($input['units'] ?? null, 'Units', 1, 1000000000);
        $delta = match ($input['direction'] ?? null) {
            'add' => $units,
            'remove' => -$units,
            default => throw new InputError('Choose whether to add or remove units.'),
        };
        Write::run($this->db, function () use ($id, $delta): void {
            $product = $this->products->get($id);
            $stock = $product['stock'];
            // Retain enough integer capacity to restore all open orders.
            $reserved = $this->db->prepare('SELECT COALESCE(SUM(l.quantity), 0)
                FROM order_lines l JOIN orders o ON o.id = l.order_id
                WHERE l.product_id = ? AND o.cancelled_at IS NULL');
            $reserved->execute([$id]);
            if ($delta > 0 && $stock > PHP_INT_MAX - (int) $reserved->fetchColumn() - $delta) {
                throw new InputError('This adjustment would exceed the stock limit.');
            }
            if ($stock + $delta < 0) {
                throw new InputError('This adjustment would reduce stock below zero.');
            }
            $result = $stock + $delta;
            $this->db->prepare('UPDATE products SET stock = ? WHERE id = ?')->execute([$result, $id]);
            $this->db->prepare('INSERT INTO stock_adjustments (product_id, delta, resulting_stock) VALUES (?, ?, ?)')
                ->execute([$id, $delta, $result]);
        });
    }

    public function recent(int $id): array
    {
        $query = $this->db->prepare('SELECT * FROM stock_adjustments WHERE product_id = ? ORDER BY id DESC LIMIT 20');
        $query->execute([$id]);
        return $query->fetchAll();
    }
}
