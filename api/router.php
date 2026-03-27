<?php
/**
 * Katy & Woof - API Router v6.0
 * Punto de entrada centralizado para todas las acciones API
 */

ob_start();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

// Evita que warnings/notices rompan el JSON de salida.
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    error_log("API Warning [$errno] $errstr in $errfile:$errline");
    return true;
}, E_ALL);

// Fallback de seguridad: garantiza JSON incluso ante errores fatales.
register_shutdown_function(function() {
    $lastError = error_get_last();
    if (!$lastError) {
        return;
    }

    $fatalTypes = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR];
    if (!in_array($lastError['type'], $fatalTypes, true)) {
        return;
    }

    if (ob_get_length()) {
        ob_clean();
    }

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Fatal error en API',
        'detail' => $lastError['message']
    ]);
});

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
if (file_exists(__DIR__ . '/ecommerce-api.php')) {
    require_once __DIR__ . '/ecommerce-api.php';
}
if (file_exists(__DIR__ . '/ecommerce-initializer.php')) {
    require_once __DIR__ . '/ecommerce-initializer.php';
}

$upload_dir = 'uploads/';
$action = $_GET['action'] ?? null;
$auth_key = $_GET['auth'] ?? $_POST['auth'] ?? null;

// Crear directorio de uploads si no existe
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
    file_put_contents($upload_dir . '.htaccess', "php_flag engine off\nOptions -ExecCGI\nAddHandler cgi-script .php .php3 .php4 .php5 .phtml .pl .py .jsp .asp .htm .html .sh .cgi");
}

try {
    // Verificar autenticación para acciones protegidas
    Auth::checkAuth($action, $auth_key);

    switch ($action) {
        case 'setup':
            Database::runSetup();
            echo json_encode(["success" => true, "msg" => "v6.0 Ready"]);
            break;

        case 'get_settings':
            echo json_encode(SettingsAPI::get());
            break;

        case 'save_settings':
            $result = SettingsAPI::save($_POST, $_FILES);
            echo json_encode($result);
            break;

        case 'get_portfolio':
            echo json_encode(PortfolioAPI::get());
            break;

        case 'save_portfolio':
            $result = PortfolioAPI::save($_POST, $_FILES);
            echo json_encode($result);
            break;

        case 'delete_portfolio':
            $result = PortfolioAPI::delete($_GET['id']);
            echo json_encode($result);
            break;

        case 'get_blog':
            echo json_encode(BlogAPI::get());
            break;

        case 'save_blog':
            $result = BlogAPI::save($_POST, $_FILES);
            echo json_encode($result);
            break;

        case 'delete_blog':
            $result = BlogAPI::delete($_GET['id']);
            echo json_encode($result);
            break;

        case 'get_process':
            echo json_encode(ProcessAPI::get());
            break;

        case 'save_process':
            $result = ProcessAPI::save($_POST, $_FILES);
            echo json_encode($result);
            break;

        case 'delete_process':
            $result = ProcessAPI::delete($_GET['id']);
            echo json_encode($result);
            break;

        case 'get_lists':
            echo json_encode(ListsAPI::get());
            break;

        case 'save_list_item':
            $result = ListsAPI::saveItem($_POST);
            echo json_encode($result);
            break;

        case 'delete_list_item':
            $result = ListsAPI::deleteItem($_GET['id']);
            echo json_encode($result);
            break;

        case 'audit_schema':
            $schemaManager = new SchemaManager();
            $audit = $schemaManager->auditSchema();
            echo json_encode([
                'success' => true,
                'data' => $audit
            ]);
            break;

        case 'sync_database':
        case 'repair_database':
            $schemaManager = new SchemaManager();
            $syncResult = $schemaManager->syncDatabase();
            $eventName = $action === 'repair_database' ? 'database_repair' : 'database_sync';
            $eventMessagePrefix = $action === 'repair_database' ? 'Reparación ejecutada: ' : 'Sincronización ejecutada: ';
            logEvent($eventName, $eventMessagePrefix . ($syncResult['success'] ? 'Exitosa' : 'Fallida'), $_SERVER['REMOTE_ADDR'] ?? '');
            echo json_encode($syncResult);
            break;

        case 'initialize_database':
            $schemaManager = new SchemaManager();
            $initResult = $schemaManager->syncDatabase();
            logEvent('database_init', 'Inicialización de BD: ' . ($initResult['success'] ? 'Exitosa' : 'Fallida'), $_SERVER['REMOTE_ADDR'] ?? '');
            echo json_encode($initResult);
            break;

        case 'get_db_status':
            try {
                $schemaManager = new SchemaManager();
                $connectionTest = $schemaManager->testConnection();

                if ($connectionTest['success']) {
                    $tableCount = Database::getConnection()->query("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = '" . DB_NAME . "'")->fetchColumn();
                    $sizeQuery = "SELECT ROUND(SUM(DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024, 2) as size_mb
                                  FROM information_schema.TABLES
                                  WHERE TABLE_SCHEMA = ?";
                    $sizeStmt = Database::getConnection()->prepare($sizeQuery);
                    $sizeStmt->execute([DB_NAME]);
                    $sizeResult = $sizeStmt->fetch(PDO::FETCH_ASSOC);

                    $connectionTest['table_count'] = (int)$tableCount;
                    $connectionTest['size_mb'] = floatval($sizeResult['size_mb'] ?? 0);
                    
                    echo json_encode([
                        'success' => true,
                        'data' => $connectionTest
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'data' => $connectionTest
                    ]);
                }
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'data' => [
                        'success' => false,
                        'error' => $e->getMessage()
                    ]
                ]);
            }
            break;

        case 'test_connection':
            $pdo = Database::getConnection();
            $pdo->query("SELECT 1");
            $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            $writable = is_writable($upload_dir) || is_writable('.');
            $webp = function_exists('imagewebp');

            echo json_encode([
                "success" => true,
                "msg" => "Conexión con Atelier y Base de Datos exitosa",
                "php_version" => PHP_VERSION,
                "finfo_enabled" => class_exists('finfo'),
                "db_status" => "Connected",
                "tables" => $tables,
                "upload_dir_writable" => $writable,
                "upload_dir" => $upload_dir,
                "webp_support" => $webp
            ]);
            break;

        case 'get_categories':
        case 'ecommerce/categories':
            if (class_exists('EcommerceAPI')) {
                $result = EcommerceAPI::getCategories();

                // Fallback por compatibilidad de esquema (sin deleted_at)
                if (!empty($result['success'])) {
                    echo json_encode($result);
                    break;
                }

                $fallbackNeeded = isset($result['error'])
                    && stripos($result['error'], 'deleted_at') !== false;

                if ($fallbackNeeded) {
                    try {
                        $pdo = Database::getConnection();
                        $stmt = $pdo->prepare("SELECT id, name, slug, description, parent_id, image_url FROM product_categories ORDER BY parent_id, name ASC");
                        $stmt->execute();
                        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        echo json_encode(['success' => true, 'data' => $rows, 'count' => count($rows)]);
                    } catch (Exception $e) {
                        echo json_encode(['success' => false, 'error' => 'Fallback categorías falló: ' . $e->getMessage(), 'data' => []]);
                    }
                } else {
                    echo json_encode($result);
                }
            } else {
                echo json_encode(['success' => false, 'error' => 'Ecommerce API no disponible', 'data' => []]);
            }
            break;

        case 'get_products':
        case 'ecommerce/products':
            if (class_exists('EcommerceAPI')) {
                $result = EcommerceAPI::getProducts();

                // Fallback por compatibilidad de esquema (sin regular_price/deleted_at)
                if (!empty($result['success'])) {
                    echo json_encode($result);
                    break;
                }

                $fallbackNeeded = isset($result['error'])
                    && (
                        stripos($result['error'], 'regular_price') !== false
                        || stripos($result['error'], 'deleted_at') !== false
                    );

                if ($fallbackNeeded) {
                    try {
                        $pdo = Database::getConnection();
                        $stmt = $pdo->prepare("SELECT p.id, p.name, p.slug, p.description, p.sku, p.price, p.price AS regular_price, p.sale_price, p.stock_quantity, p.status, p.image_url, p.category_id, pc.name as category_name, p.stock_status, p.created_at, p.updated_at FROM products p LEFT JOIN product_categories pc ON p.category_id = pc.id ORDER BY p.created_at DESC");
                        $stmt->execute();
                        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($rows as &$product) {
                            $product['price'] = floatval($product['price'] ?? 0);
                            $product['regular_price'] = floatval($product['regular_price'] ?? 0);
                            $product['sale_price'] = floatval($product['sale_price'] ?? 0);
                            $product['stock_quantity'] = intval($product['stock_quantity'] ?? 0);
                        }
                        echo json_encode(['success' => true, 'data' => $rows, 'count' => count($rows)]);
                    } catch (Exception $e) {
                        echo json_encode(['success' => false, 'error' => 'Fallback productos falló: ' . $e->getMessage(), 'data' => []]);
                    }
                } else {
                    echo json_encode($result);
                }
            } else {
                echo json_encode(['success' => false, 'error' => 'Ecommerce API no disponible', 'data' => []]);
            }
            break;

        case 'get_catalog':
        case 'ecommerce/catalog':
            if (class_exists('EcommerceAPI')) {
                echo json_encode(EcommerceAPI::getCatalog());
            } else {
                echo json_encode(['success' => false, 'error' => 'Ecommerce API no disponible', 'data' => []]);
            }
            break;

        case 'get_orders':
        case 'ecommerce/orders':
            if (class_exists('EcommerceAPI')) {
                echo json_encode(EcommerceAPI::getOrders());
            } else {
                echo json_encode(['success' => false, 'error' => 'Ecommerce API no disponible', 'data' => []]);
            }
            break;

        case 'ecommerce/orders/stats':
            if (class_exists('EcommerceAPI')) {
                echo json_encode(EcommerceAPI::getOrderStats());
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => 'Ecommerce API no disponible',
                    'data' => [
                        'total' => 0,
                        'completed' => 0,
                        'pending' => 0,
                        'cancelled' => 0
                    ]
                ]);
            }
            break;

        case 'get_coupons':
        case 'ecommerce/coupons':
            if (class_exists('EcommerceAPI')) {
                echo json_encode(EcommerceAPI::getCoupons());
            } else {
                echo json_encode(['success' => false, 'error' => 'Ecommerce API no disponible', 'data' => []]);
            }
            break;

        case 'ecommerce_init':
            if (class_exists('EcommerceDatabaseInitializer')) {
                echo json_encode(EcommerceDatabaseInitializer::initialize());
            } else {
                $schemaManager = new SchemaManager();
                echo json_encode($schemaManager->syncDatabase());
            }
            break;

        case 'ecommerce_status':
            if (class_exists('EcommerceDatabaseInitializer')) {
                echo json_encode(EcommerceDatabaseInitializer::checkStatus());
            } else {
                $schemaManager = new SchemaManager();
                $audit = $schemaManager->auditSchema();
                echo json_encode([
                    'success' => true,
                    'total_tables' => $audit['total_tables'] ?? 0,
                    'created_tables' => $audit['ok_tables'] ?? 0,
                    'missing_tables' => $audit['missing_tables_count'] ?? 0,
                    'details' => $audit['tables'] ?? []
                ]);
            }
            break;

        // ── MENSAJES ──
        case 'get_messages':
            echo json_encode(MessagesAPI::getAll());
            break;

        case 'send_message':
            $body = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            echo json_encode(MessagesAPI::create($body));
            break;

        case 'mark_message_read':
            echo json_encode(MessagesAPI::markRead($_GET['id'] ?? $_POST['id'] ?? 0));
            break;

        case 'delete_message':
            echo json_encode(MessagesAPI::delete($_GET['id'] ?? $_POST['id'] ?? 0));
            break;

        // ── CLIENTES ──
        case 'get_customers':
            echo json_encode(CustomersAPI::getAll());
            break;

        // ── CATEGORIAS CRUD ──
        case 'create_category':
        case 'update_category': {
            $body = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            $pdo  = Database::getConnection();
            $name = trim($body['name'] ?? '');
            if (!$name) { echo json_encode(['success'=>false,'error'=>'Nombre requerido']); break; }
            $slug = trim($body['slug'] ?? '') ?: strtolower(preg_replace('/[^a-z0-9]+/i','-',$name));
            $desc = trim($body['description'] ?? '');
            $id   = intval($body['id'] ?? 0);
            if ($id) {
                $pdo->prepare("UPDATE product_categories SET name=?,slug=?,description=? WHERE id=?")->execute([$name,$slug,$desc,$id]);
            } else {
                $pdo->prepare("INSERT INTO product_categories (name,slug,description) VALUES (?,?,?)")->execute([$name,$slug,$desc]);
            }
            echo json_encode(['success'=>true]);
            break;
        }
        case 'delete_category': {
            $id = intval($_GET['id'] ?? $_POST['id'] ?? 0);
            Database::getConnection()->prepare("DELETE FROM product_categories WHERE id=?")->execute([$id]);
            echo json_encode(['success'=>true]);
            break;
        }

        // ── PRODUCTOS CRUD ──
        case 'create_product':
        case 'update_product': {
            $pdo  = Database::getConnection();
            $id   = intval($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            if (!$name) { echo json_encode(['success'=>false,'error'=>'Nombre requerido']); break; }
            $slug  = strtolower(preg_replace('/[^a-z0-9]+/i','-',$name)) . '-' . time();
            $price = floatval($_POST['price'] ?? 0);
            $sale  = floatval($_POST['sale_price'] ?? 0);
            $stock = intval($_POST['stock_quantity'] ?? 0);
            $cat   = intval($_POST['category_id'] ?? 0) ?: null;
            $sku   = trim($_POST['sku'] ?? '');
            $desc  = trim($_POST['description'] ?? '');
            $status = $_POST['status'] ?? 'publish';
            $imgUrl = trim($_POST['image_url'] ?? '');
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $res = ImageHandler::optimizeAndSaveImage($_FILES['image'], 'products');
                if ($res['success']) $imgUrl = '/' . ltrim($res['path'], '/');
            }
            if ($id) {
                $pdo->prepare("UPDATE products SET name=?,slug=?,description=?,sku=?,price=?,sale_price=?,stock_quantity=?,category_id=?,status=?" . ($imgUrl ? ",image_url=?" : "") . " WHERE id=?")
                    ->execute($imgUrl ? [$name,$slug,$desc,$sku,$price,$sale,$stock,$cat,$status,$imgUrl,$id] : [$name,$slug,$desc,$sku,$price,$sale,$stock,$cat,$status,$id]);
            } else {
                $pdo->prepare("INSERT INTO products (name,slug,description,sku,price,sale_price,stock_quantity,category_id,status,image_url) VALUES (?,?,?,?,?,?,?,?,?,?)")
                    ->execute([$name,$slug,$desc,$sku,$price,$sale,$stock,$cat,$status,$imgUrl]);
            }
            echo json_encode(['success'=>true]);
            break;
        }
        case 'delete_product': {
            $id = intval($_POST['id'] ?? $_GET['id'] ?? 0);
            Database::getConnection()->prepare("DELETE FROM products WHERE id=?")->execute([$id]);
            echo json_encode(['success'=>true]);
            break;
        }

        // ── ORDENES CRUD ──
        case 'update_order_status': {
            $body   = json_decode(file_get_contents('php://input'), true) ?? [];
            $id     = intval($body['id'] ?? 0);
            $status = $body['status'] ?? '';
            Database::getConnection()->prepare("UPDATE orders SET status=? WHERE id=?")->execute([$status,$id]);
            echo json_encode(['success'=>true]);
            break;
        }

        // ── CUPONES CRUD ──
        case 'create_coupon':
        case 'update_coupon': {
            $body   = json_decode(file_get_contents('php://input'), true) ?? [];
            $pdo    = Database::getConnection();
            $id     = intval($body['id'] ?? 0);
            $code   = strtoupper(trim($body['code'] ?? ''));
            if (!$code) { echo json_encode(['success'=>false,'error'=>'Código requerido']); break; }
            $type   = $body['discount_type'] ?? 'percentage';
            $value  = floatval($body['discount_value'] ?? 0);
            $min    = floatval($body['minimum_amount'] ?? 0);
            $limit  = intval($body['usage_limit'] ?? 0);
            $expiry = $body['expiry_date'] ?: null;
            $desc   = trim($body['description'] ?? '');
            $status = $body['status'] ?? 'active';
            if ($id) {
                $pdo->prepare("UPDATE coupons SET code=?,discount_type=?,discount_value=?,minimum_amount=?,usage_limit=?,expiry_date=?,description=?,status=? WHERE id=?")
                    ->execute([$code,$type,$value,$min,$limit,$expiry,$desc,$status,$id]);
            } else {
                $pdo->prepare("INSERT INTO coupons (code,discount_type,discount_value,minimum_amount,usage_limit,expiry_date,description,status) VALUES (?,?,?,?,?,?,?,?)")
                    ->execute([$code,$type,$value,$min,$limit,$expiry,$desc,$status]);
            }
            echo json_encode(['success'=>true]);
            break;
        }
        case 'delete_coupon': {
            $id = intval($_POST['id'] ?? $_GET['id'] ?? 0);
            Database::getConnection()->prepare("DELETE FROM coupons WHERE id=?")->execute([$id]);
            echo json_encode(['success'=>true]);
            break;
        }
        case 'toggle_coupon': {
            $body   = json_decode(file_get_contents('php://input'), true) ?? [];
            $id     = intval($body['id'] ?? 0);
            $status = $body['status'] ?? 'active';
            Database::getConnection()->prepare("UPDATE coupons SET status=? WHERE id=?")->execute([$status,$id]);
            echo json_encode(['success'=>true]);
            break;
        }

        // ── USUARIOS ──
        case 'user_register':
            $body = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            echo json_encode(UsersAPI::register($body));
            break;
        case 'user_login':
            $body = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            echo json_encode(UsersAPI::login($body));
            break;
        case 'user_logout':
            echo json_encode(UsersAPI::logout());
            break;
        case 'user_me':
            echo json_encode(UsersAPI::me());
            break;
        case 'user_update':
            $body = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            echo json_encode(UsersAPI::updateProfile($body));
            break;
        case 'user_orders':
            echo json_encode(UsersAPI::getOrders());
            break;

        // ── CARRITO ──
        case 'cart_get':
            echo json_encode(CartAPI::get());
            break;
        case 'cart_add':
            $body = json_decode(file_get_contents('php://input'), true) ?? [];
            echo json_encode(CartAPI::add($body));
            break;
        case 'cart_update':
            $body = json_decode(file_get_contents('php://input'), true) ?? [];
            echo json_encode(CartAPI::update($body));
            break;
        case 'cart_remove':
            $body = json_decode(file_get_contents('php://input'), true) ?? [];
            echo json_encode(CartAPI::remove($body));
            break;
        case 'cart_clear':
            echo json_encode(CartAPI::clear());
            break;

        // ── CHECKOUT ──
        case 'checkout_create':
            $body = json_decode(file_get_contents('php://input'), true) ?? [];
            echo json_encode(CheckoutAPI::create($body));
            break;
        case 'checkout_confirm':
            echo json_encode(CheckoutAPI::confirm($_GET));
            break;

        default:
            echo json_encode(PortfolioAPI::get());
            break;
    }
} catch (Exception $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>