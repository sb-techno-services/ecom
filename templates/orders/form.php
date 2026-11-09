<?php require $root . '/partials/form-error.php'; ?>
<?php if (!$products): ?>
    <p>There are no active products. <a href="<?= e(url('product-add')) ?>">Add a product</a> to create an order.</p>
<?php else: ?>
<form method="post" class="panel form">
    <?php require $root . '/partials/csrf.php'; ?>
    <label>Customer name <input name="customer" required maxlength="160" value="<?= e(is_string($post['customer'] ?? null) ? $post['customer'] : '') ?>"></label>
    <?php foreach ($rows as $index => $row): ?>
        <?php require $root . '/orders/line-input.php'; ?>
    <?php endforeach; ?>
    <p class="muted">The total uses current product prices when the order is saved.</p>
    <div class="actions">
        <button name="intent" value="create">Create order</button>
        <?php if (count($rows) < \Admin\Orders\OrderInput::MAX_LINES): ?>
            <button name="intent" value="add" class="secondary" formnovalidate>Add line</button>
        <?php endif; ?>
        <a href="<?= e(url('orders')) ?>">Back to orders</a>
    </div>
</form>
<?php endif; ?>
