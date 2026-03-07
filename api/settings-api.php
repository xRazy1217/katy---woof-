<?php
/**
 * Katy & Woof - Settings API Module v6.0
 * Gestión de configuraciones del sitio
 */

require_once 'database.php';
require_once 'image-handler.php';

class SettingsAPI {
    public static function get() {
        $pdo = Database::getConnection();
        try {
            return $pdo->query("SELECT * FROM site_settings")->fetchAll();
        } catch (Exception $e) {
            Database::runSetup();
            return $pdo->query("SELECT * FROM site_settings")->fetchAll();
        }
    }

    public static function save($postData, $files) {
        $pdo = Database::getConnection();

        // Guardar o crear settings (INSERT ON DUPLICATE KEY UPDATE garantiza existencia)
        $upsertStmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        foreach ($postData as $k => $v) {
            if ($k === 'auth') continue;
            $upsertStmt->execute([$k, $v]);
        }

        $file_fields = ['site_logo', 'site_favicon', 'hero_image', 'nosotros_image'];
        foreach ($file_fields as $f) {
            if (isset($files[$f]) && $files[$f]['error'] !== UPLOAD_ERR_NO_FILE) {
                // Obtener ruta antigua para borrarla (si existe)
                $old = $pdo->prepare("SELECT setting_value FROM site_settings WHERE setting_key = ?");
                $old->execute([$f]);
                $old_path = $old->fetchColumn();

                $res = ImageHandler::optimizeAndSaveImage($files[$f], "brand_{$f}");
                if ($res['success']) {
                    $upsertStmt->execute([$f, $res['path']]);
                    ImageHandler::deletePhysicalFile($old_path);
                } else {
                    return $res;
                }
            }
        }

        return ["success" => true];
    }
}
?>