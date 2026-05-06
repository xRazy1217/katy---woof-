# Katy & Woof - Configuración Local vs Producción

## 🏠 TRABAJAR EN LOCAL (WAMP)

### 1. Configurar base de datos
```
1. Abre phpMyAdmin: http://localhost/phpmyadmin
2. Crea base de datos: katywoof_ecommerce
3. Importa el SQL de producción (si lo tienes)
```

### 2. Verificar .env
El archivo `.env` debe tener:
```
DB_HOST=localhost
DB_NAME=katywoof_ecommerce
DB_USER=root
DB_PASS=
APP_ENV=development
APP_URL=https://retratodemascotas.cl
FLOW_SANDBOX=true
```

### 3. Acceder al proyecto
- Sitio: https://retratodemascotas.cl/
- Admin: https://retratodemascotas.cl/admin/
- Contraseña: Asesor25

---

## 🚀 SUBIR A PRODUCCIÓN (SiteGround)

### 1. Copiar credenciales de producción
```bash
# Renombrar archivo de backup
cp .env.production .env
```

O copiar manualmente desde `.env.production` a `.env`

### 2. Verificar .env en producción
```
DB_HOST=localhost
DB_NAME=dbydpyunocwvaw
DB_USER=ubvjxkcdgoega
DB_PASS=1497200++
APP_ENV=production
APP_URL=https://retratodemascotas.cl
FLOW_SANDBOX=false
```

### 3. Subir archivos vía FTP/cPanel
- Subir todos los archivos EXCEPTO:
  - `.env.production` (es solo backup local)
  - `setup-local-db.sql` (es solo para local)
  - `README-CONFIG.md` (este archivo)

### 4. Verificar permisos en hosting
- Carpetas: 755
- Archivos PHP: 644
- .htaccess: 644

---

## 🏗️ ARQUITECTURA DE LA API (v6.5)

El sistema utiliza un router centralizado en `api/router.php`.
- **Módulos**:
  - `EcommerceAPI` -> Productos, Categorías, Órdenes, Cupones.
  - `SettingsAPI` -> Ajustes del sitio (textos e imágenes).
  - `UsersAPI` -> Autenticación y perfiles.
  - `CartAPI` / `CheckoutAPI` -> Flujo de compra.

---

## 📁 ARCHIVOS IMPORTANTES

- `.env` → Configuración activa (cambiar según entorno).
- `.env.production` → Backup de credenciales de producción (NO SUBIR).
- `api/router.php` → Punto de entrada único para todas las acciones.
- `admin/js/admin.settings.js` → Lógica de guardado individual de ajustes.

---

## 🔧 TROUBLESHOOTING

### Error 500 en la API
1. Verifica que `api/router.php` no tenga errores de sintaxis.
2. Verifica que todos los archivos `*-api.php` existan en la carpeta `api/`.
3. SiteGround utiliza OPcache; si subes cambios y no se reflejan, intenta renombrar el archivo temporalmente o limpiar caché desde el cPanel.

### Los ajustes no se guardan
1. Verifica que la tabla `ecommerce_settings` tenga permisos de escritura.
2. Abre la consola del navegador (F12) y revisa la pestaña "Network" para ver la respuesta de la API.
3. Asegúrate de que la `ADMIN_KEY` en `config.php` coincida con la configurada en el cliente.

### Error de conexión a BD en local
1. Verifica que WAMP esté corriendo.
2. Verifica que la BD `katywoof_ecommerce` exista.
3. Verifica usuario/contraseña en `.env`.
