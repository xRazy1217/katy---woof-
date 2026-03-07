<?php
/**
 * Katy & Woof - API Entry Point v6.0
 * Redirige a router modular
 */

// Incluir el router modular
require_once 'api/router.php';
?>

// Headers para garantizar que los datos siempre son frescos (sin cache)
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

$upload_dir = 'uploads/';
$pdo = getDBConnection();
$action = $_GET['action'] ?? null;
$auth_key = $_GET['auth'] ?? $_POST['auth'] ?? null;
$master_key = 'fotopet2026'; // Coincide con admin-ui.js

// Acciones que requieren autenticación
$protected_actions = [
    'save_settings', 'save_portfolio', 'save_service', 'save_blog', 
    'save_process', 'save_list_item', 'delete_service', 'delete_blog', 
    'delete_portfolio', 'delete_list_item', 'delete_process'
];

if (in_array($action, $protected_actions) && $auth_key !== $master_key) {
    http_response_code(401);
    echo json_encode(["success" => false, "error" => "Unauthorized"]);
    exit;
}

if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
    // Crear .htaccess para prevenir ejecución de scripts en la carpeta de subidas
    file_put_contents($upload_dir . '.htaccess', "php_flag engine off\nOptions -ExecCGI\nAddHandler cgi-script .php .php3 .php4 .php5 .phtml .pl .py .jsp .asp .htm .html .sh .cgi");
}

/**
 * Obtiene la calidad de compresión WebP óptima según las dimensiones
 */
function getOptimalQuality($width, $height) {
    $megapixels = ($width * $height) / 1000000;
    
    if ($megapixels > 12) return 70;      // Imágenes 4K o grandes: menos calidad
    if ($megapixels > 8) return 75;       // Imágenes muy grandes
    if ($megapixels > 4) return 80;       // Imágenes medianas
    if ($megapixels > 1) return 82;       // Imágenes pequeñas: mayor calidad
    return 85;                             // Imágenes muy pequeñas: máxima calidad
}

/**
 * Optimiza y guarda una imagen subida con validaciones de seguridad mejoradas
 */
function optimizeAndSaveImage($file, $prefix, $upload_dir) {
    $max_size = 10 * 1024 * 1024; // 10MB
    $allowed_types = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ["success" => false, "error" => "Error en la subida del archivo."];
    }

    if ($file['size'] > $max_size) {
        return ["success" => false, "error" => "El archivo es demasiado grande (Máx 10MB)."];
    }

    $mime_type = null;
    if (class_exists('finfo')) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime_type = $finfo->file($file['tmp_name']);
    } else {
        // Fallback si finfo no está disponible
        $mime_type = $file['type'];
    }
    
    if (!in_array($mime_type, $allowed_types)) {
        return ["success" => false, "error" => "Formato de archivo no permitido ($mime_type). Solo imágenes."];
    }
    
    $tmp_name = $file['tmp_name'];
    // decide extension and filename base
    $ext_map = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
    $ext = isset($ext_map[$mime_type]) ? $ext_map[$mime_type] : 'dat';
    $new_name = $prefix . "_" . time() . "_" . bin2hex(random_bytes(4)) . "." . $ext;
    $target_path = $upload_dir . $new_name;

    try {
        list($width, $height, $type) = getimagesize($tmp_name);
        if (!$width || !$height) {
            return ["success" => false, "error" => "No se pudieron leer las dimensiones de la imagen."];
        }
        
        // 🔴 VALIDACIÓN: Dimensiones mínimas y máximas
        $min_dimension = 300;
        $max_dimension = 4096;
        
        if ($width < $min_dimension || $height < $min_dimension) {
            return ["success" => false, 
                   "error" => "Imagen demasiado pequeña. Mínimo {$min_dimension}x{$min_dimension}px. Actual: {$width}x{$height}px"];
        }
        
        if ($width > $max_dimension || $height > $max_dimension) {
            return ["success" => false, 
                   "error" => "Imagen demasiado grande. Máximo {$max_dimension}x{$max_dimension}px. Actual: {$width}x{$height}px"];
        }

        switch ($type) {
            case IMAGETYPE_JPEG: $src = imagecreatefromjpeg($tmp_name); break;
            case IMAGETYPE_PNG:  $src = imagecreatefrompng($tmp_name); break;
            case IMAGETYPE_WEBP: $src = imagecreatefromwebp($tmp_name); break;
            case IMAGETYPE_GIF:  $src = imagecreatefromgif($tmp_name); break;
            default: return ["success" => false, "error" => "Tipo de imagen no procesable."];
        }

        // 🔄 Redimensionar si es muy grande (max 1920px ancho)
        $max_w = 1920;
        $resized = false;
        
        if ($width > $max_w) {
            $resized = true;
            $new_w = $max_w;
            $new_h = floor($height * ($max_w / $width));
            $dst = imagecreatetruecolor($new_w, $new_h);
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $new_w, $new_h, $width, $height);
            imagedestroy($src);
            $src = $dst;
            $width = $new_w;
            $height = $new_h;
        }

        // 🎨 Compresión inteligente: calidad adaptativa según tamaño
        $quality = getOptimalQuality($width, $height);
        
        // si la biblioteca WebP no está disponible o fue un GIF/PNG/JPEG y no se desea convertir,
        // intentamos guardar directamente o mover el archivo original
        $webp_supported = function_exists('imagewebp');
        if ($webp_supported && strtolower($ext) !== 'gif') {
            // guardar como WebP con calidad óptima
            $target_path = $upload_dir . $prefix . "_" . time() . "_" . bin2hex(random_bytes(4)) . ".webp";
            if (!imagewebp($src, $target_path, $quality)) {
                imagedestroy($src);
                // fallback: intenta mover el archivo original sin conversión
                if (move_uploaded_file($tmp_name, $upload_dir . $new_name)) {
                    return [
                        "success" => true,
                        "path" => $upload_dir . $new_name,
                        "original_size" => filesize($tmp_name),
                        "final_size" => filesize($upload_dir . $new_name),
                        "savings_percent" => 0,
                        "quality" => null,
                        "resized" => $resized,
                        "dimensions" => "{$width}x{$height}"
                    ];
                }
                return ["success" => false, "error" => "Error al procesar la imagen y al mover el archivo de respaldo."];
            }
            imagedestroy($src);
            $final_size = filesize($target_path);
            $original_size = filesize($tmp_name);
            $savings_percent = round((1 - $final_size / $original_size) * 100);
            return [
                "success" => true,
                "path" => $target_path,
                "original_size" => $original_size,
                "final_size" => $final_size,
                "savings_percent" => $savings_percent,
                "quality" => $quality,
                "resized" => $resized,
                "dimensions" => "{$width}x{$height}"
            ];
        } else {
            // no support for WebP or prefiero conservar extensión original
            imagedestroy($src);
            if (move_uploaded_file($tmp_name, $target_path)) {
                return [
                    "success" => true,
                    "path" => $target_path,
                    "original_size" => filesize($target_path),
                    "final_size" => filesize($target_path),
                    "savings_percent" => 0,
                    "quality" => null,
                    "resized" => $resized,
                    "dimensions" => "{$width}x{$height}"
                ];
            }
            return ["success" => false, "error" => "Error al mover archivo al destino."];
        }

    } catch (Exception $e) {
        return ["success" => false, "error" => "Error interno del servidor al procesar la imagen."];
    }
}

/**
 * Elimina un archivo físico si existe
 */
function deletePhysicalFile($path) {
    if (!$path || strpos($path, 'img/') === 0) return; // No borrar placeholders
    if (file_exists($path)) @unlink($path);
}

// Función de inicialización reutilizable
function runSetup($pdo) {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `portfolio` (`id` INT(11) NOT NULL AUTO_INCREMENT, `name` VARCHAR(255) NOT NULL, `description` TEXT DEFAULT NULL, `img_url` VARCHAR(500) NOT NULL, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    
    // Asegurar columnas en portfolio
    try { $pdo->exec("ALTER TABLE `portfolio` ADD COLUMN `description` TEXT AFTER `name` "); } catch(Exception $e) {}
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS `services` (`id` INT(11) NOT NULL AUTO_INCREMENT, `title` VARCHAR(255) NOT NULL, `description` TEXT, `category` VARCHAR(100) DEFAULT 'General', `main_image_url` VARCHAR(500) NOT NULL, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    $pdo->exec("CREATE TABLE IF NOT EXISTS `blog_posts` (`id` INT(11) NOT NULL AUTO_INCREMENT, `title` VARCHAR(255) NOT NULL, `content` TEXT, `category` VARCHAR(100) DEFAULT 'General', `img_url` VARCHAR(500) NOT NULL, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    $pdo->exec("CREATE TABLE IF NOT EXISTS `process_steps` (`id` INT(11) NOT NULL AUTO_INCREMENT, `step_number` INT(11), `title` VARCHAR(255), `description` TEXT, `img_url` VARCHAR(500), PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    $pdo->exec("CREATE TABLE IF NOT EXISTS `site_settings` (`id` INT(11) NOT NULL AUTO_INCREMENT, `setting_key` VARCHAR(100) UNIQUE NOT NULL, `setting_value` TEXT, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    $pdo->exec("CREATE TABLE IF NOT EXISTS `site_lists` (`id` INT(11) NOT NULL AUTO_INCREMENT, `list_key` VARCHAR(50) NOT NULL, `item_value` VARCHAR(255) NOT NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $defaults = [
        'our_history' => 'Nuestra pasión por el arte comenzó...', 
        'contact_email' => 'hello@katyandwoof.art',
        'contact_whatsapp' => '+34 000 000 000',
        'contact_address' => 'Atelier Barcelona, España',
        'site_logo' => 'img/logo.png',
        'site_favicon' => 'favicon.ico',
        'hero_title' => 'Eterniza su alma en un lienzo.',
        'hero_description' => 'Retratos de autor pintados a mano digitalmente que capturan la esencia única de tu compañero más fiel.',
        'hero_image' => 'img/hero-placeholder.jpg',
        'nosotros_image' => 'img/nosotros.jpg',
        'nosotros_title' => 'Donde el arte encuentra la lealtad.',
        'footer_philosophy' => 'Especializados en capturar la esencia de tu compañero más fiel a través del arte digital de autor. Un tributo eterno a la lealtad.',
        'social_instagram' => 'https://www.instagram.com/katyandwoof/'
    ];
    $stmt = $pdo->prepare("INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES (?, ?)");
    foreach ($defaults as $k => $v) { $stmt->execute([$k, $v]); }
    
    // Force update Instagram if it was the old default
    $pdo->prepare("UPDATE site_settings SET setting_value = ? WHERE setting_key = 'social_instagram' AND setting_value = 'https://instagram.com/katyandwoof'")->execute(['https://www.instagram.com/katyandwoof/']);
}

// Inicialización de Base de Datos
if ($action === 'setup') {
    try {
        runSetup($pdo);
        echo json_encode(["success" => true, "msg" => "v6.0 Ready"]);
        exit;
    } catch (PDOException $e) { http_response_code(500); exit(json_encode(["error" => $e->getMessage()])); }
}

// Router de Acciones
try {
    switch ($action) {
        case 'get_settings': 
            try {
                echo json_encode($pdo->query("SELECT * FROM site_settings")->fetchAll()); 
            } catch (Exception $e) {
                // Si falla, intentamos setup automático una vez
                runSetup($pdo);
                echo json_encode($pdo->query("SELECT * FROM site_settings")->fetchAll());
            }
            break;
        
    case 'save_settings':
        // Guardar o crear settings (INSERT ON DUPLICATE KEY UPDATE garantiza existencia)
        $upsertStmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        foreach ($_POST as $k => $v) { 
            if ($k === 'auth') continue;
            $upsertStmt->execute([$k, $v]); 
        }

        $file_fields = ['site_logo', 'site_favicon', 'hero_image', 'nosotros_image'];
        foreach ($file_fields as $f) {
            if (isset($_FILES[$f]) && $_FILES[$f]['error'] !== UPLOAD_ERR_NO_FILE) {
                // Obtener ruta antigua para borrarla (si existe)
                $old = $pdo->prepare("SELECT setting_value FROM site_settings WHERE setting_key = ?");
                $old->execute([$f]);
                $old_path = $old->fetchColumn();

                $res = optimizeAndSaveImage($_FILES[$f], "brand_{$f}", $upload_dir);
                if ($res['success']) {
                    $upsertStmt->execute([$f, $res['path']]);
                    deletePhysicalFile($old_path);
                } else {
                    exit(json_encode($res));
                }
            }
        }

        echo json_encode(["success" => true]);
        break;

    case 'get_services': 
        try {
            echo json_encode($pdo->query("SELECT * FROM services ORDER BY id DESC")->fetchAll()); 
        } catch (Exception $e) {
            runSetup($pdo);
            echo json_encode($pdo->query("SELECT * FROM services ORDER BY id DESC")->fetchAll());
        }
        break;
        
    case 'save_portfolio':
        if (!empty($_POST['id'])) {
            if (isset($_FILES['file']) && $_FILES['file']['error'] !== UPLOAD_ERR_NO_FILE) {
                $old = $pdo->prepare("SELECT img_url FROM portfolio WHERE id = ?");
                $old->execute([$_POST['id']]);
                $old_path = $old->fetchColumn();

                $res = optimizeAndSaveImage($_FILES['file'], "art", $upload_dir);
                if ($res['success']) {
                    $pdo->prepare("UPDATE portfolio SET name = ?, description = ?, img_url = ? WHERE id = ?")
                        ->execute([$_POST['name'], $_POST['description'], $res['path'], $_POST['id']]);
                    deletePhysicalFile($old_path);
                } else {
                    exit(json_encode($res));
                }
            } else {
                $pdo->prepare("UPDATE portfolio SET name = ?, description = ? WHERE id = ?")
                    ->execute([$_POST['name'], $_POST['description'], $_POST['id']]);
            }
        } else {
            $img = 'img/placeholder.jpg'; 
            if(isset($_FILES['file']) && $_FILES['file']['error'] !== UPLOAD_ERR_NO_FILE){
                $res = optimizeAndSaveImage($_FILES['file'], "art", $upload_dir);
                if ($res['success']) {
                    $img = $res['path'];
                } else {
                    exit(json_encode($res));
                }
            } 
            $pdo->prepare("INSERT INTO portfolio (name, description, img_url) VALUES (?, ?, ?)")->execute([$_POST['name'], $_POST['description'], $img]); 
        }
        echo json_encode(["success" => true]);
        break;

    case 'save_service': 
        if (!empty($_POST['id'])) {
            if (isset($_FILES['main_file']) && $_FILES['main_file']['error'] !== UPLOAD_ERR_NO_FILE) {
                $old = $pdo->prepare("SELECT main_image_url FROM services WHERE id = ?");
                $old->execute([$_POST['id']]);
                $old_path = $old->fetchColumn();

                $res = optimizeAndSaveImage($_FILES['main_file'], "svc", $upload_dir);
                if ($res['success']) {
                    $pdo->prepare("UPDATE services SET title = ?, description = ?, main_image_url = ? WHERE id = ?")
                        ->execute([$_POST['title'], $_POST['description'], $res['path'], $_POST['id']]);
                    deletePhysicalFile($old_path);
                } else {
                    exit(json_encode($res));
                }
            } else {
                $pdo->prepare("UPDATE services SET title = ?, description = ? WHERE id = ?")
                    ->execute([$_POST['title'], $_POST['description'], $_POST['id']]);
            }
        } else {
            $img = 'img/placeholder.jpg'; 
            if(isset($_FILES['main_file']) && $_FILES['main_file']['error'] !== UPLOAD_ERR_NO_FILE){
                $res = optimizeAndSaveImage($_FILES['main_file'], "svc", $upload_dir);
                if ($res['success']) {
                    $img = $res['path'];
                } else {
                    exit(json_encode($res));
                }
            } 
            $pdo->prepare("INSERT INTO services (title, description, main_image_url) VALUES (?, ?, ?)")->execute([$_POST['title'], $_POST['description'], $img]); 
        }
        echo json_encode(["success" => true]); 
        break;

    case 'save_blog':
        if (!empty($_POST['id'])) {
            if (isset($_FILES['file']) && $_FILES['file']['error'] !== UPLOAD_ERR_NO_FILE) {
                $old = $pdo->prepare("SELECT img_url FROM blog_posts WHERE id = ?");
                $old->execute([$_POST['id']]);
                $old_path = $old->fetchColumn();

                $res = optimizeAndSaveImage($_FILES['file'], "blog", $upload_dir);
                if ($res['success']) {
                    $pdo->prepare("UPDATE blog_posts SET title = ?, category = ?, content = ?, img_url = ? WHERE id = ?")
                        ->execute([$_POST['title'], $_POST['category'], $_POST['content'], $res['path'], $_POST['id']]);
                    deletePhysicalFile($old_path);
                } else {
                    exit(json_encode($res));
                }
            } else {
                $pdo->prepare("UPDATE blog_posts SET title = ?, category = ?, content = ? WHERE id = ?")
                    ->execute([$_POST['title'], $_POST['category'], $_POST['content'], $_POST['id']]);
            }
        } else {
            $img = 'img/placeholder.jpg'; 
            if(isset($_FILES['file']) && $_FILES['file']['error'] !== UPLOAD_ERR_NO_FILE){
                $res = optimizeAndSaveImage($_FILES['file'], "blog", $upload_dir);
                if ($res['success']) {
                    $img = $res['path'];
                } else {
                    exit(json_encode($res));
                }
            } 
            $pdo->prepare("INSERT INTO blog_posts (title, category, content, img_url) VALUES (?, ?, ?, ?)")->execute([$_POST['title'], $_POST['category'], $_POST['content'], $img]); 
        }
        echo json_encode(["success" => true]);
        break;

    case 'save_process':
        if (!empty($_POST['id'])) {
            if (isset($_FILES['file']) && $_FILES['file']['error'] !== UPLOAD_ERR_NO_FILE) {
                $old = $pdo->prepare("SELECT img_url FROM process_steps WHERE id = ?");
                $old->execute([$_POST['id']]);
                $old_path = $old->fetchColumn();

                $res = optimizeAndSaveImage($_FILES['file'], "proc", $upload_dir);
                if ($res['success']) {
                    $pdo->prepare("UPDATE process_steps SET step_number = ?, title = ?, description = ?, img_url = ? WHERE id = ?")
                        ->execute([$_POST['step_number'], $_POST['title'], $_POST['description'], $res['path'], $_POST['id']]);
                    deletePhysicalFile($old_path);
                } else {
                    exit(json_encode($res));
                }
            } else {
                $pdo->prepare("UPDATE process_steps SET step_number = ?, title = ?, description = ? WHERE id = ?")
                    ->execute([$_POST['step_number'], $_POST['title'], $_POST['description'], $_POST['id']]);
            }
        } else {
            $img = 'img/placeholder.jpg'; 
            if(isset($_FILES['file']) && $_FILES['file']['error'] !== UPLOAD_ERR_NO_FILE){
                $res = optimizeAndSaveImage($_FILES['file'], "proc", $upload_dir);
                if ($res['success']) {
                    $img = $res['path'];
                } else {
                    exit(json_encode($res));
                }
            } 
            $pdo->prepare("INSERT INTO process_steps (step_number, title, description, img_url) VALUES (?, ?, ?, ?)")->execute([$_POST['step_number'], $_POST['title'], $_POST['description'], $img]); 
        }
        echo json_encode(["success" => true]);
        break;

    case 'save_list_item':
        $pdo->prepare("INSERT INTO site_lists (list_key, item_value) VALUES (?, ?)")->execute([$_POST['list_key'], $_POST['item_value']]);
        echo json_encode(["success" => true]);
        break;

    case 'get_blog': 
        try {
            echo json_encode($pdo->query("SELECT * FROM blog_posts ORDER BY id DESC")->fetchAll()); 
        } catch (Exception $e) {
            runSetup($pdo);
            echo json_encode($pdo->query("SELECT * FROM blog_posts ORDER BY id DESC")->fetchAll());
        }
        break;

    case 'get_process': 
        try {
            echo json_encode($pdo->query("SELECT * FROM process_steps ORDER BY step_number ASC")->fetchAll()); 
        } catch (Exception $e) {
            runSetup($pdo);
            echo json_encode($pdo->query("SELECT * FROM process_steps ORDER BY step_number ASC")->fetchAll());
        }
        break;

    case 'get_lists': 
        try {
            echo json_encode($pdo->query("SELECT * FROM site_lists ORDER BY item_value ASC")->fetchAll()); 
        } catch (Exception $e) {
            runSetup($pdo);
            echo json_encode($pdo->query("SELECT * FROM site_lists ORDER BY item_value ASC")->fetchAll());
        }
        break;

    case 'delete_process': 
        $old = $pdo->prepare("SELECT img_url FROM process_steps WHERE id = ?");
        $old->execute([$_GET['id']]);
        deletePhysicalFile($old->fetchColumn());
        $pdo->prepare("DELETE FROM process_steps WHERE id = ?")->execute([$_GET['id']]); 
        echo json_encode(["success" => true]); 
        break;

    case 'delete_service': 
        $old = $pdo->prepare("SELECT main_image_url FROM services WHERE id = ?");
        $old->execute([$_GET['id']]);
        deletePhysicalFile($old->fetchColumn());
        $pdo->prepare("DELETE FROM services WHERE id = ?")->execute([$_GET['id']]); 
        echo json_encode(["success" => true]); 
        break;

    case 'delete_blog': 
        $old = $pdo->prepare("SELECT img_url FROM blog_posts WHERE id = ?");
        $old->execute([$_GET['id']]);
        deletePhysicalFile($old->fetchColumn());
        $pdo->prepare("DELETE FROM blog_posts WHERE id = ?")->execute([$_GET['id']]); 
        echo json_encode(["success" => true]); 
        break;

    case 'delete_portfolio': 
        $old = $pdo->prepare("SELECT img_url FROM portfolio WHERE id = ?");
        $old->execute([$_GET['id']]);
        deletePhysicalFile($old->fetchColumn());
        $pdo->prepare("DELETE FROM portfolio WHERE id = ?")->execute([$_GET['id']]); 
        echo json_encode(["success" => true]); 
        break;
    case 'delete_list_item': $pdo->prepare("DELETE FROM site_lists WHERE id = ?")->execute([$_GET['id']]); echo json_encode(["success" => true]); break;

    // ============================================
    // SCHEMA MANAGER - Auditoría y Sincronización
    // ============================================
    
    case 'audit_schema':
        /**
         * Audita el esquema actual vs esquema ideal
         * Retorna reporte detallado de discrepancias
         */
        require_once 'schema-manager.php';
        
        try {
            $schemaManager = new SchemaManager();
            $audit = $schemaManager->auditSchema();
            
            echo json_encode([
                'success' => true,
                'data' => $audit
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        break;

    case 'sync_database':
        /**
         * Ejecuta la sincronización del esquema
         * Crea tablas faltantes y agrega columnas
         */
        // Solo autorizar si la clave es correcta
        if ($auth_key !== $master_key) {
            http_response_code(403);
            echo json_encode(["success" => false, "error" => "Acceso denegado"]);
            break;
        }

        require_once 'schema-manager.php';
        
        try {
            $schemaManager = new SchemaManager();
            $syncResult = $schemaManager->syncDatabase();
            
            // Registrar en logs
            logEvent('database_sync', "Sincronización ejecutada: " . ($syncResult['success'] ? 'Exitosa' : 'Fallida'), $_SERVER['REMOTE_ADDR'] ?? '');
            
            echo json_encode($syncResult);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        break;

    case 'get_db_status':
        /**
         * Obtiene el estado general de la base de datos
         * Conexión, versión, tamaño, etc
         */
        require_once 'schema-manager.php';
        
        try {
            $schemaManager = new SchemaManager();
            $connectionTest = $schemaManager->testConnection();
            
            // Obtener información adicional
            if ($connectionTest['success']) {
                $tableCount = $pdo->query("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = '" . DB_NAME . "'")->fetchColumn();
                
                // Obtener tamaño de la base de datos
                $sizeQuery = "SELECT ROUND(SUM(DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024, 2) as size_mb 
                              FROM information_schema.TABLES 
                              WHERE TABLE_SCHEMA = ?";
                $sizeStmt = $pdo->prepare($sizeQuery);
                $sizeStmt->execute([DB_NAME]);
                $sizeResult = $sizeStmt->fetch(PDO::FETCH_ASSOC);
                
                $connectionTest['table_count'] = (int)$tableCount;
                $connectionTest['size_mb'] = floatval($sizeResult['size_mb'] ?? 0);
            }
            
            echo json_encode([
                'success' => true,
                'data' => $connectionTest
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        break;

    case 'test_connection':
        try {
            $pdo->query("SELECT 1");
            $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            $writable = is_writable($upload_dir) || is_writable('.');
            $webp = function_exists('imagewebp');
            
            echo json_encode([
                "success" => true, 
                "msg" => "Conexión con Atelier y Base de Datos exitosa", 
                "php_version" => PHP_VERSION, 
                "finfo_enabled" => class_exists('finfo'),
                "db_status" => "Connected",
                "tables" => $tables,
                "upload_dir_writable" => $writable,
                "upload_dir" => $upload_dir,
                "webp_support" => $webp
            ]);
        } catch (Exception $e) {
            echo json_encode([
                "success" => false, 
                "error" => "Error de Base de Datos: " . $e->getMessage(),
                "php_version" => PHP_VERSION
            ]);
        }
        break;

    default:
        try {
            echo json_encode($pdo->query("SELECT * FROM portfolio ORDER BY id DESC")->fetchAll());
        } catch (Exception $e) {
            runSetup($pdo);
            echo json_encode($pdo->query("SELECT * FROM portfolio ORDER BY id DESC")->fetchAll());
        }
        break;
}
} catch (Exception $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
