<h2>Recent manual adjustments</h2>
<?php if (!$history): ?>
    <p>No manual adjustments recorded.</p>
<?php else: ?>
<div class="table-scroll">
    <table>
        <thead><tr><th scope="col">When</th><th scope="col">Change</th><th scope="col">Stock after adjustment</th></tr></thead>
        <tbody>
        <?php foreach ($history as $entry): ?>
            <tr><td><?= e(utc($entry['created_at'])) ?></td><td><?= e(sprintf('%+d', $entry['delta'])) ?></td><td><?= e($entry['resulting_stock']) ?></td></tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
