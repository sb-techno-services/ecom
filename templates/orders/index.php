<p><a class="button" href="<?= e(url('order-add')) ?>">Create order</a></p>
<?php if (!$orders): ?>
    <p>No orders yet.</p>
<?php else: ?>
<div class="table-scroll">
    <table>
        <thead><tr><th scope="col">Order</th><th scope="col">Created</th><th scope="col">Customer</th><th scope="col">Total</th><th scope="col">Status</th></tr></thead>
        <tbody>
        <?php foreach ($orders as $order): ?>
            <tr>
                <th scope="row"><a href="<?= e(url('order', $order['id'])) ?>"><?= e(\Admin\Orders\OrderDisplay::number($order['id'])) ?></a></th>
                <td><?= e(utc($order['created_at'])) ?></td>
                <td><?= e($order['customer']) ?></td>
                <td><?= e(\Admin\Money::format($order['total_pence'])) ?></td>
                <td><span class="badge"><?= e(\Admin\Orders\OrderDisplay::status($order)) ?></span></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
