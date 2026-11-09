<p><a class="button" href="<?= e(url('product-add')) ?>">Add product</a></p>
<?php if (!$products): ?>
    <p>No products yet. Add a product to start managing stock.</p>
<?php else: ?>
<div class="table-scroll">
    <table>
        <thead><tr><th scope="col">Product</th><th scope="col">SKU</th><th scope="col">Price</th><th scope="col">Stock</th><th scope="col">Status</th><th scope="col">Actions</th></tr></thead>
        <tbody>
        <?php foreach ($products as $product): ?>
            <tr>
                <th scope="row"><?= e($product['name']) ?></th>
                <td><?= e($product['sku']) ?></td>
                <td><?= e(\Admin\Money::format($product['price_pence'])) ?></td>
                <td><?= e($product['stock']) ?></td>
                <td><span class="badge"><?= $product['archived_at'] ? 'Archived' : 'Active' ?></span></td>
                <td class="actions">
                    <a href="<?= e(url('product-edit', $product['id'])) ?>">Edit</a>
                    <a href="<?= e(url('stock', $product['id'])) ?>">Adjust stock</a>
                    <?php if (!$product['archived_at']): ?>
                        <a href="<?= e(url('archive', $product['id'])) ?>">Archive</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
