<?php
/**
 * Katy & Woof - Auth Module v6.0
 * Manejo de autenticación para API
 */

class Auth {
    private static $master_key = 'Asesor25';
    private static $protected_actions = [
        'save_settings', 'save_portfolio', 'save_service', 'save_blog',
        'save_process', 'save_list_item', 'delete_service', 'delete_blog',
        'delete_portfolio', 'delete_list_item', 'delete_process', 'sync_database', 'repair_database'
    ];

    public static function isAuthorized($auth_key) {
        return $auth_key === self::$master_key;
    }

    public static function requiresAuth($action) {
        return in_array($action, self::$protected_actions);
    }

    public static function checkAuth($action, $auth_key) {
        if (self::requiresAuth($action) && !self::isAuthorized($auth_key)) {
            http_response_code(401);
            echo json_encode(["success" => false, "error" => "Unauthorized"]);
            exit;
        }
    }
}
?>