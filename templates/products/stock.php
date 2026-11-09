<p><?= e($product['name']) ?> · <?= e($product['sku']) ?> · <strong><?= e($product['stock']) ?> units in stock</strong></p>
<?php require $root . '/partials/form-error.php'; ?>
<form method="post" class="panel form">
    <?php require $root . '/partials/csrf.php'; ?>
    <label>Adjustment
        <select name="direction">
            <option value="add">Add units</option>
            <option value="remove" <?= ($post['direction'] ?? null) === 'remove' ? 'selected' : '' ?>>Remove units</option>
        </select>
    </label>
    <label>Units <input name="units" required type="number" min="1" max="1000000000" step="1" value="<?= e(is_string($post['units'] ?? null) ? $post['units'] : '') ?>"></label>
    <div class="actions"><button>Apply adjustment</button><a href="<?= e(url('products')) ?>">Back to products</a></div>
</form>
<?php require $root . '/products/stock-history.php'; ?>
