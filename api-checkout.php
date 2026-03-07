<?php
/**
 * API de Checkout y Pedidos - E-commerce Katy & Woof
 * Fase 1: Procesamiento de pedidos y checkout
 */

require_once 'ecommerce-config.php';
require_once 'api-cart.php';

class CheckoutAPI {
    private $db;
    private $cartAPI;

    public function __construct() {
        $this->db = getEcommerceDB();
        $this->cartAPI = getCartAPI();
    }

    /**
     * Crear un nuevo pedido desde el carrito
     */
    public function createOrder($orderData, $userId = null) {
        try {
            // Validar datos requeridos
            $validation = $this->validateOrderData($orderData);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => 'Datos de pedido inválidos',
                    'errors' => $validation['errors']
                ];
            }

            // Obtener carrito
            $cartResult = $this->cartAPI->getCart($userId);
            if (!$cartResult['success'] || empty($cartResult['data']['items'])) {
                return [
                    'success' => false,
                    'message' => 'El carrito está vacío'
                ];
            }

            $cart = $cartResult['data'];

            // Verificar stock de todos los productos
            $stockValidation = $this->validateStockAvailability($cart['items']);
            if (!$stockValidation['valid']) {
                return [
                    'success' => false,
                    'message' => 'Stock insuficiente para algunos productos',
                    'errors' => $stockValidation['errors']
                ];
            }

            // Generar número de orden único
            $orderNumber = $this->generateOrderNumber();

            // Calcular totales
            $totals = $this->calculateTotals($cart['items'], $orderData);

            // Preparar datos del cliente
            $customerData = $this->prepareCustomerData($orderData);

            // Iniciar transacción
            $this->db->beginTransaction();

            try {
                // Crear pedido
                $orderId = $this->db->insert('orders', [
                    'order_number' => $orderNumber,
                    'user_id' => $userId,
                    'customer_email' => $orderData['billing_email'],
                    'customer_data' => json_encode($customerData),
                    'status' => 'pending',
                    'currency' => 'CLP',
                    'subtotal' => $totals['subtotal'],
                    'tax_total' => $totals['tax'],
                    'shipping_total' => $totals['shipping'],
                    'discount_total' => $totals['discount'],
                    'total' => $totals['total'],
                    'payment_method' => $orderData['payment_method'] ?? 'flow',
                    'shipping_method' => $orderData['shipping_method'] ?? 'standard',
                    'shipping_address' => json_encode($orderData['shipping_address'] ?? $orderData['billing_address']),
                    'billing_address' => json_encode($orderData['billing_address']),
                    'order_notes' => $orderData['order_notes'] ?? null,
                    'customer_ip' => $_SERVER['REMOTE_ADDR'] ?? null,
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
                ]);

                // Crear items del pedido
                foreach ($cart['items'] as $item) {
                    $this->db->insert('order_items', [
                        'order_id' => $orderId,
                        'product_id' => $item['product_id'],
                        'variation_id' => $item['variation_id'],
                        'product_name' => $item['name'],
                        'product_sku' => $this->getProductSKU($item['product_id'], $item['variation_id']),
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'line_total' => $item['price'] * $item['quantity'],
                        'variation_data' => $item['variation_id'] ? json_encode($this->getVariationData($item['variation_id'])) : null
                    ]);
                }

                // Aplicar cupones si existen
                if (!empty($orderData['coupon_code'])) {
                    $couponResult = $this->applyCoupon($orderId, $orderData['coupon_code']);
                    if (!$couponResult['success']) {
                        throw new Exception($couponResult['message']);
                    }
                }

                // Reducir stock (esto se hace automáticamente via triggers)
                // Registrar en historial de estados
                $this->db->insert('order_status_history', [
                    'order_id' => $orderId,
                    'old_status' => null,
                    'new_status' => 'pending',
                    'notes' => 'Pedido creado desde checkout'
                ]);

                // Limpiar carrito
                $this->cartAPI->clearCart($userId);

                // Confirmar transacción
                $this->db->commit();

                ecommerceLog('Pedido creado exitosamente', 'INFO', [
                    'order_id' => $orderId,
                    'order_number' => $orderNumber,
                    'total' => $totals['total'],
                    'user_id' => $userId
                ]);

                return [
                    'success' => true,
                    'message' => 'Pedido creado exitosamente',
                    'data' => [
                        'order_id' => $orderId,
                        'order_number' => $orderNumber,
                        'total' => $totals['total'],
                        'status' => 'pending'
                    ]
                ];

            } catch (Exception $e) {
                $this->db->rollback();
                throw $e;
            }

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al crear pedido',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtener detalles de un pedido
     */
    public function getOrder($orderId, $userId = null) {
        try {
            $where = "o.id = :order_id";
            $params = ['order_id' => $orderId];

            if ($userId) {
                $where .= " AND o.user_id = :user_id";
                $params['user_id'] = $userId;
            }

            // Obtener pedido
            $order = $this->db->selectOne(
                "SELECT * FROM orders o WHERE {$where}",
                $params
            );

            if (!$order) {
                return [
                    'success' => false,
                    'message' => 'Pedido no encontrado'
                ];
            }

            // Obtener items del pedido
            $items = $this->db->select(
                "SELECT * FROM order_items WHERE order_id = ?",
                [$orderId]
            );

            // Procesar datos JSON
            $order['customer_data'] = json_decode($order['customer_data'], true);
            $order['shipping_address'] = json_decode($order['shipping_address'], true);
            $order['billing_address'] = json_decode($order['billing_address'], true);

            foreach ($items as &$item) {
                $item['variation_data'] = json_decode($item['variation_data'] ?? '{}', true);
            }

            $order['items'] = $items;

            return [
                'success' => true,
                'data' => $order
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener pedido',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtener pedidos de un usuario
     */
    public function getUserOrders($userId, $page = 1, $perPage = 10) {
        try {
            $offset = ($page - 1) * $perPage;

            $orders = $this->db->select(
                "SELECT o.*, COUNT(oi.id) as item_count
                 FROM orders o
                 LEFT JOIN order_items oi ON o.id = oi.order_id
                 WHERE o.user_id = ?
                 GROUP BY o.id
                 ORDER BY o.created_at DESC
                 LIMIT ? OFFSET ?",
                [$userId, $perPage, $offset]
            );

            // Contar total
            $totalResult = $this->db->selectOne(
                "SELECT COUNT(*) as total FROM orders WHERE user_id = ?",
                [$userId]
            );

            $total = $totalResult['total'];

            return [
                'success' => true,
                'data' => $orders,
                'pagination' => [
                    'page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'total_pages' => ceil($total / $perPage)
                ]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener pedidos',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Aplicar cupón a un pedido
     */
    private function applyCoupon($orderId, $couponCode) {
        try {
            // Obtener cupón
            $coupon = $this->db->selectOne(
                "SELECT * FROM coupons WHERE code = ? AND status = 'active'",
                [$couponCode]
            );

            if (!$coupon) {
                return [
                    'success' => false,
                    'message' => 'Cupón inválido o expirado'
                ];
            }

            // Verificar límite de uso
            if ($coupon['usage_limit'] && $coupon['usage_count'] >= $coupon['usage_limit']) {
                return [
                    'success' => false,
                    'message' => 'Cupón agotado'
                ];
            }

            // Verificar fecha de expiración
            if ($coupon['expiry_date'] && strtotime($coupon['expiry_date']) < time()) {
                return [
                    'success' => false,
                    'message' => 'Cupón expirado'
                ];
            }

            // Obtener total del pedido
            $order = $this->db->selectOne(
                "SELECT subtotal FROM orders WHERE id = ?",
                [$orderId]
            );

            // Calcular descuento
            $discount = 0;
            if ($coupon['discount_type'] === 'fixed') {
                $discount = min($coupon['discount_value'], $order['subtotal']);
            } else { // percentage
                $discount = $order['subtotal'] * ($coupon['discount_value'] / 100);
                if ($coupon['maximum_amount']) {
                    $discount = min($discount, $coupon['maximum_amount']);
                }
            }

            // Aplicar descuento al pedido
            $this->db->update(
                'orders',
                [
                    'discount_total' => $discount,
                    'total' => $order['subtotal'] + $order['tax_total'] + $order['shipping_total'] - $discount
                ],
                'id = ?',
                [$orderId]
            );

            // Registrar uso del cupón
            $this->db->insert('order_coupons', [
                'order_id' => $orderId,
                'coupon_id' => $coupon['id'],
                'coupon_code' => $couponCode,
                'discount_amount' => $discount
            ]);

            // Incrementar contador de uso
            $this->db->query(
                "UPDATE coupons SET usage_count = usage_count + 1 WHERE id = ?",
                [$coupon['id']]
            );

            return [
                'success' => true,
                'data' => [
                    'discount' => $discount,
                    'coupon' => $coupon
                ]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al aplicar cupón',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Validar datos del pedido
     */
    private function validateOrderData($data) {
        $errors = [];

        // Validar email
        if (empty($data['billing_email']) || !validateEmail($data['billing_email'])) {
            $errors[] = 'Email de facturación inválido';
        }

        // Validar dirección de facturación
        if (empty($data['billing_address']['first_name'])) {
            $errors[] = 'Nombre de facturación requerido';
        }

        if (empty($data['billing_address']['last_name'])) {
            $errors[] = 'Apellido de facturación requerido';
        }

        if (empty($data['billing_address']['address_1'])) {
            $errors[] = 'Dirección de facturación requerida';
        }

        if (empty($data['billing_address']['city'])) {
            $errors[] = 'Ciudad de facturación requerida';
        }

        if (empty($data['billing_address']['postcode'])) {
            $errors[] = 'Código postal requerido';
        }

        // Validar dirección de envío si es diferente
        if (!empty($data['ship_to_different_address'])) {
            if (empty($data['shipping_address']['first_name'])) {
                $errors[] = 'Nombre de envío requerido';
            }

            if (empty($data['shipping_address']['address_1'])) {
                $errors[] = 'Dirección de envío requerida';
            }

            if (empty($data['shipping_address']['city'])) {
                $errors[] = 'Ciudad de envío requerida';
            }
        }

        // Validar método de pago
        $validPayments = ['flow', 'bank_transfer', 'cash_on_delivery'];
        if (empty($data['payment_method']) || !in_array($data['payment_method'], $validPayments)) {
            $errors[] = 'Método de pago inválido';
        }

        // Validar términos y condiciones
        if (empty($data['terms_accepted'])) {
            $errors[] = 'Debe aceptar los términos y condiciones';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Validar disponibilidad de stock
     */
    private function validateStockAvailability($cartItems) {
        $errors = [];

        foreach ($cartItems as $item) {
            $availableStock = $this->cartAPI->checkStockAvailability($item['product_id'], $item['variation_id']);

            if ($availableStock < $item['quantity']) {
                $errors[] = "Stock insuficiente para '{$item['name']}'. Disponible: {$availableStock}";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Calcular totales del pedido
     */
    private function calculateTotals($cartItems, $orderData) {
        $subtotal = 0;
        $tax = 0;
        $shipping = 0;
        $discount = 0;

        // Calcular subtotal
        foreach ($cartItems as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        // Calcular impuesto (IVA 19% Chile)
        $tax = round($subtotal * 0.19);

        // Calcular envío
        $shipping = $this->calculateShipping($orderData['shipping_method'] ?? 'standard', $subtotal);

        // Aplicar descuento si hay cupón
        if (!empty($orderData['coupon_code'])) {
            // El descuento se calcula en applyCoupon
            $discount = 0; // Se actualizará después
        }

        $total = $subtotal + $tax + $shipping - $discount;

        return [
            'subtotal' => $subtotal,
            'tax' => $tax,
            'shipping' => $shipping,
            'discount' => $discount,
            'total' => $total
        ];
    }

    /**
     * Calcular costo de envío
     */
    private function calculateShipping($method, $subtotal) {
        // Lógica básica de envío - se puede expandir
        switch ($method) {
            case 'free_shipping':
                return 0;
            case 'flat_rate':
                return 5000; // $5.000 CLP
            case 'local_pickup':
                return 0;
            default:
                return 5000;
        }
    }

    /**
     * Preparar datos del cliente
     */
    private function prepareCustomerData($orderData) {
        return [
            'first_name' => $orderData['billing_address']['first_name'] ?? '',
            'last_name' => $orderData['billing_address']['last_name'] ?? '',
            'company' => $orderData['billing_address']['company'] ?? '',
            'phone' => $orderData['billing_address']['phone'] ?? '',
            'email' => $orderData['billing_email']
        ];
    }

    /**
     * Generar número de orden único
     */
    private function generateOrderNumber() {
        do {
            $number = 'KW-' . date('Y') . '-' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
            $exists = $this->db->selectOne("SELECT id FROM orders WHERE order_number = ?", [$number]);
        } while ($exists);

        return $number;
    }

    /**
     * Obtener SKU de producto/variación
     */
    private function getProductSKU($productId, $variationId = null) {
        if ($variationId) {
            $variation = $this->db->selectOne("SELECT sku FROM product_variations WHERE id = ?", [$variationId]);
            return $variation ? $variation['sku'] : null;
        } else {
            $product = $this->db->selectOne("SELECT sku FROM products WHERE id = ?", [$productId]);
            return $product ? $product['sku'] : null;
        }
    }

    /**
     * Obtener datos de variación
     */
    private function getVariationData($variationId) {
        $variation = $this->db->selectOne("SELECT * FROM product_variations WHERE id = ?", [$variationId]);
        return $variation ? json_decode($variation['attributes'], true) : [];
    }
}

// Función helper para obtener instancia de API de checkout
function getCheckoutAPI() {
    static $instance = null;
    if ($instance === null) {
        $instance = new CheckoutAPI();
    }
    return $instance;
}

?>