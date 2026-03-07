<?php
/**
 * Katy & Woof - Configuración Maestra SiteGround & Helper v6.0
 */

// ========================================
// Cargar variables desde .env si existe
// ========================================
function loadEnv($filePath = __DIR__ . '/.env') {
    if (!file_exists($filePath)) {
        return [];
    }
    
    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $env = [];
    
    foreach ($lines as $line) {
        // Saltar comentarios y líneas vacías
        if (strpos(trim($line), '#') === 0 || empty(trim($line))) {
            continue;
        }
        
        // Parsear línea: KEY=VALUE
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Remover comillas si existen
            $value = trim($value, '"\'');
            
            $env[$key] = $value;
        }
    }
    
    return $env;
}

// Cargar .env
$envVars = loadEnv();

// Definir constantes de base de datos (priorizar .env, usar valores por defecto como fallback)
define('DB_HOST', $envVars['DB_HOST'] ?? 'localhost'); 
define('DB_NAME', $envVars['DB_NAME'] ?? 'dbyh6du0yfle1i');
define('DB_USER', $envVars['DB_USER'] ?? 'uiuxyllculkca');
define('DB_PASS', $envVars['DB_PASS'] ?? 'l2k13l3~1@&s'); 

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

