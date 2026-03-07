<?php
/**
 * API de Productos - E-commerce Katy & Woof
 * Fase 1: Endpoints básicos de productos
 */

require_once 'ecommerce-config.php';

class ProductAPI {
    private $db;

    public function __construct() {
        $this->db = getEcommerceDB();
    }

    /**
     * Obtener lista de productos con filtros y paginación
     */
    public function getProducts($filters = []) {
        try {
            $where = ["p.status = 'publish'"];
            $params = [];
            $joins = "LEFT JOIN product_categories c ON p.category_id = c.id";

            // Filtros
            if (!empty($filters['category'])) {
                $where[] = "p.category_id = :category_id";
                $params['category_id'] = $filters['category'];
            }

            if (!empty($filters['search'])) {
                $where[] = "MATCH(p.name, p.description, p.short_description) AGAINST(:search IN NATURAL LANGUAGE MODE)";
                $params['search'] = $filters['search'];
            }

            if (!empty($filters['min_price'])) {
                $where[] = "p.price >= :min_price";
                $params['min_price'] = $filters['min_price'];
            }

            if (!empty($filters['max_price'])) {
                $where[] = "p.price <= :max_price";
                $params['max_price'] = $filters['max_price'];
            }

            if (!empty($filters['featured'])) {
                $where[] = "p.featured = 1";
            }

            if (!empty($filters['in_stock'])) {
                $where[] = "p.stock_status = 'instock'";
            }

            // Ordenamiento
            $orderBy = "p.created_at DESC";
            if (!empty($filters['orderby'])) {
                switch ($filters['orderby']) {
                    case 'price':
                        $orderBy = "p.price ASC";
                        break;
                    case 'price-desc':
                        $orderBy = "p.price DESC";
                        break;
                    case 'name':
                        $orderBy = "p.name ASC";
                        break;
                    case 'date':
                        $orderBy = "p.created_at DESC";
                        break;
                    case 'popularity':
                        $orderBy = "p.created_at DESC"; // TODO: implementar basado en ventas
                        break;
                }
            }

            // Paginación
            $page = max(1, intval($filters['page'] ?? 1));
            $perPage = min(50, max(1, intval($filters['per_page'] ?? 12)));
            $offset = ($page - 1) * $perPage;

            $whereClause = implode(' AND ', $where);

            // Consulta principal
            $sql = "SELECT
                        p.id, p.name, p.slug, p.short_description, p.price, p.sale_price,
                        p.stock_quantity, p.stock_status, p.image_url, p.gallery_images,
                        p.attributes, p.tags, p.featured, p.virtual, p.downloadable,
                        c.name as category_name, c.slug as category_slug,
                        CASE
                            WHEN p.sale_price IS NOT NULL AND p.sale_price < p.price THEN p.sale_price
                            ELSE p.price
                        END as current_price,
                        CASE
                            WHEN p.sale_price IS NOT NULL AND p.sale_price < p.price
                            THEN ROUND(((p.price - p.sale_price) / p.price) * 100, 2)
                            ELSE 0
                        END as discount_percentage
                    FROM products p
                    {$joins}
                    WHERE {$whereClause}
                    ORDER BY {$orderBy}
                    LIMIT {$perPage} OFFSET {$offset}";

            $products = $this->db->select($sql, $params);

            // Contar total para paginación
            $countSql = "SELECT COUNT(*) as total FROM products p {$joins} WHERE {$whereClause}";
            $totalResult = $this->db->selectOne($countSql, $params);
            $total = $totalResult['total'];

            // Procesar productos
            foreach ($products as &$product) {
                $product['gallery_images'] = json_decode($product['gallery_images'] ?? '[]', true);
                $product['attributes'] = json_decode($product['attributes'] ?? '{}', true);
                $product['tags'] = json_decode($product['tags'] ?? '[]', true);
                $product['url'] = "/producto/{$product['slug']}";
            }

            return [
                'success' => true,
                'data' => $products,
                'pagination' => [
                    'page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'total_pages' => ceil($total / $perPage),
                    'has_next' => $page < ceil($total / $perPage),
                    'has_prev' => $page > 1
                ]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener productos',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtener producto específico por ID o slug
     */
    public function getProduct($identifier) {
        try {
            $where = is_numeric($identifier) ? "p.id = :id" : "p.slug = :slug";
            $params = is_numeric($identifier) ? ['id' => $identifier] : ['slug' => $identifier];

            $sql = "SELECT
                        p.*, c.name as category_name, c.slug as category_slug,
                        CASE
                            WHEN p.sale_price IS NOT NULL AND p.sale_price < p.price THEN p.sale_price
                            ELSE p.price
                        END as current_price,
                        CASE
                            WHEN p.sale_price IS NOT NULL AND p.sale_price < p.price
                            THEN ROUND(((p.price - p.sale_price) / p.price) * 100, 2)
                            ELSE 0
                        END as discount_percentage
                    FROM products p
                    LEFT JOIN product_categories c ON p.category_id = c.id
                    WHERE {$where} AND p.status = 'publish'";

            $product = $this->db->selectOne($sql, $params);

            if (!$product) {
                return [
                    'success' => false,
                    'message' => 'Producto no encontrado'
                ];
            }

            // Procesar datos JSON
            $product['gallery_images'] = json_decode($product['gallery_images'] ?? '[]', true);
            $product['attributes'] = json_decode($product['attributes'] ?? '{}', true);
            $product['tags'] = json_decode($product['tags'] ?? '[]', true);

            // Obtener variaciones si existen
            $variations = $this->db->select(
                "SELECT * FROM product_variations WHERE product_id = ? AND status = 'active' ORDER BY display_order",
                [$product['id']]
            );

            foreach ($variations as &$variation) {
                $variation['attributes'] = json_decode($variation['attributes'], true);
            }

            $product['variations'] = $variations;

            // Registrar vista del producto
            $this->logProductView($product['id']);

            return [
                'success' => true,
                'data' => $product
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener producto',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtener categorías de productos
     */
    public function getCategories($parentId = null) {
        try {
            $where = "status = 'active'";
            $params = [];

            if ($parentId !== null) {
                $where .= " AND parent_id " . ($parentId === 0 ? "IS NULL" : "= :parent_id");
                if ($parentId !== 0) {
                    $params['parent_id'] = $parentId;
                }
            }

            $categories = $this->db->select(
                "SELECT * FROM product_categories WHERE {$where} ORDER BY display_order, name",
                $params
            );

            // Obtener conteo de productos por categoría
            foreach ($categories as &$category) {
                $count = $this->db->selectOne(
                    "SELECT COUNT(*) as count FROM products WHERE category_id = ? AND status = 'publish'",
                    [$category['id']]
                );
                $category['product_count'] = $count['count'];
            }

            return [
                'success' => true,
                'data' => $categories
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener categorías',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Buscar productos
     */
    public function searchProducts($query, $filters = []) {
        try {
            $filters['search'] = $query;
            return $this->getProducts($filters);

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error en búsqueda',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Registrar vista de producto para analytics
     */
    private function logProductView($productId) {
        try {
            $data = [
                'product_id' => $productId,
                'session_id' => session_id(),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                'referrer' => $_SERVER['HTTP_REFERER'] ?? null
            ];

            // Solo loguear si no hay vista reciente de la misma sesión
            $recentView = $this->db->selectOne(
                "SELECT id FROM product_views
                 WHERE product_id = ? AND session_id = ? AND viewed_at > DATE_SUB(NOW(), INTERVAL 30 MINUTE)",
                [$productId, $data['session_id']]
            );

            if (!$recentView) {
                $this->db->insert('product_views', $data);
            }

        } catch (Exception $e) {
            // No fallar la petición principal por error de logging
            ecommerceLog('Error logging product view: ' . $e->getMessage(), 'WARNING');
        }
    }

    /**
     * Validar datos de producto
     */
    private function validateProductData($data) {
        $errors = [];

        if (empty($data['name'])) {
            $errors[] = 'El nombre del producto es requerido';
        }

        if (empty($data['price']) || !is_numeric($data['price']) || $data['price'] < 0) {
            $errors[] = 'El precio debe ser un número positivo';
        }

        if (!empty($data['sale_price']) && (!is_numeric($data['sale_price']) || $data['sale_price'] < 0)) {
            $errors[] = 'El precio de oferta debe ser un número positivo';
        }

        if (!empty($data['sale_price']) && $data['sale_price'] >= $data['price']) {
            $errors[] = 'El precio de oferta debe ser menor al precio regular';
        }

        if (!empty($data['stock_quantity']) && (!is_numeric($data['stock_quantity']) || $data['stock_quantity'] < 0)) {
            $errors[] = 'La cantidad en stock debe ser un número positivo';
        }

        return $errors;
    }
}

// Función helper para obtener instancia de API
function getProductAPI() {
    static $instance = null;
    if ($instance === null) {
        $instance = new ProductAPI();
    }
    return $instance;
}

?>