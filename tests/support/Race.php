<?php
declare(strict_types=1);

final class Race
{
    public static function run(Fixture $fixture, string $action, int $id): array
    {
        $children = [];
        $fixture->db->exec('BEGIN IMMEDIATE');
        try {
            for ($index = 0; $index < 2; ++$index) {
                $ready = $fixture->directory . "/ready-$index";
                $log = $fixture->directory . "/worker-$index.log";
                $process = proc_open(
                    [PHP_BINARY, __DIR__ . '/race-worker.php', $fixture->path, $ready, $action, (string) $id],
                    [1 => ['file', $log, 'w'], 2 => ['file', $log, 'a']], $pipes
                );
                if (!is_resource($process)) {
                    throw new RuntimeException('Cannot start concurrency worker.');
                }
                $children[] = [$process, $ready, $log];
            }
            $deadline = microtime(true) + 6;
            foreach ($children as [, $ready, $log]) {
                while (!is_file($ready)) {
                    if (microtime(true) >= $deadline) {
                        throw new RuntimeException('Worker did not observe the lock: ' . file_get_contents($log));
                    }
                    usleep(10000);
                    clearstatcache(true, $ready);
                }
            }
        } finally {
            $fixture->db->exec('ROLLBACK');
            $results = [];
            $codes = [];
            foreach ($children as [$process, , $log]) {
                $codes[] = proc_close($process);
                $results[] = trim(file_get_contents($log));
            }
        }
        if ($codes !== [0, 0]) {
            throw new RuntimeException('Concurrency workers failed: ' . implode(' | ', $results));
        }
        sort($results, SORT_STRING);
        return $results;
    }
}
