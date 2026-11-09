<?php
declare(strict_types=1);

namespace Admin\Http;

final class View
{
    public static function render(string $template, string $title, array $data = [], int $status = 200): void
    {
        http_response_code($status);
        $root = dirname(__DIR__, 2) . '/templates';
        extract($data, EXTR_SKIP);
        require $root . '/layout.php';
    }

    public static function redirect(string $page, ?int $id, string $message): void
    {
        $_SESSION['notice'] = $message;
        header('Location: ' . url($page, $id), true, 303);
    }
}
