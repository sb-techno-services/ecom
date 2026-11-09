# Commerce Admin

A standalone local application for products, stock and manually entered orders. It uses PHP, PDO SQLite, HTML and CSS with no Composer packages or front-end build.

## Prerequisites

- 64-bit PHP 8.0 or later, with PDO, `pdo_sqlite` and sessions enabled.
- A writable project directory on a local filesystem for the SQLite database.
- For the complete test suite, PHP CLI must also allow `proc_open`, loopback sockets, HTTP streams (`allow_url_fopen`) and access to the system temporary directory.

Check your PHP installation with `php -v` and `php -m`. On Debian or Ubuntu the packaged prerequisites are `php-cli` and `php-sqlite3`.

## Start from a clean copy

Run these commands from the extracted `commerce-admin` directory.

```sh
php bin/setup.php
php bin/seed.php
php -S 127.0.0.1:8080 -t public
```

Open [http://127.0.0.1:8080](http://127.0.0.1:8080). Use Ctrl+C to stop the server. Setup can be repeated and applies outstanding migrations. Seeding is optional and preserves rows with matching SKUs. Sample data is fictitious.

The database is created at `var/admin.sqlite`, outside the `public/` document root. The server assumes a trusted local machine and operator. Anyone who can access the loopback app has full admin access. Keep the server bound to `127.0.0.1` and use it directly from that machine. This phase has no login, roles, remote hosting, storefront, payments or shipping integration.

## Use the app

1. In **Products**, add a product with a name, SKU, GBP price and opening stock. Edit its details from the list. Adjust stock separately using **Adjust stock**.
2. In **Orders**, select **Create order**. Enter a customer name and choose products and quantities. Use **Add line** and **Remove line** to change the draft. The order uses the prices in the database when it is saved.
3. Use **Archive** and confirm to remove a product from sale. Product snapshots remain visible in order history.
4. Open an order and select **Cancel order**, then confirm. The order remains in history and its stock is restored once.

## Run the tests

```sh
php tests/run.php
```

The suite uses temporary SQLite databases and a temporary local PHP server. It can run without setup or sample data. See [verification](docs/verification.md) for the checks performed, [data rules](docs/data-rules.md) for concurrency and validation details, and [reference review](docs/reference-review.md) for the legacy workflow findings.

## Project layout

```text
commerce-admin/
├── app/                 Catalog, orders, SQLite and HTTP handling
├── bin/                 Database setup and sample data commands
├── database/            Ordered migrations and sample product data
├── docs/                Reference review, data rules and verification
├── public/              HTTP entry point and stylesheet
├── templates/           Product, stock and order screens
├── tests/               Unit, integration and HTTP tests
├── bootstrap.php        Autoloading and shared view functions
└── README.md
```
