<?php
declare(strict_types=1);

spl_autoload_register(static function (string $class): void {
    if (strncmp($class, 'Admin\\', 6) === 0) {
        require __DIR__ . '/app/' . str_replace('\\', '/', substr($class, 6)) . '.php';
    }
});
require __DIR__ . '/app/Http/helpers.php';
