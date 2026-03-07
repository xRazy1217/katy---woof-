# Sistema de Monitoreo y Sincronización de Esquema MySQL
## Documentación Técnica Completa

---

## 📋 Índice
1. [Visión General](#visión-general)
2. [Estructura de Archivos](#estructura-de-archivos)
3. [Componentes del Sistema](#componentes-del-sistema)
4. [Guía de Uso](#guía-de-uso)
5. [Mantenimiento](#mantenimiento)
6. [Escalabilidad](#escalabilidad)
7. [Troubleshooting](#troubleshooting)

---

## 🎯 Visión General

Este sistema proporciona una solución **integral** para mantener sincronizado el esquema de tu base de datos MySQL con la configuración esperada en el código.

### Características Principales:
- ✅ **Auditoría Automática**: Detecta tablas y columnas faltantes
- ✅ **Sincronización Segura**: Usa transacciones para evitar inconsistencias
- ✅ **Interfaz Intuitiva**: Panel visual en el admin
- ✅ **Logging**: Registra todos los eventos de sincronización
- ✅ **Mantenible**: Fácil de agregar nuevas tablas/columnas

---

## 📁 Estructura de Archivos

```
proyecto/
├── schema-definition.php        # Definición del esquema ideal (EDITAR AQUÍ)
├── schema-manager.php           # Lógica de auditoría y sincronización
├── config.php                   # Configuración general + función logEvent()
├── api.php                      # Endpoints de auditoría y sync
├── admin.html                   # UI de la pestaña Sistema
└── admin-system.js              # Lógica frontend de Sistema
```

---

## 🔧 Componentes del Sistema

### 1. **schema-definition.php**
Define la estructura **ideal** que espera tu aplicación.

```php
return [
    'tabla_ejemplo' => [
        'columns' => [
            'id'         => 'INT AUTO_INCREMENT PRIMARY KEY',
            'nombre'     => 'VARCHAR(255) NOT NULL',
            'email'      => 'VARCHAR(255) UNIQUE',
            'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
        ]
    ]
];
```

**Tipos SQL Soportados:**
- `INT`, `BIGINT`, `SMALLINT`
- `VARCHAR(255)`, `CHAR(10)`, `TEXT`, `LONGTEXT`
- `DECIMAL(10,2)`, `FLOAT`, `DOUBLE`
- `DATE`, `DATETIME`, `TIMESTAMP`
- `BOOLEAN` (convertido a TINYINT)
- Con constraints: `AUTO_INCREMENT`, `PRIMARY KEY`, `NOT NULL`, `UNIQUE`, `DEFAULT`, etc.

---

### 2. **schema-manager.php** (Clase SchemaManager)

**Métodos Públicos:**

#### `auditSchema()`
Audita todo el esquema y retorna reporte detallado.
```php
$manager = new SchemaManager();
$report = $manager->auditSchema();
// Retorna: [
//   'database' => 'nombre_bd',
//   'timestamp' => '2026-03-03 10:30:00',
//   'tables' => [
//       'usuarios' => [
//           'status' => 'OK|MISSING_TABLE|MISSING_COLUMNS',
//           'issues' => ['descripción del problema'],
//           'missing_columns' => [...]
//       ],
//       ...
//   ],
//   'ok_tables' => 6,
//   'tables_with_issues' => 2
// ]
```

#### `syncDatabase()`
Ejecuta la sincronización **dentro de una transacción**.
```php
$result = $manager->syncDatabase();
// Retorna: [
//   'success' => true,
//   'executed_statements' => [...],
//   'tables_created' => 2,
//   'columns_added' => 5
// ]
```

#### `testConnection()`
Verifica la conexión y obtiene información.
```php
$connInfo = $manager->testConnection();
// Retorna: ['success' => true, 'database' => '...', 'mysql_version' => '...']
```

#### `generateSyncSQL()`
Genera los SQL statements sin ejecutarlos (útil para inspección).
```php
$sqlQueries = $manager->generateSyncSQL();
```

---

### 3. **api.php** (Nuevos Endpoints)

#### POST/GET `api.php?action=audit_schema`
Audita el esquema y retorna JSON.

**Ejemplo de uso:**
```bash
curl "http://localhost/api.php?action=audit_schema"
```

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "database": "db_katy",
    "overall_status": "NEEDS_SYNC",
    "tables": { ... }
  }
}
```

#### POST `api.php?action=sync_database&auth=fotopet2026`
Ejecuta la sincronización **(requiere autenticación)**.

**Ejemplo de uso:**
```bash
curl -X POST "http://localhost/api.php?action=sync_database&auth=fotopet2026"
```

#### GET `api.php?action=get_db_status`
Obtiene estado de conexión y métricas.

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "database": "db_katy",
    "host": "localhost",
    "mysql_version": "8.0.28",
    "table_count": 8,
    "size_mb": 2.50
  }
}
```

---

### 4. **admin-system.js** (Frontend)

Objeto `AdminSystem` con métodos:

```javascript
AdminSystem.loadInitialStatus()      // Carga estado inicial
AdminSystem.checkDatabaseStatus()    // Chequea conexión
AdminSystem.auditSchema()            // Realiza auditoría
AdminSystem.syncDatabase()           // Ejecuta sincronización
AdminSystem.displayAuditResults()    // Muestra reporte
```

---

## 🚀 Guía de Uso

### Por Primera Vez

1. **Abre el panel admin** → Pestaña "✓ Sistema"
2. **Verifica conexión** → Debería mostrar verde ✓
3. **Haz clic en "Auditar Esquema"** → Espera el análisis
4. **Si hay problemas**, presiona **"Sincronizar BD"**
5. **Confirma en el popup** → Se ejecutarán los cambios

### Ver Histórico de Sincronizaciones

El sistema registra cada sincronización en la tabla `logs`:

```sql
SELECT * FROM logs WHERE event_type = 'database_sync' ORDER BY created_at DESC;
```

---

## 🔧 Mantenimiento

### Agregar una Nueva Tabla

1. Edita `schema-definition.php`:
```php
'comentarios' => [
    'columns' => [
        'id'          => 'INT AUTO_INCREMENT PRIMARY KEY',
        'post_id'     => 'INT NOT NULL',
        'author'      => 'VARCHAR(100)',
        'content'     => 'TEXT',
        'created_at'  => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
    ]
]
```

2. Abre el admin → Pestaña Sistema
3. Presiona **Auditar Esquema**
4. Presiona **Sincronizar BD**
5. ¡Listo! La tabla se crea automáticamente

### Agregar una Columna a Tabla Existente

1. Edita `schema-definition.php`:
```php
'usuarios' => [
    'columns' => [
        // ... columnas existentes ...
        'verificado'  => 'BOOLEAN DEFAULT FALSE'  // NUEVA
    ]
]
```

2. Audita y sincroniza (igual proceso que arriba)

### Cambiar Tipo de Dato

⚠️ **Nota:** El sistema **NO** modifica columnas existentes, solo agrega nuevas.
Si necesitas cambiar un tipo de dato:

```sql
ALTER TABLE usuarios MODIFY COLUMN edad INT;
```

Hazlo manualmente en la BD, luego actualiza `schema-definition.php` para reflejar el cambio.

---

## 📈 Escalabilidad

### Para Proyectos Grandes

Si tu aplicación tiene **100+ tablas**, considera:

1. **Dividir el schema en módulos:**
```php
// schema-definition.php
return array_merge(
    require 'schema-modules/usuarios.php',
    require 'schema-modules/blog.php',
    require 'schema-modules/ecommerce.php'
);
```

2. **Crear índices:**
```php
'usuarios' => [
    'columns' => [ ... ],
    'indexes' => [
        'email' => 'UNIQUE',
        'created_at' => 'INDEX'
    ]
]
```

3. **Agregar foreign keys:**
```php
// En schema-manager.php, extender generateCreateTableSQL()
```

---

## 🐛 Troubleshooting

### "Conexión Rechazada"
- Verifica credenciales en `config.php`
- Asegúrate que MySQL está corriendo
- Intenta con 127.0.0.1 en lugar de localhost

### "Acceso Denegado al Sincronizar"
- Verifica que `$auth_key` en `api.php` coincida con tu clave
- Comprueba en `admin-system.js` que `authKey` sea correcto

### "Error CREATE TABLE"
- Revisa la sintaxis en `schema-definition.php`
- Algunos servidores no soportan tipos como `JSON` -- usa `LONGTEXT` en su lugar
- Verifica que charsets sean utf8mb4

### "Columnas no se agregan"
- Asegúrate que el nombre de la columna sea **exacto** (case-sensitive)
- Verifica el tipo de dato en `information_schema.COLUMNS`

### Revertir Cambios
Si algo salió mal:

1. **Restaurar tabla completa:**
```sql
DROP TABLE nombre_tabla;
-- Actualizar schema-definition.php
-- Auditar y sincronizar nuevamente
```

2. **Restaurar desde backup:**
```bash
mysql -u usuario -p db_name < backup.sql
```

---

## 📊 Ejemplos Avanzados

### Verificar Estado Manualmente en terminal
```php
<?php
require 'schema-manager.php';
$mgr = new SchemaManager();

// Auditoría
$audit = $mgr->auditSchema();
echo json_encode($audit, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>
```

### Sincronizar desde CLI
```bash
php -r "require 'schema-manager.php'; $m = new SchemaManager(); $r = $m->syncDatabase(); echo json_encode(\$r, JSON_PRETTY_PRINT);"
```

### Generar SQL sin ejecutar
```php
$sql = $mgr->generateSyncSQL();
foreach ($sql as $statement) {
    echo $statement . "\n\n";
}
```

---

## 🔐 Seguridad

- ✅ Los endpoints de sincronización requieren **autenticación** (`auth_key`)
- ✅ Se usa **transacciones** para evitar estados inconsistentes
- ✅ Cada operación se **registra** en la tabla `logs`
- ✅ Los errores SQL se capturan y se revierte la transacción si falla

---

## 📝 Notas Importantes

1. **Este sistema es **aditivo** por defecto**
   - Crea tablas que faltan
   - Agrega columnas que faltan
   - **NO elimina** tablas ni columnas

2. **Úsalo en desarrollo primero**
   - Prueba los cambios en tu máquina local
   - Haz backup antes de sincronizar en producción

3. **El schema debe ser la "fuente de verdad"**
   - Siempre actualiza `schema-definition.php` antes de sincronizar

---

## 🎓 Diagrama de Flujo

```
┌─────────────────────────────────────┐
│  Usuario presiona "Auditar Esquema"│
└────────────┬────────────────────────┘
             │
             ▼
┌─────────────────────────────────────┐
│  SchemaManager->auditSchema()       │
│  - Lee schema ideal                 │
│  - Consulta MySQL (information_sch) │
│  - Compara ambos                    │
└────────────┬────────────────────────┘
             │
             ▼
┌─────────────────────────────────────┐
│  Frontend muestra reporte           │
│  - Tablas OK / Con problemas       │
│  - Detalle de discrepancias        │
└────────────┬────────────────────────┘
             │
             ▼
┌─────────────────────────────────────┐
│  Usuario presiona "Sincronizar BD" │
│  (Solo si hay inconsistencias)      │
└────────────┬────────────────────────┘
             │
             ▼
┌─────────────────────────────────────┐
│  SchemaManager->syncDatabase()      │
│  - BEGIN TRANSACTION                │
│  - Genera CREATE/ALTER statements   │
│  - Las ejecuta una por una          │
│  - Si todo va bien: COMMIT          │
│  - Si hay error: ROLLBACK           │
└────────────┬────────────────────────┘
             │
             ▼
┌─────────────────────────────────────┐
│  logEvent('database_sync', ...)     │
│  Registra en tabla logs             │
└────────────┬────────────────────────┘
             │
             ▼
┌─────────────────────────────────────┐
│  Frontend muestra resultado         │
│  - Tablas creadas                  │
│  - Columnas agregadas              │
│  - Vuelve a auditar                │
└─────────────────────────────────────┘
```

---

## 📞 Soporte

Para reportar bugs o solicitar mejoras, revisa:
- Los logs en la tabla `logs`
- Console del navegador (F12 → Console)
- Archivo de errores PHP si está configurado

---

**Versión:** 1.0  
**Última actualización:** Marzo 2026  
**Autor:** Sistema de Admin - Katy & Woof Studios
