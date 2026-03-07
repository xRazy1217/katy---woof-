<?php
/**
 * API de Carrito de Compras - E-commerce Katy & Woof
 * Fase 1: Gestión del carrito de compras
 */

require_once 'ecommerce-config.php';
require_once 'api-products.php';

class CartAPI {
    private $db;
    private $productAPI;
    private $sessionId;

    public function __construct() {
        $this->db = getEcommerceDB();
        $this->productAPI = getProductAPI();
        $this->sessionId = session_id();
    }

    /**
     * Obtener contenido del carrito
     */
    public function getCart($userId = null) {
        try {
            $where = "session_id = :session_id";
            $params = ['session_id' => $this->sessionId];

            if ($userId) {
                $where .= " OR user_id = :user_id";
                $params['user_id'] = $userId;
            }

            $cartItems = $this->db->select(
                "SELECT ci.*, p.name, p.slug, p.image_url, p.stock_quantity, p.stock_status
                 FROM cart_items ci
                 INNER JOIN products p ON ci.product_id = p.id
                 WHERE {$where}
                 ORDER BY ci.added_at DESC",
                $params
            );

            $cart = [
                'items' => [],
                'totals' => [
                    'subtotal' => 0,
                    'tax' => 0,
                    'total' => 0,
                    'item_count' => 0
                ]
            ];

            foreach ($cartItems as $item) {
                // Verificar stock disponible
                $availableStock = $this->checkStockAvailability($item['product_id'], $item['variation_id']);
                $item['available_stock'] = $availableStock;
                $item['max_quantity'] = min($item['quantity'] + $availableStock, $item['stock_quantity']);

                // Calcular totales
                $lineTotal = $item['price'] * $item['quantity'];
                $cart['totals']['subtotal'] += $lineTotal;
                $cart['totals']['item_count'] += $item['quantity'];

                $cart['items'][] = $item;
            }

            // Calcular impuestos (IVA Chile 19%)
            $taxRate = 0.19;
            $cart['totals']['tax'] = round($cart['totals']['subtotal'] * $taxRate);
            $cart['totals']['total'] = $cart['totals']['subtotal'] + $cart['totals']['tax'];

            return [
                'success' => true,
                'data' => $cart
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener carrito',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Agregar producto al carrito
     */
    public function addToCart($productId, $quantity = 1, $variationId = null, $userId = null) {
        try {
            // Validar producto
            $productResult = $this->productAPI->getProduct($productId);
            if (!$productResult['success']) {
                return $productResult;
            }

            $product = $productResult['data'];

            // Validar cantidad
            if ($quantity < 1) {
                return [
                    'success' => false,
                    'message' => 'La cantidad debe ser al menos 1'
                ];
            }

            // Verificar stock
            $availableStock = $this->checkStockAvailability($productId, $variationId);
            if ($availableStock < $quantity) {
                return [
                    'success' => false,
                    'message' => 'Stock insuficiente. Disponible: ' . $availableStock
                ];
            }

            // Determinar precio
            $price = $product['current_price'];

            // Verificar si el producto ya está en el carrito
            $existingItem = $this->db->selectOne(
                "SELECT id, quantity FROM cart_items
                 WHERE product_id = :product_id
                 AND (variation_id = :variation_id OR (variation_id IS NULL AND :variation_id IS NULL))
                 AND ((user_id = :user_id AND :user_id IS NOT NULL) OR (session_id = :session_id AND :user_id IS NULL))",
                [
                    'product_id' => $productId,
                    'variation_id' => $variationId,
                    'user_id' => $userId,
                    'session_id' => $this->sessionId
                ]
            );

            if ($existingItem) {
                // Actualizar cantidad existente
                $newQuantity = $existingItem['quantity'] + $quantity;

                // Verificar límite de stock
                if ($newQuantity > $availableStock) {
                    return [
                        'success' => false,
                        'message' => 'No se puede agregar esa cantidad. Stock disponible: ' . $availableStock
                    ];
                }

                $this->db->update(
                    'cart_items',
                    ['quantity' => $newQuantity, 'updated_at' => date('Y-m-d H:i:s')],
                    'id = ?',
                    [$existingItem['id']]
                );

                $itemId = $existingItem['id'];
            } else {
                // Agregar nuevo item
                $itemId = $this->db->insert('cart_items', [
                    'session_id' => $this->sessionId,
                    'user_id' => $userId,
                    'product_id' => $productId,
                    'variation_id' => $variationId,
                    'quantity' => $quantity,
                    'price' => $price
                ]);
            }

            ecommerceLog('Producto agregado al carrito', 'INFO', [
                'product_id' => $productId,
                'quantity' => $quantity,
                'user_id' => $userId,
                'session_id' => $this->sessionId
            ]);

            return [
                'success' => true,
                'message' => 'Producto agregado al carrito',
                'data' => [
                    'item_id' => $itemId,
                    'product_name' => $product['name'],
                    'quantity' => $existingItem ? $existingItem['quantity'] + $quantity : $quantity
                ]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al agregar producto al carrito',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Actualizar cantidad de producto en carrito
     */
    public function updateCartItem($itemId, $quantity, $userId = null) {
        try {
            // Validar cantidad
            if ($quantity < 0) {
                return [
                    'success' => false,
                    'message' => 'La cantidad no puede ser negativa'
                ];
            }

            // Obtener item del carrito
            $where = "id = :item_id";
            $params = ['item_id' => $itemId];

            if ($userId) {
                $where .= " AND user_id = :user_id";
                $params['user_id'] = $userId;
            } else {
                $where .= " AND session_id = :session_id";
                $params['session_id'] = $this->sessionId;
            }

            $item = $this->db->selectOne("SELECT * FROM cart_items WHERE {$where}", $params);

            if (!$item) {
                return [
                    'success' => false,
                    'message' => 'Item no encontrado en el carrito'
                ];
            }

            if ($quantity === 0) {
                // Eliminar item
                $this->db->delete('cart_items', 'id = ?', [$itemId]);
                return [
                    'success' => true,
                    'message' => 'Producto eliminado del carrito'
                ];
            }

            // Verificar stock disponible
            $availableStock = $this->checkStockAvailability($item['product_id'], $item['variation_id']);
            if ($availableStock < $quantity) {
                return [
                    'success' => false,
                    'message' => 'Stock insuficiente. Disponible: ' . $availableStock
                ];
            }

            // Actualizar cantidad
            $this->db->update(
                'cart_items',
                ['quantity' => $quantity, 'updated_at' => date('Y-m-d H:i:s')],
                'id = ?',
                [$itemId]
            );

            return [
                'success' => true,
                'message' => 'Cantidad actualizada',
                'data' => ['quantity' => $quantity]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al actualizar item del carrito',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Eliminar producto del carrito
     */
    public function removeFromCart($itemId, $userId = null) {
        try {
            $where = "id = :item_id";
            $params = ['item_id' => $itemId];

            if ($userId) {
                $where .= " AND user_id = :user_id";
                $params['user_id'] = $userId;
            } else {
                $where .= " AND session_id = :session_id";
                $params['session_id'] = $this->sessionId;
            }

            $deleted = $this->db->delete('cart_items', $where, $params);

            if ($deleted === 0) {
                return [
                    'success' => false,
                    'message' => 'Item no encontrado en el carrito'
                ];
            }

            return [
                'success' => true,
                'message' => 'Producto eliminado del carrito'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al eliminar producto del carrito',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Vaciar carrito completo
     */
    public function clearCart($userId = null) {
        try {
            $where = "";
            $params = [];

            if ($userId) {
                $where = "user_id = :user_id";
                $params['user_id'] = $userId;
            } else {
                $where = "session_id = :session_id";
                $params['session_id'] = $this->sessionId;
            }

            $this->db->delete('cart_items', $where, $params);

            return [
                'success' => true,
                'message' => 'Carrito vaciado'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al vaciar carrito',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Verificar stock disponible para un producto
     */
    private function checkStockAvailability($productId, $variationId = null) {
        try {
            if ($variationId) {
                // Verificar stock de variación
                $variation = $this->db->selectOne(
                    "SELECT stock_quantity, stock_status FROM product_variations WHERE id = ?",
                    [$variationId]
                );

                if (!$variation || $variation['stock_status'] === 'outofstock') {
                    return 0;
                }

                return $variation['stock_quantity'];
            } else {
                // Verificar stock del producto principal
                $product = $this->db->selectOne(
                    "SELECT stock_quantity, stock_status FROM products WHERE id = ?",
                    [$productId]
                );

                if (!$product || $product['stock_status'] === 'outofstock') {
                    return 0;
                }

                return $product['stock_quantity'];
            }

        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Migrar carrito de sesión a usuario (cuando se loguea)
     */
    public function migrateCartToUser($userId) {
        try {
            // Obtener items del carrito de sesión
            $sessionItems = $this->db->select(
                "SELECT * FROM cart_items WHERE session_id = ? AND user_id IS NULL",
                [$this->sessionId]
            );

            foreach ($sessionItems as $item) {
                // Verificar si ya existe el mismo producto para el usuario
                $existingItem = $this->db->selectOne(
                    "SELECT id, quantity FROM cart_items
                     WHERE user_id = ? AND product_id = ? AND (variation_id = ? OR (variation_id IS NULL AND ? IS NULL))",
                    [$userId, $item['product_id'], $item['variation_id'], $item['variation_id']]
                );

                if ($existingItem) {
                    // Combinar cantidades
                    $newQuantity = $existingItem['quantity'] + $item['quantity'];
                    $this->db->update(
                        'cart_items',
                        ['quantity' => $newQuantity, 'updated_at' => date('Y-m-d H:i:s')],
                        'id = ?',
                        [$existingItem['id']]
                    );

                    // Eliminar item de sesión
                    $this->db->delete('cart_items', 'id = ?', [$item['id']]);
                } else {
                    // Asignar item a usuario
                    $this->db->update(
                        'cart_items',
                        ['user_id' => $userId, 'updated_at' => date('Y-m-d H:i:s')],
                        'id = ?',
                        [$item['id']]
                    );
                }
            }

            return [
                'success' => true,
                'message' => 'Carrito migrado exitosamente'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al migrar carrito',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtener conteo de items en carrito
     */
    public function getCartCount($userId = null) {
        try {
            $where = "session_id = :session_id";
            $params = ['session_id' => $this->sessionId];

            if ($userId) {
                $where .= " OR user_id = :user_id";
                $params['user_id'] = $userId;
            }

            $result = $this->db->selectOne(
                "SELECT SUM(quantity) as total FROM cart_items WHERE {$where}",
                $params
            );

            return [
                'success' => true,
                'data' => [
                    'count' => intval($result['total'] ?? 0)
                ]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener conteo del carrito',
                'error' => $e->getMessage()
            ];
        }
    }
}

// Función helper para obtener instancia de API del carrito
function getCartAPI() {
    static $instance = null;
    if ($instance === null) {
        $instance = new CartAPI();
    }
    return $instance;
}

?>