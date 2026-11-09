<div class="table-scroll">
    <table>
        <thead><tr><th scope="col">Product at purchase</th><th scope="col">SKU</th><th scope="col">Quantity</th><th scope="col">Unit price</th><th scope="col">Line total</th></tr></thead>
        <tbody>
        <?php foreach ($lines as $item): ?>
            <tr>
                <th scope="row"><?= e($item['product_name']) ?></th>
                <td><?= e($item['sku']) ?></td>
                <td><?= e($item['quantity']) ?></td>
                <td><?= e(\Admin\Money::format($item['unit_pence'])) ?></td>
                <td><?= e(\Admin\Money::format($item['line_pence'])) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot><tr><th scope="row" colspan="4">Order total</th><td><?= e(\Admin\Money::format($order['total_pence'])) ?></td></tr></tfoot>
    </table>
</div>
