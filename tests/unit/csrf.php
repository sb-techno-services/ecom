<?php
declare(strict_types=1);

use Admin\Http\Csrf;

return [
    'CSRF tokens are session scoped and accept only the original string' => static function (): void {
        $_SESSION = [];
        same(false, Csrf::accepts('fake'));
        $token = Csrf::token();
        same(64, strlen($token));
        same(true, Csrf::accepts($token));
        same(false, Csrf::accepts([$token]));
        $_SESSION = [];
        same(false, Csrf::accepts($token));
    },
];
