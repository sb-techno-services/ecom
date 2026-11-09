<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?> | Commerce Admin</title>
    <link rel="stylesheet" href="/assets/admin.css">
</head>
<body>
    <?php require $root . '/partials/nav.php'; ?>
    <main id="main">
        <?php require $root . '/partials/notice.php'; ?>
        <h1><?= e($title) ?></h1>
        <?php require $root . '/' . $template . '.php'; ?>
    </main>
</body>
</html>
