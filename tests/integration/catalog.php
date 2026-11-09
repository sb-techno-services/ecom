<?php
declare(strict_types=1);

use Admin\InputError;

return [
    'product creation, editing and stale stock protection' => static function (): void {
        $f = new Fixture();
        $id = $f->add('A-1', 3);
        $f->stock->adjust($id, ['direction' => 'add', 'units' => 5]);
        $f->products->edit($id, ['name' => 'Updated', 'sku' => 'NEW', 'description' => 'Details', 'price' => '4.25', 'stock' => 3]);
        $product = $f->products->get($id);
        same(['Updated', 'NEW', 'Details', 425, 8],
            [$product['name'], $product['sku'], $product['description'], $product['price_pence'], $product['stock']]);
        rejects(InputError::class, static fn () => $f->add('BAD-STOCK', -1));
    },
    'SKU uniqueness is case-insensitive and includes archived rows' => static function (): void {
        $f = new Fixture();
        $id = $f->add('A-1', 1);
        $f->products->archive($id);
        rejects(InputError::class, static fn () => $f->add('a-1', 2));
        rejects(PDOException::class, static fn () => $f->db->exec("INSERT INTO products (name, sku, price_pence, stock) VALUES ('Other', 'A-1', 20, 0)"));
        same([], $f->products->all(true));
        same(1, count($f->products->all()));
    },
    'manual stock adjustment records valid changes and rejects negative stock' => static function (): void {
        $f = new Fixture();
        $id = $f->add('A', 4);
        $f->stock->adjust($id, ['direction' => 'add', 'units' => '2']);
        $f->stock->adjust($id, ['direction' => 'remove', 'units' => '6']);
        rejects(InputError::class, static fn () => $f->stock->adjust($id, ['direction' => 'remove', 'units' => '1']));
        same(0, $f->products->get($id)['stock']);
        same([-6, 2], array_column($f->stock->recent($id), 'delta'));
    },
];
