# Verification

Verified with 64-bit PHP CLI 8.3.6 and PDO SQLite.

| Command | Result |
| --- | --- |
| `find . -name '*.php' -print0 \| xargs -0 -n1 php -l` | All PHP files passed syntax checks. |
| `php bin/setup.php` | Created the database and applied all four migrations. A second run succeeded. |
| `php bin/seed.php` | Loaded four products. A second run added zero rows and preserved the existing products. |
| `php -S 127.0.0.1:8080 -t public` | Served the seeded product list over loopback. |
| `php tests/run.php` | 21 passed, 0 failed. |

The HTTP tests exercise real form routes with a session cookie and token. They cover adding and editing products, adjusting stock, adding and removing draft lines, creating and viewing an order, archive and cancellation confirmation, repeated cancellation, stale archived product submissions, CSRF rejection and the retry response under lock contention. Separate worker processes verify one order for the last unit and one restoration for overlapping cancellations. Each worker observes a competing writer lock before executing its request.

The screens were exercised through HTTP. A visual browser pass and testing on other PHP 8 minor versions were outside this verification run.
