# 📦 Guía de Subida al Hosting - Katy & Woof

## ✅ Paso 1: Preparar las Credenciales

### 1.1 Obtener credenciales de MySQL desde tu hosting

1. **Accede al panel de control** de tu hosting (cPanel, Plesk, etc.)
2. **Busca la sección "MySQL Databases"** o "Bases de datos MySQL"
3. **Anota los siguientes datos:**
   - Nombre del host (generalmente `localhost`)
   - Nombre de la base de datos
   - Usuario de la base de datos
   - Contraseña

### 1.2 Crear base de datos (si no existe)

Si aún no tienes una base de datos creada:

1. En cPanel → **MySQL Databases**
2. **Crear nueva base de datos:**
   - Nombre: `katywoof_db` (o el que prefieras)
   - Click en "Create Database"
3. **Crear usuario:**
   - Username: `katywoof_user`
   - Password: (genera una contraseña segura)
   - Click en "Create User"
4. **Asignar usuario a la base de datos:**
   - Selecciona el usuario
   - Selecciona la base de datos
   - Marca "ALL PRIVILEGES"
   - Click en "Make Changes"

### 1.3 Editar el archivo `.env`

Abre el archivo `.env` que acabas de crear y modifica estas líneas:

```env
DB_HOST=localhost
DB_NAME=katywoof_db          # ← Cambia por tu nombre de BD
DB_USER=katywoof_user        # ← Cambia por tu usuario
DB_PASS=tu_contraseña_aqui   # ← Cambia por tu contraseña

ADMIN_AUTH_KEY=mi_clave_super_secreta_2026  # ← Cambia por seguridad
```

---

## 📤 Paso 2: Subir Archivos al Hosting

### 2.1 Usando el Administrador de Archivos del hosting

1. **Accede al File Manager** de tu hosting
2. **Navega a la carpeta `public_html`** (o `www`, `htdocs` según tu hosting)
3. **Sube TODOS los archivos** del proyecto:
   - Puedes subirlos en ZIP y descomprimirlos allí
   - O subir archivo por archivo

### 2.2 Archivos que DEBES subir

✅ **Archivos esenciales:**
```
.env                          ← TU ARCHIVO DE CREDENCIALES
.htaccess                     ← Protección de seguridad
config.php                    ← Configuración maestra
index.php                     ← Página principal
admin.html                    ← Panel de administración
api.php, ecommerce-api.php   ← APIs del sistema

/admin/                       ← Todos los archivos del panel
/api/                         ← Todos los archivos de API
/js/                          ← JavaScript
/css/                         ← Estilos
/uploads/                     ← Carpeta de imágenes (crear vacía)
/logs/                        ← Carpeta de logs (crear vacía)

INIT_SCHEMA_SYSTEM.sql        ← Para importar estructura BD
INIT_ECOMMERCE_SCHEMA.sql     ← Para e-commerce (opcional)
```

❌ **Archivos que NO debes subir:**
```
.env.example                  ← Es solo plantilla
.env.local                    ← Es solo local
node_modules/                 ← Si existe
.git/                         ← Si existe
*.md (archivos markdown)      ← Documentación innecesaria en producción
```

### 2.3 Permisos de carpetas (IMPORTANTE)

Asegúrate de que estas carpetas tengan permisos de escritura:

1. En File Manager, click derecho en cada carpeta
2. Selecciona **"Change Permissions"** o **"Permisos"**
3. Configura estos valores:

```
/uploads/     → 755 o 775
/logs/        → 755 o 775
```

Si no existen estas carpetas, créalas con:
- Click derecho → "New Folder"
- Nombre: `uploads` y `logs`

---

## 🗄️ Paso 3: Importar la Estructura de Base de Datos

### 3.1 Acceder a phpMyAdmin

1. En tu panel de control, busca **"phpMyAdmin"**
2. Click para acceder
3. Selecciona tu base de datos en el panel izquierdo

### 3.2 Importar la estructura base

1. Click en la pestaña **"Import"** o **"Importar"**
2. Click en **"Choose File"** o **"Seleccionar archivo"**
3. Selecciona: `INIT_SCHEMA_SYSTEM.sql`
4. Click en **"Go"** o **"Continuar"**
5. Espera el mensaje: "Import has been successfully finished"

### 3.3 Importar e-commerce (opcional)

Si vas a usar el sistema de tienda online:

1. Repite el proceso anterior con: `INIT_ECOMMERCE_SCHEMA.sql`
2. Click en "Import" y selecciona el archivo
3. Click en "Go"

---

## 🧪 Paso 4: Verificar que Todo Funcione

### 4.1 Probar la página principal

1. Abre tu navegador
2. Ve a: `https://tudominio.com`
3. Deberías ver tu sitio web funcionando

### 4.2 Acceder al panel de administración

1. Ve a: `https://tudominio.com/admin.html`
2. Deberías ver el panel de administración
3. La clave de acceso es la que definiste en `ADMIN_AUTH_KEY` del archivo `.env`

### 4.3 Ejecutar autodiagnóstico

1. En el panel admin, ve a la pestaña **"Sistema"**
2. Click en el botón: **"✅ Ejecutar Autodiagnóstico Visual"**
3. Deberías ver un banner verde que diga: **"Autodiagnostico: OK | Tabs cargados: 11/11"**

### 4.4 Auditar la base de datos

1. En la misma pestaña "Sistema", click en: **"🔍 Auditar Esquema"**
2. El sistema verificará que todas las tablas y columnas existan
3. Si aparecen errores, click en: **"🛠️ Reparar Estructura BD"**

---

## 🔒 Paso 5: Seguridad Adicional

### 5.1 Cambiar la clave de administración

1. Edita el archivo `.env` en el hosting
2. Cambia la línea:
   ```env
   ADMIN_AUTH_KEY=mi_clave_super_secreta_unica_2026
   ```

3. **IMPORTANTE:** También debes cambiarla en el archivo `admin-ui.js`
4. Busca la línea que dice:
   ```javascript
   localStorage.setItem('auth_key', 'fotopet2026');
   ```
5. Cámbiala por tu nueva clave

### 5.2 Verificar protección del .env

1. Intenta acceder en el navegador a: `https://tudominio.com/.env`
2. **Deberías ver un error 403 Forbidden** ← Esto es BUENO
3. Si ves el contenido del archivo, contacta a tu hosting para activar `.htaccess`

---

## 🎯 Resumen Rápido (Checklist)

- [ ] Obtuve las credenciales de MySQL de mi hosting
- [ ] Edité el archivo `.env` con mis credenciales reales
- [ ] Subí TODOS los archivos al hosting (incluido `.env` y `.htaccess`)
- [ ] Creé las carpetas `/uploads/` y `/logs/` con permisos 755
- [ ] Importé `INIT_SCHEMA_SYSTEM.sql` en phpMyAdmin
- [ ] (Opcional) Importé `INIT_ECOMMERCE_SCHEMA.sql`
- [ ] Accedí a mi sitio y funciona: `https://tudominio.com`
- [ ] Accedí al admin: `https://tudominio.com/admin.html`
- [ ] Ejecuté el autodiagnóstico visual → Sale OK
- [ ] Audité el esquema de base de datos → Sin errores
- [ ] Cambié `ADMIN_AUTH_KEY` por una clave única
- [ ] Verifiqué que `.env` no sea accesible públicamente

---

## ❓ Problemas Comunes

### Error: "Database connection failed"

**Causa:** Credenciales incorrectas en `.env`

**Solución:**
1. Verifica que el archivo `.env` esté en la raíz del proyecto
2. Revisa que las credenciales sean exactas (sin espacios extra)
3. Intenta cambiar `DB_HOST=localhost` por `DB_HOST=127.0.0.1`

### Error 500: Internal Server Error

**Causa:** Problema con PHP o permisos

**Solución:**
1. Verifica la versión de PHP (debe ser 7.4 o superior)
2. En cPanel → "Select PHP Version" → Elige PHP 8.0 o 8.1
3. Revisa los permisos de las carpetas

### La página se ve sin estilos

**Causa:** Archivos CSS no se cargaron

**Solución:**
1. Verifica que subiste todas las carpetas `/css/` y archivos `.css`
2. Revisa en File Manager que existan: `main.css`, `header.css`, `footer.css`

### El panel admin no carga los tabs

**Causa:** Falta la carpeta `/admin/`

**Solución:**
1. Verifica que subiste TODA la carpeta `/admin/` con sus subcarpetas
2. Debe contener: `tab-*.html` y la carpeta `/admin/js/`

---

## 📞 Soporte

Si tienes problemas:

1. Revisa el archivo `logs/` para ver errores del sistema
2. Abre la consola del navegador (F12) y busca errores en rojo
3. Verifica en phpMyAdmin que las tablas se crearon correctamente

---

**¡Listo! Tu sitio Katy & Woof debería estar funcionando en tu hosting.** 🎉
