<?php
/**
 * API Principal - E-commerce Katy & Woof
 * Fase 1: Router principal y endpoints REST
 */

require_once 'ecommerce-config.php';
require_once 'api-products.php';
require_once 'api-cart.php';
require_once 'api-checkout.php';

// Configurar headers CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-Token');
header('Content-Type: application/json; charset=utf-8');

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Obtener método HTTP y ruta
$method = $_SERVER['REQUEST_METHOD'];
$request = $_SERVER['REQUEST_URI'];

// Limpiar la ruta de query strings y obtener solo el path
$path = parse_url($request, PHP_URL_PATH);
$path = str_replace('/ecommerce-api/', '', $path); // Remover prefijo /ecommerce-api/
$path = trim($path, '/');

// Obtener parámetros de la URL
$pathParts = explode('/', $path);
$endpoint = $pathParts[0] ?? '';
$resourceId = $pathParts[1] ?? null;
$action = $pathParts[2] ?? null;

// Obtener datos del request
$input = json_decode(file_get_contents('php://input'), true) ?? [];
$input = array_merge($input, $_GET, $_POST);

// Obtener usuario autenticado (si existe)
$userId = null;
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
}

// Función para validar CSRF token en requests POST/PUT/DELETE
function validateCSRF() {
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $input['csrf_token'] ?? null;
    if (!$token || !validateCSRFToken($token)) {
        jsonResponse(['success' => false, 'message' => 'Token CSRF inválido'], 403);
    }
}

try {
    switch ($endpoint) {
        case 'products':
            handleProductsEndpoint($method, $resourceId, $action, $input);
            break;

        case 'categories':
            handleCategoriesEndpoint($method, $resourceId, $action, $input);
            break;

        case 'cart':
            handleCartEndpoint($method, $resourceId, $action, $input, $userId);
            break;

        case 'checkout':
            handleCheckoutEndpoint($method, $resourceId, $action, $input, $userId);
            break;

        case 'orders':
            handleOrdersEndpoint($method, $resourceId, $action, $input, $userId);
            break;

        case 'coupons':
            handleCouponsEndpoint($method, $resourceId, $action, $input);
            break;

        case 'settings':
            handleSettingsEndpoint($method, $resourceId, $action, $input);
            break;

        default:
            jsonResponse(['success' => false, 'message' => 'Endpoint no encontrado'], 404);
    }

} catch (Exception $e) {
    ecommerceLog('API Error: ' . $e->getMessage(), 'ERROR', [
        'endpoint' => $endpoint,
        'method' => $method,
        'user_id' => $userId
    ]);

    jsonResponse([
        'success' => false,
        'message' => 'Error interno del servidor',
        'error' => getenv('APP_ENV') === 'development' ? $e->getMessage() : null
    ], 500);
}

/**
 * Manejar endpoints de productos
 */
function handleProductsEndpoint($method, $resourceId, $action, $input) {
    $productAPI = getProductAPI();

    switch ($method) {
        case 'GET':
            if ($resourceId) {
                // Obtener producto específico
                $result = $productAPI->getProduct($resourceId);
            } else {
                // Listar productos con filtros
                $filters = [
                    'page' => $input['page'] ?? 1,
                    'per_page' => $input['per_page'] ?? 12,
                    'category' => $input['category'] ?? null,
                    'search' => $input['search'] ?? null,
                    'min_price' => $input['min_price'] ?? null,
                    'max_price' => $input['max_price'] ?? null,
                    'orderby' => $input['orderby'] ?? null,
                    'featured' => $input['featured'] ?? null,
                    'in_stock' => $input['in_stock'] ?? null
                ];
                $result = $productAPI->getProducts($filters);
            }
            break;

        default:
            $result = ['success' => false, 'message' => 'Método no permitido', 'allowed' => ['GET']];
            http_response_code(405);
    }

    jsonResponse($result);
}

/**
 * Manejar endpoints de categorías
 */
function handleCategoriesEndpoint($method, $resourceId, $action, $input) {
    $productAPI = getProductAPI();

    switch ($method) {
        case 'GET':
            $parentId = $input['parent'] ?? null;
            $result = $productAPI->getCategories($parentId);
            break;

        default:
            $result = ['success' => false, 'message' => 'Método no permitido', 'allowed' => ['GET']];
            http_response_code(405);
    }

    jsonResponse($result);
}

/**
 * Manejar endpoints del carrito
 */
function handleCartEndpoint($method, $resourceId, $action, $input, $userId) {
    $cartAPI = getCartAPI();

    switch ($method) {
        case 'GET':
            if ($resourceId === 'count') {
                $result = $cartAPI->getCartCount($userId);
            } else {
                $result = $cartAPI->getCart($userId);
            }
            break;

        case 'POST':
            validateCSRF();
            if ($action === 'add') {
                $result = $cartAPI->addToCart(
                    $input['product_id'],
                    $input['quantity'] ?? 1,
                    $input['variation_id'] ?? null,
                    $userId
                );
            } elseif ($action === 'clear') {
                $result = $cartAPI->clearCart($userId);
            } elseif ($action === 'migrate') {
                $result = $cartAPI->migrateCartToUser($userId);
            } else {
                $result = ['success' => false, 'message' => 'Acción no válida'];
                http_response_code(400);
            }
            break;

        case 'PUT':
            validateCSRF();
            if ($resourceId && is_numeric($resourceId)) {
                $result = $cartAPI->updateCartItem($resourceId, $input['quantity'], $userId);
            } else {
                $result = ['success' => false, 'message' => 'ID de item requerido'];
                http_response_code(400);
            }
            break;

        case 'DELETE':
            validateCSRF();
            if ($resourceId && is_numeric($resourceId)) {
                $result = $cartAPI->removeFromCart($resourceId, $userId);
            } else {
                $result = ['success' => false, 'message' => 'ID de item requerido'];
                http_response_code(400);
            }
            break;

        default:
            $result = ['success' => false, 'message' => 'Método no permitido', 'allowed' => ['GET', 'POST', 'PUT', 'DELETE']];
            http_response_code(405);
    }

    jsonResponse($result);
}

/**
 * Manejar endpoints de checkout
 */
function handleCheckoutEndpoint($method, $resourceId, $action, $input, $userId) {
    $checkoutAPI = getCheckoutAPI();

    switch ($method) {
        case 'POST':
            validateCSRF();
            if ($action === 'create') {
                $result = $checkoutAPI->createOrder($input, $userId);
            } else {
                $result = ['success' => false, 'message' => 'Acción no válida'];
                http_response_code(400);
            }
            break;

        default:
            $result = ['success' => false, 'message' => 'Método no permitido', 'allowed' => ['POST']];
            http_response_code(405);
    }

    jsonResponse($result);
}

/**
 * Manejar endpoints de pedidos
 */
function handleOrdersEndpoint($method, $resourceId, $action, $input, $userId) {
    $checkoutAPI = getCheckoutAPI();

    switch ($method) {
        case 'GET':
            if ($resourceId && is_numeric($resourceId)) {
                // Obtener pedido específico
                $result = $checkoutAPI->getOrder($resourceId, $userId);
            } else {
                // Obtener pedidos del usuario
                $page = $input['page'] ?? 1;
                $perPage = $input['per_page'] ?? 10;
                $result = $checkoutAPI->getUserOrders($userId, $page, $perPage);
            }
            break;

        default:
            $result = ['success' => false, 'message' => 'Método no permitido', 'allowed' => ['GET']];
            http_response_code(405);
    }

    jsonResponse($result);
}

/**
 * Manejar endpoints de cupones
 */
function handleCouponsEndpoint($method, $resourceId, $action, $input) {
    // Solo validación básica de cupones por ahora
    switch ($method) {
        case 'POST':
            validateCSRF();
            if ($action === 'validate') {
                $result = validateCoupon($input['code'] ?? '');
            } else {
                $result = ['success' => false, 'message' => 'Acción no válida'];
                http_response_code(400);
            }
            break;

        default:
            $result = ['success' => false, 'message' => 'Método no permitido', 'allowed' => ['POST']];
            http_response_code(405);
    }

    jsonResponse($result);
}

/**
 * Manejar endpoints de configuración
 */
function handleSettingsEndpoint($method, $resourceId, $action, $input) {
    $db = getEcommerceDB();

    switch ($method) {
        case 'GET':
            if ($resourceId) {
                // Obtener configuración específica
                $setting = $db->selectOne(
                    "SELECT * FROM ecommerce_settings WHERE setting_key = ? AND is_public = 1",
                    [$resourceId]
                );

                if ($setting) {
                    $value = json_decode($setting['setting_value'], true) ?? $setting['setting_value'];
                    $result = ['success' => true, 'data' => ['key' => $resourceId, 'value' => $value]];
                } else {
                    $result = ['success' => false, 'message' => 'Configuración no encontrada'];
                    http_response_code(404);
                }
            } else {
                // Obtener todas las configuraciones públicas
                $settings = $db->select("SELECT setting_key, setting_value, setting_type FROM ecommerce_settings WHERE is_public = 1");

                $data = [];
                foreach ($settings as $setting) {
                    $value = json_decode($setting['setting_value'], true) ?? $setting['setting_value'];
                    $data[$setting['setting_key']] = $value;
                }

                $result = ['success' => true, 'data' => $data];
            }
            break;

        default:
            $result = ['success' => false, 'message' => 'Método no permitido', 'allowed' => ['GET']];
            http_response_code(405);
    }

    jsonResponse($result);
}

/**
 * Función helper para validar cupones
 */
function validateCoupon($code) {
    if (empty($code)) {
        return ['success' => false, 'message' => 'Código de cupón requerido'];
    }

    $db = getEcommerceDB();

    try {
        $coupon = $db->selectOne(
            "SELECT * FROM coupons WHERE code = ? AND status = 'active'",
            [$code]
        );

        if (!$coupon) {
            return ['success' => false, 'message' => 'Cupón inválido'];
        }

        // Verificar expiración
        if ($coupon['expiry_date'] && strtotime($coupon['expiry_date']) < time()) {
            return ['success' => false, 'message' => 'Cupón expirado'];
        }

        // Verificar límite de uso
        if ($coupon['usage_limit'] && $coupon['usage_count'] >= $coupon['usage_limit']) {
            return ['success' => false, 'message' => 'Cupón agotado'];
        }

        return [
            'success' => true,
            'data' => [
                'code' => $coupon['code'],
                'description' => $coupon['description'],
                'discount_type' => $coupon['discount_type'],
                'discount_value' => $coupon['discount_value'],
                'minimum_amount' => $coupon['minimum_amount'],
                'maximum_amount' => $coupon['maximum_amount']
            ]
        ];

    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error al validar cupón', 'error' => $e->getMessage()];
    }
}

?>