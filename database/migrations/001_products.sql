CREATE TABLE products (
    id INTEGER PRIMARY KEY,
    name TEXT NOT NULL CHECK (length(name) > 0),
    sku TEXT NOT NULL COLLATE NOCASE UNIQUE,
    description TEXT NOT NULL DEFAULT '',
    price_pence INTEGER NOT NULL CHECK (typeof(price_pence) = 'integer' AND price_pence BETWEEN 0 AND 99999999),
    stock INTEGER NOT NULL CHECK (typeof(stock) = 'integer' AND stock >= 0),
    archived_at TEXT
);
