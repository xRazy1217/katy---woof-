-- =====================================================
-- Katy & Woof - SQL Initialization for Schema System
-- =====================================================
-- Execute this SQL to set up the foundation tables
-- needed by the schema monitoring system.

-- Create logs table (if it doesn't exist)
CREATE TABLE IF NOT EXISTS `logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `event_type` VARCHAR(100) NOT NULL,
  `message` LONGTEXT,
  `ip_address` VARCHAR(45),
  `user_agent` VARCHAR(500),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_event_type` (`event_type`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create site_settings table (if doesn't exist)
CREATE TABLE IF NOT EXISTS `site_settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(255) NOT NULL UNIQUE,
  `setting_value` LONGTEXT,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create list_items table (if doesn't exist)
CREATE TABLE IF NOT EXISTS `list_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `list_key` VARCHAR(100) NOT NULL,
  `item_value` VARCHAR(255) NOT NULL,
  `item_order` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_list_key` (`list_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Log the initial setup
INSERT INTO `logs` (`event_type`, `message`) 
VALUES ('system_setup', 'Schema monitoring system initialized')
ON DUPLICATE KEY UPDATE `message` = `message`;

-- =====================================================
-- NOTES:
-- =====================================================
-- 1. This will NOT drop existing tables
-- 2. It uses CREATE TABLE IF NOT EXISTS (safe)
-- 3. You can run this multiple times without issues
-- 4. All tables use UTF-8 encoding for international chars
-- 5. Timestamps use CURRENT_TIMESTAMP for automatic tracking

-- Grant permissions (if needed, customize db_name and user)
-- GRANT ALL PRIVILEGES ON `dbyh6du0yfle1i`.* TO 'uiuxyllculkca'@'localhost';
-- FLUSH PRIVILEGES;
