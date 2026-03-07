-- =========================================
-- Katy & Woof E-commerce Database Schema
-- Fase 1: Base de Datos y Estructuras
-- Fecha: Marzo 2026
-- =========================================

-- Crear base de datos si no existe
CREATE DATABASE IF NOT EXISTS katywoof_ecommerce
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE katywoof_ecommerce;

-- =========================================
-- 1. TABLAS DE PRODUCTOS Y CATÁLOGOS
-- =========================================

-- Categorías de productos (jerárquicas)
CREATE TABLE product_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT,
    parent_id INT NULL,
    image_url VARCHAR(500),
    display_order INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_parent (parent_id),
    INDEX idx_status (status),
    INDEX idx_display_order (display_order),
    FOREIGN KEY (parent_id) REFERENCES product_categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Productos principales
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description LONGTEXT,
    short_description TEXT,
    sku VARCHAR(100) UNIQUE,
    price DECIMAL(10,2) NOT NULL CHECK (price >= 0),
    sale_price DECIMAL(10,2) NULL CHECK (sale_price >= 0),
    stock_quantity INT DEFAULT 0 CHECK (stock_quantity >= 0),
    stock_status ENUM('instock', 'outofstock', 'onbackorder') DEFAULT 'instock',
    weight DECIMAL(5,2) NULL CHECK (weight > 0),
    dimensions VARCHAR(100), -- formato: "largo x ancho x alto" en cm
    category_id INT,
    image_url VARCHAR(500),
    gallery_images JSON, -- array de URLs de imágenes adicionales
    attributes JSON, -- atributos variables como color, tamaño, etc.
    tags JSON, -- array de tags para búsqueda
    seo_title VARCHAR(255),
    seo_description TEXT,
    status ENUM('publish', 'draft', 'trash') DEFAULT 'publish',
    featured BOOLEAN DEFAULT FALSE,
    virtual BOOLEAN DEFAULT FALSE, -- producto digital
    downloadable BOOLEAN DEFAULT FALSE, -- producto descargable
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category_id),
    INDEX idx_status (status),
    INDEX idx_featured (featured),
    INDEX idx_sku (sku),
    INDEX idx_price (price),
    INDEX idx_sale_price (sale_price),
    FULLTEXT idx_search (name, description, short_description),
    FOREIGN KEY (category_id) REFERENCES product_categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Variaciones de productos (para productos con opciones)
CREATE TABLE product_variations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    sku VARCHAR(100) UNIQUE,
    price DECIMAL(10,2) NULL,
    sale_price DECIMAL(10,2) NULL,
    stock_quantity INT DEFAULT 0,
    stock_status ENUM('instock', 'outofstock', 'onbackorder') DEFAULT 'instock',
    attributes JSON NOT NULL, -- ej: {"color": "rojo", "talla": "M"}
    image_url VARCHAR(500),
    display_order INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_product (product_id),
    INDEX idx_status (status),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================
-- 2. TABLAS DE CARRITO Y CHECKOUT
-- =========================================

-- Carrito de compras (por sesión/usuario)
CREATE TABLE cart_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    session_id VARCHAR(255) NOT NULL, -- para usuarios no logueados
    user_id INT NULL, -- para usuarios logueados
    product_id INT NOT NULL,
    variation_id INT NULL, -- si es una variación específica
    quantity INT NOT NULL CHECK (quantity > 0),
    price DECIMAL(10,2) NOT NULL, -- precio al momento de agregar
    line_total DECIMAL(10,2) GENERATED ALWAYS AS (price * quantity) STORED,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_session (session_id),
    INDEX idx_user (user_id),
    INDEX idx_product (product_id),
    INDEX idx_variation (variation_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (variation_id) REFERENCES product_variations(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Direcciones de envío/ facturación guardadas
CREATE TABLE user_addresses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    type ENUM('billing', 'shipping') NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    company VARCHAR(100),
    address_1 VARCHAR(255) NOT NULL,
    address_2 VARCHAR(255),
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100), -- región/provincia
    postcode VARCHAR(20),
    country VARCHAR(2) DEFAULT 'CL',
    phone VARCHAR(50),
    email VARCHAR(255),
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_type (type),
    INDEX idx_default (is_default),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================
-- 3. TABLAS DE PEDIDOS Y TRANSACCIONES
-- =========================================

-- Pedidos principales
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    user_id INT NULL, -- puede ser NULL para pedidos de invitados
    customer_email VARCHAR(255) NOT NULL,
    customer_data JSON NOT NULL, -- datos del cliente (nombre, teléfono, etc.)
    status ENUM('pending', 'processing', 'shipped', 'completed', 'cancelled', 'refunded', 'failed') DEFAULT 'pending',
    currency VARCHAR(3) DEFAULT 'CLP',
    subtotal DECIMAL(10,2) NOT NULL CHECK (subtotal >= 0),
    tax_total DECIMAL(10,2) DEFAULT 0 CHECK (tax_total >= 0),
    shipping_total DECIMAL(10,2) DEFAULT 0 CHECK (shipping_total >= 0),
    discount_total DECIMAL(10,2) DEFAULT 0 CHECK (discount_total >= 0),
    total DECIMAL(10,2) NOT NULL CHECK (total >= 0),
    payment_method VARCHAR(50) DEFAULT 'flow',
    payment_status ENUM('pending', 'paid', 'failed', 'refunded', 'cancelled') DEFAULT 'pending',
    transaction_id VARCHAR(255) NULL, -- ID de transacción de Flow
    flow_order_id VARCHAR(255) NULL, -- Order ID de Flow
    shipping_method VARCHAR(100),
    shipping_address JSON, -- dirección de envío
    billing_address JSON, -- dirección de facturación
    order_notes TEXT,
    customer_ip VARCHAR(45), -- IPv4 o IPv6
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_payment_status (payment_status),
    INDEX idx_order_number (order_number),
    INDEX idx_created_at (created_at),
    INDEX idx_total (total)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Items de cada pedido
CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    variation_id INT NULL,
    product_name VARCHAR(255) NOT NULL,
    product_sku VARCHAR(100),
    quantity INT NOT NULL CHECK (quantity > 0),
    price DECIMAL(10,2) NOT NULL CHECK (price >= 0), -- precio unitario al momento de la compra
    line_total DECIMAL(10,2) NOT NULL CHECK (line_total >= 0),
    line_tax DECIMAL(10,2) DEFAULT 0,
    variation_data JSON, -- datos de la variación seleccionada
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_order (order_id),
    INDEX idx_product (product_id),
    INDEX idx_variation (variation_id),
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
    FOREIGN KEY (variation_id) REFERENCES product_variations(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Historial de estados de pedidos
CREATE TABLE order_status_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    old_status ENUM('pending', 'processing', 'shipped', 'completed', 'cancelled', 'refunded', 'failed'),
    new_status ENUM('pending', 'processing', 'shipped', 'completed', 'cancelled', 'refunded', 'failed') NOT NULL,
    notes TEXT,
    changed_by INT NULL, -- ID del usuario/admin que cambió el estado
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_order (order_id),
    INDEX idx_status (new_status),
    INDEX idx_changed_by (changed_by),
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================
-- 4. TABLAS DE CUPONES Y DESCUENTOS
-- =========================================

-- Cupones de descuento
CREATE TABLE coupons (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    discount_type ENUM('fixed', 'percentage') DEFAULT 'fixed',
    discount_value DECIMAL(10,2) NOT NULL CHECK (discount_value > 0),
    usage_limit INT NULL CHECK (usage_limit > 0), -- NULL = ilimitado
    usage_count INT DEFAULT 0 CHECK (usage_count >= 0),
    expiry_date DATE NULL,
    minimum_amount DECIMAL(10,2) NULL CHECK (minimum_amount >= 0),
    maximum_amount DECIMAL(10,2) NULL CHECK (maximum_amount >= 0),
    product_ids JSON, -- IDs de productos específicos (NULL = todos)
    category_ids JSON, -- IDs de categorías específicas (NULL = todas)
    exclude_product_ids JSON, -- productos excluidos
    exclude_category_ids JSON, -- categorías excluidas
    individual_use BOOLEAN DEFAULT TRUE, -- no combinable con otros cupones
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_code (code),
    INDEX idx_status (status),
    INDEX idx_expiry (expiry_date),
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Uso de cupones en pedidos
CREATE TABLE order_coupons (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    coupon_id INT NOT NULL,
    coupon_code VARCHAR(50) NOT NULL,
    discount_amount DECIMAL(10,2) NOT NULL CHECK (discount_amount >= 0),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_order (order_id),
    INDEX idx_coupon (coupon_id),
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================
-- 5. TABLAS DE CONFIGURACIÓN E-COMMERCE
-- =========================================

-- Configuraciones generales del e-commerce
CREATE TABLE ecommerce_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value LONGTEXT,
    setting_group VARCHAR(50) DEFAULT 'general',
    setting_type ENUM('string', 'number', 'boolean', 'json', 'array') DEFAULT 'string',
    is_public BOOLEAN DEFAULT FALSE, -- si puede ser accedido desde frontend
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_key (setting_key),
    INDEX idx_group (setting_group),
    INDEX idx_public (is_public)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Zonas de envío
CREATE TABLE shipping_zones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    countries JSON NOT NULL, -- array de códigos de país
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Métodos de envío por zona
CREATE TABLE shipping_methods (
    id INT PRIMARY KEY AUTO_INCREMENT,
    zone_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    method_type ENUM('flat_rate', 'free_shipping', 'local_pickup') DEFAULT 'flat_rate',
    cost DECIMAL(10,2) DEFAULT 0,
    min_amount DECIMAL(10,2) NULL, -- monto mínimo para envío gratis
    max_weight DECIMAL(5,2) NULL, -- peso máximo permitido
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_zone (zone_id),
    INDEX idx_status (status),
    FOREIGN KEY (zone_id) REFERENCES shipping_zones(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================
-- 6. TABLAS DE ANALYTICS Y REPORTES
-- =========================================

-- Visitas a productos (para analytics)
CREATE TABLE product_views (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    user_id INT NULL,
    session_id VARCHAR(255),
    ip_address VARCHAR(45),
    user_agent TEXT,
    referrer VARCHAR(500),
    viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_product (product_id),
    INDEX idx_user (user_id),
    INDEX idx_session (session_id),
    INDEX idx_viewed_at (viewed_at),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Búsquedas realizadas (para mejorar UX)
CREATE TABLE search_queries (
    id INT PRIMARY KEY AUTO_INCREMENT,
    query VARCHAR(255) NOT NULL,
    results_count INT DEFAULT 0,
    user_id INT NULL,
    session_id VARCHAR(255),
    ip_address VARCHAR(45),
    user_agent TEXT,
    searched_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_query (query),
    INDEX idx_user (user_id),
    INDEX idx_session (session_id),
    INDEX idx_searched_at (searched_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================
-- 7. EXTENSIÓN DE TABLAS EXISTENTES
-- =========================================

-- Agregar campos de e-commerce a la tabla users existente
ALTER TABLE users
ADD COLUMN billing_address JSON AFTER phone,
ADD COLUMN shipping_address JSON AFTER billing_address,
ADD COLUMN marketing_consent BOOLEAN DEFAULT FALSE AFTER shipping_address,
ADD COLUMN ecommerce_data JSON AFTER marketing_consent, -- datos adicionales de e-commerce
ADD COLUMN last_order_date TIMESTAMP NULL AFTER ecommerce_data,
ADD COLUMN total_orders INT DEFAULT 0 AFTER last_order_date,
ADD COLUMN total_spent DECIMAL(10,2) DEFAULT 0 AFTER total_orders;

-- =========================================
-- 8. DATOS INICIALES Y CONFIGURACIÓN
-- =========================================

-- Insertar configuraciones iniciales
INSERT INTO ecommerce_settings (setting_key, setting_value, setting_group, setting_type) VALUES
('store_name', 'Katy & Woof Creative Studio', 'general', 'string'),
('store_email', 'ventas@katywoof.com', 'general', 'string'),
('currency', 'CLP', 'general', 'string'),
('currency_symbol', '$', 'general', 'string'),
('price_decimals', '0', 'general', 'number'),
('tax_rate', '19', 'tax', 'number'), -- IVA Chile
('tax_label', 'IVA', 'tax', 'string'),
('free_shipping_threshold', '50000', 'shipping', 'number'), -- $50.000 CLP
('store_country', 'CL', 'general', 'string'),
('store_city', 'Santiago', 'general', 'string'),
('enable_coupons', 'true', 'coupons', 'boolean'),
('enable_reviews', 'false', 'reviews', 'boolean'),
('enable_wishlist', 'true', 'wishlist', 'boolean'),
('items_per_page', '12', 'catalog', 'number'),
('enable_stock_management', 'true', 'inventory', 'boolean'),
('low_stock_threshold', '5', 'inventory', 'number'),
('enable_guest_checkout', 'true', 'checkout', 'boolean'),
('checkout_terms_required', 'true', 'checkout', 'boolean');

-- Crear zona de envío inicial (Chile)
INSERT INTO shipping_zones (name, countries) VALUES
('Chile', '["CL"]');

-- Crear métodos de envío iniciales
INSERT INTO shipping_methods (zone_id, name, description, method_type, cost, min_amount) VALUES
(1, 'Envío Estándar', 'Entrega en 3-5 días hábiles', 'flat_rate', 5000, NULL),
(1, 'Envío Express', 'Entrega en 1-2 días hábiles', 'flat_rate', 8000, NULL),
(1, 'Envío Gratis', 'Entrega gratuita en compras sobre $50.000', 'free_shipping', 0, 50000),
(1, 'Retiro en Tienda', 'Retira gratis en nuestro local', 'local_pickup', 0, NULL);

-- Crear categorías iniciales
INSERT INTO product_categories (name, slug, description, display_order) VALUES
('Portafolio Artístico', 'portafolio-artistico', 'Obras de arte originales y personalizadas', 1),
('Productos Personalizados', 'productos-personalizados', 'Artículos únicos creados especialmente para ti', 2),
('Accesorios', 'accesorios', 'Complementos para tu hogar y estilo de vida', 3),
('Digital Art', 'digital-art', 'Arte digital descargable', 4);

-- Crear producto de ejemplo
INSERT INTO products (name, slug, description, short_description, sku, price, stock_quantity, category_id, status) VALUES
('Retrato Personalizado de Mascota', 'retrato-personalizado-mascota',
 'Un retrato artístico personalizado de tu mascota, creado con técnicas profesionales de dibujo y pintura digital.',
 'Retrato artístico personalizado de tu fiel compañero', 'RP001', 25000, 10, 1, 'publish');

-- =========================================
-- 9. ÍNDICES ADICIONALES PARA PERFORMANCE
-- =========================================

-- Índices compuestos para consultas comunes
CREATE INDEX idx_orders_user_status ON orders(user_id, status);
CREATE INDEX idx_orders_date_range ON orders(created_at, status);
CREATE INDEX idx_products_category_status ON products(category_id, status);
CREATE INDEX idx_products_price_range ON products(price, status);
CREATE INDEX idx_cart_session_user ON cart_items(session_id, user_id);
CREATE INDEX idx_order_items_order_product ON order_items(order_id, product_id);

-- Índices para búsquedas y filtros
CREATE INDEX idx_products_featured_status ON products(featured, status);
CREATE INDEX idx_products_virtual_status ON products(virtual, status);
CREATE INDEX idx_coupons_active_expiry ON coupons(status, expiry_date);

-- =========================================
-- 10. TRIGGERS PARA INTEGRIDAD DE DATOS
-- =========================================

-- Trigger para actualizar stock cuando se crea una orden
DELIMITER ;;
CREATE TRIGGER update_stock_on_order AFTER INSERT ON order_items
FOR EACH ROW
BEGIN
    -- Reducir stock del producto principal
    UPDATE products
    SET stock_quantity = stock_quantity - NEW.quantity,
        stock_status = CASE
            WHEN stock_quantity - NEW.quantity <= 0 THEN 'outofstock'
            WHEN stock_quantity - NEW.quantity <= (SELECT setting_value FROM ecommerce_settings WHERE setting_key = 'low_stock_threshold') THEN 'onbackorder'
            ELSE 'instock'
        END
    WHERE id = NEW.product_id;

    -- Si hay variación, actualizar también su stock
    IF NEW.variation_id IS NOT NULL THEN
        UPDATE product_variations
        SET stock_quantity = stock_quantity - NEW.quantity,
            stock_status = CASE
                WHEN stock_quantity - NEW.quantity <= 0 THEN 'outofstock'
                WHEN stock_quantity - NEW.quantity <= 5 THEN 'onbackorder'
                ELSE 'instock'
            END
        WHERE id = NEW.variation_id;
    END IF;
END;;
DELIMITER ;

-- Trigger para restaurar stock cuando se cancela una orden
DELIMITER ;;
CREATE TRIGGER restore_stock_on_cancel AFTER UPDATE ON orders
FOR EACH ROW
BEGIN
    IF OLD.status != 'cancelled' AND NEW.status = 'cancelled' THEN
        -- Restaurar stock de todos los items de la orden
        UPDATE products p
        INNER JOIN order_items oi ON p.id = oi.product_id
        SET p.stock_quantity = p.stock_quantity + oi.quantity
        WHERE oi.order_id = NEW.id;

        -- Restaurar stock de variaciones
        UPDATE product_variations pv
        INNER JOIN order_items oi ON pv.id = oi.variation_id
        SET pv.stock_quantity = pv.stock_quantity + oi.quantity
        WHERE oi.order_id = NEW.id AND oi.variation_id IS NOT NULL;
    END IF;
END;;
DELIMITER ;

-- Trigger para historial de estados de pedidos
DELIMITER ;;
CREATE TRIGGER order_status_history AFTER UPDATE ON orders
FOR EACH ROW
BEGIN
    IF OLD.status != NEW.status THEN
        INSERT INTO order_status_history (order_id, old_status, new_status, changed_by)
        VALUES (NEW.id, OLD.status, NEW.status, @current_user_id);
    END IF;
END;;
DELIMITER ;

-- =========================================
-- 11. VISTAS PARA REPORTES Y ANALYTICS
-- =========================================

-- Vista de productos con información de categoría
CREATE VIEW product_catalog AS
SELECT
    p.*,
    c.name as category_name,
    c.slug as category_slug,
    CASE
        WHEN p.sale_price IS NOT NULL AND p.sale_price < p.price THEN p.sale_price
        ELSE p.price
    END as current_price,
    CASE
        WHEN p.sale_price IS NOT NULL AND p.sale_price < p.price THEN ROUND(((p.price - p.sale_price) / p.price) * 100, 2)
        ELSE 0
    END as discount_percentage
FROM products p
LEFT JOIN product_categories c ON p.category_id = c.id
WHERE p.status = 'publish';

-- Vista de resumen de pedidos
CREATE VIEW order_summary AS
SELECT
    o.*,
    COUNT(oi.id) as item_count,
    SUM(oi.quantity) as total_quantity,
    GROUP_CONCAT(DISTINCT p.name SEPARATOR ', ') as product_names
FROM orders o
LEFT JOIN order_items oi ON o.id = oi.order_id
LEFT JOIN products p ON oi.product_id = p.id
GROUP BY o.id;

-- Vista de productos más vendidos
CREATE VIEW top_selling_products AS
SELECT
    p.id,
    p.name,
    p.sku,
    p.price,
    p.image_url,
    SUM(oi.quantity) as total_sold,
    SUM(oi.line_total) as total_revenue,
    COUNT(DISTINCT oi.order_id) as order_count,
    AVG(oi.price) as avg_sale_price
FROM products p
INNER JOIN order_items oi ON p.id = oi.product_id
INNER JOIN orders o ON oi.order_id = o.id
WHERE o.status IN ('processing', 'shipped', 'completed')
GROUP BY p.id
ORDER BY total_sold DESC;

-- =========================================
-- FIN DEL ESQUEMA DE BASE DE DATOS
-- =========================================

-- Notas importantes:
-- 1. Todas las tablas usan InnoDB para soporte de transacciones
-- 2. Índices optimizados para consultas comunes
-- 3. Constraints y checks para integridad de datos
-- 4. Triggers automáticos para mantener consistencia
-- 5. Vistas para facilitar reportes y analytics
-- 6. Datos iniciales incluidos para testing

COMMIT;</content>
<parameter name="filePath">C:\Users\obal_\Downloads\katy-&-woof---creative-studio (12)-20260305T183150Z-3-001\katy-&-woof---creative-studio (12)\INIT_ECOMMERCE_SCHEMA.sql