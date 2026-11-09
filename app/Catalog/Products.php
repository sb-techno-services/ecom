<?php
declare(strict_types=1);

namespace Admin\Catalog;

use Admin\{Input, InputError, NotFound};
use Admin\Database\Write;
use PDO;
use PDOException;

final class Products
{
    public function __construct(private PDO $db) {}

    public function all(bool $activeOnly = false): array
    {
        $where = $activeOnly ? ' WHERE archived_at IS NULL' : '';
        return $this->db->query("SELECT * FROM products$where ORDER BY name COLLATE NOCASE, id")->fetchAll();
    }

    public function get(int $id): array
    {
        $query = $this->db->prepare('SELECT * FROM products WHERE id = ?');
        $query->execute([$id]);
        return $query->fetch() ?: throw new NotFound('Product not found.');
    }

    public function create(array $input): int
    {
        $details = ProductData::details($input);
        $details[] = Input::integer($input['stock'] ?? null, 'Opening stock', 0, 1000000000);
        return $this->save('INSERT INTO products (name, sku, description, price_pence, stock) VALUES (?, ?, ?, ?, ?)', $details);
    }

    public function edit(int $id, array $input): void
    {
        $details = ProductData::details($input);
        $details[] = $id;
        $this->save('UPDATE products SET name = ?, sku = ?, description = ?, price_pence = ? WHERE id = ?', $details);
    }

    public function archive(int $id): void
    {
        Write::run($this->db, function () use ($id): void {
            $this->get($id);
            $query = $this->db->prepare('UPDATE products SET archived_at = CURRENT_TIMESTAMP WHERE id = ? AND archived_at IS NULL');
            $query->execute([$id]);
        });
    }

    private function save(string $sql, array $parameters): int
    {
        try {
            return Write::run($this->db, function () use ($sql, $parameters): int {
                $query = $this->db->prepare($sql);
                $query->execute($parameters);
                if (!$query->rowCount()) {
                    throw new NotFound('Product not found.');
                }
                return (int) $this->db->lastInsertId();
            });
        } catch (PDOException $error) {
            if (strpos($error->getMessage(), 'UNIQUE constraint failed: products.sku') !== false) {
                throw new InputError('This SKU is already in use, including archived products.', 0, $error);
            }
            throw $error;
        }
    }
}
