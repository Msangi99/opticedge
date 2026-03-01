-- Edit table product_list (MySQL/MariaDB).
-- Run each statement. If you get "duplicate column name", skip that ALTER (column already exists).

-- 1) purchase_id — IMEISHIA (usirunde hii ikiwa tayari una purchase_id)
-- ALTER TABLE product_list
--   ADD COLUMN purchase_id BIGINT UNSIGNED NULL DEFAULT NULL AFTER stock_id,
--   ADD CONSTRAINT product_list_purchase_id_foreign
--     FOREIGN KEY (purchase_id) REFERENCES purchases(id) ON DELETE SET NULL;

-- 2) sold_at — IMEISHIA (usirunde ikiwa tayari una sold_at)
-- ALTER TABLE product_list
--   ADD COLUMN sold_at TIMESTAMP NULL DEFAULT NULL AFTER product_id;

-- 3) agent_sale_id — IMEISHIA (usirunde ikiwa tayari una agent_sale_id)
-- ALTER TABLE product_list
--   ADD COLUMN agent_sale_id BIGINT UNSIGNED NULL DEFAULT NULL AFTER sold_at,
--   ADD CONSTRAINT product_list_agent_sale_id_foreign
--     FOREIGN KEY (agent_sale_id) REFERENCES agent_sales(id) ON DELETE SET NULL;

-- 4) Fix data (runda HII tu): set sold_at for rows already linked to a sale but sold_at is NULL
UPDATE product_list pl
INNER JOIN agent_sales s ON s.id = pl.agent_sale_id
SET pl.sold_at = COALESCE(pl.sold_at, s.date)
WHERE pl.agent_sale_id IS NOT NULL AND pl.sold_at IS NULL;
