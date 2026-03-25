-- ============================================
-- Katy & Woof - Setup Base de Datos LOCAL
-- ============================================
-- Ejecutar este script en phpMyAdmin (localhost)
-- ============================================

-- Crear base de datos
CREATE DATABASE IF NOT EXISTS `katywoof_ecommerce` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `katywoof_ecommerce`;

-- Importar estructura desde producción
-- INSTRUCCIONES:
-- 1. Abre phpMyAdmin en http://localhost/phpmyadmin
-- 2. Crea la base de datos 'katywoof_ecommerce'
-- 3. Selecciona la base de datos
-- 4. Ve a la pestaña "Importar"
-- 5. Sube el archivo SQL de producción que exportaste
-- 6. Haz clic en "Continuar"

-- O copia y pega aquí el SQL que exportaste de producción
