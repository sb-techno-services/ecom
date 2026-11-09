<?php
declare(strict_types=1);

use Admin\Database\{Connection, Schema};

return [
    'migrations rerun and new connections enforce foreign keys' => static function (): void {
        $f = new Fixture();
        $other = Connection::open($f->path);
        Schema::migrate($other);
        same(4, (int) $other->query('SELECT count(*) FROM migrations')->fetchColumn());
        rejects(PDOException::class, static fn () => $other->exec(
            'INSERT INTO stock_adjustments (product_id, delta, resulting_stock) VALUES (999, 1, 1)'
        ));
    },
];
