<?php
declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';
require __DIR__ . '/support/assert.php';
require __DIR__ . '/support/Fixture.php';
require __DIR__ . '/support/Race.php';
require __DIR__ . '/support/HttpClient.php';
require __DIR__ . '/support/HttpServer.php';

$files = array_merge(glob(__DIR__ . '/unit/*.php'), glob(__DIR__ . '/integration/*.php'));
$passed = 0;
$failed = 0;
foreach ($files as $file) {
    foreach (require $file as $name => $test) {
        try {
            $test();
            echo "PASS $name\n";
            ++$passed;
        } catch (Throwable $exception) {
            fwrite(STDERR, "FAIL $name\n$exception\n");
            ++$failed;
        }
    }
}
echo "$passed passed, $failed failed\n";
exit($failed === 0 ? 0 : 1);
