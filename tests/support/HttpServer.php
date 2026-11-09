<?php
declare(strict_types=1);

use Admin\Database\{Connection, Schema};

final class HttpServer
{
    public static function run(callable $test): void
    {
        $fixture = new Fixture();
        $project = $fixture->directory . '/app-copy';
        mkdir($project);
        $source = dirname(__DIR__, 2);
        foreach (['app', 'public', 'templates', 'database'] as $directory) {
            self::copyTree($source . '/' . $directory, $project . '/' . $directory);
        }
        copy($source . '/bootstrap.php', $project . '/bootstrap.php');
        mkdir($project . '/var', 0700);
        $db = Connection::open($project . '/var/admin.sqlite');
        Schema::migrate($db);

        $socket = stream_socket_server('tcp://127.0.0.1:0');
        if ($socket === false) {
            throw new RuntimeException('Cannot reserve a loopback port for HTTP tests.');
        }
        $address = stream_socket_get_name($socket, false);
        fclose($socket);
        $log = $fixture->directory . '/server.log';
        $process = proc_open(
            [PHP_BINARY, '-d', 'display_errors=0', '-d', 'session.save_path=' . $fixture->directory, '-S', $address, '-t', $project . '/public'],
            [1 => ['file', $log, 'w'], 2 => ['file', $log, 'a']], $pipes, $project
        );
        if (!is_resource($process)) {
            throw new RuntimeException('Cannot start the PHP test server.');
        }
        try {
            $deadline = microtime(true) + 4;
            do {
                $probe = @stream_socket_client('tcp://' . $address, $errno, $error, 0.1);
                if (is_resource($probe)) {
                    fclose($probe);
                    $test(new HttpClient('http://' . $address), $db);
                    return;
                }
                usleep(10000);
            } while (microtime(true) < $deadline && proc_get_status($process)['running']);
            throw new RuntimeException('PHP test server did not start: ' . file_get_contents($log));
        } finally {
            proc_terminate($process);
            proc_close($process);
        }
    }

    private static function copyTree(string $source, string $destination): void
    {
        mkdir($destination);
        foreach (new FilesystemIterator($source) as $entry) {
            $path = $destination . '/' . $entry->getFilename();
            if ($entry->isDir()) {
                self::copyTree($entry->getPathname(), $path);
            } else {
                copy($entry->getPathname(), $path);
            }
        }
    }
}
