<?php
/**
 * Katy & Woof - Settings API Module v6.0
 * Gestión de configuraciones del sitio
 */

require_once 'database.php';
require_once 'image-handler.php';

class SettingsAPI {
    private static function normalizeImageSettingPath($key, $value) {
        $defaultByKey = [
            'site_favicon' => '/img/favicon.svg',
            'site_logo' => '/img/placeholder.svg',
            'hero_image' => '/img/placeholder.svg',
            'nosotros_image' => '/img/placeholder.svg'
        ];

        $defaultPath = $defaultByKey[$key] ?? '/img/placeholder.svg';
        $raw = trim((string)$value);
        if ($raw === '') {
            return $defaultPath;
        }

        // URL remota: se respeta.
        if (preg_match('/^https?:\/\//i', $raw)) {
            return $raw;
        }

        $trimmed = ltrim($raw, '/');
        if ($trimmed === 'img/placeholder.jpg' || $trimmed === 'img/placeholder.jpeg') {
            return '/img/placeholder.svg';
        }

        $candidates = [];
        if (strpos($trimmed, '/') !== false) {
            $candidates[] = '/' . $trimmed;
        } else {
            // Compatibilidad con rutas legacy guardadas como nombre de archivo suelto.
            $candidates[] = '/uploads/' . $trimmed;
            $candidates[] = '/' . $trimmed;
        }

        $projectRoot = realpath(__DIR__ . '/..');
        foreach ($candidates as $publicPath) {
            if (!$projectRoot) {
                continue;
            }

            $fsPath = $projectRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, ltrim($publicPath, '/'));
            if (is_file($fsPath)) {
                return $publicPath;
            }
        }

        return $defaultPath;
    }

    public static function get() {
        $pdo = Database::getConnection();
        try {
            $rows = $pdo->query("SELECT * FROM site_settings")->fetchAll();
            foreach ($rows as &$row) {
                if (in_array($row['setting_key'], ['site_logo', 'site_favicon', 'hero_image', 'nosotros_image'], true)) {
                    $row['setting_value'] = self::normalizeImageSettingPath($row['setting_key'], $row['setting_value']);
                }
            }
            return $rows;
        } catch (Exception $e) {
            Database::runSetup();
            $rows = $pdo->query("SELECT * FROM site_settings")->fetchAll();
            foreach ($rows as &$row) {
                if (in_array($row['setting_key'], ['site_logo', 'site_favicon', 'hero_image', 'nosotros_image'], true)) {
                    $row['setting_value'] = self::normalizeImageSettingPath($row['setting_key'], $row['setting_value']);
                }
            }
            return $rows;
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
                    $normalizedPath = '/' . ltrim((string)$res['path'], '/');
                    $upsertStmt->execute([$f, $normalizedPath]);
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