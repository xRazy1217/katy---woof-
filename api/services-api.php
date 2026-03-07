<?php
/**
 * Katy & Woof - Services API Module v6.0
 * Gestión de servicios
 */

require_once 'database.php';
require_once 'image-handler.php';

class ServicesAPI {
    public static function get() {
        $pdo = Database::getConnection();
        try {
            return $pdo->query("SELECT * FROM services ORDER BY id DESC")->fetchAll();
        } catch (Exception $e) {
            Database::runSetup();
            return $pdo->query("SELECT * FROM services ORDER BY id DESC")->fetchAll();
        }
    }

    public static function save($postData, $files) {
        $pdo = Database::getConnection();

        if (!empty($postData['id'])) {
            // Update
            if (isset($files['main_file']) && $files['main_file']['error'] !== UPLOAD_ERR_NO_FILE) {
                $old = $pdo->prepare("SELECT main_image_url FROM services WHERE id = ?");
                $old->execute([$postData['id']]);
                $old_path = $old->fetchColumn();

                $res = ImageHandler::optimizeAndSaveImage($files['main_file'], "svc");
                if ($res['success']) {
                    $pdo->prepare("UPDATE services SET title = ?, description = ?, main_image_url = ? WHERE id = ?")
                        ->execute([$postData['title'], $postData['description'], $res['path'], $postData['id']]);
                    ImageHandler::deletePhysicalFile($old_path);
                } else {
                    return $res;
                }
            } else {
                $pdo->prepare("UPDATE services SET title = ?, description = ? WHERE id = ?")
                    ->execute([$postData['title'], $postData['description'], $postData['id']]);
            }
        } else {
            // Insert
            $img = 'img/placeholder.jpg';
            if(isset($files['main_file']) && $files['main_file']['error'] !== UPLOAD_ERR_NO_FILE){
                $res = ImageHandler::optimizeAndSaveImage($files['main_file'], "svc");
                if ($res['success']) {
                    $img = $res['path'];
                } else {
                    return $res;
                }
            }
            $pdo->prepare("INSERT INTO services (title, description, main_image_url) VALUES (?, ?, ?)")->execute([$postData['title'], $postData['description'], $img]);
        }
        return ["success" => true];
    }

    public static function delete($id) {
        $pdo = Database::getConnection();
        $old = $pdo->prepare("SELECT main_image_url FROM services WHERE id = ?");
        $old->execute([$id]);
        ImageHandler::deletePhysicalFile($old->fetchColumn());
        $pdo->prepare("DELETE FROM services WHERE id = ?")->execute([$id]);
        return ["success" => true];
    }
}
?>