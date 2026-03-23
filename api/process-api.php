<?php
/**
 * Katy & Woof - Process API Module v6.0
 * Gestión de pasos del proceso
 */

require_once 'database.php';
require_once 'image-handler.php';

class ProcessAPI {
    public static function get() {
        $pdo = Database::getConnection();
        try {
            return $pdo->query("SELECT * FROM process_steps ORDER BY step_number ASC")->fetchAll();
        } catch (Exception $e) {
            Database::runSetup();
            return $pdo->query("SELECT * FROM process_steps ORDER BY step_number ASC")->fetchAll();
        }
    }

    public static function save($postData, $files) {
        $pdo = Database::getConnection();

        if (!empty($postData['id'])) {
            // Update
            if (isset($files['file']) && $files['file']['error'] !== UPLOAD_ERR_NO_FILE) {
                $old = $pdo->prepare("SELECT img_url FROM process_steps WHERE id = ?");
                $old->execute([$postData['id']]);
                $old_path = $old->fetchColumn();

                $res = ImageHandler::optimizeAndSaveImage($files['file'], "proc");
                if ($res['success']) {
                    $pdo->prepare("UPDATE process_steps SET step_number = ?, title = ?, description = ?, img_url = ? WHERE id = ?")
                        ->execute([$postData['step_number'], $postData['title'], $postData['description'], $res['path'], $postData['id']]);
                    ImageHandler::deletePhysicalFile($old_path);
                } else {
                    return $res;
                }
            } else {
                $pdo->prepare("UPDATE process_steps SET step_number = ?, title = ?, description = ? WHERE id = ?")
                    ->execute([$postData['step_number'], $postData['title'], $postData['description'], $postData['id']]);
            }
        } else {
            // Insert
            $img = '/img/placeholder.svg';
            if(isset($files['file']) && $files['file']['error'] !== UPLOAD_ERR_NO_FILE){
                $res = ImageHandler::optimizeAndSaveImage($files['file'], "proc");
                if ($res['success']) {
                    $img = '/' . ltrim((string)$res['path'], '/');
                } else {
                    return $res;
                }
            }
            $pdo->prepare("INSERT INTO process_steps (step_number, title, description, img_url) VALUES (?, ?, ?, ?)")->execute([$postData['step_number'], $postData['title'], $postData['description'], $img]);
        }
        return ["success" => true];
    }

    public static function delete($id) {
        $pdo = Database::getConnection();
        $old = $pdo->prepare("SELECT img_url FROM process_steps WHERE id = ?");
        $old->execute([$id]);
        ImageHandler::deletePhysicalFile($old->fetchColumn());
        $pdo->prepare("DELETE FROM process_steps WHERE id = ?")->execute([$id]);
        return ["success" => true];
    }
}
?>