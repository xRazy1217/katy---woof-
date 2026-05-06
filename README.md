# Katy & Woof - Plataforma E-commerce Dinámica

Bienvenido al repositorio oficial de **Katy & Woof** (`retratodemascotas.cl`), una plataforma de comercio electrónico especializada en retratos de mascotas personalizados.

## 🌟 Características Principales

- **Gestión Dinámica Total:** El contenido del sitio (textos, imágenes, misión, visión, etc.) se gestiona íntegramente desde el panel administrativo.
- **Panel de Administración Pro:** 
  *   Guardado individual de ajustes (campo por campo).
  *   Gestión de productos, categorías y cupones.
  *   Estadísticas en tiempo real de órdenes y ventas.
  *   Gestión de mensajes de contacto.
- **Arquitectura Modular:** API refactorizada con un router centralizado (`v6.5`) que delega la lógica a módulos especializados.
- **Optimización para Producción:** Configuración específica para entornos de SiteGround con gestión de caché y seguridad.

## 🛠️ Arquitectura Técnica

- **Backend:** PHP 7.4+ / MySQL.
- **API:** Arquitectura RESTful simplificada con router centralizado en `api/router.php`.
- **Frontend:** HTML5, CSS3 (Vanilla), JavaScript (ES6+).
- **Persistencia:** Base de datos relacional con tabla `ecommerce_settings` para configuración dinámica.

## 📂 Estructura del Proyecto

- `/admin`: Panel administrativo (HTML, JS, CSS).
- `/api`: Núcleo lógico del sitio (Handlers de BD, Auth, Routers).
- `/uploads`: Directorio de almacenamiento de imágenes (Productos, Logos, etc.).
- `/css` y `/js`: Estilos y lógica del lado del cliente (sitio público).
- `config.php`: Configuración global y funciones auxiliares.
- `.env`: Variables de entorno para fácil transición entre Local y Producción.

## 🚀 Despliegue Rápido

Consulte el archivo [README-CONFIG.md](README-CONFIG.md) para obtener instrucciones detalladas sobre la configuración de entornos locales y el despliegue en SiteGround.

## 🔧 Mantenimiento

Para cualquier cambio en los textos o imágenes del sitio, **no edite los archivos .php**. Utilice siempre el **Panel de Ajustes** en `/admin`.

---
© 2026 Katy & Woof. Todos los derechos reservados.
