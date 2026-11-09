<?php
declare(strict_types=1);

use Admin\InputError;

return [
    'multi-item order uses current prices and deducts stock' => static function (): void {
        $f = new Fixture();
        $a = $f->add('A', 7);
        $b = $f->add('B', 5, '2.01');
        $input = Fixture::order([$a, 2], [$b, 3]);
        $input['total_pence'] = 1;
        $input['lines'][0]['unit_pence'] = 1;
        $id = $f->orders->create($input);
        same(2653, $f->orders->get($id)['total_pence']);
        same([2050, 603], array_column($f->orders->lines($id), 'line_pence'));
        same([5, 2], [$f->products->get($a)['stock'], $f->products->get($b)['stock']]);
    },
    'combined lines are checked against stock and saved as one line' => static function (): void {
        $f = new Fixture();
        $a = $f->add('A', 4);
        rejects(InputError::class, static fn () => $f->orders->create(Fixture::order([$a, 3], [$a, 2])));
        same(4, $f->products->get($a)['stock']);
        $id = $f->orders->create(Fixture::order([$a, 3], [$a, 1]));
        same([4], array_column($f->orders->lines($id), 'quantity'));
        same(0, $f->products->get($a)['stock']);
    },
    'failed later line rolls back prior deductions and preserves order history' => static function (): void {
        $f = new Fixture();
        $a = $f->add('A', 5);
        $b = $f->add('B', 1);
        $f->orders->create(Fixture::order([$a, 1]));
        $history = $f->orders->all();
        rejects(InputError::class, static fn () => $f->orders->create(Fixture::order([$a, 1], [$b, 2])));
        same($history, $f->orders->all());
        same(1, (int) $f->db->query('SELECT COUNT(*) FROM order_lines')->fetchColumn());
        same([4, 1], [$f->products->get($a)['stock'], $f->products->get($b)['stock']]);
    },
];
