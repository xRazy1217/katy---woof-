-- ============================================
-- Katy & Woof - Base de Datos Completa
-- ============================================
-- Ejecutar en phpMyAdmin (localhost)
-- ============================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Crear base de datos
CREATE DATABASE IF NOT EXISTS `katywoof_ecommerce` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `katywoof_ecommerce`;

-- ============================================
-- TABLAS PRINCIPALES
-- ============================================

-- Site Settings
CREATE TABLE IF NOT EXISTS `site_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_value` longtext COLLATE utf8mb4_unicode_ci,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar configuraciones por defecto
INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES
('our_history', 'Nuestra pasión por el arte comenzó...'),
('contact_email', 'hello@katyandwoof.art'),
('contact_whatsapp', '+56 9 XXXX XXXX'),
('contact_address', 'Santiago, Chile'),
('site_logo', '/img/placeholder.svg'),
('site_favicon', '/img/favicon.svg'),
('hero_title', 'Eterniza su alma en un lienzo.'),
('hero_description', 'Retratos de autor pintados a mano digitalmente que capturan la esencia única de tu compañero más fiel.'),
('hero_image', '/img/placeholder.svg'),
('nosotros_image', '/img/placeholder.svg'),
('nosotros_title', 'Donde el arte encuentra la lealtad.'),
('footer_philosophy', 'Especializados en capturar la esencia de tu compañero más fiel a través del arte digital de autor. Un tributo eterno a la lealtad.'),
('social_instagram', 'https://www.instagram.com/katyandwoof/');

-- ============================================
-- E-COMMERCE
-- ============================================

-- Product Categories
CREATE TABLE IF NOT EXISTS `product_categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `parent_id` int DEFAULT NULL,
  `image_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `display_order` int DEFAULT '0',
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Products
CREATE TABLE IF NOT EXISTS `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode ci,
  `short_description` text COLLATE utf8mb4_unicode ci,
  `sku` varchar(100) COLLATE utf8mb4_unicode ci DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `sale_price` decimal(10,2) DEFAULT NULL,
  `stock_quantity` int DEFAULT '0',
  `stock_status` enum('instock','outofstock','onbackorder') COLLATE utf8mb4_unicode ci DEFAULT 'instock',
  `weight` decimal(5,2) DEFAULT NULL,
  `dimensions` varchar(100) COLLATE utf8mb4_unicode ci DEFAULT NULL,
  `category_id` int DEFAULT NULL,
  `image_url` varchar(500) COLLATE utf8mb4_unicode ci DEFAULT NULL,
  `gallery_images` json DEFAULT NULL,
  `attributes` json DEFAULT NULL,
  `tags` json DEFAULT NULL,
  `seo_title` varchar(255) COLLATE utf8mb4_unicode ci DEFAULT NULL,
  `seo_description` text COLLATE utf8mb4_unicode ci,
  `status` enum('publish','draft','trash') COLLATE utf8mb4_unicode ci DEFAULT 'publish',
  `product_type` enum('service','physical') COLLATE utf8mb4_unicode ci NOT NULL DEFAULT 'physical',
  `requires_shipping` tinyint(1) NOT NULL DEFAULT '1',
  `service_category` varchar(100) COLLATE utf8mb4_unicode ci DEFAULT NULL,
  `service_duration_minutes` int DEFAULT NULL,
  `service_mode` enum('online','presencial','mixto') COLLATE utf8mb4_unicode ci DEFAULT NULL,
  `featured` tinyint(1) DEFAULT '0',
  `virtual` tinyint(1) DEFAULT '0',
  `downloadable` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  UNIQUE KEY `sku` (`sku`),
  KEY `idx_product_type` (`product_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode ci;

-- Insertar producto de ejemplo
INSERT INTO `products` (`name`, `slug`, `description`, `sku`, `price`, `sale_price`, `stock_quantity`, `stock_status`, `category_id`, `image_url`, `status`) VALUES
('Retrato Digital de Mascota', 'retrato-digital-mascota', 'Retrato artístico colorido de tu mascota en formato digital de alta resolución.', 'RET-001', 25000.00, NULL, 99, 'instock', NULL, '/uploads/placeholder-product.svg', 'publish');

-- Product Variations
CREATE TABLE IF NOT EXISTS `product_variations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `sku` varchar(100) COLLATE utf8mb4_unicode ci DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `sale_price` decimal(10,2) DEFAULT NULL,
  `stock_quantity` int DEFAULT '0',
  `stock_status` enum('instock','outofstock','onbackorder') COLLATE utf8mb4_unicode ci DEFAULT 'instock',
  `attributes` json NOT NULL,
  `image_url` varchar(500) COLLATE utf8mb4_unicode ci DEFAULT NULL,
  `display_order` int DEFAULT '0',
  `status` enum('active','inactive') COLLATE utf8mb4_unicode ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sku` (`sku`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode ci;

-- Orders
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_number` varchar(50) COLLATE utf8mb4_unicode ci NOT NULL,
  `user_id` int DEFAULT NULL,
  `customer_email` varchar(255) COLLATE utf8mb4_unicode ci NOT NULL,
  `customer_data` json NOT NULL,
  `status` enum('pending','processing','shipped','completed','cancelled','refunded','failed') COLLATE utf8mb4_unicode ci DEFAULT 'pending',
  `currency` varchar(3) COLLATE utf8mb4_unicode ci DEFAULT 'CLP',
  `subtotal` decimal(10,2) NOT NULL,
  `tax_total` decimal(10,2) DEFAULT '0.00',
  `shipping_total` decimal(10,2) DEFAULT '0.00',
  `discount_total` decimal(10,2) DEFAULT '0.00',
  `total` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) COLLATE utf8mb4_unicode ci DEFAULT 'flow',
  `payment_status` enum('pending','paid','failed','refunded','cancelled') COLLATE utf8mb4_unicode ci DEFAULT 'pending',
  `transaction_id` varchar(255) COLLATE utf8mb4_unicode ci DEFAULT NULL,
  `flow_order_id` varchar(255) COLLATE utf8mb4_unicode ci DEFAULT NULL,
  `shipping_method` varchar(100) COLLATE utf8mb4_unicode ci DEFAULT NULL,
  `shipping_address` json DEFAULT NULL,
  `billing_address` json DEFAULT NULL,
  `order_notes` text COLLATE utf8mb4_unicode ci,
  `customer_ip` varchar(45) COLLATE utf8mb4_unicode ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode ci;

-- Order Items
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `variation_id` int DEFAULT NULL,
  `product_name` varchar(255) COLLATE utf8mb4_unicode ci NOT NULL,
  `product_sku` varchar(100) COLLATE utf8mb4_unicode ci DEFAULT NULL,
  `quantity` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `line_total` decimal(10,2) NOT NULL,
  `line_tax` decimal(10,2) DEFAULT '0.00',
  `variation_data` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode ci;

-- Order Status History
CREATE TABLE IF NOT EXISTS `order_status_history` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `old_status` enum('pending','processing','shipped','completed','cancelled','refunded','failed') COLLATE utf8mb4_unicode ci DEFAULT NULL,
  `new_status` enum('pending','processing','shipped','completed','cancelled','refunded','failed') COLLATE utf8mb4_unicode ci NOT NULL,
  `notes` text COLLATE utf8mb4_unicode ci,
  `changed_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode ci;

-- Coupons
CREATE TABLE IF NOT EXISTS `coupons` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(50) COLLATE utf8mb4_unicode ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode ci,
  `discount_type` enum('fixed','percentage') COLLATE utf8mb4_unicode ci DEFAULT 'fixed',
  `discount_value` decimal(10,2) NOT NULL,
  `usage_limit` int DEFAULT NULL,
  `usage_count` int DEFAULT '0',
  `expiry_date` date DEFAULT NULL,
  `minimum_amount` decimal(10,2) DEFAULT NULL,
  `maximum_amount` decimal(10,2) DEFAULT NULL,
  `product_ids` json DEFAULT NULL,
  `category_ids` json DEFAULT NULL,
  `exclude_product_ids` json DEFAULT NULL,
  `exclude_category_ids` json DEFAULT NULL,
  `individual_use` tinyint(1) DEFAULT '1',
  `status` enum('active','inactive') COLLATE utf8mb4_unicode ci DEFAULT 'active',
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode ci;

-- Order Coupons
CREATE TABLE IF NOT EXISTS `order_coupons` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `coupon_id` int NOT NULL,
  `coupon_code` varchar(50) COLLATE utf8mb4_unicode ci NOT NULL,
  `discount_amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode ci;

-- Cart Items
CREATE TABLE IF NOT EXISTS `cart_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `session_id` varchar(255) COLLATE utf8mb4_unicode ci NOT NULL,
  `user_id` int DEFAULT NULL,
  `product_id` int NOT NULL,
  `variation_id` int DEFAULT NULL,
  `quantity` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `line_total` decimal(10,2) GENERATED ALWAYS AS ((`price` * `quantity`)) STORED,
  `added_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode ci;

-- ============================================
-- CONTENIDO
-- ============================================

-- Portfolio Items
CREATE TABLE IF NOT EXISTS `portfolio_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode ci,
  `img_url` varchar(500) COLLATE utf8mb4_unicode ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode ci;

-- Blog Posts
CREATE TABLE IF NOT EXISTS `blog_posts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` text,
  `category` varchar(100) DEFAULT 'General',
  `img_url` varchar(500) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `slug` varchar(255) DEFAULT NULL,
  `excerpt` varchar(500) DEFAULT NULL,
  `featured_image` varchar(500) DEFAULT NULL,
  `author` varchar(100) DEFAULT NULL,
  `views_count` int DEFAULT '0',
  `published` tinyint(1) DEFAULT '0',
  `published_at` datetime DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode ci;

-- Process Steps
CREATE TABLE IF NOT EXISTS `process_steps` (
  `id` int NOT NULL AUTO_INCREMENT,
  `step_number` int DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text,
  `img_url` varchar(500) DEFAULT NULL,
  `icon_name` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode ci;

-- Services
CREATE TABLE IF NOT EXISTS `services` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text,
  `category` varchar(100) DEFAULT 'General',
  `main_image_url` varchar(500) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `icon_name` varchar(100) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `featured` tinyint(1) DEFAULT '0',
  `display_order` int DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode ci;

-- ============================================
-- SISTEMA
-- ============================================

-- Logs
CREATE TABLE IF NOT EXISTS `logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `event_type` varchar(100) COLLATE utf8mb4_unicode ci NOT NULL,
  `message` longtext COLLATE utf8mb4_unicode ci,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode ci DEFAULT NULL,
  `user_agent` varchar(500) COLLATE utf8mb4_unicode ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_event_type` (`event_type`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode ci;

-- List Items
CREATE TABLE IF NOT EXISTS `list_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `list_key` varchar(100) COLLATE utf8mb4_unicode ci NOT NULL,
  `item_value` varchar(255) COLLATE utf8mb4_unicode ci NOT NULL,
  `item_order` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_list_key` (`list_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode ci;

-- E-commerce Settings
CREATE TABLE IF NOT EXISTS `ecommerce_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) COLLATE utf8mb4_unicode ci NOT NULL,
  `setting_value` longtext COLLATE utf8mb4_unicode ci,
  `setting_group` varchar(50) COLLATE utf8mb4_unicode ci DEFAULT 'general',
  `setting_type` enum('string','number','boolean','json','array') COLLATE utf8mb4_unicode ci DEFAULT 'string',
  `is_public` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode ci;

-- Product Views (Analytics)
CREATE TABLE IF NOT EXISTS `product_views` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `session_id` varchar(255) COLLATE utf8mb4_unicode ci DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode ci,
  `referrer` varchar(500) COLLATE utf8mb4_unicode ci DEFAULT NULL,
  `viewed_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode ci;

-- Search Queries (Analytics)
CREATE TABLE IF NOT EXISTS `search_queries` (
  `id` int NOT NULL AUTO_INCREMENT,
  `query` varchar(255) COLLATE utf8mb4_unicode ci NOT NULL,
  `results_count` int DEFAULT '0',
  `user_id` int DEFAULT NULL,
  `session_id` varchar(255) COLLATE utf8mb4_unicode ci DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode ci,
  `searched_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode ci;

-- Shipping Zones
CREATE TABLE IF NOT EXISTS `shipping_zones` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode ci NOT NULL,
  `countries` json NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode ci;

-- Shipping Methods
CREATE TABLE IF NOT EXISTS `shipping_methods` (
  `id` int NOT NULL AUTO_INCREMENT,
  `zone_id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode ci,
  `method_type` enum('flat_rate','free_shipping','local_pickup') COLLATE utf8mb4_unicode ci DEFAULT 'flat_rate',
  `cost` decimal(10,2) DEFAULT '0.00',
  `min_amount` decimal(10,2) DEFAULT NULL,
  `max_weight` decimal(5,2) DEFAULT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode ci;

-- User Addresses
CREATE TABLE IF NOT EXISTS `user_addresses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `type` enum('billing','shipping') COLLATE utf8mb4_unicode ci NOT NULL,
  `first_name` varchar(100) COLLATE utf8mb4_unicode ci NOT NULL,
  `last_name` varchar(100) COLLATE utf8mb4_unicode ci NOT NULL,
  `company` varchar(100) COLLATE utf8mb4_unicode ci DEFAULT NULL,
  `address_1` varchar(255) COLLATE utf8mb4_unicode ci NOT NULL,
  `address_2` varchar(255) COLLATE utf8mb4_unicode ci DEFAULT NULL,
  `city` varchar(100) COLLATE utf8mb4_unicode ci NOT NULL,
  `state` varchar(100) COLLATE utf8mb4_unicode ci DEFAULT NULL,
  `postcode` varchar(20) COLLATE utf8mb4_unicode ci DEFAULT NULL,
  `country` varchar(2) COLLATE utf8mb4_unicode ci DEFAULT 'CL',
  `phone` varchar(50) COLLATE utf8mb4_unicode ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode ci DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode ci;

COMMIT;

-- ============================================
-- FIN DEL SCRIPT
-- ============================================
-- Base de datos creada exitosamente
-- Ahora puedes acceder al admin en:
-- https://retratodemascotas.cl/admin/
-- Contraseña: Asesor25
-- ============================================
