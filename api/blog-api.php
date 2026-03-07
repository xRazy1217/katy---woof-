<?php
/**
 * Katy & Woof - Blog API Module v6.0
 * Gestión de posts del blog
 */

require_once 'database.php';
require_once 'image-handler.php';

class BlogAPI {
    public static function get() {
        $pdo = Database::getConnection();
        try {
            return $pdo->query("SELECT * FROM blog_posts ORDER BY id DESC")->fetchAll();
        } catch (Exception $e) {
            Database::runSetup();
            return $pdo->query("SELECT * FROM blog_posts ORDER BY id DESC")->fetchAll();
        }
    }

    public static function save($postData, $files) {
        $pdo = Database::getConnection();

        if (!empty($postData['id'])) {
            // Update
            if (isset($files['file']) && $files['file']['error'] !== UPLOAD_ERR_NO_FILE) {
                $old = $pdo->prepare("SELECT img_url FROM blog_posts WHERE id = ?");
                $old->execute([$postData['id']]);
                $old_path = $old->fetchColumn();

                $res = ImageHandler::optimizeAndSaveImage($files['file'], "blog");
                if ($res['success']) {
                    $pdo->prepare("UPDATE blog_posts SET title = ?, category = ?, content = ?, img_url = ? WHERE id = ?")
                        ->execute([$postData['title'], $postData['category'], $postData['content'], $res['path'], $postData['id']]);
                    ImageHandler::deletePhysicalFile($old_path);
                } else {
                    return $res;
                }
            } else {
                $pdo->prepare("UPDATE blog_posts SET title = ?, category = ?, content = ? WHERE id = ?")
                    ->execute([$postData['title'], $postData['category'], $postData['content'], $postData['id']]);
            }
        } else {
            // Insert
            $img = 'img/placeholder.jpg';
            if(isset($files['file']) && $files['file']['error'] !== UPLOAD_ERR_NO_FILE){
                $res = ImageHandler::optimizeAndSaveImage($files['file'], "blog");
                if ($res['success']) {
                    $img = $res['path'];
                } else {
                    return $res;
                }
            }
            $pdo->prepare("INSERT INTO blog_posts (title, category, content, img_url) VALUES (?, ?, ?, ?)")->execute([$postData['title'], $postData['category'], $postData['content'], $img]);
        }
        return ["success" => true];
    }

    public static function delete($id) {
        $pdo = Database::getConnection();
        $old = $pdo->prepare("SELECT img_url FROM blog_posts WHERE id = ?");
        $old->execute([$id]);
        ImageHandler::deletePhysicalFile($old->fetchColumn());
        $pdo->prepare("DELETE FROM blog_posts WHERE id = ?")->execute([$id]);
        return ["success" => true];
    }
}
?>