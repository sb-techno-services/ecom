<?php
declare(strict_types=1);

namespace Admin\Catalog;

use Admin\{Input, InputError, Money};

final class ProductData
{
    public static function details(array $input): array
    {
        $name = Input::text($input['name'] ?? '', 'Name', 160);
        $sku = Input::text($input['sku'] ?? '', 'SKU', 64);
        if (!preg_match('/\A[A-Za-z0-9][A-Za-z0-9._-]*\z/', $sku)) {
            throw new InputError('Use letters, digits, periods, hyphens or underscores for the SKU, starting with a letter or digit.');
        }
        return [
            $name, $sku,
            Input::text($input['description'] ?? '', 'Description', 4000, false),
            Money::parse($input['price'] ?? null),
        ];
    }
}
