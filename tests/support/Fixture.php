<?php
declare(strict_types=1);

use Admin\Catalog\{Products, Stock};
use Admin\Database\{Connection, Schema};
use Admin\Orders\Orders;

final class Fixture
{
    public string $directory;
    public string $path;
    public PDO $db;
    public Products $products;
    public Stock $stock;
    public Orders $orders;

    public function __construct()
    {
        $this->directory = sys_get_temp_dir() . '/commerce-test-' . bin2hex(random_bytes(8));
        if (!mkdir($this->directory, 0700)) {
            throw new RuntimeException('Cannot create test workspace.');
        }
        $directory = $this->directory;
        register_shutdown_function(static fn () => self::removeTree($directory));
        $this->path = $directory . '/fixture.sqlite';
        $this->db = Connection::open($this->path);
        Schema::migrate($this->db);
        $this->products = new Products($this->db);
        $this->stock = new Stock($this->db, $this->products);
        $this->orders = new Orders($this->db);
    }

    public function add(string $sku, int $stock, string $price = '10.25'): int
    {
        return $this->products->create(['name' => "Product $sku", 'sku' => $sku, 'stock' => $stock, 'price' => $price]);
    }

    public static function order(array ...$lines): array
    {
        return ['customer' => 'Test Customer', 'lines' => array_map(
            static fn (array $line): array => ['product_id' => $line[0], 'quantity' => $line[1]], $lines
        )];
    }

    public static function removeTree(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }
        foreach (new FilesystemIterator($directory) as $file) {
            if ($file->isDir() && !$file->isLink()) {
                self::removeTree($file->getPathname());
            } else {
                unlink($file->getPathname());
            }
        }
        rmdir($directory);
    }
}
