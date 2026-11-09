<?php
declare(strict_types=1);

namespace Admin\Database;

use PDO;
use RuntimeException;

final class Connection
{
    public static function path(): string
    {
        return dirname(__DIR__, 2) . '/var/admin.sqlite';
    }

    public static function open(string $path, int $timeoutMs = 2000): PDO
    {
        if (PHP_INT_SIZE < 8 || !in_array('sqlite', PDO::getAvailableDrivers(), true)) {
            throw new RuntimeException('Use 64-bit PHP with pdo_sqlite enabled.');
        }
        $db = new PDO('sqlite:' . $path, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $db->exec('PRAGMA foreign_keys = ON');
        $db->exec('PRAGMA busy_timeout = ' . $timeoutMs);
        return $db;
    }
}
