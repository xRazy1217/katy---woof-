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
APP_URL=http://localhost/katy-&-woof/katy-woof
FLOW_SANDBOX=true
```

### 3. Acceder al proyecto
- Sitio: http://localhost/katy-&-woof/katy-woof/
- Admin: http://localhost/katy-&-woof/katy-woof/admin/
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
DB_NAME=dblvwvro0bzl5v
DB_USER=uposiqjihhwsg
DB_PASS=$^6T5AC1&5lE
APP_ENV=production
APP_URL=https://retratodemascotas.cl/katy-&-woof
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

## 📁 ARCHIVOS IMPORTANTES

- `.env` → Configuración activa (cambiar según entorno)
- `.env.production` → Backup de credenciales de producción (NO SUBIR)
- `.htaccess` → Configuración Apache (subir a producción)
- `api/.htaccess` → CORS para API (subir a producción)

---

## 🔧 TROUBLESHOOTING

### Error de conexión a BD en local
1. Verifica que WAMP esté corriendo
2. Verifica que la BD `katywoof_ecommerce` exista
3. Verifica usuario/contraseña en `.env`

### Error CORS en producción
1. Verifica que `api/.htaccess` exista
2. Verifica que `.htaccess` principal exista
3. Limpia caché del navegador (Ctrl+Shift+R)

### Admin no carga CSS
1. Verifica que `admin/css/admin.css` exista
2. Verifica permisos de archivos (644)
3. Limpia caché del navegador
