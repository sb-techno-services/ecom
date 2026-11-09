<?php if ($order['cancelled_at']): ?>
    <p>This order is already cancelled.</p>
    <p><a href="<?= e(url('order', $order['id'])) ?>">Back to order</a></p>
<?php else: ?>
    <p>Cancel <strong><?= e(\Admin\Orders\OrderDisplay::number($order['id'])) ?></strong> and return its items to stock?</p>
    <form method="post" class="actions">
        <?php require $root . '/partials/csrf.php'; ?>
        <button class="danger">Confirm cancellation</button>
        <a href="<?= e(url('order', $order['id'])) ?>">Keep order open</a>
    </form>
<?php endif; ?>
