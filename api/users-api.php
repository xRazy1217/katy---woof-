<?php
/**
 * Katy & Woof - Users API
 */

require_once __DIR__ . '/../config.php';

class UsersAPI {

    public static function register(array $data): array {
        $pdo = getDBConnection();
        $name     = trim($data['name'] ?? '');
        $email    = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';
        $phone    = trim($data['phone'] ?? '');

        if (!$name || !$email || !$password)
            return ['success' => false, 'error' => 'Nombre, correo y contraseña son requeridos'];
        if (!filter_var($email, FILTER_VALIDATE_EMAIL))
            return ['success' => false, 'error' => 'Correo inválido'];
        if (strlen($password) < 6)
            return ['success' => false, 'error' => 'La contraseña debe tener al menos 6 caracteres'];

        $exists = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $exists->execute([$email]);
        if ($exists->fetch())
            return ['success' => false, 'error' => 'Este correo ya está registrado'];

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO users (name, email, password, phone) VALUES (?,?,?,?)")
            ->execute([$name, $email, $hash, $phone]);

        $userId = $pdo->lastInsertId();
        return self::startSession($userId, $pdo);
    }

    public static function login(array $data): array {
        $pdo      = getDBConnection();
        $email    = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';

        if (!$email || !$password)
            return ['success' => false, 'error' => 'Correo y contraseña son requeridos'];

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password']))
            return ['success' => false, 'error' => 'Correo o contraseña incorrectos'];

        return self::startSession($user['id'], $pdo);
    }

    public static function logout(): array {
        if (session_status() === PHP_SESSION_NONE) session_start();
        session_destroy();
        return ['success' => true];
    }

    public static function me(): array {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['kw_user_id']))
            return ['success' => false, 'user' => null];

        $pdo  = getDBConnection();
        $stmt = $pdo->prepare("SELECT id, name, email, phone, avatar, created_at FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['kw_user_id']]);
        $user = $stmt->fetch();
        if (!$user) return ['success' => false, 'user' => null];

        return ['success' => true, 'user' => $user];
    }

    public static function updateProfile(array $data): array {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['kw_user_id']))
            return ['success' => false, 'error' => 'No autenticado'];

        $pdo   = getDBConnection();
        $id    = $_SESSION['kw_user_id'];
        $name  = trim($data['name'] ?? '');
        $phone = trim($data['phone'] ?? '');

        if (!$name) return ['success' => false, 'error' => 'El nombre es requerido'];

        $pdo->prepare("UPDATE users SET name=?, phone=? WHERE id=?")->execute([$name, $phone, $id]);

        if (!empty($data['password']) && strlen($data['password']) >= 6) {
            $hash = password_hash($data['password'], PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE users SET password=? WHERE id=?")->execute([$hash, $id]);
        }

        return self::me();
    }

    public static function getOrders(): array {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['kw_user_id']))
            return ['success' => false, 'error' => 'No autenticado', 'data' => []];

        $pdo  = getDBConnection();
        $stmt = $pdo->prepare("
            SELECT id, order_number, status, payment_status, total, created_at
            FROM orders WHERE user_id = ?
            ORDER BY created_at DESC
        ");
        $stmt->execute([$_SESSION['kw_user_id']]);
        return ['success' => true, 'data' => $stmt->fetchAll()];
    }

    private static function startSession(int $userId, $pdo): array {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['kw_user_id'] = $userId;

        $stmt = $pdo->prepare("SELECT id, name, email, phone, avatar, created_at FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        return ['success' => true, 'user' => $user];
    }
}
?>
