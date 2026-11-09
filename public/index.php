<?php
declare(strict_types=1);

use Admin\Catalog\{Products, Stock};
use Admin\Database\{Busy, Connection, Write};
use Admin\Http\{OrderPages, ProductPages, Router, View};
use Admin\Orders\Orders;
use Admin\{InputError, NotFound};

require dirname(__DIR__) . '/bootstrap.php';

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
header("Content-Security-Policy: default-src 'none'; style-src 'self'; form-action 'self'; frame-ancestors 'none'; base-uri 'none'");

ini_set('session.use_strict_mode', '1');
session_name('commerce_admin_session');
session_start(['cookie_httponly' => true, 'cookie_samesite' => 'Strict']);

try {
    if (!is_file(Connection::path())) {
        View::render('error', 'Setup required', ['error' => 'Run php bin/setup.php from the project directory.'], 503);
        return;
    }
    $db = Connection::open(Connection::path());
    $products = new Products($db);
    $router = new Router(new ProductPages($products, new Stock($db, $products)), new OrderPages(new Orders($db), $products));
    $router->dispatch($_SERVER['REQUEST_METHOD'], $_GET, $_POST);
} catch (InputError $exception) {
    View::render('error', 'Invalid request', ['error' => $exception->getMessage()], 422);
} catch (NotFound $exception) {
    View::render('error', 'Not found', ['error' => $exception->getMessage()], 404);
} catch (Throwable $exception) {
    if ($exception instanceof Busy || ($exception instanceof PDOException && Write::isBusy($exception))) {
        header('Retry-After: 2');
        View::render('error', 'Database busy', ['error' => 'The database is busy. Please retry in a moment.'], 503);
    } else {
        error_log((string) $exception);
        View::render('error', 'Application error', ['error' => 'The request could not be completed. Check the server log.'], 500);
    }
}
