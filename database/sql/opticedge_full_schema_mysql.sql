-- OpticEdge / opticedge — FULL MySQL schema (structure only)
-- Charset/engine aligned for foreign keys (BIGINT UNSIGNED + InnoDB).
-- Import: mysql -u USER -p DATABASE < opticedge_full_schema_mysql.sql
-- Then:   php artisan db:seed   (optional data)
-- BACK UP before importing on non-empty DB.

SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;
SET FOREIGN_KEY_CHECKS = 0;
SET sql_mode = 'NO_ENGINE_SUBSTITUTION';

DROP TABLE IF EXISTS `agent_product_transfer_items`;
DROP TABLE IF EXISTS `agent_product_transfers`;
DROP TABLE IF EXISTS `agent_product_list_assignments`;
DROP TABLE IF EXISTS `agent_credit_payments`;
DROP TABLE IF EXISTS `agent_credits`;
DROP TABLE IF EXISTS `branch_transfer_logs`;
DROP TABLE IF EXISTS `distribution_sale_payments`;
DROP TABLE IF EXISTS `customer_needs`;
DROP TABLE IF EXISTS `purchase_payments`;
DROP TABLE IF EXISTS `product_list`;
DROP TABLE IF EXISTS `agent_assignments`;
DROP TABLE IF EXISTS `cart_items`;
DROP TABLE IF EXISTS `carts`;
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `selcompays`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `addresses`;
DROP TABLE IF EXISTS `pending_sales`;
DROP TABLE IF EXISTS `purchases`;
DROP TABLE IF EXISTS `distribution_sales`;
DROP TABLE IF EXISTS `agent_sales`;
DROP TABLE IF EXISTS `shop_records`;
DROP TABLE IF EXISTS `payables`;
DROP TABLE IF EXISTS `expenses`;
DROP TABLE IF EXISTS `stocks`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `payment_options`;
DROP TABLE IF EXISTS `branches`;
DROP TABLE IF EXISTS `vendors`;
DROP TABLE IF EXISTS `personal_access_tokens`;
DROP TABLE IF EXISTS `sessions`;
DROP TABLE IF EXISTS `failed_jobs`;
DROP TABLE IF EXISTS `job_batches`;
DROP TABLE IF EXISTS `jobs`;
DROP TABLE IF EXISTS `cache_locks`;
DROP TABLE IF EXISTS `cache`;
DROP TABLE IF EXISTS `password_reset_tokens`;
DROP TABLE IF EXISTS `settings`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `migrations`;

CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cache` (
  `key` varchar(191) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cache_locks` (
  `key` varchar(191) NOT NULL,
  `owner` varchar(191) NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(191) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `job_batches` (
  `id` varchar(191) NOT NULL,
  `name` varchar(191) NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(191) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `password_reset_tokens` (
  `email` varchar(191) NOT NULL,
  `token` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sessions` (
  `id` varchar(191) NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `payload` longtext NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `payment_options` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `balance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `opening_balance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `is_hidden` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `branches` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `vendors` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `office_name` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `email` varchar(191) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'customer',
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `business_name` varchar(100) DEFAULT NULL,
  `business_type` varchar(100) DEFAULT NULL,
  `category_id` bigint unsigned DEFAULT NULL,
  `phone` varchar(100) DEFAULT NULL,
  `how_did_you_hear` varchar(255) DEFAULT NULL,
  `referred_by` bigint unsigned DEFAULT NULL,
  `branch_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_category_id_foreign` (`category_id`),
  KEY `users_referred_by_foreign` (`referred_by`),
  KEY `users_branch_id_foreign` (`branch_id`),
  CONSTRAINT `users_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_referred_by_foreign` FOREIGN KEY (`referred_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `brand` varchar(255) NOT NULL DEFAULT 'Samsung',
  `price` decimal(10,2) NOT NULL,
  `rating` decimal(2,1) NOT NULL DEFAULT 5.0,
  `stock_quantity` int NOT NULL DEFAULT 0,
  `description` text,
  `images` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `category_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `products_category_id_foreign` (`category_id`),
  CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `stocks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `stock_limit` int unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `default_category_id` bigint unsigned DEFAULT NULL,
  `default_model` varchar(255) DEFAULT NULL,
  `default_quantity` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stocks_default_category_id_foreign` (`default_category_id`),
  CONSTRAINT `stocks_default_category_id_foreign` FOREIGN KEY (`default_category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `addresses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'Home',
  `address` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `state` varchar(255) DEFAULT NULL,
  `zip` varchar(255) DEFAULT NULL,
  `country` varchar(255) NOT NULL DEFAULT 'Tanzania',
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `addresses_user_id_foreign` (`user_id`),
  CONSTRAINT `addresses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `total_price` decimal(10,2) NOT NULL,
  `shipping_address` text,
  `payment_method` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `address_id` bigint unsigned DEFAULT NULL,
  `payment_option_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `orders_user_id_foreign` (`user_id`),
  KEY `orders_address_id_foreign` (`address_id`),
  KEY `orders_payment_option_id_foreign` (`payment_option_id`),
  CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `orders_address_id_foreign` FOREIGN KEY (`address_id`) REFERENCES `addresses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `orders_payment_option_id_foreign` FOREIGN KEY (`payment_option_id`) REFERENCES `payment_options` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `carts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `carts_user_id_foreign` (`user_id`),
  CONSTRAINT `carts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cart_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cart_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `quantity` int NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cart_items_cart_id_foreign` (`cart_id`),
  KEY `cart_items_product_id_foreign` (`product_id`),
  CONSTRAINT `cart_items_cart_id_foreign` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cart_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `order_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `quantity` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_items_order_id_foreign` (`order_id`),
  KEY `order_items_product_id_foreign` (`product_id`),
  CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(150) NOT NULL,
  `value` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `selcompays` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `transid` varchar(191) NOT NULL,
  `order_id` varchar(255) DEFAULT NULL,
  `phone_number` varchar(255) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `payment_status` varchar(255) NOT NULL DEFAULT 'pending',
  `local_order_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `selcompays_transid_unique` (`transid`),
  KEY `selcompays_local_order_id_foreign` (`local_order_id`),
  CONSTRAINT `selcompays_local_order_id_foreign` FOREIGN KEY (`local_order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `shop_records` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `opening_stock` int NOT NULL DEFAULT 0,
  `quantity_sold` int NOT NULL DEFAULT 0,
  `transfer_quantity` int NOT NULL DEFAULT 0,
  `date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `shop_records_product_id_foreign` (`product_id`),
  CONSTRAINT `shop_records_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `payables` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `item_name` varchar(255) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `distribution_sales` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `dealer_id` bigint unsigned DEFAULT NULL,
  `order_id` bigint unsigned DEFAULT NULL,
  `dealer_name` varchar(255) DEFAULT NULL,
  `seller_name` varchar(255) DEFAULT NULL,
  `product_id` bigint unsigned NOT NULL,
  `quantity_sold` int NOT NULL,
  `purchase_price` decimal(15,2) DEFAULT NULL,
  `selling_price` decimal(15,2) DEFAULT NULL,
  `total_purchase_value` decimal(15,2) DEFAULT NULL,
  `total_selling_value` decimal(15,2) DEFAULT NULL,
  `profit` decimal(15,2) DEFAULT NULL,
  `commission` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `to_be_paid` decimal(15,2) DEFAULT NULL,
  `paid_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `collection_date` date DEFAULT NULL,
  `collected_amount` decimal(15,2) DEFAULT NULL,
  `balance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `payment_option_id` bigint unsigned DEFAULT NULL,
  `date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `distribution_sales_dealer_id_foreign` (`dealer_id`),
  KEY `distribution_sales_order_id_foreign` (`order_id`),
  KEY `distribution_sales_product_id_foreign` (`product_id`),
  KEY `distribution_sales_payment_option_id_foreign` (`payment_option_id`),
  CONSTRAINT `distribution_sales_dealer_id_foreign` FOREIGN KEY (`dealer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `distribution_sales_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `distribution_sales_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `distribution_sales_payment_option_id_foreign` FOREIGN KEY (`payment_option_id`) REFERENCES `payment_options` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `agent_sales` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `agent_id` bigint unsigned DEFAULT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `seller_name` varchar(255) DEFAULT NULL,
  `product_id` bigint unsigned NOT NULL,
  `quantity_sold` int NOT NULL,
  `purchase_price` decimal(15,2) DEFAULT NULL,
  `selling_price` decimal(15,2) DEFAULT NULL,
  `total_purchase_value` decimal(15,2) DEFAULT NULL,
  `total_selling_value` decimal(15,2) DEFAULT NULL,
  `profit` decimal(15,2) DEFAULT NULL,
  `commission_paid` decimal(15,2) DEFAULT NULL,
  `date_of_collection` date DEFAULT NULL,
  `balance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `stock_remaining` int DEFAULT NULL,
  `date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `payment_option_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `agent_sales_agent_id_foreign` (`agent_id`),
  KEY `agent_sales_product_id_foreign` (`product_id`),
  KEY `agent_sales_payment_option_id_foreign` (`payment_option_id`),
  CONSTRAINT `agent_sales_agent_id_foreign` FOREIGN KEY (`agent_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `agent_sales_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `agent_sales_payment_option_id_foreign` FOREIGN KEY (`payment_option_id`) REFERENCES `payment_options` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `agent_assignments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `agent_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `quantity_assigned` int NOT NULL DEFAULT 0,
  `quantity_sold` int NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `agent_assignments_agent_id_product_id_unique` (`agent_id`,`product_id`),
  KEY `agent_assignments_agent_id_foreign` (`agent_id`),
  KEY `agent_assignments_product_id_foreign` (`product_id`),
  CONSTRAINT `agent_assignments_agent_id_foreign` FOREIGN KEY (`agent_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `agent_assignments_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `purchases` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `stock_id` bigint unsigned DEFAULT NULL,
  `branch_id` bigint unsigned DEFAULT NULL,
  `product_id` bigint unsigned NOT NULL,
  `quantity` int NOT NULL,
  `unit_price` decimal(15,2) NOT NULL,
  `distributor_name` varchar(255) DEFAULT NULL,
  `total_amount` decimal(15,2) DEFAULT NULL,
  `paid_date` date DEFAULT NULL,
  `paid_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `payment_status` varchar(255) NOT NULL DEFAULT 'pending',
  `date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `limit_status` varchar(255) NOT NULL DEFAULT 'pending',
  `limit_remaining` int DEFAULT NULL,
  `sell_price` decimal(15,2) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `payment_receipt_image` varchar(255) DEFAULT NULL,
  `payment_option_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchases_stock_id_foreign` (`stock_id`),
  KEY `purchases_branch_id_foreign` (`branch_id`),
  KEY `purchases_product_id_foreign` (`product_id`),
  KEY `purchases_payment_option_id_foreign` (`payment_option_id`),
  CONSTRAINT `purchases_stock_id_foreign` FOREIGN KEY (`stock_id`) REFERENCES `stocks` (`id`) ON DELETE SET NULL,
  CONSTRAINT `purchases_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `purchases_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `purchases_payment_option_id_foreign` FOREIGN KEY (`payment_option_id`) REFERENCES `payment_options` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `purchase_payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `purchase_id` bigint unsigned NOT NULL,
  `payment_option_id` bigint unsigned DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `paid_date` date DEFAULT NULL,
  `notes` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_payments_purchase_id_foreign` (`purchase_id`),
  KEY `purchase_payments_payment_option_id_foreign` (`payment_option_id`),
  CONSTRAINT `purchase_payments_purchase_id_foreign` FOREIGN KEY (`purchase_id`) REFERENCES `purchases` (`id`) ON DELETE CASCADE,
  CONSTRAINT `purchase_payments_payment_option_id_foreign` FOREIGN KEY (`payment_option_id`) REFERENCES `payment_options` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `pending_sales` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `customer_name` varchar(255) DEFAULT NULL,
  `seller_name` varchar(255) DEFAULT NULL,
  `seller_id` bigint unsigned DEFAULT NULL,
  `product_id` bigint unsigned NOT NULL,
  `quantity_sold` int NOT NULL,
  `purchase_price` decimal(15,2) DEFAULT NULL,
  `selling_price` decimal(15,2) DEFAULT NULL,
  `total_purchase_value` decimal(15,2) DEFAULT NULL,
  `total_selling_value` decimal(15,2) DEFAULT NULL,
  `profit` decimal(15,2) DEFAULT NULL,
  `payment_option_id` bigint unsigned DEFAULT NULL,
  `date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pending_sales_product_id_foreign` (`product_id`),
  KEY `pending_sales_payment_option_id_foreign` (`payment_option_id`),
  KEY `pending_sales_seller_id_foreign` (`seller_id`),
  CONSTRAINT `pending_sales_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pending_sales_payment_option_id_foreign` FOREIGN KEY (`payment_option_id`) REFERENCES `payment_options` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pending_sales_seller_id_foreign` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `agent_credits` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `agent_id` bigint unsigned NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_phone` varchar(64) DEFAULT NULL,
  `product_list_id` bigint unsigned DEFAULT NULL,
  `product_id` bigint unsigned NOT NULL,
  `total_amount` decimal(15,2) NOT NULL,
  `paid_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `payment_status` varchar(255) NOT NULL DEFAULT 'pending',
  `payment_option_id` bigint unsigned DEFAULT NULL,
  `installment_count` int unsigned DEFAULT NULL,
  `installment_amount` decimal(15,2) DEFAULT NULL,
  `installment_interval_days` smallint unsigned DEFAULT NULL,
  `first_due_date` date DEFAULT NULL,
  `installment_notes` text,
  `date` date NOT NULL,
  `paid_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `agent_credits_agent_id_foreign` (`agent_id`),
  KEY `agent_credits_product_list_id_foreign` (`product_list_id`),
  KEY `agent_credits_product_id_foreign` (`product_id`),
  KEY `agent_credits_payment_option_id_foreign` (`payment_option_id`),
  CONSTRAINT `agent_credits_agent_id_foreign` FOREIGN KEY (`agent_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `agent_credits_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `agent_credits_payment_option_id_foreign` FOREIGN KEY (`payment_option_id`) REFERENCES `payment_options` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `agent_credit_payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `agent_credit_id` bigint unsigned NOT NULL,
  `payment_option_id` bigint unsigned DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `paid_date` date DEFAULT NULL,
  `notes` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `agent_credit_payments_agent_credit_id_foreign` (`agent_credit_id`),
  KEY `agent_credit_payments_payment_option_id_foreign` (`payment_option_id`),
  CONSTRAINT `agent_credit_payments_agent_credit_id_foreign` FOREIGN KEY (`agent_credit_id`) REFERENCES `agent_credits` (`id`) ON DELETE CASCADE,
  CONSTRAINT `agent_credit_payments_payment_option_id_foreign` FOREIGN KEY (`payment_option_id`) REFERENCES `payment_options` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `product_list` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `stock_id` bigint unsigned NOT NULL,
  `category_id` bigint unsigned NOT NULL,
  `model` varchar(255) NOT NULL,
  `imei_number` varchar(512) NOT NULL,
  `product_id` bigint unsigned DEFAULT NULL,
  `sold_at` timestamp NULL DEFAULT NULL,
  `agent_sale_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `purchase_id` bigint unsigned DEFAULT NULL,
  `branch_id` bigint unsigned DEFAULT NULL,
  `pending_sale_id` bigint unsigned DEFAULT NULL,
  `agent_credit_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_list_imei_number_unique` (`imei_number`),
  KEY `product_list_stock_id_foreign` (`stock_id`),
  KEY `product_list_category_id_foreign` (`category_id`),
  KEY `product_list_product_id_foreign` (`product_id`),
  KEY `product_list_agent_sale_id_foreign` (`agent_sale_id`),
  KEY `product_list_purchase_id_foreign` (`purchase_id`),
  KEY `product_list_branch_id_foreign` (`branch_id`),
  KEY `product_list_pending_sale_id_foreign` (`pending_sale_id`),
  KEY `product_list_agent_credit_id_foreign` (`agent_credit_id`),
  CONSTRAINT `product_list_stock_id_foreign` FOREIGN KEY (`stock_id`) REFERENCES `stocks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_list_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_list_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  CONSTRAINT `product_list_agent_sale_id_foreign` FOREIGN KEY (`agent_sale_id`) REFERENCES `agent_sales` (`id`) ON DELETE SET NULL,
  CONSTRAINT `product_list_purchase_id_foreign` FOREIGN KEY (`purchase_id`) REFERENCES `purchases` (`id`) ON DELETE SET NULL,
  CONSTRAINT `product_list_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `product_list_pending_sale_id_foreign` FOREIGN KEY (`pending_sale_id`) REFERENCES `pending_sales` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `agent_credits`
  ADD CONSTRAINT `agent_credits_product_list_id_foreign` FOREIGN KEY (`product_list_id`) REFERENCES `product_list` (`id`) ON DELETE SET NULL;

ALTER TABLE `product_list`
  ADD CONSTRAINT `product_list_agent_credit_id_foreign` FOREIGN KEY (`agent_credit_id`) REFERENCES `agent_credits` (`id`) ON DELETE SET NULL;

CREATE TABLE `expenses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `activity` varchar(255) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `cash_used` varchar(255) DEFAULT NULL,
  `date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `payment_option_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `expenses_payment_option_id_foreign` (`payment_option_id`),
  CONSTRAINT `expenses_payment_option_id_foreign` FOREIGN KEY (`payment_option_id`) REFERENCES `payment_options` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `agent_product_transfers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `from_agent_id` bigint unsigned NOT NULL,
  `to_agent_id` bigint unsigned NOT NULL,
  `status` varchar(32) NOT NULL DEFAULT 'pending',
  `message` text,
  `admin_note` text,
  `decided_at` timestamp NULL DEFAULT NULL,
  `decided_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `agent_product_transfers_from_agent_id_foreign` (`from_agent_id`),
  KEY `agent_product_transfers_to_agent_id_foreign` (`to_agent_id`),
  KEY `agent_product_transfers_decided_by_foreign` (`decided_by`),
  KEY `agent_product_transfers_status_created_at_index` (`status`,`created_at`),
  CONSTRAINT `agent_product_transfers_from_agent_id_foreign` FOREIGN KEY (`from_agent_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `agent_product_transfers_to_agent_id_foreign` FOREIGN KEY (`to_agent_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `agent_product_transfers_decided_by_foreign` FOREIGN KEY (`decided_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `agent_product_transfer_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `agent_product_transfer_id` bigint unsigned NOT NULL,
  `product_list_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `apti_transfer_product_list_uniq` (`agent_product_transfer_id`,`product_list_id`),
  KEY `agent_product_transfer_items_agent_product_transfer_id_foreign` (`agent_product_transfer_id`),
  KEY `agent_product_transfer_items_product_list_id_foreign` (`product_list_id`),
  CONSTRAINT `agent_product_transfer_items_agent_product_transfer_id_foreign` FOREIGN KEY (`agent_product_transfer_id`) REFERENCES `agent_product_transfers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `agent_product_transfer_items_product_list_id_foreign` FOREIGN KEY (`product_list_id`) REFERENCES `product_list` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `agent_product_list_assignments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `agent_id` bigint unsigned NOT NULL,
  `product_list_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `agent_product_list_assignments_product_list_id_unique` (`product_list_id`),
  KEY `agent_product_list_assignments_agent_id_foreign` (`agent_id`),
  CONSTRAINT `agent_product_list_assignments_agent_id_foreign` FOREIGN KEY (`agent_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `agent_product_list_assignments_product_list_id_foreign` FOREIGN KEY (`product_list_id`) REFERENCES `product_list` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `branch_transfer_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_list_id` bigint unsigned NOT NULL,
  `from_branch_id` bigint unsigned DEFAULT NULL,
  `to_branch_id` bigint unsigned DEFAULT NULL,
  `admin_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `branch_transfer_logs_product_list_id_foreign` (`product_list_id`),
  KEY `branch_transfer_logs_from_branch_id_foreign` (`from_branch_id`),
  KEY `branch_transfer_logs_to_branch_id_foreign` (`to_branch_id`),
  KEY `branch_transfer_logs_admin_id_foreign` (`admin_id`),
  KEY `branch_transfer_logs_created_at_index` (`created_at`),
  CONSTRAINT `branch_transfer_logs_product_list_id_foreign` FOREIGN KEY (`product_list_id`) REFERENCES `product_list` (`id`) ON DELETE CASCADE,
  CONSTRAINT `branch_transfer_logs_from_branch_id_foreign` FOREIGN KEY (`from_branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `branch_transfer_logs_to_branch_id_foreign` FOREIGN KEY (`to_branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `branch_transfer_logs_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `distribution_sale_payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `distribution_sale_id` bigint unsigned NOT NULL,
  `payment_option_id` bigint unsigned DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `paid_date` date DEFAULT NULL,
  `notes` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `distribution_sale_payments_distribution_sale_id_foreign` (`distribution_sale_id`),
  KEY `distribution_sale_payments_payment_option_id_foreign` (`payment_option_id`),
  CONSTRAINT `distribution_sale_payments_distribution_sale_id_foreign` FOREIGN KEY (`distribution_sale_id`) REFERENCES `distribution_sales` (`id`) ON DELETE CASCADE,
  CONSTRAINT `distribution_sale_payments_payment_option_id_foreign` FOREIGN KEY (`payment_option_id`) REFERENCES `payment_options` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `customer_needs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `agent_id` bigint unsigned NOT NULL,
  `category_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `customer_phone` varchar(64) DEFAULT NULL,
  `branch_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_needs_agent_id_foreign` (`agent_id`),
  KEY `customer_needs_category_id_foreign` (`category_id`),
  KEY `customer_needs_product_id_foreign` (`product_id`),
  KEY `customer_needs_branch_id_foreign` (`branch_id`),
  CONSTRAINT `customer_needs_agent_id_foreign` FOREIGN KEY (`agent_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `customer_needs_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `customer_needs_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `customer_needs_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` (`migration`, `batch`) VALUES
('0001_01_01_000000_create_users_table',1),
('0001_01_01_000001_create_cache_table',1),
('0001_01_01_000002_create_jobs_table',1),
('2019_12_14_000001_create_personal_access_tokens_table',1),
('2026_01_19_190141_add_role_to_users_table',1),
('2026_01_19_193406_create_products_table',1),
('2026_01_19_194238_change_image_path_to_images_in_products_table',1),
('2026_01_19_201008_add_rating_to_products_table',1),
('2026_01_19_202047_create_carts_table',1),
('2026_01_19_202048_create_cart_items_table',1),
('2026_01_19_202050_create_orders_table',1),
('2026_01_19_202052_create_order_items_table',1),
('2026_01_19_213034_create_addresses_table',1),
('2026_01_19_214006_add_status_to_users_table',1),
('2026_01_19_221029_add_lat_long_to_addresses_table',1),
('2026_01_19_223246_add_address_id_to_orders_table',1),
('2026_01_21_193721_create_settings_table',1),
('2026_01_21_201717_create_selcompays_table',1),
('2026_01_25_161236_create_categories_table',1),
('2026_01_25_161244_add_category_id_to_products_table',1),
('2026_01_26_234744_add_image_to_categories_table',1),
('2026_01_27_000353_add_dealer_fields_to_users_table',1),
('2026_02_06_185855_create_stock_tables',1),
('2026_02_06_201700_add_how_did_you_hear_to_users_table',1),
('2026_02_08_000001_add_distribution_and_referrer_fields',1),
('2026_02_08_100000_create_agent_assignments_and_agent_id_on_agent_sales',1),
('2026_02_15_025930_create_expenses_table',1),
('2026_02_22_100000_create_stocks_table',1),
('2026_02_22_100001_create_product_list_table',1),
('2026_02_22_120000_add_stock_to_purchases_and_defaults_to_stocks',1),
('2026_02_24_100000_add_limit_status_sell_price_to_purchases',1),
('2026_02_24_100001_add_purchase_id_to_product_list',1),
('2026_02_24_120000_add_name_to_purchases',1),
('2026_02_27_190651_add_payment_receipt_image_to_purchases_table',1),
('2026_02_28_000000_add_payment_option_id_to_purchases_table',1),
('2026_02_28_000001_create_purchase_payments_table',1),
('2026_03_05_170829_create_payment_options_table',1),
('2026_03_05_170832_create_pending_sales_table',1),
('2026_03_05_170913_update_expenses_table_add_payment_option_id',1),
('2026_03_05_174410_add_pending_sale_id_to_product_list_table',1),
('2026_03_05_204623_add_opening_balance_to_payment_options_table',1),
('2026_03_07_000000_add_is_hidden_to_payment_options_table',1),
('2026_03_07_120000_add_payment_option_id_to_distribution_sales_table',1),
('2026_03_07_150000_make_expenses_cash_used_nullable',1),
('2026_03_11_194543_add_payment_option_id_to_agent_sales_table',1),
('2026_03_14_000001_add_payment_option_id_to_orders_table',1),
('2026_03_27_173750_create_branches_table_and_add_branch_id_to_purchases',1),
('2026_03_28_120000_ensure_payment_option_id_on_agent_sales_table',1),
('2026_03_28_140000_create_agent_credits_tables',1),
('2026_03_28_140001_ensure_agent_credit_payments_table',1),
('2026_03_28_200000_add_installment_interval_days_to_agent_credits',1),
('2026_03_28_210000_add_customer_phone_to_agent_credits',1),
('2026_03_29_100000_create_agent_product_list_assignments_table',1),
('2026_03_29_120000_extend_product_list_imei_number_length',1),
('2026_03_31_180000_create_vendors_table',1),
('2026_04_02_100000_create_agent_product_transfers_tables',1),
('2026_04_02_100001_add_branch_id_to_product_list_table',1),
('2026_04_02_100002_create_branch_transfer_logs_table',1),
('2026_04_05_120000_add_seller_id_to_pending_sales_table',1),
('2026_04_12_000001_create_distribution_sale_payments_table',1),
('2026_04_12_000002_create_customer_needs_table',1),
('2026_04_14_000001_add_customer_and_branch_to_customer_needs_table',1),
('2026_04_14_120000_add_branch_id_to_users_table',1);

SET FOREIGN_KEY_CHECKS = 1;
