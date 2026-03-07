<?php
/**
 * Katy & Woof - API Router v6.0
 * Punto de entrada centralizado para todas las acciones API
 */

ob_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

require_once '../config.php';
require_once 'auth.php';
require_once 'database.php';
require_once 'image-handler.php';
require_once 'settings-api.php';
require_once 'portfolio-api.php';
require_once 'services-api.php';
require_once 'blog-api.php';
require_once 'process-api.php';
require_once 'lists-api.php';
require_once 'schema-auditor.php';

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

        case 'get_services':
            echo json_encode(ServicesAPI::get());
            break;

        case 'save_service':
            $result = ServicesAPI::save($_POST, $_FILES);
            echo json_encode($result);
            break;

        case 'delete_service':
            $result = ServicesAPI::delete($_GET['id']);
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

        case 'get_db_status':
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
            }

            echo json_encode([
                'success' => true,
                'data' => $connectionTest
            ]);
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

        default:
            // Default: get portfolio
            echo json_encode(PortfolioAPI::get());
            break;
    }
} catch (Exception $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>