<?php
/**
 * Katy & Woof - Image Handler Module v6.0
 * Optimización y manejo de imágenes
 */

class ImageHandler {
    private static $upload_dir = 'uploads/';

    /**
     * Obtiene la calidad de compresión WebP óptima según las dimensiones
     */
    public static function getOptimalQuality($width, $height) {
        $megapixels = ($width * $height) / 1000000;

        if ($megapixels > 12) return 70;      // Imágenes 4K o grandes: menos calidad
        if ($megapixels > 8) return 75;       // Imágenes muy grandes
        if ($megapixels > 4) return 80;       // Imágenes medianas
        if ($megapixels > 1) return 82;       // Imágenes pequeñas: mayor calidad
        return 85;                             // Imágenes muy pequeñas: máxima calidad
    }

    /**
     * Optimiza y guarda una imagen subida con validaciones de seguridad mejoradas
     * Acepta parámetro opcional quality (70-95) para personalizar compresión
     */
    public static function optimizeAndSaveImage($file, $prefix, $quality = null) {
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
        $base_name = $prefix . "_" . time() . "_" . bin2hex(random_bytes(4));
        $new_name = $base_name . "." . $ext;
        $target_path = self::$upload_dir . $new_name;

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

            // Procesar según tipo
            $image = null;
            switch ($mime_type) {
                case 'image/jpeg':
                    $image = imagecreatefromjpeg($tmp_name);
                    break;
                case 'image/png':
                    $image = imagecreatefrompng($tmp_name);
                    break;
                case 'image/webp':
                    if (!function_exists('imagecreatefromwebp')) {
                        return ["success" => false, "error" => "El servidor no soporta lectura WebP."];
                    }
                    $image = imagecreatefromwebp($tmp_name);
                    break;
                case 'image/gif':
                    $image = imagecreatefromgif($tmp_name);
                    break;
                default:
                    return ["success" => false, "error" => "Tipo de imagen no soportado."];
            }

            if (!$image) {
                return ["success" => false, "error" => "Error al procesar la imagen."];
            }

            // Calcular nueva dimensión manteniendo aspect ratio (máx 2048px)
            $max_size = 2048;
            if ($width > $max_size || $height > $max_size) {
                $ratio = min($max_size / $width, $max_size / $height);
                $new_width = round($width * $ratio);
                $new_height = round($height * $ratio);

                $resized = imagecreatetruecolor($new_width, $new_height);
                // Preserve transparency for PNG
                if ($mime_type === 'image/png') {
                    imagealphablending($resized, false);
                    imagesavealpha($resized, true);
                    $transparent = imagecolorallocatealpha($resized, 255, 255, 255, 127);
                    imagefill($resized, 0, 0, $transparent);
                }

                imagecopyresampled($resized, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                imagedestroy($image);
                $image = $resized;
                $width = $new_width;
                $height = $new_height;
            }

            // Guardar como WebP si está soportado, sino mantener formato original
            // Usar qualidad personalizada si viene, sino usar la óptima automática
            if ($quality === null) {
                $quality = self::getOptimalQuality($width, $height);
            } else {
                // Asegurar que está en rango válido (70-95)
                $quality = max(70, min(95, (int)$quality));
            }
            
            $saved = false;

            if (function_exists('imagewebp')) {
                $target_path = self::$upload_dir . $base_name . '.webp';
                $saved = imagewebp($image, $target_path, $quality);
            } else {
                // Fallback al formato original
                switch ($mime_type) {
                    case 'image/jpeg':
                        $saved = imagejpeg($image, $target_path, $quality);
                        break;
                    case 'image/png':
                        $saved = imagepng($image, $target_path, 8); // PNG quality 0-9, 8 es bueno
                        break;
                    case 'image/gif':
                        $saved = imagegif($image, $target_path);
                        break;
                    case 'image/webp':
                        return ["success" => false, "error" => "El servidor no soporta escritura WebP."];
                        break;
                }
            }

            imagedestroy($image);

            if (!$saved) {
                return ["success" => false, "error" => "Error al guardar la imagen."];
            }

            return ["success" => true, "path" => $target_path];

        } catch (Exception $e) {
            return ["success" => false, "error" => "Error procesando imagen: " . $e->getMessage()];
        }
    }
    
    /**
     * Versión estática que acepta parámetro quality desde FormData
     */
    public static function optimizeAndSaveImageWithQuality($file, $prefix) {
        // Buscar quality en $_POST o $_REQUEST
        $quality = null;
        if (!empty($_POST['image_quality'])) {
            $quality = (int)$_POST['image_quality'];
        } elseif (!empty($_REQUEST['image_quality'])) {
            $quality = (int)$_REQUEST['image_quality'];
        }
        
        return self::optimizeAndSaveImage($file, $prefix, $quality);
    }

    /**
     * Elimina un archivo físico si existe
     */
    public static function deletePhysicalFile($path) {
        if (!$path || strpos($path, 'img/') === 0) return; // No borrar placeholders
        if (file_exists($path)) @unlink($path);
    }
}
?>