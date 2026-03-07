<?php
/**
 * Katy & Woof - Portfolio API Module v6.0
 * Gestión de portafolio de obras
 */

require_once 'database.php';
require_once 'image-handler.php';

class PortfolioAPI {
    public static function get() {
        $pdo = Database::getConnection();
        try {
            return $pdo->query("SELECT * FROM portfolio ORDER BY id DESC")->fetchAll();
        } catch (Exception $e) {
            Database::runSetup();
            return $pdo->query("SELECT * FROM portfolio ORDER BY id DESC")->fetchAll();
        }
    }

    public static function save($postData, $files) {
        $pdo = Database::getConnection();

        if (!empty($postData['id'])) {
            // Update
            if (isset($files['file']) && $files['file']['error'] !== UPLOAD_ERR_NO_FILE) {
                $old = $pdo->prepare("SELECT img_url FROM portfolio WHERE id = ?");
                $old->execute([$postData['id']]);
                $old_path = $old->fetchColumn();

                $res = ImageHandler::optimizeAndSaveImage($files['file'], "art");
                if ($res['success']) {
                    $pdo->prepare("UPDATE portfolio SET name = ?, description = ?, img_url = ? WHERE id = ?")
                        ->execute([$postData['name'], $postData['description'], $res['path'], $postData['id']]);
                    ImageHandler::deletePhysicalFile($old_path);
                } else {
                    return $res;
                }
            } else {
                $pdo->prepare("UPDATE portfolio SET name = ?, description = ? WHERE id = ?")
                    ->execute([$postData['name'], $postData['description'], $postData['id']]);
            }
        } else {
            // Insert
            $img = 'img/placeholder.jpg';
            if(isset($files['file']) && $files['file']['error'] !== UPLOAD_ERR_NO_FILE){
                $res = ImageHandler::optimizeAndSaveImage($files['file'], "art");
                if ($res['success']) {
                    $img = $res['path'];
                } else {
                    return $res;
                }
            }
            $pdo->prepare("INSERT INTO portfolio (name, description, img_url) VALUES (?, ?, ?)")->execute([$postData['name'], $postData['description'], $img]);
        }
        return ["success" => true];
    }

    public static function delete($id) {
        $pdo = Database::getConnection();
        $old = $pdo->prepare("SELECT img_url FROM portfolio WHERE id = ?");
        $old->execute([$id]);
        ImageHandler::deletePhysicalFile($old->fetchColumn());
        $pdo->prepare("DELETE FROM portfolio WHERE id = ?")->execute([$id]);
        return ["success" => true];
    }
}
?>