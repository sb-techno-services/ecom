<?php if (isset($_SESSION['notice'])): ?>
    <p class="notice" role="status"><?= e($_SESSION['notice']) ?></p>
    <?php unset($_SESSION['notice']); ?>
<?php endif; ?>
