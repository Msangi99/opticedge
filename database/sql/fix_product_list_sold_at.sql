-- Fix product_list: ensure sold_at/agent_sale_id columns exist, then fix historical data.
-- Run this if your product_list table was missing these columns or has sold items with sold_at = NULL.
-- Database: MySQL/MariaDB (adjust for PostgreSQL if needed).

-- ============================================================
-- 1) ALTER TABLE: add columns only if they don't exist
-- ============================================================
-- MySQL: add sold_at if missing
ALTER TABLE product_list
ADD COLUMN IF NOT EXISTS sold_at TIMESTAMP NULL DEFAULT NULL AFTER product_id;

-- MySQL: add agent_sale_id if missing (requires agent_sales table to exist)
-- ALTER TABLE product_list
-- ADD COLUMN IF NOT EXISTS agent_sale_id BIGINT UNSIGNED NULL DEFAULT NULL AFTER sold_at,
-- ADD CONSTRAINT product_list_agent_sale_id_foreign
--   FOREIGN KEY (agent_sale_id) REFERENCES agent_sales(id) ON DELETE SET NULL;

-- If your MySQL version doesn't support ADD COLUMN IF NOT EXISTS, use this instead (run once):
-- ALTER TABLE product_list ADD COLUMN sold_at TIMESTAMP NULL DEFAULT NULL AFTER product_id;
-- ALTER TABLE product_list ADD COLUMN agent_sale_id BIGINT UNSIGNED NULL DEFAULT NULL AFTER sold_at;

-- ============================================================
-- 2) UPDATE: set sold_at for rows that were sold but have sold_at = NULL
-- (e.g. because the model $fillable was missing these fields before)
-- ============================================================
UPDATE product_list pl
INNER JOIN agent_sales s ON s.id = pl.agent_sale_id
SET pl.sold_at = COALESCE(pl.sold_at, s.date)
WHERE pl.agent_sale_id IS NOT NULL
  AND pl.sold_at IS NULL;

-- If agent_sales has a created_at but no date column, use this variant:
-- UPDATE product_list pl
-- INNER JOIN agent_sales s ON s.id = pl.agent_sale_id
-- SET pl.sold_at = COALESCE(pl.sold_at, s.created_at)
-- WHERE pl.agent_sale_id IS NOT NULL AND pl.sold_at IS NULL;
