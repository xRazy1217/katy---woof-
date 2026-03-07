# ⚡ CHEATSHEET - Sistema de Sincronización de Esquema

## 🚀 En 30 Segundos

```bash
1. Edita schema-definition.php
2. Abre admin → Sistema   
3. Audita 🔍
4. Sincroniza ⚙️ (si hay discrepancias)
→ ¡Listo!
```

---

## 📋 Operaciones Comunes

### Agregar una TABLA NUEVA

```php
// En schema-definition.php
'comentarios' => [
    'columns' => [
        'id'      => 'INT AUTO_INCREMENT PRIMARY KEY',
        'texto'   => 'TEXT NOT NULL',
        'post_id' => 'INT NOT NULL'
    ]
]
```
**Luego:** Audita + Sincroniza en admin

### Agregar una COLUMNA

```php
// En tabla existente 'usuarios'
'usuarios' => [
    'columns' => [
        'id'        => 'INT AUTO_INCREMENT PRIMARY KEY',
        'nombre'    => 'VARCHAR(255)',
        'email'     => 'VARCHAR(255)',
        'activo'    => 'BOOLEAN DEFAULT TRUE'  ← NUEVA
    ]
]
```
**Luego:** Audita + Sincroniza

### Ver HISTORIAL

```sql
SELECT * FROM logs 
WHERE event_type = 'database_sync' 
ORDER BY created_at DESC LIMIT 10;
```

### Verificar ESTRUCTURA ACTUAL

```sql
-- Ver columnas de una tabla
DESCRIBE nombre_tabla;

-- Ver todas las tablas
SHOW TABLES;

-- Ver información MySQL
SELECT VERSION();
```

---

## 🔧 Tipos de Datos Comunes

| Tipo | Ejemplo | Uso |
|------|---------|-----|
| `INT` | `INT AUTO_INCREMENT PRIMARY KEY` | IDs, números enteros |
| `VARCHAR(n)` | `VARCHAR(255)` | Texto corto |
| `TEXT` | `TEXT` | Párrafos |
| `LONGTEXT` | `LONGTEXT` | Artículos, descripciones |
| `DECIMAL(10,2)` | `DECIMAL(10,2)` | Dinero |
| `DATE` | `DATE` | Solo fecha |
| `DATETIME` | `DATETIME` | Fecha+hora |
| `TIMESTAMP` | `TIMESTAMP DEFAULT...` | Auto-marca |
| `BOOLEAN` | `BOOLEAN DEFAULT FALSE` | Sí/No |

---

## 🎨 Modificadores Comunes

```sql
NOT NULL              -- Campo obligatorio
UNIQUE                -- Valor único en tabla
PRIMARY KEY           -- Identificador único
AUTO_INCREMENT        -- Incrementa automático
DEFAULT valor         -- Valor por defecto
UNIQUE INDEX name     -- Índice único
```

**Ejemplo completo:**
```php
'usuarios' => [
    'columns' => [
        'id'       => 'INT AUTO_INCREMENT PRIMARY KEY',
        'email'    => 'VARCHAR(255) NOT NULL UNIQUE',
        'nombre'   => 'VARCHAR(255) NOT NULL',
        'activo'   => 'BOOLEAN DEFAULT TRUE',
        'created'  => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
    ]
]
```

---

## 🔌 API Endpoints

### Auditar Esquema
```bash
GET api.php?action=audit_schema

Response:
{
  "success": true,
  "data": {
    "overall_status": "OK|NEEDS_SYNC",
    "tables": { ... }
  }
}
```

### Sincronizar BD
```bash
POST api.php?action=sync_database&auth=fotopet2026

Response:
{
  "success": true,
  "tables_created": 2,
  "columns_added": 5
}
```

### Estado de Conexión
```bash
GET api.php?action=get_db_status

Response:
{
  "success": true,
  "data": {
    "database": "db_name",
    "mysql_version": "8.0.28",
    "table_count": 12,
    "size_mb": 2.50
  }
}
```

---

## 💻 CLI (Terminal)

### Auditar desde terminal
```bash
php -r "require 'schema-manager.php'; \
\$m = new SchemaManager(); \
echo json_encode(\$m->auditSchema(), JSON_PRETTY_PRINT);"
```

### Sincronizar desde terminal
```bash
php -r "require 'schema-manager.php'; \
\$m = new SchemaManager(); \
echo json_encode(\$m->syncDatabase(), JSON_PRETTY_PRINT);"
```

### Ver logs
```bash
php -r "require 'config.php'; \
\$pdo = getDBConnection(); \
\$logs = \$pdo->query('SELECT * FROM logs ORDER BY created_at DESC LIMIT 5')->fetchAll(PDO::FETCH_ASSOC); \
echo json_encode(\$logs, JSON_PRETTY_PRINT);"
```

---

## 🐛 Troubleshooting Rápido

| Problema | Solución |
|----------|----------|
| "Botón Sincronizar deshabilitado" | BD ya sincronizada (OK) ✓ |
| "Error: Acceso Denegado" | Clave auth incorrecta en admin-system.js |
| "Tabla no se crea" | Revisa sintaxis en schema-definition.php |
| "Columna no aparece" | Recarga página, audita nuevamente |
| "Error de conexión" | Verifica credentials en config.php |

---

## 📂 Archivos Clave

| Archivo | Acción |
|---------|--------|
| `schema-definition.php` | ✏️ EDITAR - Tu esquema |
| `schema-manager.php` | 👀 LEER - Lógica core |
| `admin-system.js` | 👀 LEER - Frontend |
| `api.php` | 👀 LEER - Endpoints |
| `admin.html` | 👀 LEER - UI |
| `config.php` | 👀 LEER - Setup |
| `QUICK_START.md` | 📖 LEER PRIMERO |

---

## ✅ Checklist Antes de Sincronizar

- [ ] Editaste `schema-definition.php`?
- [ ] Guardaste los cambios?
- [ ] Hiciste backup de la BD? (recomendado en producción)
- [ ] La tabla `logs` existe?
- [ ] Verificaste sintaxis SQL en schema?

---

## 🔐 Seguridad

```php
// Autenticación requerida:
AUTH_KEY = 'fotopet2026'  // En admin-system.js
$master_key = 'fotopet2026'  // En api.php

// Deben coincidir para aplicar cambios
```

---

## 📊 Estados Posibles de Tabla

| Estado | Significado |
|--------|-------------|
| ✓ OK | Tabla sincronizada |
| ⚠️ MISSING_COLUMNS | Faltan algunas columnas |
| ✗ MISSING_TABLE | Tabla no existe |

---

## 🎯 Workflows Típicos

### Workflow 1: Setup Inicial
```
1. Clona schema-definition.php
2. Agrega ub 5 tablas básicas
3. Audita (debe mostrar 5 missing)
4. Sincroniza (crea todas)
5. BD lista ✓
```

### Workflow 2: Desarrollo
```
1. Necesito nueva functionality
2. Añado tabla/columna a schema-definition.php
3. Audita en admin
4. Sincroniza si hay discrepancias
5. Codifico feature
6. Deploy ✓
```

### Workflow 3: Mantenimiento
```
1. Reviso tabla `logs` regularmente
2. Veo qué cambios se hicieron
3. Verifico que BD está sincronizada
4. Audita cada semana (opcional)
5. Todo OK ✓
```

---

## 🎓 Ejemplos Mini

### Tabla: Usuarios
```php
'usuarios' => [
    'columns' => [
        'id'         => 'INT AUTO_INCREMENT PRIMARY KEY',
        'email'      => 'VARCHAR(255) NOT NULL UNIQUE',
        'password'   => 'VARCHAR(255)',
        'nombre'     => 'VARCHAR(100)',
        'activo'     => 'BOOLEAN DEFAULT TRUE',
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
    ]
]
```

### Tabla: Posts
```php
'posts' => [
    'columns' => [
        'id'         => 'INT AUTO_INCREMENT PRIMARY KEY',
        'titulo'     => 'VARCHAR(255) NOT NULL',
        'contenido'  => 'LONGTEXT',
        'autor_id'   => 'INT NOT NULL',
        'published'  => 'BOOLEAN DEFAULT FALSE',
        'views'      => 'INT DEFAULT 0',
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
    ]
]
```

### Tabla: Settings
```php
'settings' => [
    'columns' => [
        'id'    => 'INT AUTO_INCREMENT PRIMARY KEY',
        'clave' => 'VARCHAR(255) NOT NULL UNIQUE',
        'valor' => 'LONGTEXT',
        'tipo'  => 'VARCHAR(50) DEFAULT "text"'
    ]
]
```

---

## 🚨 No Olvides

- ✅ Guardar schema-definition.php
- ✅ Hacer backup before sincronizar en prod
- ✅ Auditar después de cambios
- ✅ Revisar tabla `logs` regularmente
- ✅ Documentar cambios importantes

---

## 🆘 Emergency Contact

Si algo falla:

1. **Verifica consola:** F12 → Console (JS errors)
2. **Revisa logs:** `SELECT * FROM logs ORDER BY created_at DESC`
3. **Lees docs:** SCHEMA_SYSTEM_DOCS.md → Troubleshooting
4. **Rollback:** Restaura backup de BD

---

## 📱 Atajos Teclado

| Acción | Shortcut |
|--------|----------|
| Abrir DevTools | F12 |
| Consola JS | F12 → Console |
| Elementos HTML | F12 → Inspector |
| Auditar en admin | [Botón 🔍] |
| Sincronizar en admin | [Botón ⚙️] |

---

## 💾 Guardar Cambios

```php
// SIEMPRE guarda después de editar:
File → Save // Ctrl+S o Cmd+S
```

---

## ✨ Tips Pro

1. **Usa schema-definition.php** como fuente de verdad
2. **Ordena columnas** de forma lógica (id, datos, timestamps)
3. **Documenta** tablas complejas con comentarios
4. **Revisa regex** antes de cambios en producción
5. **Agrupa tables** por funcionalidad (usuarios, posts, etc)

---

**Referencia rápida lista. Bookmarkea esta página.** 🔖

Para más info: `SCHEMA_SYSTEM_DOCS.md`
