<?php
declare(strict_types=1);

use Admin\Database\{Connection, Schema};

require dirname(__DIR__) . '/bootstrap.php';

try {
    $path = Connection::path();
    $directory = dirname($path);
    if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) {
        throw new RuntimeException('Cannot create the data directory.');
    }
    Schema::migrate(Connection::open($path));
    echo "Database ready at $path\n";
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage() . PHP_EOL);
    exit(1);
}
