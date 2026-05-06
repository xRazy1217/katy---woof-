<?php
/**
 * Katy & Woof - E-commerce API Module v1.0
 * Integración de categorías, productos, órdenes y cupones
 */

class EcommerceAPI {

    private static function normalizeCatalogImageUrl($path, $updatedAt = null) {
        $raw = trim((string)$path);
        if ($raw === '') {
            return '/uploads/placeholder-product.svg';
        }

        if (!preg_match('/^https?:\/\//i', $raw) && strpos($raw, '/') !== 0) {
            $raw = '/' . ltrim($raw, '/');
        }

        $token = $updatedAt ? strtotime((string)$updatedAt) : time();
        $sep = strpos($raw, '?') !== false ? '&' : '?';
        return $raw . $sep . 'v=' . intval($token ?: time());
    }

    /**
     * Obtener catálogo unificado (productos y servicios).
     *
     * Si existen columnas nuevas de tipificación, se usan directamente.
     * Si no existen, se aplica inferencia para mantener compatibilidad.
     */
    public static function getCatalog() {
        try {
            $pdo = getDBConnection();

            $columnMap = [
                'product_type' => false,
                'requires_shipping' => false,
                'service_category' => false
            ];

            try {
                $columnsStmt = $pdo->query("SHOW COLUMNS FROM products");
                $columns = $columnsStmt->fetchAll(PDO::FETCH_COLUMN, 0);
                foreach ($columnMap as $column => $exists) {
                    $columnMap[$column] = in_array($column, $columns, true);
                }
            } catch (Exception $e) {
                // Si no podemos inspeccionar columnas, seguimos con fallback seguro.
            }

            $selectProductType = $columnMap['product_type'] ? "p.product_type" : "NULL";
            $selectRequiresShipping = $columnMap['requires_shipping'] ? "p.requires_shipping" : "NULL";
            $selectServiceCategory = $columnMap['service_category'] ? "p.service_category" : "NULL";

            $stmt = $pdo->prepare("\n                SELECT p.id, p.name, p.slug, p.description, p.sku, p.price,\n                       p.price AS regular_price, p.sale_price, p.stock_quantity, p.status,\n                       p.image_url, p.category_id, pc.name as category_name,\n                       p.attributes, p.stock_status, p.virtual, p.downloadable,\n                       {$selectProductType} AS product_type,\n                       {$selectRequiresShipping} AS requires_shipping,\n                       {$selectServiceCategory} AS service_category,\n                       p.created_at, p.updated_at\n                FROM products p\n                LEFT JOIN product_categories pc ON p.category_id = pc.id\n                WHERE p.status = 'publish'\n                  AND (p.product_type IS NULL OR p.product_type <> 'service')\n                ORDER BY p.created_at DESC\n            ");
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $catalog = [];
            foreach ($rows as $row) {
                $name = trim((string)($row['name'] ?? ''));
                $category = trim((string)($row['category_name'] ?? ''));

                $requiresShipping = 1;
                if ($row['requires_shipping'] !== null) {
                    $requiresShipping = intval($row['requires_shipping']) ? 1 : 0;
                } else {
                    $requiresShipping = 1;
                }

                $imageUrl = self::normalizeCatalogImageUrl($row['image_url'] ?: '/uploads/placeholder-product.svg', $row['updated_at'] ?? null);

                $catalog[] = [
                    'id' => intval($row['id']),
                    'name' => $row['name'],
                    'title' => $row['name'],
                    'slug' => $row['slug'],
                    'description' => $row['description'],
                    'short_description' => $row['description'],
                    'sku' => $row['sku'],
                    'attributes' => $row['attributes'],
                    'type' => 'physical',
                    'product_type' => 'physical',
                    'category' => $row['category_name'] ?: 'General',
                    'category_name' => $row['category_name'] ?: 'General',
                    'price' => floatval($row['price'] ?? 0),
                    'regular_price' => floatval($row['regular_price'] ?? 0),
                    'sale_price' => floatval($row['sale_price'] ?? 0),
                    'stock_quantity' => intval($row['stock_quantity'] ?? 0),
                    'stock_status' => $row['stock_status'] ?? 'instock',
                    'requires_shipping' => $requiresShipping,
                    'virtual' => 0,
                    'image_url' => $imageUrl,
                    'main_image_url' => $imageUrl,
                    'source' => 'products',
                    'purchasable' => true,
                    'created_at' => $row['created_at']
                ];
            }

            return [
                'success' => true,
                'data' => $catalog,
                'count' => count($catalog),
                'unified_catalog' => defined('UNIFIED_CATALOG') ? UNIFIED_CATALOG : true
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error al obtener catálogo: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * Obtener todas las categorías de productos
     */
    public static function getCategories() {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("\n                SELECT id, name, slug, description, parent_id, image_url\n                FROM product_categories\n                ORDER BY parent_id, name ASC\n            ");
            $stmt->execute();
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $categories,
                'count' => count($categories)
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error al obtener categorías: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * Obtener todos los productos
     */
    public static function getProducts() {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("\n                SELECT p.id, p.name, p.slug, p.description, p.sku, p.price,\n                       p.price AS regular_price, p.sale_price, p.stock_quantity, p.status,\n                       p.image_url, p.category_id, pc.name as category_name,\n                       p.stock_status, p.created_at, p.updated_at\n                FROM products p\n                LEFT JOIN product_categories pc ON p.category_id = pc.id\n                ORDER BY p.created_at DESC\n            ");
            $stmt->execute();
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($products as &$product) {
                $product['price'] = floatval($product['price'] ?? 0);
                $product['regular_price'] = floatval($product['regular_price'] ?? 0);
                $product['sale_price'] = floatval($product['sale_price'] ?? 0);
                $product['stock_quantity'] = intval($product['stock_quantity'] ?? 0);
            }

            return [
                'success' => true,
                'data' => $products,
                'count' => count($products)
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error al obtener productos: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * Obtener todas las órdenes
     */
    public static function getOrders() {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("\n                SELECT o.id, o.order_number, o.customer_email, o.customer_data,\n                       o.subtotal, o.total, o.tax_total, o.shipping_total, o.discount_total,\n                       o.status, o.payment_method, o.payment_status,\n                       o.shipping_address, o.billing_address,\n                       (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) AS items_count,\n                       o.created_at, o.updated_at\n                FROM orders o\n                ORDER BY o.created_at DESC\n            ");
            $stmt->execute();
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($orders as &$order) {
                $customerData = [];
                if (!empty($order['customer_data'])) {
                    $decoded = json_decode($order['customer_data'], true);
                    if (is_array($decoded)) {
                        $customerData = $decoded;
                    }
                }

                $firstName = $customerData['first_name'] ?? '';
                $lastName = $customerData['last_name'] ?? '';
                $fullName = trim($firstName . ' ' . $lastName);

                $order['customer_name'] = $fullName !== '' ? $fullName : ($customerData['name'] ?? $order['customer_email']);
                $order['customer_phone'] = $customerData['phone'] ?? null;

                $order['subtotal'] = floatval($order['subtotal'] ?? 0);
                $order['total'] = floatval($order['total'] ?? 0);
                $order['tax_total'] = floatval($order['tax_total'] ?? 0);
                $order['shipping_total'] = floatval($order['shipping_total'] ?? 0);
                $order['discount_total'] = floatval($order['discount_total'] ?? 0);

                // Alias legacy para mantener compatibilidad con vistas antiguas.
                $order['total_amount'] = $order['total'];
                $order['tax'] = $order['tax_total'];
                $order['shipping_cost'] = $order['shipping_total'];

                $order['items_count'] = intval($order['items_count'] ?? 0);
            }

            return [
                'success' => true,
                'data' => $orders,
                'count' => count($orders)
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error al obtener órdenes: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * Obtener estadísticas rápidas de órdenes
     */
    public static function getOrderStats() {
        try {
            $pdo = getDBConnection();

            $stmt = $pdo->query("SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed,
                SUM(CASE WHEN status IN ('pending','processing') THEN 1 ELSE 0 END) AS pending,
                SUM(CASE WHEN status IN ('cancelled','refunded') THEN 1 ELSE 0 END) AS cancelled
                FROM orders");

            $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

            return [
                'success' => true,
                'data' => [
                    'total' => intval($row['total'] ?? 0),
                    'completed' => intval($row['completed'] ?? 0),
                    'pending' => intval($row['pending'] ?? 0),
                    'cancelled' => intval($row['cancelled'] ?? 0)
                ]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error al obtener estadísticas de órdenes: ' . $e->getMessage(),
                'data' => [
                    'total' => 0,
                    'completed' => 0,
                    'pending' => 0,
                    'cancelled' => 0
                ]
            ];
        }
    }

    /**
     * Obtener todos los cupones
     */
    public static function getCoupons() {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("\n                SELECT id, code, description, discount_type, discount_value,\n                       minimum_amount, usage_limit, usage_count, expiry_date,\n                       status, created_at, updated_at\n                FROM coupons\n                ORDER BY created_at DESC\n            ");
            $stmt->execute();
            $coupons = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($coupons as &$coupon) {
                $coupon['discount_value'] = floatval($coupon['discount_value'] ?? 0);
                $coupon['minimum_amount'] = floatval($coupon['minimum_amount'] ?? 0);
                $coupon['min_spend'] = $coupon['minimum_amount'];
                $coupon['usage_limit'] = intval($coupon['usage_limit'] ?? 0);
                $coupon['usage_count'] = intval($coupon['usage_count'] ?? 0);
                $coupon['used_count'] = $coupon['usage_count'];
                $coupon['is_active'] = ($coupon['status'] ?? 'inactive') === 'active';
            }

            return [
                'success' => true,
                'data' => $coupons,
                'count' => count($coupons)
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error al obtener cupones: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * Eliminar un producto
     */
    public static function deleteProduct($id) {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([intval($id)]);
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Actualizar estado de una orden
     */
    public static function updateOrderStatus($id, $status) {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->execute([$status, intval($id)]);
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Guardar o actualizar un cupón
     */
    public static function saveCoupon($body) {
        try {
            $pdo = getDBConnection();
            $id = intval($body['id'] ?? 0);
            $code = strtoupper(trim($body['code'] ?? ''));
            if (!$code) return ['success' => false, 'error' => 'Código requerido'];

            $type = $body['discount_type'] ?? 'percentage';
            $value = floatval($body['discount_value'] ?? 0);
            $min = floatval($body['minimum_amount'] ?? 0);
            $limit = intval($body['usage_limit'] ?? 0);
            $expiry = !empty($body['expiry_date']) ? $body['expiry_date'] : null;
            $desc = trim($body['description'] ?? '');
            $status = $body['status'] ?? 'active';

            if ($id) {
                $stmt = $pdo->prepare("UPDATE coupons SET code=?, discount_type=?, discount_value=?, minimum_amount=?, usage_limit=?, expiry_date=?, description=?, status=? WHERE id=?");
                $stmt->execute([$code, $type, $value, $min, $limit, $expiry, $desc, $status, $id]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO coupons (code, discount_type, discount_value, minimum_amount, usage_limit, expiry_date, description, status) VALUES (?,?,?,?,?,?,?,?)");
                $stmt->execute([$code, $type, $value, $min, $limit, $expiry, $desc, $status]);
            }
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Eliminar un cupón
     */
    public static function deleteCoupon($id) {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("DELETE FROM coupons WHERE id = ?");
            $stmt->execute([intval($id)]);
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Alternar estado de un cupón
     */
    public static function toggleCoupon($id, $status) {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("UPDATE coupons SET status = ? WHERE id = ?");
            $stmt->execute([$status, intval($id)]);
            return ['success' => true];
        } catch (Exception $e) {
        }
    }

    /**
     * Guardar o actualizar un producto (con imagen)
     */
    public static function saveProduct($data, $files) {
        try {
            $pdo = getDBConnection();
            $id = intval($data['id'] ?? 0);
            $name = trim($data['name'] ?? '');
            $slug = !empty($data['slug']) ? $data['slug'] : strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
            $sku = $data['sku'] ?? '';
            $desc = $data['description'] ?? '';
            $price = floatval($data['price'] ?? 0);
            $sale_price = floatval($data['sale_price'] ?? 0);
            $stock = intval($data['stock_quantity'] ?? 0);
            $cat_id = intval($data['category_id'] ?? 0);
            $status = $data['status'] ?? 'publish';

            $imageUrl = $data['image_url'] ?? '';

            if (!empty($files['image']) && $files['image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../uploads/products/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                $ext = pathinfo($files['image']['name'], PATHINFO_EXTENSION);
                $filename = 'prod_' . time() . '.' . $ext;
                if (move_uploaded_file($files['image']['tmp_name'], $uploadDir . $filename)) {
                    $imageUrl = '/uploads/products/' . $filename;
                }
            }

            if ($id) {
                $stmt = $pdo->prepare("UPDATE products SET name=?, slug=?, sku=?, description=?, price=?, sale_price=?, stock_quantity=?, category_id=?, status=?, image_url=? WHERE id=?");
                $stmt->execute([$name, $slug, $sku, $desc, $price, $sale_price, $stock, $cat_id, $status, $imageUrl, $id]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO products (name, slug, sku, description, price, sale_price, stock_quantity, category_id, status, image_url) VALUES (?,?,?,?,?,?,?,?,?,?)");
                $stmt->execute([$name, $slug, $sku, $desc, $price, $sale_price, $stock, $cat_id, $status, $imageUrl]);
            }
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Guardar o actualizar una categoría
     */
    public static function saveCategory($data) {
        try {
            $pdo = getDBConnection();
            $id = intval($data['id'] ?? 0);
            $name = trim($data['name'] ?? '');
            $slug = !empty($data['slug']) ? $data['slug'] : strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
            $desc = $data['description'] ?? '';

            if ($id) {
                $stmt = $pdo->prepare("UPDATE product_categories SET name=?, slug=?, description=? WHERE id=?");
                $stmt->execute([$name, $slug, $desc, $id]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO product_categories (name, slug, description) VALUES (?,?,?)");
                $stmt->execute([$name, $slug, $desc]);
            }
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Eliminar una categoría
     */
    public static function deleteCategory($id) {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("DELETE FROM product_categories WHERE id = ?");
            $stmt->execute([intval($id)]);
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
?>
