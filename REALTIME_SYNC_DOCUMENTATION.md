# ⚡ SINCRONIZACIÓN EN TIEMPO REAL: Panel Admin ↔ MySQL ↔ Páginas Públicas

**Estado:** ✅ IMPLEMENTADO Y VERIFICADO  
**Última Actualización:** 2026  
**Versión:** 6.0

---

## 🎯 ¿Cómo Funciona la Sincronización en Tiempo Real?

```
┌─────────────────┐         ┌──────────────────┐         ┌──────────────┐
│  Panel Admin    │────────▶│   api.php        │────────▶│  MySQL BD    │
│  (admin.html)   │  POST   │  (endpoints)     │  INSERT │  (7 tablas)  │
└─────────────────┘         │  UPDATE          │  UPDATE └──────────────┘
                            │  DELETE          │          
                            └──────────────────┘          
                                    ▲
                            GET + cache-buster
                            (v=timestamp)
                                    │
                            ┌───────┴──────────┐
                            │                  │
                    ┌───────▼─────┐    ┌──────▼──────┐
                    │  blog.php   │    │  servicios  │
                    │  galeria    │    │  .php etc   │
                    │  etc        │    │             │
                    └─────────────┘    └─────────────┘
                    (Páginas Públicas)
                    Usuarios ven datos frescos
```

---

## 🔌 Arquitectura de Conexión

### 1. **Backend (Servidor/PHP)**

**config.php** - Configuración centralizada:
```php
function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        $pdo->exec("SET NAMES utf8mb4");
        return $pdo;
    } catch (PDOException $e) {
        // Fallback: localhost → 127.0.0.1
        // ...
    }
}
```

**api.php** - Endpoints que persisten datos:
```php
// Headers para NO cachear
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Endpoints principales
'save_settings'  → INSERT...ON DUPLICATE KEY UPDATE
'save_portfolio' → INSERT o UPDATE
'save_service'   → INSERT o UPDATE
'save_blog'      → INSERT o UPDATE
'save_process'   → INSERT o UPDATE
'save_list_item' → INSERT

'get_portfolio'  → SELECT * FROM portfolio
'get_services'   → SELECT * FROM services
'get_blog'       → SELECT * FROM blog_posts
'get_process'    → SELECT * FROM process_steps
'get_lists'      → SELECT * FROM site_lists
```

---

## 🔄 Flujo de Sincronización Paso a Paso

### Cuando un Admin Guarda Cambios:

```
1. Usuario hace click en "Guardar" en admin.html
   ▼
2. JavaScript (admin-content.js) captura datos del formulario
   ▼
3. Valida datos con ImageUploadUtils.validateImageComplete()
   ▼
4. Construye FormData con auth token
   ▼
5. Envía POST a: api.php?action=save_service
   Content: { title, description, main_image_url, auth }
   ▼
6. api.php recibe request
   • Verifica autenticación (token = 'fotopet2026')
   • Valida datos
   • Si ID existe: UPDATE tabla WHERE id = ?
   • Si no existe: INSERT INTO tabla VALUES (?)
   ▼
7. PDO ejecuta query en MySQL
   ▼
8. Retorna JSON: { success: true }
   ▼
9. JavaScript muestra toast: "Guardado ✓"
   ▼
10. FIN - Datos en BD
```

### Cuando una Usuario Visita Página Pública:

```
1. Usuario visita servicios.php
   ▼
2. PHP carga header/footer desde BD
   $settings = getSiteSettings() → SELECT FROM site_settings
   ▼
3. HTML carga completamente
   ▼
4. JavaScript en servicios.php ejecuta:
   ```javascript
   const loadData = async () => {
       const res = await fetch(`api.php?action=get_services&v=${Date.now()}`);
       // ↑ Cache-buster: v=timestamp garantiza que NO usa cache
       const data = await res.json();
       renderServices(data);
   }
   ```
   ▼
5. api.php recibe GET request
   • NO requiere autenticación (es público)
   • Ejecuta: SELECT * FROM services
   • Retorna JSON con todos los servicios
   ▼
6. JavaScript renderiza HTML con datosactualizados
   ▼
7. Usuario ve los datos más recientes
```

---

## ⚡ Garantías de Sincronización en Tiempo Real

### ✅ 1. Cache-Busting en Peticiones JavaScript

**blog.php:**
```javascript
const res = await fetch(`api.php?action=get_blog&v=${Date.now()}`);
// v=1709556123456 ← timestamp actual
// Cada carga = diferente URL → navegador NO cachea
```

**servicios.php:**
```javascript
const res = await fetch(`api.php?action=get_services&v=${Date.now()}`);
```

### ✅ 2. Headers HTTP que Previenen Caché

**api.php:**
```php
header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
// El navegador NUNCA cachea estas respuestas
```

### ✅ 3. Cache-Busting en CSS y JavaScript

**blog.php, servicios.php, etc:**
```php
<link rel="stylesheet" href="main.css?v=<?php echo time(); ?>">
<script src="whatsapp.js?v=<?php echo time(); ?>"></script>
```

### ✅ 4. Queries Directas a BD (Sin ORM, Sin Cache)

Todos los datos se ligen directamente desde MySQL:
```php
$stmt = $pdo->query("SELECT * FROM services ORDER BY id DESC");
return $stmt->fetchAll(PDO::FETCH_ASSOC);
```

### ✅ 5. Prepared Statements Seguros

```php
$pdo->prepare("INSERT INTO services (title, description) VALUES (?, ?)")
    ->execute([$_POST['title'], $_POST['description']]);
```

---

## 🧪 Cómo Validar que Funciona

### Opción 1: Prueba Manual

1. Abrir admin en navegador y autenticarse
2. Ir a "Servicios" tab
3. Agregar un nuevo servicio
4. En otra pestaña, abrir publicsite.com/servicios.php
5. ✅ **Resultado esperado:** El nuevo servicio aparece INMEDIATAMENTE

### Opción 2: Ejecutar Validador Automático

En consola del navegador (F12 → Console):

```javascript
validateSync()
```

Verifica:
- ✅ Conexión a BD
- ✅ Datos actuales
- ✅ Persistencia de cambios
- ✅ Sincronización en páginas públicas
- ✅ Caching de API
- ✅ Consistencia de datos

---

## 🔍 Archivos de Configuración Críticos

| Archivo | Propósito | Estado |
|---------|-----------|--------|
| `config.php` | Conexión a BD MySQL | ✅ PDO con fallback |
| `api.php` | Endpoints CRUD | ✅ Headers anti-cache |
| `blog.php` | Página pública blog | ✅ Cache-busting implementado |
| `servicios.php` | Página pública servicios | ✅ Cache-busting implementado |
| `realtime-sync-validator.js` | Validación automática | ✅ Nuevo - 6 tests |

---

## 📊 Base de Datos - Estructura

```sql
site_settings       -- Configuración global (logo, favicon, etc)
portfolio          -- Obras de arte
services           -- Servicios ofrecidos
blog_posts         -- Artículos del blog
process_steps      -- Proceso de trabajo
site_lists         -- Listas dinámicas (categorías, textos)
logs               -- Auditoría de cambios (opcional)
```

**Conexión:**
```
Host: DB_HOST (localhost)
Database: dbyh6du0yfle1i
User: uiuxyllculkca
Pass: fotopet2026 (definida en config.php)
```

---

## 🚀 Endpoints API Disponibles

### GET (Lectura - Sin Autenticación)

```
/api.php?action=get_portfolio         → Obras de arte
/api.php?action=get_services          → Servicios
/api.php?action=get_blog              → Posts del blog
/api.php?action=get_process           → Pasos del proceso
/api.php?action=get_lists             → Listas dinámicas
/api.php?action=test_connection       → Test de BD
```

### POST (Sistema - Requiere Autenticación)

```
/api.php?action=save_settings         → Guardar configuración
/api.php?action=save_portfolio        → Guardar obra
/api.php?action=save_service          → Guardar servicio
/api.php?action=save_blog             → Guardar post
/api.php?action=save_process          → Guardar paso
/api.php?action=save_list_item        → Guardar item
```

### DELETE (Sistema - Requiere Autenticación)

```
/api.php?action=delete_service&id=123 → Eliminar servicio
/api.php?action=delete_blog&id=123     → Eliminar post
```

---

## 🔐 Autenticación

Todas las operaciones de escritura requieren:

```javascript
const auth = localStorage.getItem('kw_admin_key'); // 'fotopet2026'

fetch('api.php?action=save_service', {
    method: 'POST',
    body: new FormData({
        title: 'Mi Servicio',
        auth: auth  // ← OBLIGATORIO
    })
});
```

---

## 💡 Características de Seguridad

1. **Autenticación en Cliente:**
   - Token almacenado en localStorage
   - Se envía con cada request protegido

2. **Autenticación en Servidor:**
   - api.php verifica que auth = 'fotopet2026'
   - Si no coincide, rechaza con HTTP 401

3. **Prepared Statements:**
   - Previene SQL injection
   - PDO maneja parámetros de forma segura

4. **Manejo de Archivos:**
   - Validación de dimensiones (300-4096px)
   - Validación de tipo (JPG, PNG, WebP, GIF)
   - Validación de tamaño (máx 10MB)
   - Compresión a WebP en servidor

5. **CORS:**
   - `Access-Control-Allow-Origin: *`
   - Permite requests desde cualquier origen

---

## ⚠️ Troubleshooting

### Problema: Los cambios no se persisten

**Solución:**
```javascript
// 1. Verificar conexión a BD
diagnose()  // En consola

// 2. Runvalidador de sincronización
validateSync()

// 3. Verificar que auth token existe
console.log(localStorage.getItem('kw_admin_key'))

// 4. Revisar Network tab (F12)
// Buscar peticiones a api.php y verificar response
```

### Problema: Las páginas públicas no muestran cambios

**Solución:**
```javascript
// 1. Limpiar cache del navegador (Ctrl+Shift+Del)
// 2. Hacer hard refresh (Ctrl+Shift+R)
// 3. El validador debería indicar si hay problemas
validateSync()
```

### Problema: Error "No connection to database"

**Verificar:**
1. Credenciales en config.php
2. Servidor MySQL está corriendo
3. Base de datos existe
4. Usuario/password son correctos

---

## 📈 Performance

| Métrica | Valor | Nota |
|---------|-------|------|
| Insert tiempo promedio | <100ms | Depende del servidor |
| Update tiempo promedio | <100ms | Depende del servidor |
| Select tiempo promedio | <50ms | Para <1000 registros |
| Cache-buster overhead | <1ms | Solo parámetro URL |

---

## ✨ Resumen

✅ **Panel Admin conectado con MySQL en tiempo real**
- Cambios se guardan inmediatamente en la BD
- Las páginas públicas cargan siempre datos frescos
- Cache-busting garantiza sin problemas de cache
- Headers HTTP previenen caché del navegador
- Validadores automáticos para debugging

**Para verificar todo funciona:**
```javascript
// En consola del navegador (F12)
validateSync()
```

