<?php
$value = static function (string $key, string $fallback = '') use ($post): string {
    $field = $post[$key] ?? $fallback;
    return is_string($field) ? $field : '';
};
require $root . '/partials/form-error.php';
?>
<form method="post" class="panel form">
    <?php require $root . '/partials/csrf.php'; ?>
    <label>Name <input name="name" required maxlength="160" value="<?= e($value('name', $product['name'] ?? '')) ?>"></label>
    <label>SKU <input name="sku" required maxlength="64" value="<?= e($value('sku', $product['sku'] ?? '')) ?>"></label>
    <label>Description <textarea name="description" rows="4" maxlength="4000"><?= e($value('description', $product['description'] ?? '')) ?></textarea></label>
    <label>Price in GBP <input name="price" required inputmode="decimal" placeholder="19.95" value="<?= e($value('price', $product ? \Admin\Money::decimal($product['price_pence']) : '')) ?>"></label>
    <?php if (!$product): ?>
        <label>Opening stock <input name="stock" required type="number" min="0" max="1000000000" step="1" value="<?= e($value('stock', '0')) ?>"></label>
    <?php else: ?>
        <p>Current stock is <?= e($product['stock']) ?>. <a href="<?= e(url('stock', $product['id'])) ?>">Adjust stock separately</a>.</p>
    <?php endif; ?>
    <div class="actions"><button>Save product</button><a href="<?= e(url('products')) ?>">Back to products</a></div>
</form>
