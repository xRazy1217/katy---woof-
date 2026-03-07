<?php
/**
 * Configuración de Base de Datos para E-commerce
 * Katy & Woof Creative Studio
 * Fase 1: Configuración de Conexión
 */

class EcommerceDatabase {
    private static $instance = null;
    private $connection;
    private $config;

    private function __construct() {
        $this->config = [
            'host' => 'localhost',
            'database' => 'katywoof_ecommerce',
            'username' => 'root', // Cambiar en producción
            'password' => '', // Cambiar en producción
            'charset' => 'utf8mb4',
            'port' => 3306
        ];

        $this->connect();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function connect() {
        try {
            $dsn = sprintf(
                "mysql:host=%s;dbname=%s;charset=%s;port=%s",
                $this->config['host'],
                $this->config['database'],
                $this->config['charset'],
                $this->config['port']
            );

            $this->connection = new PDO($dsn, $this->config['username'], $this->config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ]);

        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            throw new Exception("Error de conexión a la base de datos");
        }
    }

    public function getConnection() {
        return $this->connection;
    }

    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }

    public function commit() {
        return $this->connection->commit();
    }

    public function rollback() {
        return $this->connection->rollBack();
    }

    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Query error: " . $e->getMessage() . " - SQL: " . $sql);
            throw new Exception("Error en la consulta de base de datos");
        }
    }

    public function select($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll();
    }

    public function selectOne($sql, $params = []) {
        return $this->query($sql, $params)->fetch();
    }

    public function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";

        $this->query($sql, $data);
        return $this->connection->lastInsertId();
    }

    public function update($table, $data, $where, $whereParams = []) {
        $setParts = [];
        foreach ($data as $key => $value) {
            $setParts[] = "{$key} = :{$key}";
        }
        $setClause = implode(', ', $setParts);

        $sql = "UPDATE {$table} SET {$setClause} WHERE {$where}";

        $params = array_merge($data, $whereParams);
        return $this->query($sql, $params)->rowCount();
    }

    public function delete($table, $where, $params = []) {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        return $this->query($sql, $params)->rowCount();
    }

    public function getLastInsertId() {
        return $this->connection->lastInsertId();
    }

    // Método para verificar conexión
    public function ping() {
        try {
            $this->connection->query('SELECT 1');
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    // Método para obtener información de la base de datos
    public function getDatabaseInfo() {
        return [
            'database' => $this->config['database'],
            'host' => $this->config['host'],
            'charset' => $this->config['charset'],
            'version' => $this->connection->getAttribute(PDO::ATTR_SERVER_VERSION),
            'driver' => $this->connection->getAttribute(PDO::ATTR_DRIVER_NAME)
        ];
    }
}

// Función helper para obtener instancia de DB
function getEcommerceDB() {
    return EcommerceDatabase::getInstance();
}

// Función helper para sanitizar input
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

// Función helper para validar email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Función helper para generar respuesta JSON
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// Función helper para manejar errores
function handleError($message, $statusCode = 500, $details = null) {
    $error = [
        'success' => false,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ];

    if ($details && getenv('APP_ENV') === 'development') {
        $error['details'] = $details;
    }

    jsonResponse($error, $statusCode);
}

// Función helper para validar token CSRF
function validateCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        handleError('Token CSRF inválido', 403);
    }
}

// Función helper para generar token CSRF
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Función helper para logging
function ecommerceLog($message, $level = 'INFO', $context = []) {
    $logEntry = sprintf(
        "[%s] [%s] %s %s\n",
        date('Y-m-d H:i:s'),
        $level,
        $message,
        !empty($context) ? json_encode($context) : ''
    );

    $logFile = __DIR__ . '/../logs/ecommerce.log';
    $logDir = dirname($logFile);

    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

// Inicializar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

?>