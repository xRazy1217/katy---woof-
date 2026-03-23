<?php
/**
 * Katy & Woof - Database Module v6.0
 * Manejo de conexiones y setup de BD
 */

require_once __DIR__ . '/../config.php';

class Database {
    private static $pdo = null;

    public static function getConnection() {
        if (self::$pdo === null) {
            self::$pdo = getDBConnection();
        }
        return self::$pdo;
    }

    public static function runSetup() {
        $pdo = self::getConnection();
        $pdo->exec("CREATE TABLE IF NOT EXISTS `portfolio` (`id` INT(11) NOT NULL AUTO_INCREMENT, `name` VARCHAR(255) NOT NULL, `description` TEXT DEFAULT NULL, `img_url` VARCHAR(500) NOT NULL, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        // Asegurar columnas en portfolio
        try { $pdo->exec("ALTER TABLE `portfolio` ADD COLUMN `description` TEXT AFTER `name` "); } catch(Exception $e) {}

        $pdo->exec("CREATE TABLE IF NOT EXISTS `blog_posts` (`id` INT(11) NOT NULL AUTO_INCREMENT, `title` VARCHAR(255) NOT NULL, `content` TEXT, `category` VARCHAR(100) DEFAULT 'General', `img_url` VARCHAR(500) NOT NULL, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        $pdo->exec("CREATE TABLE IF NOT EXISTS `process_steps` (`id` INT(11) NOT NULL AUTO_INCREMENT, `step_number` INT(11), `title` VARCHAR(255), `description` TEXT, `img_url` VARCHAR(500), PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        $pdo->exec("CREATE TABLE IF NOT EXISTS `site_settings` (`id` INT(11) NOT NULL AUTO_INCREMENT, `setting_key` VARCHAR(100) UNIQUE NOT NULL, `setting_value` TEXT, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        $pdo->exec("CREATE TABLE IF NOT EXISTS `site_lists` (`id` INT(11) NOT NULL AUTO_INCREMENT, `list_key` VARCHAR(50) NOT NULL, `item_value` VARCHAR(255) NOT NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        // Compatibilidad para catalogo unificado: columnas opcionales en products.
        try { $pdo->exec("ALTER TABLE `products` ADD COLUMN `product_type` ENUM('service','physical') NOT NULL DEFAULT 'physical' AFTER `status`"); } catch(Exception $e) {}
        try { $pdo->exec("ALTER TABLE `products` ADD COLUMN `requires_shipping` TINYINT(1) NOT NULL DEFAULT 1 AFTER `product_type`"); } catch(Exception $e) {}
        try { $pdo->exec("ALTER TABLE `products` ADD COLUMN `service_category` VARCHAR(100) NULL AFTER `requires_shipping`"); } catch(Exception $e) {}
        try { $pdo->exec("ALTER TABLE `products` ADD COLUMN `service_duration_minutes` INT NULL AFTER `service_category`"); } catch(Exception $e) {}
        try { $pdo->exec("ALTER TABLE `products` ADD COLUMN `service_mode` ENUM('online','presencial','mixto') NULL AFTER `service_duration_minutes`"); } catch(Exception $e) {}
        try { $pdo->exec("CREATE INDEX idx_product_type ON products(product_type)"); } catch(Exception $e) {}

        $defaults = [
            'our_history' => 'Nuestra pasión por el arte comenzó...',
            'contact_email' => 'hello@katyandwoof.art',
            'contact_whatsapp' => '+34 000 000 000',
            'contact_address' => 'Atelier Barcelona, España',
            'site_logo' => '/img/placeholder.svg',
            'site_favicon' => '/img/favicon.svg',
            'hero_title' => 'Eterniza su alma en un lienzo.',
            'hero_description' => 'Retratos de autor pintados a mano digitalmente que capturan la esencia única de tu compañero más fiel.',
            'hero_image' => '/img/placeholder.svg',
            'nosotros_image' => '/img/placeholder.svg',
            'nosotros_title' => 'Donde el arte encuentra la lealtad.',
            'footer_philosophy' => 'Especializados en capturar la esencia de tu compañero más fiel a través del arte digital de autor. Un tributo eterno a la lealtad.',
            'social_instagram' => 'https://www.instagram.com/katyandwoof/'
        ];
        $stmt = $pdo->prepare("INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES (?, ?)");
        foreach ($defaults as $k => $v) { $stmt->execute([$k, $v]); }

        // Force update Instagram if it was the old default
        $pdo->prepare("UPDATE site_settings SET setting_value = ? WHERE setting_key = 'social_instagram' AND setting_value = 'https://instagram.com/katyandwoof'")->execute(['https://www.instagram.com/katyandwoof/']);
    }
}
?>