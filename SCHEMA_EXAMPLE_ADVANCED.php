<?php
/**
 * EJEMPLO AVANZADO: Esquema Realista para Katy & Woof
 * 
 * Este archivo muestra un ejemplo más completo del sistema
 * adaptado al tipo de negocio de Katy & Woof (Creative Studio)
 * 
 * Basado en las tablas detectadas en el proyecto.
 */

return [
    // ========================================
    // IDENTIDAD Y CONFIGURACIÓN
    // ========================================
    
    'site_settings' => [
        'description' => 'Configuración global del sitio',
        'columns' => [
            'id'              => 'INT AUTO_INCREMENT PRIMARY KEY',
            'setting_key'     => 'VARCHAR(255) NOT NULL UNIQUE',
            'setting_value'   => 'LONGTEXT',
            'setting_type'    => 'VARCHAR(50) DEFAULT "string"', // string, number, boolean, json
            'restricted'      => 'BOOLEAN DEFAULT FALSE',         // Si requiere auth para cambiar
            'updated_at'      => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
        ]
    ],

    // ========================================
    // SECCIONES DINÁMICAS / TAXONOMÍA
    // ========================================
    
    'list_items' => [
        'description' => 'Elementos dinámicos: estilos de arte, categorías de servicios, etc',
        'columns' => [
            'id'           => 'INT AUTO_INCREMENT PRIMARY KEY',
            'list_key'     => 'VARCHAR(100) NOT NULL',
            'item_value'   => 'VARCHAR(255) NOT NULL',
            'item_order'   => 'INT DEFAULT 0',
            'icon_url'     => 'VARCHAR(500)',
            'description'  => 'TEXT',
            'active'       => 'BOOLEAN DEFAULT TRUE',
            'created_at'   => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            'updated_at'   => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
        ]
    ],

    // ========================================
    // PORTAFOLIO / GALERÍA
    // ========================================
    
    'portfolio_items' => [
        'description' => 'Proyectos/trabajos del portafolio',
        'columns' => [
            'id'              => 'INT AUTO_INCREMENT PRIMARY KEY',
            'title'           => 'VARCHAR(255) NOT NULL',
            'slug'            => 'VARCHAR(255) UNIQUE',
            'description'     => 'LONGTEXT',
            'detailed_info'   => 'LONGTEXT',
            'image_url'       => 'VARCHAR(500)',
            'thumbnail_url'   => 'VARCHAR(500)',
            'gallery_images'  => 'LONGTEXT',  // JSON array de URLs
            'art_style'       => 'VARCHAR(100)',
            'technique'       => 'VARCHAR(100)',
            'project_date'    => 'DATE',
            'featured'        => 'BOOLEAN DEFAULT FALSE',
            'display_order'   => 'INT DEFAULT 0',
            'views_count'     => 'INT DEFAULT 0',
            'created_at'      => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            'updated_at'      => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
        ]
    ],

    // ========================================
    // SERVICIOS
    // ========================================
    
    'services' => [
        'description' => 'Servicios que ofrece el studio',
        'columns' => [
            'id'                => 'INT AUTO_INCREMENT PRIMARY KEY',
            'title'             => 'VARCHAR(255) NOT NULL',
            'slug'              => 'VARCHAR(255) UNIQUE',
            'short_description' => 'VARCHAR(500)',
            'description'       => 'LONGTEXT',
            'icon_name'         => 'VARCHAR(100)',
            'main_image_url'    => 'VARCHAR(500)',
            'category'          => 'VARCHAR(100)',
            'price'             => 'DECIMAL(10, 2)',
            'currency'          => 'VARCHAR(3) DEFAULT "EUR"',
            'duration'          => 'VARCHAR(100)',  // ej: "2-4 semanas"
            'featured'          => 'BOOLEAN DEFAULT FALSE',
            'display_order'     => 'INT DEFAULT 0',
            'available'         => 'BOOLEAN DEFAULT TRUE',
            'created_at'        => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            'updated_at'        => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
        ]
    ],

    // ========================================
    // BLOG / ARTÍCULOS
    // ========================================
    
    'blog_posts' => [
        'description' => 'Artículos y noticias del blog',
        'columns' => [
            'id'              => 'INT AUTO_INCREMENT PRIMARY KEY',
            'title'           => 'VARCHAR(255) NOT NULL',
            'slug'            => 'VARCHAR(255) UNIQUE',
            'content'         => 'LONGTEXT',
            'excerpt'         => 'VARCHAR(500)',
            'featured_image'  => 'VARCHAR(500)',
            'thumbnail_image' => 'VARCHAR(500)',
            'category'        => 'VARCHAR(100)',
            'author'          => 'VARCHAR(100)',
            'meta_description'=> 'VARCHAR(160)',
            'tags'            => 'VARCHAR(500)',  // Separadas por comas
            'views_count'     => 'INT DEFAULT 0',
            'published'       => 'BOOLEAN DEFAULT FALSE',
            'featured'        => 'BOOLEAN DEFAULT FALSE',
            'published_at'    => 'DATETIME',
            'created_at'      => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            'updated_at'      => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
        ]
    ],

    // ========================================
    // PROCESO CREATIVO
    // ========================================
    
    'process_steps' => [
        'description' => 'Pasos del proceso creativo/metodología',
        'columns' => [
            'id'              => 'INT AUTO_INCREMENT PRIMARY KEY',
            'title'           => 'VARCHAR(255) NOT NULL',
            'description'     => 'LONGTEXT',
            'step_number'     => 'INT NOT NULL UNIQUE',
            'icon_name'       => 'VARCHAR(100)',
            'icon_color'      => 'VARCHAR(20)',
            'duration'        => 'VARCHAR(100)',
            'deliverables'    => 'TEXT',
            'created_at'      => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            'updated_at'      => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
        ]
    ],

    // ========================================
    // CONTACTOS / INQUIRIES
    // ========================================
    
    'contact_inquiries' => [
        'description' => 'Mensajes de contacto recibidos',
        'columns' => [
            'id'              => 'INT AUTO_INCREMENT PRIMARY KEY',
            'name'            => 'VARCHAR(255) NOT NULL',
            'email'           => 'VARCHAR(255) NOT NULL',
            'phone'           => 'VARCHAR(20)',
            'service_type'    => 'VARCHAR(100)',
            'message'         => 'LONGTEXT NOT NULL',
            'budget'          => 'VARCHAR(100)',
            'timeline'        => 'VARCHAR(100)',
            'attachments'     => 'LONGTEXT',
            'status'          => 'VARCHAR(50) DEFAULT "new"',  // new, read, responded, closed
            'assigned_to'     => 'VARCHAR(100)',
            'notes'           => 'LONGTEXT',
            'ip_address'      => 'VARCHAR(45)',
            'user_agent'      => 'VARCHAR(500)',
            'created_at'      => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            'updated_at'      => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
        ]
    ],

    // ========================================
    // NEWSLETTER
    // ========================================
    
    'newsletter_subscribers' => [
        'description' => 'Suscriptores a newsletter',
        'columns' => [
            'id'              => 'INT AUTO_INCREMENT PRIMARY KEY',
            'email'           => 'VARCHAR(255) NOT NULL UNIQUE',
            'name'            => 'VARCHAR(255)',
            'status'          => 'VARCHAR(50) DEFAULT "pending"',  // pending, confirmed, unsubscribed
            'confirmed_at'    => 'DATETIME',
            'ip_address'      => 'VARCHAR(45)',
            'created_at'      => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            'updated_at'      => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
        ]
    ],

    // ========================================
    // SISTEMA / LOGS
    // ========================================
    
    'logs' => [
        'description' => 'Registro de eventos del sistema',
        'columns' => [
            'id'              => 'INT AUTO_INCREMENT PRIMARY KEY',
            'event_type'      => 'VARCHAR(100) NOT NULL',  // database_sync, login, file_upload, etc
            'message'         => 'LONGTEXT',
            'data'            => 'LONGTEXT',  // JSON con detalles adicionales
            'ip_address'      => 'VARCHAR(45)',
            'user_agent'      => 'VARCHAR(500)',
            'severity'        => 'VARCHAR(20) DEFAULT "info"',  // debug, info, warning, error
            'created_at'      => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
        ]
    ],

    'audit_trail' => [
        'description' => 'Auditoría de cambios en contenido crítico',
        'columns' => [
            'id'              => 'INT AUTO_INCREMENT PRIMARY KEY',
            'table_name'      => 'VARCHAR(100) NOT NULL',
            'record_id'       => 'INT',
            'action'          => 'VARCHAR(50)',  // INSERT, UPDATE, DELETE
            'old_values'      => 'LONGTEXT',  // JSON
            'new_values'      => 'LONGTEXT',  // JSON
            'changed_by'      => 'VARCHAR(100)',
            'reason'          => 'TEXT',
            'created_at'      => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
        ]
    ]
];

/**
 * NOTAS:
 * 
 * 1. Cada tabla tiene un campo 'description' para documentar su propósito
 * 
 * 2. Las columnas timestamp siguos una convención:
 *    - created_at: Se setea al crear el registro (NUNCA cambia)
 *    - updated_at: Se actualiza cada vez que se modifica
 * 
 * 3. Los slugs son únicos y se usan para URLs legibles
 * 
 * 4. Los campos order/step_number permiten ordenar sin necesidad de cambiar IDs
 * 
 * 5. Los campos "active", "available", "published" controlan visibilidad
 * 
 * 6. Los campos *_url apunta a archivos/images en el servidor
 * 
 * 7. Los campos tipo JSON (ej gallery_images, tags) se guardan como LONGTEXT
 *    y se manipulan en PHP con json_encode/json_decode
 * 
 * PARA AGREGAR INDICES (próxima versión):
 * 
 * 'portfolio_items' => [
 *     'columns' => [...],
 *     'indexes' => [
 *         'slug' => 'UNIQUE',
 *         'art_style' => 'INDEX',
 *         'created_at' => 'INDEX',
 *         'featured_created' => 'INDEX(featured, created_at)'  // Índice compuesto
 *     ]
 * ]
 */
