<?php
/**
 * Katy & Woof - Definición del Esquema Ideal de Base de Datos v1.0
 * 
 * Este archivo centraliza la definición de todas las tablas esperadas
 * y sus columnas. Se usa para auditar y sincronizar la base de datos.
 * 
 * ESTRUCTURA:
 * [
 *     'table_name' => [
 *         'columns' => [
 *             'column_name' => 'TIPO_SQL'
 *         ]
 *     ]
 * ]
 */

return [
    // ============================================
    // TABLA: site_settings
    // Descripción: Configuración global del sitio
    // ============================================
    'site_settings' => [
        'columns' => [
            'id'              => 'INT AUTO_INCREMENT PRIMARY KEY',
            'setting_key'     => 'VARCHAR(255) NOT NULL UNIQUE',
            'setting_value'   => 'LONGTEXT',
            'updated_at'      => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
        ]
    ],

    // ============================================
    // TABLA: list_items
    // Descripción: Ítems dinámicos (categorías, estilos, etc)
    // ============================================
    'list_items' => [
        'columns' => [
            'id'           => 'INT AUTO_INCREMENT PRIMARY KEY',
            'list_key'     => 'VARCHAR(100) NOT NULL',
            'item_value'   => 'VARCHAR(255) NOT NULL',
            'item_order'   => 'INT DEFAULT 0',
            'created_at'   => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            'updated_at'   => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
        ]
    ],

    // ============================================
    // TABLA: portfolio_items
    // Descripción: Proyectos del portafolios
    // ============================================
    'portfolio_items' => [
        'columns' => [
            'id'              => 'INT AUTO_INCREMENT PRIMARY KEY',
            'title'           => 'VARCHAR(255) NOT NULL',
            'description'     => 'LONGTEXT',
            'image_url'       => 'VARCHAR(500)',
            'art_style'       => 'VARCHAR(100)',
            'project_date'    => 'DATE',
            'featured'        => 'BOOLEAN DEFAULT FALSE',
            'display_order'   => 'INT DEFAULT 0',
            'created_at'      => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            'updated_at'      => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
        ]
    ],

    // ============================================
    // TABLA: services
    // Descripción: Servicios ofrecidos
    // ============================================
    'services' => [
        'columns' => [
            'id'              => 'INT AUTO_INCREMENT PRIMARY KEY',
            'title'           => 'VARCHAR(255) NOT NULL',
            'description'     => 'LONGTEXT',
            'icon_name'       => 'VARCHAR(100)',
            'category'        => 'VARCHAR(100)',
            'price'           => 'DECIMAL(10, 2)',
            'featured'        => 'BOOLEAN DEFAULT FALSE',
            'display_order'   => 'INT DEFAULT 0',
            'created_at'      => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            'updated_at'      => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
        ]
    ],

    // ============================================
    // TABLA: blog_posts
    // Descripción: Artículos del blog
    // ============================================
    'blog_posts' => [
        'columns' => [
            'id'              => 'INT AUTO_INCREMENT PRIMARY KEY',
            'title'           => 'VARCHAR(255) NOT NULL',
            'slug'            => 'VARCHAR(255) UNIQUE',
            'content'         => 'LONGTEXT',
            'excerpt'         => 'VARCHAR(500)',
            'featured_image'  => 'VARCHAR(500)',
            'category'        => 'VARCHAR(100)',
            'author'          => 'VARCHAR(100)',
            'views_count'     => 'INT DEFAULT 0',
            'published'       => 'BOOLEAN DEFAULT FALSE',
            'published_at'    => 'DATETIME',
            'created_at'      => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            'updated_at'      => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
        ]
    ],

    // ============================================
    // TABLA: process_steps
    // Descripción: Pasos del proceso creativo
    // ============================================
    'process_steps' => [
        'columns' => [
            'id'              => 'INT AUTO_INCREMENT PRIMARY KEY',
            'title'           => 'VARCHAR(255) NOT NULL',
            'description'     => 'LONGTEXT',
            'step_number'     => 'INT NOT NULL',
            'icon_name'       => 'VARCHAR(100)',
            'created_at'      => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            'updated_at'      => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
        ]
    ],

    // ============================================
    // TABLA: logs
    // Descripción: Registro de eventos del sistema
    // ============================================
    'logs' => [
        'columns' => [
            'id'              => 'INT AUTO_INCREMENT PRIMARY KEY',
            'event_type'      => 'VARCHAR(100) NOT NULL',
            'message'         => 'LONGTEXT',
            'ip_address'      => 'VARCHAR(45)',
            'user_agent'      => 'VARCHAR(500)',
            'created_at'      => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
        ]
    ],

    // ============================================
    // TABLAS E-COMMERCE
    // ============================================
    'product_categories' => [
        'columns' => [
            'id'              => 'INT AUTO_INCREMENT PRIMARY KEY',
            'name'            => 'VARCHAR(255) NOT NULL',
            'slug'            => 'VARCHAR(255) UNIQUE NOT NULL',
            'description'     => 'TEXT',
            'parent_id'       => 'INT NULL',
            'image_url'       => 'VARCHAR(500)',
            'display_order'   => 'INT DEFAULT 0',
            'status'          => "ENUM('active', 'inactive') DEFAULT 'active'",
            'created_at'      => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            'updated_at'      => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
        ]
    ],

    'products' => [
        'columns' => [
            'id'                => 'INT AUTO_INCREMENT PRIMARY KEY',
            'name'              => 'VARCHAR(255) NOT NULL',
            'slug'              => 'VARCHAR(255) UNIQUE NOT NULL',
            'description'       => 'LONGTEXT',
            'short_description' => 'TEXT',
            'sku'               => 'VARCHAR(100) UNIQUE',
            'price'             => 'DECIMAL(10,2) NOT NULL',
            'sale_price'        => 'DECIMAL(10,2) NULL',
            'stock_quantity'    => 'INT DEFAULT 0',
            'stock_status'      => "ENUM('instock', 'outofstock', 'onbackorder') DEFAULT 'instock'",
            'weight'            => 'DECIMAL(5,2) NULL',
            'dimensions'        => 'VARCHAR(100)',
            'category_id'       => 'INT',
            'image_url'         => 'VARCHAR(500)',
            'gallery_images'    => 'JSON',
            'attributes'        => 'JSON',
            'tags'              => 'JSON',
            'seo_title'         => 'VARCHAR(255)',
            'seo_description'   => 'TEXT',
            'status'            => "ENUM('publish', 'draft', 'trash') DEFAULT 'publish'",
            'featured'          => 'BOOLEAN DEFAULT FALSE',
            'virtual'           => 'BOOLEAN DEFAULT FALSE',
            'downloadable'      => 'BOOLEAN DEFAULT FALSE',
            'created_at'        => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            'updated_at'        => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
        ]
    ],

    'product_variations' => [
        'columns' => [
            'id'              => 'INT AUTO_INCREMENT PRIMARY KEY',
            'product_id'      => 'INT NOT NULL',
            'sku'             => 'VARCHAR(100) UNIQUE',
            'price'           => 'DECIMAL(10,2) NULL',
            'sale_price'      => 'DECIMAL(10,2) NULL',
            'stock_quantity'  => 'INT DEFAULT 0',
            'stock_status'    => "ENUM('instock', 'outofstock', 'onbackorder') DEFAULT 'instock'",
            'attributes'      => 'JSON NOT NULL',
            'image_url'       => 'VARCHAR(500)',
            'display_order'   => 'INT DEFAULT 0',
            'status'          => "ENUM('active', 'inactive') DEFAULT 'active'",
            'created_at'      => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            'updated_at'      => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
        ]
    ],

    'cart_items' => [
        'columns' => [
            'id'           => 'INT AUTO_INCREMENT PRIMARY KEY',
            'session_id'   => 'VARCHAR(255) NOT NULL',
            'user_id'      => 'INT NULL',
            'product_id'   => 'INT NOT NULL',
            'variation_id' => 'INT NULL',
            'quantity'     => 'INT NOT NULL',
            'price'        => 'DECIMAL(10,2) NOT NULL',
            'line_total'   => 'DECIMAL(10,2) GENERATED ALWAYS AS (price * quantity) STORED',
            'added_at'     => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            'updated_at'   => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
        ]
    ],

    'user_addresses' => [
        'columns' => [
            'id'         => 'INT AUTO_INCREMENT PRIMARY KEY',
            'user_id'    => 'INT NOT NULL',
            'type'       => "ENUM('billing', 'shipping') NOT NULL",
            'first_name' => 'VARCHAR(100) NOT NULL',
            'last_name'  => 'VARCHAR(100) NOT NULL',
            'company'    => 'VARCHAR(100)',
            'address_1'  => 'VARCHAR(255) NOT NULL',
            'address_2'  => 'VARCHAR(255)',
            'city'       => 'VARCHAR(100) NOT NULL',
            'state'      => 'VARCHAR(100)',
            'postcode'   => 'VARCHAR(20)',
            'country'    => "VARCHAR(2) DEFAULT 'CL'",
            'phone'      => 'VARCHAR(50)',
            'email'      => 'VARCHAR(255)',
            'is_default' => 'BOOLEAN DEFAULT FALSE',
            'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
        ]
    ],

    'orders' => [
        'columns' => [
            'id'              => 'INT AUTO_INCREMENT PRIMARY KEY',
            'order_number'    => 'VARCHAR(50) UNIQUE NOT NULL',
            'user_id'         => 'INT NULL',
            'customer_email'  => 'VARCHAR(255) NOT NULL',
            'customer_data'   => 'JSON NOT NULL',
            'status'          => "ENUM('pending', 'processing', 'shipped', 'completed', 'cancelled', 'refunded', 'failed') DEFAULT 'pending'",
            'currency'        => "VARCHAR(3) DEFAULT 'CLP'",
            'subtotal'        => 'DECIMAL(10,2) NOT NULL',
            'tax_total'       => 'DECIMAL(10,2) DEFAULT 0',
            'shipping_total'  => 'DECIMAL(10,2) DEFAULT 0',
            'discount_total'  => 'DECIMAL(10,2) DEFAULT 0',
            'total'           => 'DECIMAL(10,2) NOT NULL',
            'payment_method'  => "VARCHAR(50) DEFAULT 'flow'",
            'payment_status'  => "ENUM('pending', 'paid', 'failed', 'refunded', 'cancelled') DEFAULT 'pending'",
            'transaction_id'  => 'VARCHAR(255) NULL',
            'flow_order_id'   => 'VARCHAR(255) NULL',
            'shipping_method' => 'VARCHAR(100)',
            'shipping_address'=> 'JSON',
            'billing_address' => 'JSON',
            'order_notes'     => 'TEXT',
            'customer_ip'     => 'VARCHAR(45)',
            'user_agent'      => 'TEXT',
            'created_at'      => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            'updated_at'      => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
        ]
    ],

    'order_items' => [
        'columns' => [
            'id'             => 'INT AUTO_INCREMENT PRIMARY KEY',
            'order_id'       => 'INT NOT NULL',
            'product_id'     => 'INT NOT NULL',
            'variation_id'   => 'INT NULL',
            'product_name'   => 'VARCHAR(255) NOT NULL',
            'product_sku'    => 'VARCHAR(100)',
            'quantity'       => 'INT NOT NULL',
            'price'          => 'DECIMAL(10,2) NOT NULL',
            'line_total'     => 'DECIMAL(10,2) NOT NULL',
            'line_tax'       => 'DECIMAL(10,2) DEFAULT 0',
            'variation_data' => 'JSON',
            'created_at'     => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
        ]
    ],

    'order_status_history' => [
        'columns' => [
            'id'         => 'INT AUTO_INCREMENT PRIMARY KEY',
            'order_id'   => 'INT NOT NULL',
            'old_status' => "ENUM('pending', 'processing', 'shipped', 'completed', 'cancelled', 'refunded', 'failed')",
            'new_status' => "ENUM('pending', 'processing', 'shipped', 'completed', 'cancelled', 'refunded', 'failed') NOT NULL",
            'notes'      => 'TEXT',
            'changed_by' => 'INT NULL',
            'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
        ]
    ],

    'coupons' => [
        'columns' => [
            'id'                   => 'INT AUTO_INCREMENT PRIMARY KEY',
            'code'                 => 'VARCHAR(50) UNIQUE NOT NULL',
            'description'          => 'TEXT',
            'discount_type'        => "ENUM('fixed', 'percentage') DEFAULT 'fixed'",
            'discount_value'       => 'DECIMAL(10,2) NOT NULL',
            'usage_limit'          => 'INT NULL',
            'usage_count'          => 'INT DEFAULT 0',
            'expiry_date'          => 'DATE NULL',
            'minimum_amount'       => 'DECIMAL(10,2) NULL',
            'maximum_amount'       => 'DECIMAL(10,2) NULL',
            'product_ids'          => 'JSON',
            'category_ids'         => 'JSON',
            'exclude_product_ids'  => 'JSON',
            'exclude_category_ids' => 'JSON',
            'individual_use'       => 'BOOLEAN DEFAULT TRUE',
            'status'               => "ENUM('active', 'inactive') DEFAULT 'active'",
            'created_by'           => 'INT NULL',
            'created_at'           => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            'updated_at'           => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
        ]
    ],

    'order_coupons' => [
        'columns' => [
            'id'              => 'INT AUTO_INCREMENT PRIMARY KEY',
            'order_id'        => 'INT NOT NULL',
            'coupon_id'       => 'INT NOT NULL',
            'coupon_code'     => 'VARCHAR(50) NOT NULL',
            'discount_amount' => 'DECIMAL(10,2) NOT NULL',
            'created_at'      => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
        ]
    ],

    'ecommerce_settings' => [
        'columns' => [
            'id'            => 'INT AUTO_INCREMENT PRIMARY KEY',
            'setting_key'   => 'VARCHAR(100) UNIQUE NOT NULL',
            'setting_value' => 'LONGTEXT',
            'setting_group' => "VARCHAR(50) DEFAULT 'general'",
            'setting_type'  => "ENUM('string', 'number', 'boolean', 'json', 'array') DEFAULT 'string'",
            'is_public'     => 'BOOLEAN DEFAULT FALSE',
            'created_at'    => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            'updated_at'    => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
        ]
    ],

    'shipping_zones' => [
        'columns' => [
            'id'         => 'INT AUTO_INCREMENT PRIMARY KEY',
            'name'       => 'VARCHAR(255) NOT NULL',
            'countries'  => 'JSON NOT NULL',
            'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
        ]
    ],

    'shipping_methods' => [
        'columns' => [
            'id'          => 'INT AUTO_INCREMENT PRIMARY KEY',
            'zone_id'     => 'INT NOT NULL',
            'name'        => 'VARCHAR(255) NOT NULL',
            'description' => 'TEXT',
            'method_type' => "ENUM('flat_rate', 'free_shipping', 'local_pickup') DEFAULT 'flat_rate'",
            'cost'        => 'DECIMAL(10,2) DEFAULT 0',
            'min_amount'  => 'DECIMAL(10,2) NULL',
            'max_weight'  => 'DECIMAL(5,2) NULL',
            'status'      => "ENUM('active', 'inactive') DEFAULT 'active'",
            'created_at'  => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            'updated_at'  => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
        ]
    ],

    'product_views' => [
        'columns' => [
            'id'         => 'INT AUTO_INCREMENT PRIMARY KEY',
            'product_id' => 'INT NOT NULL',
            'user_id'    => 'INT NULL',
            'session_id' => 'VARCHAR(255)',
            'ip_address' => 'VARCHAR(45)',
            'user_agent' => 'TEXT',
            'referrer'   => 'VARCHAR(500)',
            'viewed_at'  => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
        ]
    ],

    'search_queries' => [
        'columns' => [
            'id'            => 'INT AUTO_INCREMENT PRIMARY KEY',
            'query'         => 'VARCHAR(255) NOT NULL',
            'results_count' => 'INT DEFAULT 0',
            'user_id'       => 'INT NULL',
            'session_id'    => 'VARCHAR(255)',
            'ip_address'    => 'VARCHAR(45)',
            'user_agent'    => 'TEXT',
            'searched_at'   => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
        ]
    ]
];
