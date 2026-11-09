<?php
declare(strict_types=1);

use Admin\Catalog\Products;

return [
    'HTTP workflow creates products, edits and adjusts stock, builds and cancels an order' => static function (): void {
        HttpServer::run(static function (HttpClient $http, PDO $db): void {
            same(200, $http->request('/assets/admin.css')[0]);
            [, $form] = $http->request('/?page=product-add');
            $csrf = ['_csrf' => HttpClient::token($form)];
            $a = ['name' => 'Vase <special>', 'sku' => 'VASE', 'price' => '10.25', 'stock' => '6'];
            $b = ['name' => 'Oak stand', 'sku' => 'STAND', 'price' => '2.01', 'stock' => '5'];
            same(303, $http->request('/?page=product-add', $a + $csrf)[0]);
            same(303, $http->request('/?page=product-add', $b + $csrf)[0]);
            contains('Vase &lt;special&gt;', $http->request('/')[1]);

            $products = new Products($db);
            $id = $products->all()[1]['id'];
            $stand = $products->all()[0]['id'];
            same(303, $http->request("/?page=stock&id=$id", ['direction' => 'remove', 'units' => '1'] + $csrf)[0]);
            same(303, $http->request("/?page=product-edit&id=$id", ['price' => '11.25'] + $a + $csrf)[0]);
            same(5, $products->get($id)['stock']);
            contains('Recent manual adjustments', $http->request("/?page=stock&id=$id")[1]);

            $draft = Fixture::order([$id, 2]);
            [$status, $html] = $http->request('/?page=order-add', ['intent' => 'add'] + $draft + $csrf);
            same(200, $status);
            contains('Line 2', $html);
            [$status, $html] = $http->request('/?page=order-add', ['intent' => 'remove:1'] + Fixture::order([$id, 2], [$stand, 3]) + $csrf);
            same(200, $status);
            same(false, strpos($html, 'Line 2'));

            [$status, , $location] = $http->request('/?page=order-add', Fixture::order([$id, 2], [$stand, 3]) + $csrf);
            same(303, $status);
            same('/?page=order&id=1', $location);
            $detail = $http->request($location)[1];
            contains('ORD-000001', $detail);
            contains('£28.53', $detail);
            contains('Test Customer', $detail);
            contains('ORD-000001', $http->request('/?page=orders')[1]);
            same(3, $products->get($id)['stock']);

            contains('Confirm archive', $http->request("/?page=archive&id=$id")[1]);
            same(303, $http->request("/?page=archive&id=$id", $csrf)[0]);
            contains('Confirm cancellation', $http->request('/?page=cancel&id=1')[1]);
            same(303, $http->request('/?page=cancel&id=1', $csrf)[0]);
            same(303, $http->request('/?page=cancel&id=1', $csrf)[0]);
            contains('Cancelled', $http->request($location)[1]);
            same(5, $products->get($id)['stock']);
            same(5, $products->get($stand)['stock']);
        });
    },
];
