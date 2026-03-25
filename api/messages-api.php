<?php
/**
 * Katy & Woof - Messages & Customers API
 */

require_once 'database.php';

class MessagesAPI {
    public static function setup() {
        $pdo = Database::getConnection();
        $pdo->exec("CREATE TABLE IF NOT EXISTS `contact_messages` (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(255) NOT NULL,
            `email` VARCHAR(255) NOT NULL,
            `phone` VARCHAR(50),
            `subject` VARCHAR(255),
            `message` TEXT NOT NULL,
            `read_at` DATETIME DEFAULT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    public static function getAll() {
        $pdo = Database::getConnection();
        try {
            return ['success' => true, 'data' => $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC")->fetchAll()];
        } catch (Exception $e) {
            self::setup();
            return ['success' => true, 'data' => []];
        }
    }

    public static function create($data) {
        $pdo = Database::getConnection();
        try {
            $pdo->prepare("INSERT INTO contact_messages (name, email, phone, subject, message) VALUES (?,?,?,?,?)")
                ->execute([
                    trim($data['name'] ?? ''),
                    trim($data['email'] ?? ''),
                    trim($data['phone'] ?? ''),
                    trim($data['subject'] ?? ''),
                    trim($data['message'] ?? '')
                ]);
            return ['success' => true];
        } catch (Exception $e) {
            self::setup();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public static function markRead($id) {
        $pdo = Database::getConnection();
        $pdo->prepare("UPDATE contact_messages SET read_at = NOW() WHERE id = ?")->execute([$id]);
        return ['success' => true];
    }

    public static function delete($id) {
        $pdo = Database::getConnection();
        $pdo->prepare("DELETE FROM contact_messages WHERE id = ?")->execute([$id]);
        return ['success' => true];
    }
}

class CustomersAPI {
    public static function getAll() {
        $pdo = Database::getConnection();
        try {
            $rows = $pdo->query("
                SELECT
                    customer_email AS email,
                    MAX(JSON_UNQUOTE(JSON_EXTRACT(customer_data, '$.name'))) AS name,
                    MAX(JSON_UNQUOTE(JSON_EXTRACT(customer_data, '$.phone'))) AS phone,
                    COUNT(*) AS total_orders,
                    SUM(total) AS total_spent,
                    MAX(created_at) AS last_order
                FROM orders
                WHERE customer_email IS NOT NULL AND customer_email != ''
                GROUP BY customer_email
                ORDER BY total_spent DESC
            ")->fetchAll();
            return ['success' => true, 'data' => $rows];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage(), 'data' => []];
        }
    }
}
?>
