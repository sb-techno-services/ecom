<?php
declare(strict_types=1);

use Admin\Database\{Busy, Connection, Write};

return [
    'competing orders for the final unit produce exactly one order' => static function (): void {
        $f = new Fixture();
        $product = $f->add('LAST', 1);
        same(['Insufficient stock for SKU LAST.', 'created'], Race::run($f, 'create', $product));
        same(0, $f->products->get($product)['stock']);
        $orders = $f->orders->all();
        same(1, count($orders));
        same([1], array_column($f->orders->lines($orders[0]['id']), 'quantity'));
    },
    'overlapping cancellations restore stock exactly once' => static function (): void {
        $f = new Fixture();
        $product = $f->add('LAST', 3);
        $order = $f->orders->create(Fixture::order([$product, 2]));
        $f->products->archive($product);
        same(['already cancelled', 'cancelled'], Race::run($f, 'cancel', $order));
        same(3, $f->products->get($product)['stock']);
        same(true, is_string($f->orders->get($order)['cancelled_at']));
        same([2], array_column($f->orders->lines($order), 'quantity'));
    },
    'expired lock wait gives a retry message without writing' => static function (): void {
        $f = new Fixture();
        $other = Connection::open($f->path, 10);
        $f->db->exec('BEGIN IMMEDIATE');
        try {
            $error = rejects(Busy::class, static fn () => Write::run($other, static function (): void {
                throw new RuntimeException('Busy transaction should never enter the operation.');
            }));
            contains('retry', $error->getMessage());
        } finally {
            $f->db->exec('ROLLBACK');
        }
        same([], $f->orders->all());
    },
];
