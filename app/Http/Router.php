<?php
declare(strict_types=1);

namespace Admin\Http;

use Admin\Input;
use Admin\NotFound;

final class Router
{
    private const WRITABLE = ['product-add', 'product-edit', 'stock', 'archive', 'order-add', 'cancel'];

    public function __construct(private ProductPages $products, private OrderPages $orders) {}

    public function dispatch(string $method, array $query, array $body): void
    {
        $page = $query['page'] ?? 'products';
        if ($method !== 'GET' && ($method !== 'POST' || !in_array($page, self::WRITABLE, true))) {
            header('Allow: ' . (in_array($page, self::WRITABLE, true) ? 'GET, POST' : 'GET'));
            View::render('error', 'Method not allowed', ['error' => 'Use the form provided by the application.'], 405);
            return;
        }
        $post = $method === 'POST' ? $body : null;
        if ($post !== null && !Csrf::accepts($post['_csrf'] ?? null)) {
            View::render('error', 'Request rejected', ['error' => 'Invalid or missing form token. Reload the form and try again.'], 403);
            return;
        }
        $id = static fn (): int => Input::id($query['id'] ?? null);
        match ($page) {
            'products' => $this->products->index(),
            'product-add' => $this->products->form(null, $post),
            'product-edit' => $this->products->form($id(), $post),
            'stock' => $this->products->adjustment($id(), $post),
            'archive' => $this->products->archive($id(), $post !== null),
            'orders' => $this->orders->index(),
            'order' => $this->orders->detail($id()),
            'order-add' => $this->orders->form($post),
            'cancel' => $this->orders->cancel($id(), $post !== null),
            default => throw new NotFound('Page not found.'),
        };
    }
}
