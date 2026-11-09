<?php
declare(strict_types=1);

namespace Admin\Http;

use Admin\Catalog\Products;
use Admin\InputError;
use Admin\Orders\{OrderInput, Orders};

final class OrderPages
{
    public function __construct(private Orders $orders, private Products $products) {}

    public function index(): void
    {
        View::render('orders/index', 'Orders', ['orders' => $this->orders->all()]);
    }

    public function detail(int $id): void
    {
        $order = $this->orders->get($id);
        $lines = $this->orders->lines($id);
        View::render('orders/detail', 'Order details', compact('order', 'lines'));
    }

    public function form(?array $post): void
    {
        $error = null;
        $rows = $post['lines'] ?? [];
        $rows = is_array($rows) ? array_values(array_slice($rows, 0, OrderInput::MAX_LINES)) : [];
        $intent = $post['intent'] ?? 'create';
        if ($post !== null) {
            if ($intent === 'add') {
                if (count($rows) < OrderInput::MAX_LINES) {
                    $rows[] = [];
                }
            } elseif (is_string($intent) && preg_match('/\Aremove:([0-9]{1,2})\z/', $intent, $matches)) {
                unset($rows[(int) $matches[1]]);
            } else {
                try {
                    if ($intent !== 'create') {
                        throw new InputError('Invalid order form action.');
                    }
                    $id = $this->orders->create($post);
                    View::redirect('order', $id, 'Order created.');
                    return;
                } catch (InputError $exception) {
                    $error = $exception->getMessage();
                }
            }
        }
        $rows = array_values($rows ?: [[]]);
        $products = $this->products->all(true);
        View::render('orders/form', 'Create order', compact('products', 'post', 'error', 'rows'), $error ? 422 : 200);
    }

    public function cancel(int $id, bool $confirmed): void
    {
        if ($confirmed) {
            $message = $this->orders->cancel($id) ? 'Order cancelled and stock restored.' : 'Order was already cancelled.';
            View::redirect('order', $id, $message);
            return;
        }
        $order = $this->orders->get($id);
        View::render('orders/cancel', 'Cancel order', compact('order'));
    }
}
