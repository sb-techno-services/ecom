<dl class="summary">
    <div><dt>Order number</dt><dd><?= e(\Admin\Orders\OrderDisplay::number($order['id'])) ?></dd></div>
    <div><dt>Created</dt><dd><?= e(utc($order['created_at'])) ?></dd></div>
    <div><dt>Customer</dt><dd><?= e($order['customer']) ?></dd></div>
    <div><dt>Status</dt><dd><?= e(\Admin\Orders\OrderDisplay::status($order)) ?></dd></div>
    <?php if ($order['cancelled_at']): ?>
        <div><dt>Cancelled</dt><dd><?= e(utc($order['cancelled_at'])) ?></dd></div>
    <?php endif; ?>
</dl>
<?php require $root . '/orders/lines-table.php'; ?>
<p class="actions">
    <a href="<?= e(url('orders')) ?>">Back to orders</a>
    <?php if (!$order['cancelled_at']): ?>
        <a class="button danger" href="<?= e(url('cancel', $order['id'])) ?>">Cancel order</a>
    <?php endif; ?>
</p>
