CREATE TABLE stock_adjustments (
    id INTEGER PRIMARY KEY,
    product_id INTEGER NOT NULL REFERENCES products(id),
    delta INTEGER NOT NULL CHECK (typeof(delta) = 'integer' AND delta <> 0),
    resulting_stock INTEGER NOT NULL CHECK (typeof(resulting_stock) = 'integer' AND resulting_stock >= 0),
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX stock_adjustments_product ON stock_adjustments(product_id, id);
