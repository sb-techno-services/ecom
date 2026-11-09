<?php
declare(strict_types=1);

namespace Admin\Database;

use PDO;
use RuntimeException;

final class Schema
{
    public static function migrate(PDO $db): void
    {
        $db->exec('PRAGMA journal_mode = WAL');
        Write::run($db, static function () use ($db): void {
            $db->exec('CREATE TABLE IF NOT EXISTS migrations (name TEXT PRIMARY KEY)');
            $files = glob(dirname(__DIR__, 2) . '/database/migrations/*.sql');
            $lookup = $db->prepare('SELECT 1 FROM migrations WHERE name = ?');
            $record = $db->prepare('INSERT INTO migrations (name) VALUES (?)');
            foreach ($files as $file) {
                $name = basename($file);
                $lookup->execute([$name]);
                $applied = $lookup->fetchColumn();
                $lookup->closeCursor();
                if (!$applied) {
                    $sql = file_get_contents($file);
                    if ($sql === false) {
                        throw new RuntimeException("Cannot read migration $name.");
                    }
                    $db->exec($sql);
                    $record->execute([$name]);
                }
            }
        });
    }
}
