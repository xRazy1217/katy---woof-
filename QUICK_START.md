# 🚀 Guía Rápida - Sistema de Sincronización de Esquema

## ¿Qué es esto?

Un sistema que **automáticamente** crea tablas y columnas que falten en tu base de datos MySQL basándose en la definición que tú controlas en el código.

---

## 5 Minutos para Empezar

### Paso 1: Definir tu Esquema Ideal
Edita `schema-definition.php` y describe las tablas que necesitas:

```php
return [
    'usuarios' => [
        'columns' => [
            'id'           => 'INT AUTO_INCREMENT PRIMARY KEY',
            'email'        => 'VARCHAR(255) NOT NULL UNIQUE',
            'nombre'       => 'VARCHAR(255)',
            'contrasena'   => 'VARCHAR(255)',
            'activo'       => 'BOOLEAN DEFAULT TRUE',
            'created_at'   => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
        ]
    ],
    
    'posts' => [
        'columns' => [
            'id'          => 'INT AUTO_INCREMENT PRIMARY KEY',
            'titulo'      => 'VARCHAR(255) NOT NULL',
            'contenido'   => 'LONGTEXT',
            'autor_id'    => 'INT NOT NULL',
            'published'   => 'BOOLEAN DEFAULT FALSE',
            'created_at'  => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
        ]
    ]
];
```

### Paso 2: Abrir la Pestaña "Sistema"
- Accede a tu panel admin
- Busca la pestaña **"✓ Sistema"** arriba a la derecha
- Haz clic en ella

### Paso 3: Auditar
- Presiona el botón **🔍 Auditar Esquema**
- Espera a que cargue (2-3 segundos)
- Verás un reporte como este:

```
✓ tabla_usuarios      → Tabla completa y sincronizada
✓ tabla_posts         → Tabla completa y sincronizada
⚠️ tabla_comentarios  → Falta la columna 'email'
✗ tabla_logs          → Tabla no existe
```

### Paso 4: Sincronizar (si hay problemas)
- Si el reporte muestra problemas, verás un botón **⚙️ Sincronizar BD**
- Presiona para ejecutar los cambios
- **Confirma en el popup** que aparece
- ¡Listo! Las tablas se crean y las columnas se agregan automáticamente

---

## Casos de Uso Comunes

### 1️⃣ Agregar una Nueva Tabla
```php
// schema-definition.php
'comentarios' => [
    'columns' => [
        'id'       => 'INT AUTO_INCREMENT PRIMARY KEY',
        'texto'    => 'TEXT NOT NULL',
        'post_id'  => 'INT NOT NULL',
        'usuario'  => 'VARCHAR(100)'
    ]
]
```
→ Audita + sincroniza = tabla creada ✓

### 2️⃣ Agregar una Columna
```php
// schema-definition.php
'usuarios' => [
    'columns' => [
        // ... columnas existentes ...
        'verificado_email' => 'BOOLEAN DEFAULT FALSE'  // ← NUEVA
    ]
]
```
→ Audita + sincroniza = columna agregada ✓

### 3️⃣ Ver Historial de Cambios
En tu editor SQL (phpMyAdmin, MySQL Workbench):
```sql
SELECT * FROM logs WHERE event_type = 'database_sync' ORDER BY created_at DESC;
```

---

## ⚠️ Importante Saber

| ✅ SI HACE | ❌ NO HACE |
|-----------|-----------|
| Crea tablas nuevas | Elimina tablas |
| Agrega columnas | Modifica tipos de datos |
| Agrega índices (pronto) | Borra columnas |
| Registra cambios | Modifica datos existentes |

---

## 🔒 Seguridad

- Solo usuarios autenticados pueden sincronizar
- Cada cambio se registra en `logs`
- Los cambios se revierten si algo falla (transacciones)

---

## 🆘 ¿Algo Salió Mal?

### "El botón Sincronizar no aparece"
→ Significa que tu esquema ya está sincronizado. ¡Bien! ✓

### "Error: Acceso Denegado"
→ La clave de autenticación no coincide. Verifica `AUTH_KEY` en `admin-system.js`

### "La columna no se agregó"
→ Quizás era falso positivo. Recarga la página y audita de nuevo.

---

## 📋 Cheatsheet de Tipos de Datos

| Tipo | Ejemplo | Uso |
|------|---------|-----|
| `INT` | `INT AUTO_INCREMENT PRIMARY KEY` | IDs, números enteros |
| `VARCHAR(n)` | `VARCHAR(255)` | Texto corto (emails, nombres) |
| `TEXT` | `TEXT` | Texto mediano |
| `LONGTEXT` | `LONGTEXT` | Texto largo (artículos, descripciones) |
| `DECIMAL(10,2)` | `DECIMAL(10,2)` | Dinero, precios |
| `DATE` | `DATE` | Solo fecha (YYYY-MM-DD) |
| `DATETIME` | `DATETIME` | Fecha y hora |
| `TIMESTAMP` | `TIMESTAMP DEFAULT CURRENT_TIMESTAMP` | Marca automática |
| `BOOLEAN` | `BOOLEAN DEFAULT FALSE` | Verdadero/Falso |
| `JSON` | `JSON` | Datos estructurados (en BD) |

---

## 💡 Mejores Prácticas

1. **Edita `schema-definition.php`** → Audita → Sincroniza
2. **No hagas cambios directos en la BD** - usa el esquema como fuente de verdad
3. **Haz backup antes de sincronizar en producción**
4. **Revisa el historial de logs regularmente**

---

## 📞 Necesitas Más Ayuda?

Lee `SCHEMA_SYSTEM_DOCS.md` para documentación técnica completa.

---

**¡Tu BD siempre en sincronía! 🎉**
