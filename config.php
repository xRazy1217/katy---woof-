<?php
/**
 * Katy & Woof - Configuración Maestra v8.0
 */

// ── Cargar .env ──
function loadEnv(string $filePath = ''): array {
    if (!$filePath) $filePath = __DIR__ . '/.env';
    if (!file_exists($filePath)) return [];
    $env = [];
    foreach (file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
        [$k, $v] = explode('=', $line, 2);
        $env[trim($k)] = trim(trim($v), "\"'");
    }
    return $env;
}

$envVars = loadEnv();

// ── Base de datos ──
define('DB_HOST', $envVars['DB_HOST'] ?? 'localhost');
define('DB_NAME', $envVars['DB_NAME'] ?? 'katywoof_ecommerce');
define('DB_USER', $envVars['DB_USER'] ?? 'root');
define('DB_PASS', $envVars['DB_PASS'] ?? '');

// ── Entorno ──
define('APP_ENV',  $envVars['APP_ENV']  ?? 'development');   // 'production' en SiteGround
define('APP_URL',  rtrim($envVars['APP_URL'] ?? self_detect_url(), '/'));
define('ADMIN_KEY',$envVars['ADMIN_AUTH_KEY'] ?? 'Asesor25');
define('UNIFIED_CATALOG', ($envVars['UNIFIED_CATALOG'] ?? 'true') === 'true');

// ── Detectar URL base automáticamente ──
function self_detect_url(): string {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    // Subir hasta la raíz del proyecto (donde está config.php)
    $base   = rtrim(dirname(dirname($script)), '/');
    return $scheme . '://' . $host . ($base === '/' ? '' : $base);
}

// ── Errores: solo mostrar en development ──
if (APP_ENV === 'development') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => true, // Cambiado a true para mayor compatibilidad
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        $pdo->exec("SET NAMES utf8mb4");
        return $pdo;
    } catch (PDOException $e) {
        // Si falla con localhost, intentamos con 127.0.0.1
        if (DB_HOST === 'localhost') {
            try {
                $dsn = "mysql:host=127.0.0.1;dbname=" . DB_NAME;
                $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
                $pdo->exec("SET NAMES utf8mb4");
                return $pdo;
            } catch (PDOException $e2) {
                // Si ambos fallan, devolvemos el error original
            }
        }
        header('Content-Type: application/json', true, 500);
        echo json_encode(['success' => false, 'error' => "Error de Conexión DB: " . $e->getMessage()]);
        exit;
    }
}

// Versión alternativa que lanza excepción en lugar de exit (para SchemaManager)
function getDBConnectionOrThrow() {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => true,
    ];
    
    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        $pdo->exec("SET NAMES utf8mb4");
        return $pdo;
    } catch (PDOException $e) {
        // Si falla con localhost, intentamos con 127.0.0.1
        if (DB_HOST === 'localhost') {
            try {
                $dsn = "mysql:host=127.0.0.1;dbname=" . DB_NAME;
                $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
                $pdo->exec("SET NAMES utf8mb4");
                return $pdo;
            } catch (PDOException $e2) {
                // Si ambos fallan, lanzamos el error original
                throw $e;
            }
        }
        throw $e;
    }
}

// Helper para obtener todos los settings de una vez
function getSiteSettings() {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
        $settings = [];
        while ($row = $stmt->fetch()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    } catch (Exception $e) {
        // Retornar array vacío si la tabla no existe o hay error
        return [];
    }
}

// Helper para registrar eventos/logs del sistema
function logEvent($event_type, $message, $ip_address = '', $user_agent = '') {
    try {
        $pdo = getDBConnection();
        
        if (empty($ip_address)) {
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'N/A';
        }
        if (empty($user_agent)) {
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'N/A';
        }
        
        $stmt = $pdo->prepare(
            "INSERT INTO logs (event_type, message, ip_address, user_agent) 
             VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$event_type, $message, $ip_address, $user_agent]);
        return true;
    } catch (Exception $e) {
        // Silent fail - no interrumpir operaciones por fallo de logging
        return false;
    }
}

