<?php
declare(strict_types=1);

namespace Admin\Http;

use Admin\Catalog\{Products, Stock};
use Admin\InputError;

final class ProductPages
{
    public function __construct(private Products $products, private Stock $stock) {}

    public function index(): void
    {
        View::render('products/index', 'Products', ['products' => $this->products->all()]);
    }

    public function form(?int $id, ?array $post): void
    {
        $product = $id === null ? null : $this->products->get($id);
        $title = $product === null ? 'Add product' : 'Edit product';
        $error = null;
        if ($post !== null) {
            try {
                if ($id === null) {
                    $this->products->create($post);
                } else {
                    $this->products->edit($id, $post);
                }
                View::redirect('products', null, 'Product saved.');
                return;
            } catch (InputError $exception) {
                $error = $exception->getMessage();
            }
        }
        View::render('products/form', $title, compact('product', 'post', 'error'), $error ? 422 : 200);
    }

    public function adjustment(int $id, ?array $post): void
    {
        $product = $this->products->get($id);
        $error = null;
        if ($post !== null) {
            try {
                $this->stock->adjust($id, $post);
                View::redirect('stock', $id, 'Stock adjusted.');
                return;
            } catch (InputError $exception) {
                $error = $exception->getMessage();
            }
        }
        $history = $this->stock->recent($id);
        View::render('products/stock', 'Adjust stock', compact('product', 'post', 'error', 'history'), $error ? 422 : 200);
    }

    public function archive(int $id, bool $confirmed): void
    {
        $product = $this->products->get($id);
        if ($confirmed) {
            $this->products->archive($id);
            View::redirect('products', null, 'Product archived.');
            return;
        }
        View::render('products/archive', 'Archive product', compact('product'));
    }
}
