<?php
declare(strict_types=1);

use Admin\Catalog\Products;
use Admin\Database\Connection;
use Admin\InputError;

require dirname(__DIR__) . '/bootstrap.php';

try {
    if (!is_file(Connection::path())) {
        throw new RuntimeException('Run php bin/setup.php first.');
    }
    $products = new Products(Connection::open(Connection::path()));
    $added = 0;
    foreach (require dirname(__DIR__) . '/database/seeds/products.php' as $sample) {
        try {
            $products->create($sample);
            ++$added;
        } catch (InputError $exception) {
            if (!$exception->getPrevious() instanceof PDOException) {
                throw $exception;
            }
        }
    }
    echo "Added $added sample products. Existing SKUs were kept.\n";
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage() . PHP_EOL);
    exit(1);
}
