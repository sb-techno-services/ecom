CREATE TABLE orders (
    id INTEGER PRIMARY KEY,
    customer TEXT NOT NULL CHECK (length(customer) > 0),
    total_pence INTEGER NOT NULL CHECK (typeof(total_pence) = 'integer' AND total_pence >= 0),
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    cancelled_at TEXT
);

CREATE TABLE order_lines (
    order_id INTEGER NOT NULL REFERENCES orders(id),
    product_id INTEGER NOT NULL REFERENCES products(id),
    product_name TEXT NOT NULL,
    sku TEXT NOT NULL,
    unit_pence INTEGER NOT NULL CHECK (typeof(unit_pence) = 'integer' AND unit_pence BETWEEN 0 AND 99999999),
    quantity INTEGER NOT NULL CHECK (typeof(quantity) = 'integer' AND quantity BETWEEN 1 AND 1000000000),
    PRIMARY KEY (order_id, product_id)
);
