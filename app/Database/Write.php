<?php
declare(strict_types=1);

namespace Admin\Database;

use PDO;
use PDOException;
use Throwable;

final class Write
{
    public static function run(PDO $db, callable $operation): mixed
    {
        $started = false;
        try {
            // Acquire the writer lock before reading stock or order status.
            $db->exec('BEGIN IMMEDIATE');
            $started = true;
            $result = $operation();
            $db->exec('COMMIT');
            return $result;
        } catch (Throwable $error) {
            if ($started) {
                $db->exec('ROLLBACK');
            }
            if ($error instanceof PDOException && self::isBusy($error)) {
                throw new Busy('The database is busy. Please retry in a moment.', 0, $error);
            }
            throw $error;
        }
    }

    public static function isBusy(PDOException $error): bool
    {
        return in_array(((int) ($error->errorInfo[1] ?? 0)) & 255, [5, 6], true);
    }
}
