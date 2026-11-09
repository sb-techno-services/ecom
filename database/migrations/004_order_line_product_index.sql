-- Used to find quantities that may need to be restored to a product.
CREATE INDEX order_lines_product ON order_lines(product_id);
