<?php if ($product['archived_at']): ?>
    <p>This product is already archived.</p>
    <p><a href="<?= e(url('products')) ?>">Back to products</a></p>
<?php else: ?>
    <p>Archive <strong><?= e($product['name']) ?></strong> (<?= e($product['sku']) ?>)? It will be unavailable for new orders. Existing order details will be preserved.</p>
    <form method="post" class="actions">
        <?php require $root . '/partials/csrf.php'; ?>
        <button class="danger">Confirm archive</button>
        <a href="<?= e(url('products')) ?>">Keep product active</a>
    </form>
<?php endif; ?>
