<?php
/**
 * Katy & Woof - API Router v6.5
 * Refactorizado para mayor modularidad y legibilidad.
 */

ob_start();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');

// Gestión de errores garantizando salida JSON
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    error_log("API Warning [$errno] $errstr in $errfile:$errline");
    return true;
}, E_ALL);

register_shutdown_function(function() {
    $lastError = error_get_last();
    if (!$lastError) return;
    $fatalTypes = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR];
    if (!in_array($lastError['type'], $fatalTypes, true)) return;
    if (ob_get_length()) ob_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Fatal error en API', 'detail' => $lastError['message']]);
});

// Carga de dependencias
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/image-handler.php';
require_once __DIR__ . '/settings-api.php';
require_once __DIR__ . '/portfolio-api.php';
require_once __DIR__ . '/blog-api.php';
require_once __DIR__ . '/process-api.php';
require_once __DIR__ . '/lists-api.php';
require_once __DIR__ . '/schema-auditor.php';
require_once __DIR__ . '/cart-api.php';
require_once __DIR__ . '/checkout-api.php';
require_once __DIR__ . '/messages-api.php';
require_once __DIR__ . '/users-api.php';
require_once __DIR__ . '/ecommerce-api.php';

$action = $_GET['action'] ?? null;
$auth_key = $_GET['auth'] ?? $_POST['auth'] ?? null;

try {
    Auth::checkAuth($action, $auth_key);
    $body = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    switch ($action) {
        // ── SISTEMA ──
        case 'setup':               echo json_encode(['success' => true, 'msg' => 'v6.5 Ready']); break;
        case 'get_db_status':       echo json_encode(SettingsAPI::getStatus()); break;
        case 'audit_schema':        echo json_encode(['success' => true, 'data' => (new SchemaManager())->auditSchema()]); break;
        case 'sync_database':
        case 'repair_database':
        case 'initialize_database':
            $res = (new SchemaManager())->syncDatabase();
            logEvent('database_action', "Acción $action: " . ($res['success']?'OK':'ERR'), $_SERVER['REMOTE_ADDR']??'');
            echo json_encode($res); break;

        // ── AJUSTES ──
        case 'get_settings':        echo json_encode(SettingsAPI::get()); break;
        case 'save_settings':       echo json_encode(SettingsAPI::save($_POST, $_FILES)); break;

        // ── PORTFOLIO / BLOG ──
        case 'get_portfolio':       echo json_encode(PortfolioAPI::get()); break;
        case 'save_portfolio':      echo json_encode(PortfolioAPI::save($_POST, $_FILES)); break;
        case 'delete_portfolio':    echo json_encode(PortfolioAPI::delete($_GET['id']??0)); break;
        case 'get_blog':            echo json_encode(BlogAPI::get()); break;
        case 'save_blog':           echo json_encode(BlogAPI::save($_POST, $_FILES)); break;
        case 'delete_blog':         echo json_encode(BlogAPI::delete($_GET['id']??0)); break;

        // ── E-COMMERCE (PRODUCTOS) ──
        case 'get_products':        echo json_encode(EcommerceAPI::getProducts()); break;
        case 'save_product':
        case 'create_product':
        case 'update_product':      echo json_encode(EcommerceAPI::saveProduct($_POST, $_FILES)); break;
        case 'delete_product':      echo json_encode(EcommerceAPI::deleteProduct($_POST['id']??$_GET['id']??0)); break;
        case 'get_catalog':         echo json_encode(EcommerceAPI::getCatalog()); break;
        case 'get_categories':      echo json_encode(EcommerceAPI::getCategories()); break;
        case 'save_category':
        case 'create_category':
        case 'update_category':      echo json_encode(EcommerceAPI::saveCategory($body)); break;
        case 'delete_category':      echo json_encode(EcommerceAPI::deleteCategory($_POST['id']??$_GET['id']??0)); break;

        // ── E-COMMERCE (ORDENES) ──
        case 'get_orders':          echo json_encode(EcommerceAPI::getOrders()); break;
        case 'get_order_stats':     echo json_encode(EcommerceAPI::getOrderStats()); break;
        case 'update_order_status': echo json_encode(EcommerceAPI::updateOrderStatus($body['id']??0, $body['status']??'')); break;

        // ── E-COMMERCE (CUPONES) ──
        case 'get_coupons':         echo json_encode(EcommerceAPI::getCoupons()); break;
        case 'save_coupon':
        case 'create_coupon':
        case 'update_coupon':       echo json_encode(EcommerceAPI::saveCoupon($body)); break;
        case 'delete_coupon':       echo json_encode(EcommerceAPI::deleteCoupon($_POST['id']??$_GET['id']??0)); break;
        case 'toggle_coupon':       echo json_encode(EcommerceAPI::toggleCoupon($body['id']??0, $body['status']??'active')); break;

        // ── USUARIOS ──
        case 'user_register':       echo json_encode(UsersAPI::register($body)); break;
        case 'user_login':          echo json_encode(UsersAPI::login($body)); break;
        case 'user_logout':         echo json_encode(UsersAPI::logout()); break;
        case 'user_me':             echo json_encode(UsersAPI::me()); break;
        case 'user_update':         echo json_encode(UsersAPI::updateProfile($body)); break;
        case 'user_orders':         echo json_encode(UsersAPI::getOrders()); break;

        // ── CARRITO / CHECKOUT ──
        case 'cart_get':            echo json_encode(CartAPI::get()); break;
        case 'cart_add':            echo json_encode(CartAPI::add($body)); break;
        case 'cart_update':         echo json_encode(CartAPI::update($body)); break;
        case 'cart_remove':         echo json_encode(CartAPI::remove($body)); break;
        case 'cart_clear':          echo json_encode(CartAPI::clear()); break;
        case 'checkout_create':     echo json_encode(CheckoutAPI::create($body)); break;
        case 'checkout_confirm':    echo json_encode(CheckoutAPI::confirm($_GET)); break;

        // ── MENSAJES ──
        case 'get_messages':        echo json_encode(MessagesAPI::getAll()); break;
        case 'send_message':        echo json_encode(MessagesAPI::create($body)); break;
        case 'mark_message_read':   echo json_encode(MessagesAPI::markRead($_GET['id']??$body['id']??0)); break;
        case 'delete_message':      echo json_encode(MessagesAPI::delete($_GET['id']??$body['id']??0)); break;

        default:                    echo json_encode(['error' => 'Acción no válida', 'action' => $action]); break;
    }
} catch (Exception $e) {
    if (ob_get_length()) ob_clean();
    http_response_code(500);
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}