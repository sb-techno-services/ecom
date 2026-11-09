<?php
declare(strict_types=1);

use Admin\Database\{Busy, Connection, Write};
use Admin\InputError;
use Admin\Orders\Orders;

require dirname(__DIR__, 2) . '/bootstrap.php';
[, $path, $ready, $action, $id] = $argv;

try {
    Write::run(Connection::open($path, 0), static fn () => null);
    throw new RuntimeException('Expected an existing writer to block this process.');
} catch (Busy $exception) {
    touch($ready);
}

try {
    $orders = new Orders(Connection::open($path, 10000));
    if ($action === 'create') {
        $orders->create(['customer' => 'Competing buyer', 'lines' => [['product_id' => $id, 'quantity' => 1]]]);
        echo 'created';
    } else {
        echo $orders->cancel((int) $id) ? 'cancelled' : 'already cancelled';
    }
} catch (InputError $exception) {
    echo $exception->getMessage();
}
