<?php
/**
 * Katy & Woof - Lists API Module v6.0
 * Gestión de listas (taxonomías)
 */

require_once 'database.php';

class ListsAPI {
    public static function get() {
        $pdo = Database::getConnection();
        try {
            return $pdo->query("SELECT * FROM site_lists ORDER BY item_value ASC")->fetchAll();
        } catch (Exception $e) {
            Database::runSetup();
            return $pdo->query("SELECT * FROM site_lists ORDER BY item_value ASC")->fetchAll();
        }
    }

    public static function saveItem($postData) {
        $pdo = Database::getConnection();
        $pdo->prepare("INSERT INTO site_lists (list_key, item_value) VALUES (?, ?)")->execute([$postData['list_key'], $postData['item_value']]);
        return ["success" => true];
    }

    public static function deleteItem($id) {
        $pdo = Database::getConnection();
        $pdo->prepare("DELETE FROM site_lists WHERE id = ?")->execute([$id]);
        return ["success" => true];
    }
}
?>