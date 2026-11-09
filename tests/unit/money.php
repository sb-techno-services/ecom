<?php
declare(strict_types=1);

use Admin\{InputError, Money};

return [
    'pence parsing and formatting are exact' => static function (): void {
        same(109, Money::parse('1.09'));
        same(110, Money::parse('1.1'));
        same(0, Money::parse('0'));
        same('£92233720368547758.07', Money::format(PHP_INT_MAX));
        foreach (['1.001', '-1', '1e2', '1000000', [], '£1', 1.2] as $price) {
            rejects(InputError::class, static fn () => Money::parse($price));
        }
    },
];
