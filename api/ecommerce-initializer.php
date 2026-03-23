<?php
/**
 * Katy & Woof - E-commerce Database Initializer v1.0
 * Inicializa automáticamente el esquema de e-commerce
 */

class EcommerceDatabaseInitializer {

    /**
     * Obtiene todas las instrucciones CREATE TABLE del archivo SQL
     */
    public static function getInitializations() {
        return [
            // 1. PRODUCTOS Y CATEGORÍAS
            "CREATE TABLE IF NOT EXISTS product_categories (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                slug VARCHAR(255) UNIQUE NOT NULL,
                description TEXT,
                parent_id INT NULL,
                image_url VARCHAR(500),
                display_order INT DEFAULT 0,
                status ENUM('active', 'inactive') DEFAULT 'active',
                deleted_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_parent (parent_id),
                INDEX idx_status (status),
                INDEX idx_display_order (display_order),
                FOREIGN KEY (parent_id) REFERENCES product_categories(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

            "CREATE TABLE IF NOT EXISTS products (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                slug VARCHAR(255) UNIQUE NOT NULL,
                description LONGTEXT,
                short_description TEXT,
                sku VARCHAR(100) UNIQUE,
                price DECIMAL(10,2) NOT NULL,
                regular_price DECIMAL(10,2) NULL,
                sale_price DECIMAL(10,2) NULL,
                stock_quantity INT DEFAULT 0,
                stock_status ENUM('instock', 'outofstock', 'onbackorder') DEFAULT 'instock',
                category_id INT,
                image_url VARCHAR(500),
                gallery_images JSON,
                tags JSON,
                status ENUM('publish', 'draft', 'trash') DEFAULT 'publish',
                featured BOOLEAN DEFAULT FALSE,
                deleted_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_category (category_id),
                INDEX idx_status (status),
                INDEX idx_featured (featured),
                INDEX idx_sku (sku),
                FOREIGN KEY (category_id) REFERENCES product_categories(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

            "CREATE TABLE IF NOT EXISTS product_variations (
                id INT PRIMARY KEY AUTO_INCREMENT,
                product_id INT NOT NULL,
                sku VARCHAR(100) UNIQUE,
                price DECIMAL(10,2) NULL,
                sale_price DECIMAL(10,2) NULL,
                stock_quantity INT DEFAULT 0,
                attributes JSON NOT NULL,
                image_url VARCHAR(500),
                display_order INT DEFAULT 0,
                status ENUM('active', 'inactive') DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_product (product_id),
                INDEX idx_status (status),
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

            // 2. CARRITO
            "CREATE TABLE IF NOT EXISTS cart_items (
                id INT PRIMARY KEY AUTO_INCREMENT,
                session_id VARCHAR(255) NOT NULL,
                product_id INT NOT NULL,
                variation_id INT NULL,
                quantity INT NOT NULL,
                price DECIMAL(10,2) NOT NULL,
                added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_session (session_id),
                INDEX idx_product (product_id),
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
                FOREIGN KEY (variation_id) REFERENCES product_variations(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

            // 3. USUARIOS Y DIRECCIONES
            "CREATE TABLE IF NOT EXISTS users (
                id INT PRIMARY KEY AUTO_INCREMENT,
                email VARCHAR(255) UNIQUE NOT NULL,
                password_hash VARCHAR(255),
                first_name VARCHAR(100),
                last_name VARCHAR(100),
                phone VARCHAR(50),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_email (email)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

            "CREATE TABLE IF NOT EXISTS user_addresses (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT NOT NULL,
                type ENUM('billing', 'shipping') NOT NULL,
                first_name VARCHAR(100) NOT NULL,
                last_name VARCHAR(100) NOT NULL,
                address_1 VARCHAR(255) NOT NULL,
                address_2 VARCHAR(255),
                city VARCHAR(100) NOT NULL,
                state VARCHAR(100),
                postcode VARCHAR(20),
                country VARCHAR(2) DEFAULT 'CL',
                is_default BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_user (user_id),
                INDEX idx_type (type),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

            // 4. ÓRDENES
            "CREATE TABLE IF NOT EXISTS orders (
                id INT PRIMARY KEY AUTO_INCREMENT,
                order_number VARCHAR(50) UNIQUE NOT NULL,
                user_id INT NULL,
                customer_email VARCHAR(255) NOT NULL,
                customer_name VARCHAR(255),
                status ENUM('pending', 'processing', 'shipped', 'completed', 'cancelled', 'refunded') DEFAULT 'pending',
                currency VARCHAR(3) DEFAULT 'CLP',
                total_amount DECIMAL(10,2) NOT NULL,
                tax DECIMAL(10,2) DEFAULT 0,
                shipping_cost DECIMAL(10,2) DEFAULT 0,
                discount DECIMAL(10,2) DEFAULT 0,
                payment_method VARCHAR(50) DEFAULT 'flow',
                items_count INT DEFAULT 0,
                shipping_address JSON,
                billing_address JSON,
                notes TEXT,
                deleted_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_user (user_id),
                INDEX idx_status (status),
                INDEX idx_order_number (order_number),
                INDEX idx_created_at (created_at),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

            "CREATE TABLE IF NOT EXISTS order_items (
                id INT PRIMARY KEY AUTO_INCREMENT,
                order_id INT NOT NULL,
                product_id INT NOT NULL,
                variation_id INT NULL,
                product_name VARCHAR(255) NOT NULL,
                quantity INT NOT NULL,
                price DECIMAL(10,2) NOT NULL,
                line_total DECIMAL(10,2) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_order (order_id),
                INDEX idx_product (product_id),
                FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

            "CREATE TABLE IF NOT EXISTS order_status_history (
                id INT PRIMARY KEY AUTO_INCREMENT,
                order_id INT NOT NULL,
                old_status VARCHAR(50),
                new_status VARCHAR(50) NOT NULL,
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_order (order_id),
                INDEX idx_status (new_status),
                FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

            // 5. CUPONES
            "CREATE TABLE IF NOT EXISTS coupons (
                id INT PRIMARY KEY AUTO_INCREMENT,
                code VARCHAR(50) UNIQUE NOT NULL,
                description TEXT,
                discount_type ENUM('fixed', 'percentage') DEFAULT 'fixed',
                discount_value DECIMAL(10,2) NOT NULL,
                usage_limit INT NULL,
                used_count INT DEFAULT 0,
                min_spend DECIMAL(10,2) NULL,
                expiry_date DATE NULL,
                is_active BOOLEAN DEFAULT TRUE,
                deleted_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_code (code),
                INDEX idx_status (is_active),
                INDEX idx_expiry (expiry_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

            "CREATE TABLE IF NOT EXISTS order_coupons (
                id INT PRIMARY KEY AUTO_INCREMENT,
                order_id INT NOT NULL,
                coupon_id INT NOT NULL,
                coupon_code VARCHAR(50) NOT NULL,
                discount_amount DECIMAL(10,2) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_order (order_id),
                INDEX idx_coupon (coupon_id),
                FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
                FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE RESTRICT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

            // 6. CONFIGURACIÓN
            "CREATE TABLE IF NOT EXISTS ecommerce_settings (
                id INT PRIMARY KEY AUTO_INCREMENT,
                setting_key VARCHAR(100) UNIQUE NOT NULL,
                setting_value LONGTEXT,
                setting_group VARCHAR(50) DEFAULT 'general',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_key (setting_key),
                INDEX idx_group (setting_group)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

            // 7. ENVÍOS
            "CREATE TABLE IF NOT EXISTS shipping_zones (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                countries JSON NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

            "CREATE TABLE IF NOT EXISTS shipping_methods (
                id INT PRIMARY KEY AUTO_INCREMENT,
                zone_id INT NOT NULL,
                name VARCHAR(255) NOT NULL,
                description TEXT,
                method_type ENUM('flat_rate', 'free_shipping') DEFAULT 'flat_rate',
                cost DECIMAL(10,2) DEFAULT 0,
                min_amount DECIMAL(10,2) NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_zone (zone_id),
                FOREIGN KEY (zone_id) REFERENCES shipping_zones(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

            // 8. ANALYTICS
            "CREATE TABLE IF NOT EXISTS product_views (
                id INT PRIMARY KEY AUTO_INCREMENT,
                product_id INT NOT NULL,
                session_id VARCHAR(255),
                ip_address VARCHAR(45),
                viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_product (product_id),
                INDEX idx_viewed_at (viewed_at),
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

            "CREATE TABLE IF NOT EXISTS search_queries (
                id INT PRIMARY KEY AUTO_INCREMENT,
                query VARCHAR(500) NOT NULL,
                results_count INT DEFAULT 0,
                session_id VARCHAR(255),
                ip_address VARCHAR(45),
                searched_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_query (query),
                INDEX idx_searched_at (searched_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
        ];
    }

    /**
     * Ejecuta la inicialización de la BD
     */
    public static function initialize() {
        try {
            $pdo = getDBConnection();
            $results = [
                'success' => true,
                'created_tables' => [],
                'failed_tables' => [],
                'errors' => []
            ];

            $initializations = self::getInitializations();
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            foreach ($initializations as $sql) {
                try {
                    // Extraer nombre de tabla del SQL
                    preg_match('/CREATE TABLE IF NOT EXISTS (\w+)/i', $sql, $matches);
                    $tableName = $matches[1] ?? 'unknown';

                    $pdo->exec($sql);
                    $results['created_tables'][] = $tableName;
                } catch (Exception $e) {
                    // Si falla, registrar pero continuar
                    $results['failed_tables'][] = $tableName;
                    $results['errors'][] = $e->getMessage();
                }
            }

            // Log del evento
            logEvent('ecommerce_init', 'Inicialización de esquema e-commerce: ' . count($results['created_tables']) . ' tablas', $_SERVER['REMOTE_ADDR'] ?? '');

            return $results;
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'created_tables' => [],
                'failed_tables' => []
            ];
        }
    }

    /**
     * Verifica el estatus de las tablas
     */
    public static function checkStatus() {
        try {
            $pdo = getDBConnection();
            $tableNames = array_merge(
                ['product_categories', 'products', 'product_variations', 'cart_items'],
                ['users', 'user_addresses'],
                ['orders', 'order_items', 'order_status_history'],
                ['coupons', 'order_coupons'],
                ['ecommerce_settings', 'shipping_zones', 'shipping_methods'],
                ['product_views', 'search_queries']
            );

            $status = [];
            foreach ($tableNames as $table) {
                $exists = $pdo->query("SHOW TABLES LIKE '$table'")->rowCount() > 0;
                $status[$table] = $exists ? 'EXISTS' : 'MISSING';
            }

            $missing = array_filter($status, fn($v) => $v === 'MISSING');
            $total = count($tableNames);
            $created = $total - count($missing);

            return [
                'success' => true,
                'total_tables' => $total,
                'created_tables' => $created,
                'missing_tables' => count($missing),
                'details' => $status
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
?>
