-- ============================================================================
-- OpticEdge — manual SQL for payment_options + payment child tables
-- ============================================================================
-- Use when Laravel migrate fails with errno 150 (foreign key incorrectly formed).
-- Requirements:
--   * `purchases` must exist with `id` BIGINT UNSIGNED (Laravel default).
--   * `distribution_sales` must exist before creating `distribution_sale_payments`.
--   * Server: MySQL 8+ / MariaDB 10.3+ recommended.
--
-- BACK UP YOUR DATABASE FIRST.
--
-- Typical order:
--   1) Run payment_options section.
--   2) If `purchase_payments` already exists but broken, DROP it (see section B).
--   3) Run purchase_payments CREATE.
--   4) Optional: purchases.payment_option_id column + FK (section C).
--   5) Optional: distribution_sale_payments (section D).
-- ============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------------
-- A) payment_options (full structure after migrations 2026_03_05 … 2026_03_07)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `payment_options` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `type` VARCHAR(255) NOT NULL COMMENT 'mobile, bank, etc.',
  `name` VARCHAR(255) NOT NULL,
  `balance` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `opening_balance` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `is_hidden` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- If `payment_options` already existed WITHOUT `opening_balance` / `is_hidden`, run only the
-- lines you need (skip any that error "Duplicate column"):
-- ALTER TABLE `payment_options` ADD COLUMN `opening_balance` DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER `balance`;
-- ALTER TABLE `payment_options` ADD COLUMN `is_hidden` TINYINT(1) NOT NULL DEFAULT 0 AFTER `opening_balance`;

SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------------
-- B) purchase_payments — DROP first only if the table is empty or you accept data loss
-- ---------------------------------------------------------------------------
-- UNCOMMENT IF YOU NEED A CLEAN TABLE:
-- SET FOREIGN_KEY_CHECKS = 0;
-- DROP TABLE IF EXISTS `purchase_payments`;
-- SET FOREIGN_KEY_CHECKS = 1;

SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `purchase_payments` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `purchase_id` BIGINT UNSIGNED NOT NULL,
  `payment_option_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `amount` DECIMAL(15,2) NOT NULL,
  `paid_date` DATE NULL DEFAULT NULL,
  `notes` TEXT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_payments_purchase_id_foreign` (`purchase_id`),
  KEY `purchase_payments_payment_option_id_foreign` (`payment_option_id`),
  CONSTRAINT `purchase_payments_purchase_id_foreign`
    FOREIGN KEY (`purchase_id`) REFERENCES `purchases` (`id`) ON DELETE CASCADE,
  CONSTRAINT `purchase_payments_payment_option_id_foreign`
    FOREIGN KEY (`payment_option_id`) REFERENCES `payment_options` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------------
-- C) purchases.payment_option_id (skip if column already exists)
-- ---------------------------------------------------------------------------
-- If the column is missing, uncomment:
-- ALTER TABLE `purchases`
--   ADD COLUMN `payment_option_id` BIGINT UNSIGNED NULL DEFAULT NULL
--   AFTER `payment_receipt_image`;
--
-- Then add FK (only if FK does not exist yet):
-- ALTER TABLE `purchases`
--   ADD CONSTRAINT `purchases_payment_option_id_foreign`
--   FOREIGN KEY (`payment_option_id`) REFERENCES `payment_options` (`id`) ON DELETE SET NULL;

-- ---------------------------------------------------------------------------
-- D) distribution_sale_payments (needs distribution_sales)
-- ---------------------------------------------------------------------------
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `distribution_sale_payments` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `distribution_sale_id` BIGINT UNSIGNED NOT NULL,
  `payment_option_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `amount` DECIMAL(15,2) NOT NULL,
  `paid_date` DATE NULL DEFAULT NULL,
  `notes` TEXT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `distribution_sale_payments_distribution_sale_id_foreign` (`distribution_sale_id`),
  KEY `distribution_sale_payments_payment_option_id_foreign` (`payment_option_id`),
  CONSTRAINT `distribution_sale_payments_distribution_sale_id_foreign`
    FOREIGN KEY (`distribution_sale_id`) REFERENCES `distribution_sales` (`id`) ON DELETE CASCADE,
  CONSTRAINT `distribution_sale_payments_payment_option_id_foreign`
    FOREIGN KEY (`payment_option_id`) REFERENCES `payment_options` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- If errno 150 persists, check on MySQL:
--   SHOW CREATE TABLE payment_options\G
--   SHOW CREATE TABLE purchases\G
-- `id` and referencing columns must both be BIGINT UNSIGNED, same charset, InnoDB.
-- ============================================================================
