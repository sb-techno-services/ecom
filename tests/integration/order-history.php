<?php
declare(strict_types=1);

use Admin\InputError;

return [
    'historical lines survive rename, SKU change, price change and archiving' => static function (): void {
        $f = new Fixture();
        $a = $f->add('A', 3, '2.01');
        $id = $f->orders->create(Fixture::order([$a, 2]));
        $f->products->edit($a, ['name' => 'New name', 'sku' => 'NEW', 'price' => '19.99']);
        $f->products->archive($a);
        $line = $f->orders->lines($id)[0];
        same(['Product A', 'A', 201, 402], [$line['product_name'], $line['sku'], $line['unit_pence'], $line['line_pence']]);
        same(402, $f->orders->get($id)['total_pence']);
        rejects(InputError::class, static fn () => $f->orders->create(Fixture::order([$a, 1])));
    },
    'cancelling restores each item including archived stock exactly once' => static function (): void {
        $f = new Fixture();
        $a = $f->add('A', 3);
        $b = $f->add('B', 5);
        $id = $f->orders->create(Fixture::order([$a, 2], [$b, 3]));
        $f->products->archive($a);
        same(true, $f->orders->cancel($id));
        $order = $f->orders->get($id);
        same(true, is_string($order['cancelled_at']));
        same(false, $f->orders->cancel($id));
        same($order, $f->orders->get($id));
        same([3, 5], [$f->products->get($a)['stock'], $f->products->get($b)['stock']]);
        same(2, count($f->orders->lines($id)));
    },
];
