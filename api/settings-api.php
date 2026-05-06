<?php
/**
 * Katy & Woof - Settings API Module v1.0
 */

class SettingsAPI {

    public static function get() {
        try {
            $settings = getSiteSettings();
            $formatted = [];
            foreach ($settings as $k => $v) {
                $formatted[] = ['setting_key' => $k, 'setting_value' => $v];
            }
            return $formatted;
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public static function getStatus() {
        try {
            $pdo = getDBConnection();
            $tableCount = $pdo->query("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = '" . DB_NAME . "'")->fetchColumn();
            
            $sizeStmt = $pdo->prepare("SELECT ROUND(SUM(DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024, 2) as size_mb FROM information_schema.TABLES WHERE TABLE_SCHEMA = ?");
            $sizeStmt->execute([DB_NAME]);
            $sizeResult = $sizeStmt->fetch();

            return [
                'success' => true,
                'table_count' => (int)$tableCount,
                'size_mb' => floatval($sizeResult['size_mb'] ?? 0),
                'php_version' => PHP_VERSION,
                'upload_dir_writable' => is_writable(__DIR__ . '/../uploads')
            ];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public static function save($data, $files) {
        return self::saveSettings($data, $files);
    }

    public static function saveSettings($data, $files) {
        try {
            $pdo = getDBConnection();
            $pdo->beginTransaction();

            // 1. Guardar textos
            foreach ($data as $key => $value) {
                if ($key === 'auth') continue;
                
                $stmt = $pdo->prepare("INSERT INTO ecommerce_settings (setting_key, setting_value) VALUES (?, ?) 
                                      ON DUPLICATE KEY UPDATE setting_value = ?");
                $stmt->execute([$key, $value, $value]);
            }

            // 2. Guardar imágenes
            if (!empty($files)) {
                $uploadDir = __DIR__ . '/../uploads/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

                foreach ($files as $key => $file) {
                    if ($file['error'] === UPLOAD_ERR_OK) {
                        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                        $filename = $key . '_' . time() . '.' . $ext;
                        $target = $uploadDir . $filename;
                        
                        if (move_uploaded_file($file['tmp_name'], $target)) {
                            $value = '/uploads/' . $filename;
                            $stmt = $pdo->prepare("INSERT INTO ecommerce_settings (setting_key, setting_value) VALUES (?, ?) 
                                                  ON DUPLICATE KEY UPDATE setting_value = ?");
                            $stmt->execute([$key, $value, $value]);
                        }
                    }
                }
            }

            $pdo->commit();
            return ['success' => true, 'message' => 'Ajustes guardados correctamente'];
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}