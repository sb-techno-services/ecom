<?php
$row = is_array($row) ? $row : [];
$selection = $row['product_id'] ?? '';
$quantity = $row['quantity'] ?? '1';
?>
<fieldset class="line">
    <legend>Line <?= e($index + 1) ?></legend>
    <label>Product
        <select name="lines[<?= e($index) ?>][product_id]" required>
            <option value="">Choose a product</option>
            <?php foreach ($products as $product): ?>
                <option value="<?= e($product['id']) ?>" <?= (string) $product['id'] === $selection ? 'selected' : '' ?>>
                    <?= e($product['name'] . ' (' . $product['sku'] . ') · ' . \Admin\Money::format($product['price_pence']) . ' · ' . $product['stock'] . ' in stock') ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Quantity <input name="lines[<?= e($index) ?>][quantity]" type="number" required min="1" max="1000000000" step="1" value="<?= e(is_string($quantity) ? $quantity : '') ?>"></label>
    <?php if (count($rows) > 1): ?>
        <button name="intent" value="remove:<?= e($index) ?>" class="secondary" formnovalidate>Remove line</button>
    <?php endif; ?>
</fieldset>
