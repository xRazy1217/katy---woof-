<?php
/**
 * Katy & Woof - Database Module v6.0
 * Manejo de conexiones y setup de BD
 */

require_once '../config.php';

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

        $pdo->exec("CREATE TABLE IF NOT EXISTS `services` (`id` INT(11) NOT NULL AUTO_INCREMENT, `title` VARCHAR(255) NOT NULL, `description` TEXT, `category` VARCHAR(100) DEFAULT 'General', `main_image_url` VARCHAR(500) NOT NULL, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        $pdo->exec("CREATE TABLE IF NOT EXISTS `blog_posts` (`id` INT(11) NOT NULL AUTO_INCREMENT, `title` VARCHAR(255) NOT NULL, `content` TEXT, `category` VARCHAR(100) DEFAULT 'General', `img_url` VARCHAR(500) NOT NULL, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        $pdo->exec("CREATE TABLE IF NOT EXISTS `process_steps` (`id` INT(11) NOT NULL AUTO_INCREMENT, `step_number` INT(11), `title` VARCHAR(255), `description` TEXT, `img_url` VARCHAR(500), PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        $pdo->exec("CREATE TABLE IF NOT EXISTS `site_settings` (`id` INT(11) NOT NULL AUTO_INCREMENT, `setting_key` VARCHAR(100) UNIQUE NOT NULL, `setting_value` TEXT, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        $pdo->exec("CREATE TABLE IF NOT EXISTS `site_lists` (`id` INT(11) NOT NULL AUTO_INCREMENT, `list_key` VARCHAR(50) NOT NULL, `item_value` VARCHAR(255) NOT NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        $defaults = [
            'our_history' => 'Nuestra pasión por el arte comenzó...',
            'contact_email' => 'hello@katyandwoof.art',
            'contact_whatsapp' => '+34 000 000 000',
            'contact_address' => 'Atelier Barcelona, España',
            'site_logo' => 'img/logo.png',
            'site_favicon' => 'favicon.ico',
            'hero_title' => 'Eterniza su alma en un lienzo.',
            'hero_description' => 'Retratos de autor pintados a mano digitalmente que capturan la esencia única de tu compañero más fiel.',
            'hero_image' => 'img/hero-placeholder.jpg',
            'nosotros_image' => 'img/nosotros.jpg',
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