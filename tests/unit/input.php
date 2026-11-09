<?php
declare(strict_types=1);

use Admin\{Input, InputError};

return [
    'integer input is bounded before casting' => static function (): void {
        same(PHP_INT_MAX, Input::id((string) PHP_INT_MAX));
        same(7, Input::integer('007', 'Quantity', 1, 9));
        foreach (['9223372036854775808', '1.0', '-1', '1e1', [], true, 0] as $value) {
            rejects(InputError::class, static fn () => Input::id($value));
        }
    },
    'text is trimmed and rejects invalid UTF-8 and blank required fields' => static function (): void {
        same('Café', Input::text(' Café ', 'Name', 20));
        same('', Input::text('', 'Description', 20, false));
        rejects(InputError::class, static fn () => Input::text(' ', 'Name', 20));
        rejects(InputError::class, static fn () => Input::text("\xff", 'Name', 20));
    },
];
