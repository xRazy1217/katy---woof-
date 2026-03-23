<?php
/**
 * Katy & Woof - Portfolio API Module v6.0
 * Gestión de portafolio de obras
 */

require_once 'database.php';
require_once 'image-handler.php';

class PortfolioAPI {
    private static function normalizeImagePath($path) {
        $raw = trim((string)$path);
        if ($raw === '') {
            return '/img/placeholder.svg';
        }

        if (preg_match('/^https?:\/\//i', $raw)) {
            return $raw;
        }

        $trimmed = ltrim($raw, '/');
        if ($trimmed === 'img/placeholder.jpg' || $trimmed === 'img/placeholder.jpeg') {
            return '/img/placeholder.svg';
        }

        $publicPath = '/' . $trimmed;
        $projectRoot = realpath(__DIR__ . '/..');
        if ($projectRoot) {
            $fsPath = $projectRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $trimmed);
            if (is_file($fsPath)) {
                return $publicPath;
            }
        }

        return '/img/placeholder.svg';
    }

    public static function get() {
        $pdo = Database::getConnection();
        try {
            $rows = $pdo->query("SELECT * FROM portfolio_items ORDER BY id DESC")->fetchAll();
            foreach ($rows as &$row) {
                $row['img_url'] = self::normalizeImagePath($row['img_url'] ?? '');
            }
            return $rows;
        } catch (Exception $e) {
            Database::runSetup();
            $rows = $pdo->query("SELECT * FROM portfolio_items ORDER BY id DESC")->fetchAll();
            foreach ($rows as &$row) {
                $row['img_url'] = self::normalizeImagePath($row['img_url'] ?? '');
            }
            return $rows;
        }
    }

    public static function save($postData, $files) {
        $pdo = Database::getConnection();

        if (!empty($postData['id'])) {
            // Update
            if (isset($files['file']) && $files['file']['error'] !== UPLOAD_ERR_NO_FILE) {
                $old = $pdo->prepare("SELECT img_url FROM portfolio_items WHERE id = ?");
                $old->execute([$postData['id']]);
                $old_path = $old->fetchColumn();

                $res = ImageHandler::optimizeAndSaveImage($files['file'], "art");
                if ($res['success']) {
                    $pdo->prepare("UPDATE portfolio_items SET name = ?, description = ?, img_url = ? WHERE id = ?")
                        ->execute([$postData['name'], $postData['description'], $res['path'], $postData['id']]);
                    ImageHandler::deletePhysicalFile($old_path);
                } else {
                    return $res;
                }
            } else {
                $pdo->prepare("UPDATE portfolio_items SET name = ?, description = ? WHERE id = ?")
                    ->execute([$postData['name'], $postData['description'], $postData['id']]);
            }
        } else {
            // Insert
            $img = '/img/placeholder.svg';
            if(isset($files['file']) && $files['file']['error'] !== UPLOAD_ERR_NO_FILE){
                $res = ImageHandler::optimizeAndSaveImage($files['file'], "art");
                if ($res['success']) {
                    $img = '/' . ltrim((string)$res['path'], '/');
                } else {
                    return $res;
                }
            }
            $pdo->prepare("INSERT INTO portfolio_items (name, description, img_url) VALUES (?, ?, ?)")->execute([$postData['name'], $postData['description'], $img]);
        }
        return ["success" => true];
    }

    public static function delete($id) {
        $pdo = Database::getConnection();
        $old = $pdo->prepare("SELECT img_url FROM portfolio_items WHERE id = ?");
        $old->execute([$id]);
        ImageHandler::deletePhysicalFile($old->fetchColumn());
        $pdo->prepare("DELETE FROM portfolio_items WHERE id = ?")->execute([$id]);
        return ["success" => true];
    }
}
?>