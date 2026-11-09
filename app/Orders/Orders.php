<?php
declare(strict_types=1);

namespace Admin\Orders;

use Admin\{Input, InputError, NotFound};
use Admin\Database\Write;
use PDO;

final class Orders
{
    public function __construct(private PDO $db) {}

    public function create(array $input): int
    {
        $customer = Input::text($input['customer'] ?? '', 'Customer name', 160);
        $quantities = OrderInput::quantities($input['lines'] ?? null);
        return Write::run($this->db, function () use ($customer, $quantities): int {
            $lookup = $this->db->prepare('SELECT name, sku, price_pence FROM products WHERE id = ? AND archived_at IS NULL');
            $deduct = $this->db->prepare('UPDATE products SET stock = stock - :units
                WHERE id = :id AND archived_at IS NULL AND stock >= CAST(:units AS INTEGER)');
            $total = 0;
            $snapshots = [];
            foreach ($quantities as $id => $units) {
                $lookup->execute([$id]);
                $product = $lookup->fetch();
                $lookup->closeCursor();
                if (!$product) {
                    throw new InputError("Product #$id is unavailable.");
                }
                $deduct->execute(['id' => $id, 'units' => $units]);
                if (!$deduct->rowCount()) {
                    throw new InputError('Insufficient stock for SKU ' . $product['sku'] . '.');
                }
                $total += $units * $product['price_pence'];
                $snapshots[] = [$id, $product['name'], $product['sku'], $product['price_pence'], $units];
            }
            $this->db->prepare('INSERT INTO orders (customer, total_pence) VALUES (?, ?)')->execute([$customer, $total]);
            $orderId = (int) $this->db->lastInsertId();
            $line = $this->db->prepare('INSERT INTO order_lines
                (order_id, product_id, product_name, sku, unit_pence, quantity) VALUES (?, ?, ?, ?, ?, ?)');
            foreach ($snapshots as $snapshot) {
                $line->execute([$orderId, ...$snapshot]);
            }
            return $orderId;
        });
    }

    public function cancel(int $id): bool
    {
        return Write::run($this->db, function () use ($id): bool {
            $cancel = $this->db->prepare('UPDATE orders SET cancelled_at = CURRENT_TIMESTAMP WHERE id = ? AND cancelled_at IS NULL');
            $cancel->execute([$id]);
            if (!$cancel->rowCount()) {
                $this->get($id);
                return false;
            }
            $restore = $this->db->prepare('UPDATE products SET stock = stock + ? WHERE id = ?');
            foreach ($this->lines($id) as $line) {
                $restore->execute([$line['quantity'], $line['product_id']]);
            }
            return true;
        });
    }

    public function all(): array
    {
        return $this->db->query('SELECT * FROM orders ORDER BY id DESC')->fetchAll();
    }

    public function get(int $id): array
    {
        $query = $this->db->prepare('SELECT * FROM orders WHERE id = ?');
        $query->execute([$id]);
        return $query->fetch() ?: throw new NotFound('Order not found.');
    }

    public function lines(int $id): array
    {
        $query = $this->db->prepare('SELECT *, unit_pence * quantity AS line_pence FROM order_lines WHERE order_id = ? ORDER BY product_id');
        $query->execute([$id]);
        return $query->fetchAll();
    }
}
