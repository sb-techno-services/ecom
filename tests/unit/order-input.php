<?php
declare(strict_types=1);

use Admin\InputError;
use Admin\Orders\OrderInput;

return [
    'line normalisation merges duplicate IDs and validates quantities' => static function (): void {
        $line = ['product_id' => '8', 'quantity' => '3'];
        same([8 => 6], OrderInput::quantities([$line, $line]));
        rejects(InputError::class, static fn () => OrderInput::quantities([]));
        rejects(InputError::class, static fn () => OrderInput::quantities([['product_id' => 8, 'quantity' => 0]]));
        rejects(InputError::class, static fn () => OrderInput::quantities(array_fill(0, 51, $line)));
        $large = ['product_id' => 8, 'quantity' => 1000000000];
        rejects(InputError::class, static fn () => OrderInput::quantities([$large, $large]));
    },
];
