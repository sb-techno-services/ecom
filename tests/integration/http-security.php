<?php
declare(strict_types=1);

use Admin\Catalog\Products;
use Admin\Orders\Orders;

return [
    'HTTP rejects missing or invalid CSRF on every POST action' => static function (): void {
        HttpServer::run(static function (HttpClient $http, PDO $db): void {
            $products = new Products($db);
            $orders = new Orders($db);
            $product = $products->create(['name' => 'A', 'sku' => 'A', 'price' => '1', 'stock' => 2]);
            $order = $orders->create(Fixture::order([$product, 1]));
            $http->request('/?page=product-add');
            foreach (['product-add', "product-edit&id=$product", "stock&id=$product", "archive&id=$product", 'order-add', "cancel&id=$order"] as $route) {
                foreach ([[], ['_csrf' => 'bogus'], ['_csrf' => ['bogus']]] as $body) {
                    [$status, $html] = $http->request('/?page=' . $route, $body);
                    same(403, $status);
                    contains('Invalid or missing form token', $html);
                }
            }
            same(405, $http->request("/?page=cancel&id=$order", null, 'PUT')[0]);
            same(200, $http->request("/?page=cancel&id=$order")[0]);
            same(200, $http->request("/?page=archive&id=$product")[0]);
            same(null, $orders->get($order)['cancelled_at']);
            same(null, $products->get($product)['archived_at']);
            same(1, $products->get($product)['stock']);
            same(404, $http->request('/var/admin.sqlite')[0]);
        });
    },
    'HTTP rejects an archived product submitted from a stale form' => static function (): void {
        HttpServer::run(static function (HttpClient $http, PDO $db): void {
            $products = new Products($db);
            $id = $products->create(['name' => 'Soon archived', 'sku' => 'OLD', 'price' => '2.5', 'stock' => 5]);
            [, $form] = $http->request('/?page=order-add');
            contains('Soon archived', $form);
            $token = HttpClient::token($form);
            same(303, $http->request("/?page=archive&id=$id", ['_csrf' => $token])[0]);
            [, $currentForm] = $http->request('/?page=order-add');
            same(false, strpos($currentForm, 'Soon archived'));
            [$status, $html] = $http->request('/?page=order-add', Fixture::order([$id, 1]) + ['_csrf' => $token]);
            same(422, $status);
            contains('unavailable', $html);
            same([], (new Orders($db))->all());
            same(5, $products->get($id)['stock']);
        });
    },
    'HTTP gives a retry response when a write lock persists' => static function (): void {
        HttpServer::run(static function (HttpClient $http, PDO $db): void {
            [, $form] = $http->request('/?page=product-add');
            $product = ['name' => 'Busy test', 'sku' => 'BUSY', 'price' => '1', 'stock' => 0];
            $db->exec('BEGIN IMMEDIATE');
            try {
                [$status, $body] = $http->request('/?page=product-add', $product + ['_csrf' => HttpClient::token($form)]);
                same(503, $status);
                contains('Please retry in a moment.', $body);
            } finally {
                $db->exec('ROLLBACK');
            }
            same([], (new Products($db))->all());
        });
    },
];
