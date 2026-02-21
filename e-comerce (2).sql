-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jan 26, 2026 at 11:18 PM
-- Server version: 8.4.7
-- PHP Version: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `e-comerce`
--

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

DROP TABLE IF EXISTS `addresses`;
CREATE TABLE IF NOT EXISTS `addresses` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'Home',
  `address` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `state` varchar(255) DEFAULT NULL,
  `zip` varchar(255) DEFAULT NULL,
  `country` varchar(255) NOT NULL DEFAULT 'Tanzania',
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `addresses_user_id_foreign` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `addresses`
--

INSERT INTO `addresses` (`id`, `user_id`, `type`, `address`, `city`, `state`, `zip`, `country`, `is_default`, `created_at`, `updated_at`, `latitude`, `longitude`) VALUES
(1, 3, 'Office', 'chamanzi, comfort 6', 'dar-es-salaam', 'dar es salaam', '15116', 'Tanzania', 0, '2026-01-19 19:14:28', '2026-01-19 19:14:28', -6.74371380, 39.22016144),
(2, 2, 'Office', 'chamanzi, comfort 6', 'dar-es-salaam', 'opoipj', '15116', 'Tanzania', 0, '2026-01-21 13:21:52', '2026-01-21 13:21:52', -6.80098957, 39.21539783);

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(191) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(191) NOT NULL,
  `owner` varchar(191) NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `carts`
--

DROP TABLE IF EXISTS `carts`;
CREATE TABLE IF NOT EXISTS `carts` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `carts_user_id_foreign` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `carts`
--

INSERT INTO `carts` (`id`, `user_id`, `created_at`, `updated_at`) VALUES
(1, 1, '2026-01-19 17:29:49', '2026-01-19 17:29:49'),
(6, 2, '2026-01-22 16:31:44', '2026-01-22 16:31:44'),
(3, 3, '2026-01-19 19:08:42', '2026-01-19 19:08:42');

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

DROP TABLE IF EXISTS `cart_items`;
CREATE TABLE IF NOT EXISTS `cart_items` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `cart_id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cart_items_cart_id_foreign` (`cart_id`),
  KEY `cart_items_product_id_foreign` (`product_id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `cart_items`
--

INSERT INTO `cart_items` (`id`, `cart_id`, `product_id`, `quantity`, `created_at`, `updated_at`) VALUES
(8, 6, 2, 1, '2026-01-22 16:42:11', '2026-01-22 16:42:11'),
(4, 3, 2, 1, '2026-01-19 19:08:42', '2026-01-19 19:08:42'),
(5, 1, 2, 2, '2026-01-21 13:20:12', '2026-01-21 13:20:12');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` varchar(191) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue` varchar(191) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE IF NOT EXISTS `job_batches` (
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
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_01_19_190141_add_role_to_users_table', 1),
(5, '2026_01_19_193406_create_products_table', 2),
(6, '2026_01_19_194238_change_image_path_to_images_in_products_table', 3),
(7, '2026_01_19_201008_add_rating_to_products_table', 4),
(8, '2026_01_19_202047_create_carts_table', 5),
(9, '2026_01_19_202048_create_cart_items_table', 5),
(10, '2026_01_19_202050_create_orders_table', 5),
(11, '2026_01_19_202052_create_order_items_table', 5),
(12, '2026_01_19_213034_create_addresses_table', 6),
(13, '2026_01_19_214006_add_status_to_users_table', 7),
(14, '2026_01_19_221029_add_lat_long_to_addresses_table', 8),
(15, '2026_01_19_223246_add_address_id_to_orders_table', 9),
(16, '2021_13_09_000000_create_selcom_payments_table', 99),
(18, '2026_01_21_193721_create_settings_table', 100),
(19, '2026_01_21_201717_create_selcompays_table', 100);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE IF NOT EXISTS `orders` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `payment_status` varchar(255) NOT NULL DEFAULT 'pending',
  `total_price` decimal(10,2) NOT NULL,
  `shipping_address` text,
  `payment_method` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `address_id` bigint UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `orders_user_id_foreign` (`user_id`),
  KEY `orders_address_id_foreign` (`address_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `status`, `payment_status`, `total_price`, `shipping_address`, `payment_method`, `created_at`, `updated_at`, `address_id`) VALUES
(1, 2, 'pending', 'pending', 354.00, NULL, 'selcom', '2026-01-21 21:01:23', '2026-01-21 21:01:23', 2),
(2, 2, 'pending', 'pending', 118.00, NULL, 'selcom', '2026-01-22 16:14:08', '2026-01-22 16:14:08', 2),
(3, 2, 'pending', 'pending', 118.00, NULL, 'selcom', '2026-01-22 16:31:44', '2026-01-22 16:31:44', 2),
(4, 2, 'pending', 'pending', 118.00, NULL, 'selcom', '2026-01-22 16:42:23', '2026-01-22 16:42:23', 2),
(5, 2, 'pending', 'pending', 118.00, NULL, 'selcom', '2026-01-22 16:46:32', '2026-01-22 16:46:32', 2),
(6, 2, 'pending', 'pending', 118.00, NULL, 'selcom', '2026-01-22 16:46:41', '2026-01-22 16:46:41', 2);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `quantity` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_items_order_id_foreign` (`order_id`),
  KEY `order_items_product_id_foreign` (`product_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 3, 100.00, '2026-01-21 21:01:23', '2026-01-21 21:01:23'),
(2, 2, 2, 1, 100.00, '2026-01-22 16:14:08', '2026-01-22 16:14:08'),
(3, 3, 2, 1, 100.00, '2026-01-22 16:31:44', '2026-01-22 16:31:44'),
(4, 4, 2, 1, 100.00, '2026-01-22 16:42:23', '2026-01-22 16:42:23'),
(5, 5, 2, 1, 100.00, '2026-01-22 16:46:32', '2026-01-22 16:46:32'),
(6, 6, 2, 1, 100.00, '2026-01-22 16:46:41', '2026-01-22 16:46:41');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(191) NOT NULL,
  `token` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `brand` varchar(255) NOT NULL DEFAULT 'Samsung',
  `price` decimal(10,2) NOT NULL,
  `rating` decimal(2,1) NOT NULL DEFAULT '5.0',
  `stock_quantity` int NOT NULL DEFAULT '0',
  `description` text,
  `images` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `brand`, `price`, `rating`, `stock_quantity`, `description`, `images`, `created_at`, `updated_at`) VALUES
(1, 'Galaxy S24', 'Samsung', 100.00, 5.0, 20, 'good condition', NULL, '2026-01-19 16:38:38', '2026-01-19 16:38:38'),
(2, 'samsung A05', 'Samsung', 100.00, 5.0, 51, 'good quality', '[\"products/CRq919aM2YfFGapEbMhZMpUnOYGBpun7ZS9b8dFt.png\", \"products/CuqlHnrKmLpcwQUcNrutEPLuvN0Cczn08nasvQBJ.jpg\", \"products/xCfkGNXUX4L6E2m1b6S5pkiGMlXFzGuaG9HkPF2I.jpg\", \"products/FM08lBBrh163Ofg07Y3pduS1MBOVMiSmdjQ2Mnq5.jpg\", \"products/1YnSboD4m3kIjSxx59rT2VncsOg0na87hDl0xEnJ.jpg\"]', '2026-01-19 16:46:30', '2026-01-22 16:31:44');

-- --------------------------------------------------------

--
-- Table structure for table `selcompays`
--

DROP TABLE IF EXISTS `selcompays`;
CREATE TABLE IF NOT EXISTS `selcompays` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `transid` varchar(191) NOT NULL,
  `order_id` varchar(255) DEFAULT NULL,
  `phone_number` varchar(255) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `payment_status` varchar(255) NOT NULL DEFAULT 'pending',
  `local_order_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `selcompays_transid_unique` (`transid`),
  KEY `selcompays_local_order_id_foreign` (`local_order_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `selcom_payments`
--

DROP TABLE IF EXISTS `selcom_payments`;
CREATE TABLE IF NOT EXISTS `selcom_payments` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `amount` int NOT NULL,
  `order_id` varchar(255) NOT NULL,
  `transid` varchar(255) NOT NULL,
  `selcom_transaction_id` varchar(255) DEFAULT NULL,
  `user_id` varchar(255) DEFAULT NULL,
  `gateway_buyer_uuid` varchar(255) DEFAULT NULL,
  `payment_status` varchar(255) DEFAULT NULL,
  `reference` varchar(255) DEFAULT NULL,
  `msisdn` varchar(255) DEFAULT NULL,
  `channel` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(191) NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `payload` longtext NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
CREATE TABLE IF NOT EXISTS `settings` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `key` varchar(150) NOT NULL,
  `value` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_key_unique` (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `email` varchar(191) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'customer',
  `status` varchar(255) NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `role`, `status`) VALUES
(1, 'Admin User', 'admin@amazon.pro', '2026-01-19 16:15:52', '$2y$12$urYehMIxTWBtehZDFxn.v.f/y0Az16S4JZspTiQeDZkJXtT.b8BdO', 'FFQIV2bwVKu0BAhY54mqHptqbttIRrEZSGqvgqO5hpyHw3oigP1lqzzHaZuN', '2026-01-19 16:15:52', '2026-01-19 16:15:52', 'admin', 'active'),
(2, 'ibrahim ashiraf', 'doniaparoma99@gmail.com', NULL, '$2y$12$wFyx/MWUWttbLqgiXXkuV.vnlOhfawevBmZMu8onbeOg9mxgjC0o6', NULL, '2026-01-19 17:39:20', '2026-01-19 17:39:20', 'customer', 'active'),
(3, 'Ibramatelefon', 'tilisho@gmail.com', NULL, '$2y$12$zP45FITDWDJL1PULIGBwsuEdy5AFoysGGz8t66P71OlZB9/UHa4eu', NULL, '2026-01-19 18:51:01', '2026-01-19 19:06:24', 'dealer', 'active');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
