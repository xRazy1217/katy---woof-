<?php
require_once __DIR__ . '/../config.php';

// Simple session-based auth
session_start();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    if ($_POST['password'] === ADMIN_KEY) {
        $_SESSION['kw_admin'] = true;
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
    $error = 'Contraseña incorrecta.';
}

if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

$logged = !empty($_SESSION['kw_admin']);
$env    = APP_ENV;
$apiBase = APP_URL . '/api/router.php';
$adminKey = ADMIN_KEY;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin — Katy & Woof</title>
<link rel="stylesheet" href="css/admin.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script>
  const API   = <?php echo json_encode($apiBase, JSON_UNESCAPED_SLASHES); ?>;
  const AKEY  = <?php echo json_encode($adminKey); ?>;
  const ENV   = <?php echo json_encode($env); ?>;
</script>
</head>
<body>

<?php if (!$logged): ?>
<!-- ══════════════ LOGIN ══════════════ -->
<div class="login-screen">
  <div class="login-box">
    <div class="login-logo">KATY<span>&</span>WOOF</div>
    <h2>Panel de Administración</h2>
    <p>Ingresa tu contraseña para continuar.</p>
    <?php if ($error): ?>
    <div class="login-error show"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="POST">
      <div class="form-group">
        <label>Contraseña</label>
        <input type="password" name="password" class="input" placeholder="••••••••" autofocus required>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:0.5rem">
        <i class="fa fa-arrow-right-to-bracket"></i> Entrar
      </button>
    </form>
  </div>
</div>

<?php else: ?>
<!-- ══════════════ ADMIN LAYOUT ══════════════ -->
<div class="admin-layout">

  <!-- SIDEBAR -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
      KATY<span>&</span>WOOF
      <small>Admin Panel</small>
    </div>
    <nav class="sidebar-nav">
      <div class="nav-section-label">Principal</div>
      <div class="nav-item active" data-panel="dashboard">
        <i class="fa fa-chart-pie"></i> Dashboard
      </div>

      <div class="nav-section-label">Tienda</div>
      <div class="nav-item" data-panel="products">
        <i class="fa fa-box"></i> Productos
      </div>
      <div class="nav-item" data-panel="categories">
        <i class="fa fa-tags"></i> Categorías
      </div>
      <div class="nav-item" data-panel="orders">
        <i class="fa fa-receipt"></i> Órdenes
      </div>
      <div class="nav-item" data-panel="coupons">
        <i class="fa fa-ticket"></i> Cupones
      </div>

      <div class="nav-section-label">Contenido</div>
      <div class="nav-item" data-panel="blog">
        <i class="fa fa-pen-nib"></i> Blog
      </div>

      <div class="nav-section-label">Clientes</div>
      <div class="nav-item" data-panel="customers">
        <i class="fa fa-users"></i> Clientes
      </div>
      <div class="nav-item" data-panel="messages">
        <i class="fa fa-envelope"></i> Mensajes
        <span class="msg-badge" id="msgBadge" style="display:none;margin-left:auto;background:var(--accent);color:#fff;font-size:0.6rem;padding:0.1rem 0.45rem;border-radius:100px"></span>
      </div>

      <div class="nav-section-label">Configuración</div>
      <div class="nav-item" data-panel="settings">
        <i class="fa fa-sliders"></i> Ajustes del Sitio
      </div>
      <div class="nav-item" data-panel="system">
        <i class="fa fa-database"></i> Sistema / BD
      </div>
    </nav>
    <div class="sidebar-footer">
      <form method="POST" style="margin:0">
        <button type="submit" name="logout" class="nav-item" style="width:100%;background:none;border:none;cursor:pointer">
          <i class="fa fa-right-from-bracket"></i> Cerrar sesión
        </button>
      </form>
    </div>
  </aside>

  <!-- MAIN -->
  <main class="admin-main">
    <div class="admin-topbar">
      <div style="display:flex;align-items:center;gap:1rem">
        <button class="btn btn-ghost btn-icon" id="sidebarToggle" style="display:none">
          <i class="fa fa-bars"></i>
        </button>
        <span class="topbar-title" id="topbarTitle">Dashboard</span>
      </div>
      <div class="topbar-right">
        <button class="btn btn-ghost btn-icon btn-sm" onclick="AdminTheme.toggle()" title="Cambiar tema">
          <i class="fa-solid fa-moon" id="adminThemeIcon"></i>
        </button>
        <span class="topbar-env <?php echo $env === 'production' ? 'prod' : ''; ?>">
          <?php echo strtoupper($env); ?>
        </span>
        <a href="<?php echo APP_URL; ?>" target="_blank" class="btn btn-ghost btn-sm">
          <i class="fa fa-arrow-up-right-from-square"></i> Ver sitio
        </a>
      </div>
    </div>

    <div class="admin-content">

      <!-- ── DASHBOARD ── -->
      <div class="panel active" id="panel-dashboard">
        <div class="stats-row" id="dashStats">
          <div class="stat-card"><div class="stat-card-label">Productos</div><div class="stat-card-value" id="ds-products">—</div></div>
          <div class="stat-card"><div class="stat-card-label">Categorías</div><div class="stat-card-value" id="ds-categories">—</div></div>
          <div class="stat-card accent"><div class="stat-card-label">Órdenes</div><div class="stat-card-value" id="ds-orders">—</div></div>
          <div class="stat-card"><div class="stat-card-label">Cupones</div><div class="stat-card-value" id="ds-coupons">—</div></div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
          <div class="table-wrap" style="padding:1.5rem">
            <h3 style="font-size:0.9rem;margin-bottom:1rem;color:var(--light)"><i class="fa fa-receipt" style="color:var(--accent);margin-right:0.4rem"></i>Últimas órdenes</h3>
            <table class="data-table" id="dashOrdersTable">
              <thead><tr><th>#</th><th>Cliente</th><th>Total</th><th>Estado</th></tr></thead>
              <tbody><tr><td colspan="4" class="table-empty">Cargando...</td></tr></tbody>
            </table>
          </div>
          <div class="table-wrap" style="padding:1.5rem">
            <h3 style="font-size:0.9rem;margin-bottom:1rem;color:var(--light)"><i class="fa fa-box" style="color:var(--accent);margin-right:0.4rem"></i>Productos recientes</h3>
            <table class="data-table" id="dashProductsTable">
              <thead><tr><th>Nombre</th><th>Precio</th><th>Stock</th></tr></thead>
              <tbody><tr><td colspan="3" class="table-empty">Cargando...</td></tr></tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ── PRODUCTOS ── -->
      <div class="panel" id="panel-products">
        <div class="toolbar">
          <div class="toolbar-left">
            <h2>Productos</h2>
            <div class="search-input-wrap">
              <i class="fa fa-search"></i>
              <input type="text" class="input input-sm" id="productSearch" placeholder="Buscar..." style="width:200px">
            </div>
          </div>
          <button class="btn btn-primary btn-sm" id="btnNewProduct">
            <i class="fa fa-plus"></i> Nuevo producto
          </button>
        </div>
        <div class="table-wrap">
          <table class="data-table" id="productsTable">
            <thead>
              <tr>
                <th>Imagen</th><th>Nombre</th><th>Categoría</th>
                <th>Precio</th><th>Stock</th><th>Estado</th><th>Acciones</th>
              </tr>
            </thead>
            <tbody><tr><td colspan="7" class="table-empty">Cargando...</td></tr></tbody>
          </table>
        </div>
      </div>

      <!-- ── CATEGORÍAS ── -->
      <div class="panel" id="panel-categories">
        <div class="toolbar">
          <h2>Categorías</h2>
          <button class="btn btn-primary btn-sm" id="btnNewCategory">
            <i class="fa fa-plus"></i> Nueva categoría
          </button>
        </div>
        <div class="table-wrap">
          <table class="data-table" id="categoriesTable">
            <thead>
              <tr><th>Nombre</th><th>Slug</th><th>Descripción</th><th>Productos</th><th>Acciones</th></tr>
            </thead>
            <tbody><tr><td colspan="5" class="table-empty">Cargando...</td></tr></tbody>
          </table>
        </div>
      </div>

      <!-- ── ÓRDENES ── -->
      <div class="panel" id="panel-orders">
        <div class="toolbar">
          <div class="toolbar-left">
            <h2>Órdenes</h2>
            <select class="select input-sm" id="orderStatusFilter" style="width:150px">
              <option value="">Todos los estados</option>
              <option value="pending">Pendiente</option>
              <option value="processing">Procesando</option>
              <option value="shipped">Enviado</option>
              <option value="completed">Completado</option>
              <option value="cancelled">Cancelado</option>
            </select>
          </div>
        </div>
        <div class="table-wrap">
          <table class="data-table" id="ordersTable">
            <thead>
              <tr><th>#Orden</th><th>Cliente</th><th>Email</th><th>Total</th><th>Estado</th><th>Fecha</th><th>Acciones</th></tr>
            </thead>
            <tbody><tr><td colspan="7" class="table-empty">Cargando...</td></tr></tbody>
          </table>
        </div>
      </div>

      <!-- ── CUPONES ── -->
      <div class="panel" id="panel-coupons">
        <div class="toolbar">
          <h2>Cupones</h2>
          <button class="btn btn-primary btn-sm" id="btnNewCoupon">
            <i class="fa fa-plus"></i> Nuevo cupón
          </button>
        </div>
        <div class="table-wrap">
          <table class="data-table" id="couponsTable">
            <thead>
              <tr><th>Código</th><th>Tipo</th><th>Descuento</th><th>Usos</th><th>Vencimiento</th><th>Estado</th><th>Acciones</th></tr>
            </thead>
            <tbody><tr><td colspan="7" class="table-empty">Cargando...</td></tr></tbody>
          </table>
        </div>
      </div>

      <!-- ── AJUSTES ── -->
      <div class="panel" id="panel-settings">
        <div class="toolbar"><h2>Ajustes del Sitio</h2></div>
        <form id="settingsForm">
          <div class="settings-grid">
            <div class="settings-card">
              <h3><i class="fa fa-globe"></i> General</h3>
              <div class="form-group"><label>Email de contacto</label><input type="email" name="contact_email" class="input" placeholder="email@ejemplo.com"></div>
              <div class="form-group"><label>WhatsApp</label><input type="text" name="contact_whatsapp" class="input" placeholder="+56 9 XXXX XXXX"></div>
              <div class="form-group"><label>Dirección</label><input type="text" name="contact_address" class="input" placeholder="Ciudad, País"></div>
              <div class="form-group"><label>Instagram URL</label><input type="text" name="social_instagram" class="input" placeholder="https://instagram.com/..."></div>
            </div>
            <div class="settings-card">
              <h3><i class="fa fa-image"></i> Hero</h3>
              <div class="form-group"><label>Título del Hero</label><input type="text" name="hero_title" class="input"></div>
              <div class="form-group"><label>Descripción del Hero</label><textarea name="hero_description" class="textarea"></textarea></div>
            </div>
            <div class="settings-card">
              <h3><i class="fa fa-pen-nib"></i> Nosotros</h3>
              <div class="form-group"><label>Título Nosotros</label><input type="text" name="nosotros_title" class="input"></div>
              <div class="form-group"><label>Historia</label><textarea name="our_history" class="textarea" style="min-height:120px"></textarea></div>
            </div>
            <div class="settings-card">
              <h3><i class="fa fa-quote-left"></i> Footer</h3>
              <div class="form-group"><label>Filosofía del footer</label><textarea name="footer_philosophy" class="textarea"></textarea></div>
            </div>
          </div>
          <div style="margin-top:1.5rem;display:flex;justify-content:flex-end">
            <button type="submit" class="btn btn-primary"><i class="fa fa-floppy-disk"></i> Guardar ajustes</button>
          </div>
        </form>
      </div>

      <!-- ── SISTEMA ── -->
      <div class="panel" id="panel-system">
        <div class="toolbar"><h2>Sistema / Base de Datos</h2></div>
        <div class="db-status-grid" id="dbStatusGrid">
          <div class="db-stat"><div class="db-stat-val" id="sys-tables">—</div><div class="db-stat-label">Tablas</div></div>
          <div class="db-stat"><div class="db-stat-val" id="sys-size">—</div><div class="db-stat-label">Tamaño BD</div></div>
          <div class="db-stat"><div class="db-stat-val" id="sys-php">—</div><div class="db-stat-label">PHP Version</div></div>
        </div>
        <div style="display:flex;gap:0.8rem;flex-wrap:wrap;margin-bottom:1.5rem">
          <button class="btn btn-outline btn-sm" id="btnTestConn"><i class="fa fa-plug"></i> Probar conexión</button>
          <button class="btn btn-outline btn-sm" id="btnSyncDB"><i class="fa fa-rotate"></i> Sincronizar BD</button>
          <button class="btn btn-outline btn-sm" id="btnInitEcommerce"><i class="fa fa-store"></i> Inicializar E-commerce</button>
          <button class="btn btn-danger btn-sm" id="btnRepairDB"><i class="fa fa-wrench"></i> Reparar BD</button>
        </div>
        <div id="sysLog" class="table-wrap" style="padding:1.2rem;font-family:'Space Mono',monospace;font-size:0.75rem;color:var(--mid);min-height:120px;white-space:pre-wrap">
          Listo. Usa los botones de arriba para ejecutar operaciones.
        </div>
      </div>

      <!-- ── BLOG ── -->
      <div class="panel" id="panel-blog">
        <div class="toolbar">
          <div class="toolbar-left">
            <h2>Blog</h2>
          </div>
          <button class="btn btn-primary btn-sm" id="btnNewPost">
            <i class="fa fa-plus"></i> Nuevo post
          </button>
        </div>
        <div class="table-wrap">
          <table class="data-table" id="blogTable">
            <thead>
              <tr><th>Imagen</th><th>Título</th><th>Categoría</th><th>Fecha</th><th>Acciones</th></tr>
            </thead>
            <tbody><tr><td colspan="5" class="table-empty">Cargando...</td></tr></tbody>
          </table>
        </div>
      </div>

      <!-- ── CLIENTES ── -->
      <div class="panel" id="panel-customers">
        <div class="toolbar"><h2>Clientes</h2></div>
        <div class="table-wrap">
          <table class="data-table" id="customersTable">
            <thead>
              <tr><th>Nombre</th><th>Email</th><th>Teléfono</th><th>Órdenes</th><th>Total gastado</th><th>Última orden</th></tr>
            </thead>
            <tbody><tr><td colspan="6" class="table-empty">Cargando...</td></tr></tbody>
          </table>
        </div>
      </div>

      <!-- ── MENSAJES ── -->
      <div class="panel" id="panel-messages">
        <div class="toolbar"><h2>Mensajes de contacto</h2></div>
        <div class="table-wrap">
          <table class="data-table" id="messagesTable">
            <thead>
              <tr><th>Nombre</th><th>Email</th><th>Asunto</th><th>Fecha</th><th>Estado</th><th>Acciones</th></tr>
            </thead>
            <tbody><tr><td colspan="6" class="table-empty">Cargando...</td></tr></tbody>
          </table>
        </div>
      </div>

    </div><!-- /admin-content -->
  </main>
</div><!-- /admin-layout -->

<!-- ══════════════ MODALS ══════════════ -->

<!-- Product Modal -->
<div class="modal-overlay" id="productModal">
  <div class="modal modal-lg">
    <div class="modal-header">
      <h3 id="productModalTitle">Nuevo Producto</h3>
      <button class="modal-close" data-close="productModal"><i class="fa fa-xmark"></i></button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="productId">
      <div class="form-row">
        <div class="form-group"><label>Nombre *</label><input type="text" id="productName" class="input" required></div>
        <div class="form-group"><label>SKU</label><input type="text" id="productSku" class="input" placeholder="Ej: KW-001"></div>
      </div>
      <div class="form-group"><label>Descripción</label><textarea id="productDesc" class="textarea"></textarea></div>
      <div class="form-row-3">
        <div class="form-group"><label>Precio (CLP) *</label><input type="number" id="productPrice" class="input" min="0"></div>
        <div class="form-group"><label>Precio oferta</label><input type="number" id="productSalePrice" class="input" min="0" placeholder="0 = sin oferta"></div>
        <div class="form-group"><label>Stock</label><input type="number" id="productStock" class="input" min="0" value="0"></div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Categoría</label>
          <select id="productCategory" class="select"><option value="">Sin categoría</option></select>
        </div>
        <div class="form-group">
          <label>Estado</label>
          <select id="productStatus" class="select">
            <option value="publish">Publicado</option>
            <option value="draft">Borrador</option>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label>Imagen del producto</label>
        <div class="img-upload-area" id="productImgArea">
          <input type="file" id="productImg" accept="image/*">
          <img id="productImgPreview" class="img-preview" src="" style="display:none">
          <i class="fa fa-cloud-arrow-up" id="productImgIcon"></i>
          <p>Haz clic o arrastra una imagen</p>
        </div>
        <input type="hidden" id="productImgUrl">
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" data-close="productModal">Cancelar</button>
      <button class="btn btn-primary" id="btnSaveProduct"><i class="fa fa-floppy-disk"></i> Guardar</button>
    </div>
  </div>
</div>

<!-- Category Modal -->
<div class="modal-overlay" id="categoryModal">
  <div class="modal">
    <div class="modal-header">
      <h3 id="categoryModalTitle">Nueva Categoría</h3>
      <button class="modal-close" data-close="categoryModal"><i class="fa fa-xmark"></i></button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="categoryId">
      <div class="form-group"><label>Nombre *</label><input type="text" id="categoryName" class="input" required></div>
      <div class="form-group"><label>Slug</label><input type="text" id="categorySlug" class="input" placeholder="se-genera-automático"></div>
      <div class="form-group"><label>Descripción</label><textarea id="categoryDesc" class="textarea" style="min-height:70px"></textarea></div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" data-close="categoryModal">Cancelar</button>
      <button class="btn btn-primary" id="btnSaveCategory"><i class="fa fa-floppy-disk"></i> Guardar</button>
    </div>
  </div>
</div>

<!-- Coupon Modal -->
<div class="modal-overlay" id="couponModal">
  <div class="modal">
    <div class="modal-header">
      <h3 id="couponModalTitle">Nuevo Cupón</h3>
      <button class="modal-close" data-close="couponModal"><i class="fa fa-xmark"></i></button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="couponId">
      <div class="form-row">
        <div class="form-group"><label>Código *</label><input type="text" id="couponCode" class="input" placeholder="DESCUENTO20" style="text-transform:uppercase"></div>
        <div class="form-group">
          <label>Tipo</label>
          <select id="couponType" class="select">
            <option value="percentage">Porcentaje (%)</option>
            <option value="fixed">Monto fijo (CLP)</option>
          </select>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group"><label>Valor *</label><input type="number" id="couponValue" class="input" min="0"></div>
        <div class="form-group"><label>Mínimo de compra</label><input type="number" id="couponMinSpend" class="input" min="0" placeholder="0 = sin mínimo"></div>
      </div>
      <div class="form-row">
        <div class="form-group"><label>Límite de usos</label><input type="number" id="couponLimit" class="input" min="0" placeholder="0 = ilimitado"></div>
        <div class="form-group"><label>Fecha de vencimiento</label><input type="date" id="couponExpiry" class="input"></div>
      </div>
      <div class="form-group"><label>Descripción</label><input type="text" id="couponDesc" class="input"></div>
      <div class="toggle-wrap">
        <div class="toggle on" id="couponActiveToggle"></div>
        <span class="toggle-label">Cupón activo</span>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" data-close="couponModal">Cancelar</button>
      <button class="btn btn-primary" id="btnSaveCoupon"><i class="fa fa-floppy-disk"></i> Guardar</button>
    </div>
  </div>
</div>

<!-- Order Detail Modal -->
<div class="modal-overlay" id="orderModal">
  <div class="modal modal-lg">
    <div class="modal-header">
      <h3 id="orderModalTitle">Detalle de Orden</h3>
      <button class="modal-close" data-close="orderModal"><i class="fa fa-xmark"></i></button>
    </div>
    <div class="modal-body" id="orderModalBody">
      Cargando...
    </div>
    <div class="modal-footer">
      <label style="margin:0;font-size:0.8rem;color:var(--mid)">Cambiar estado:</label>
      <select class="select input-sm" id="orderStatusSelect" style="width:160px">
        <option value="pending">Pendiente</option>
        <option value="processing">Procesando</option>
        <option value="shipped">Enviado</option>
        <option value="completed">Completado</option>
        <option value="cancelled">Cancelado</option>
      </select>
      <button class="btn btn-primary btn-sm" id="btnUpdateOrderStatus"><i class="fa fa-check"></i> Actualizar</button>
      <button class="btn btn-ghost" data-close="orderModal">Cerrar</button>
    </div>
  </div>
</div>

<!-- Confirm Delete Modal -->
<div class="modal-overlay" id="confirmModal">
  <div class="modal" style="max-width:400px">
    <div class="modal-header">
      <h3>Confirmar eliminación</h3>
      <button class="modal-close" data-close="confirmModal"><i class="fa fa-xmark"></i></button>
    </div>
    <div class="modal-body">
      <p id="confirmMsg">¿Estás segura de que deseas eliminar este elemento? Esta acción no se puede deshacer.</p>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" data-close="confirmModal">Cancelar</button>
      <button class="btn btn-danger" id="btnConfirmDelete"><i class="fa fa-trash"></i> Eliminar</button>
    </div>
  </div>
</div>

<!-- Blog Modal -->
<div class="modal-overlay" id="blogModal">
  <div class="modal modal-lg">
    <div class="modal-header">
      <h3 id="blogModalTitle">Nuevo Post</h3>
      <button class="modal-close" data-close="blogModal"><i class="fa fa-xmark"></i></button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="blogId">
      <div class="form-group"><label>Título *</label><input type="text" id="blogTitle" class="input" required></div>
      <div class="form-row">
        <div class="form-group">
          <label>Categoría</label>
          <select id="blogCategory" class="select">
            <option>General</option>
            <option>Consejos</option>
            <option>Noticias</option>
            <option>Arte</option>
            <option>Mascotas</option>
          </select>
        </div>
      </div>
      <div class="form-group"><label>Contenido *</label><textarea id="blogContent" class="textarea" style="min-height:160px" required></textarea></div>
      <div class="form-group">
        <label>Imagen</label>
        <div class="img-upload-area" id="blogImgArea">
          <input type="file" id="blogImg" accept="image/*">
          <img id="blogImgPreview" class="img-preview" src="" style="display:none">
          <i class="fa fa-cloud-arrow-up" id="blogImgIcon"></i>
          <p>Haz clic o arrastra una imagen</p>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" data-close="blogModal">Cancelar</button>
      <button class="btn btn-primary" id="btnSavePost"><i class="fa fa-floppy-disk"></i> Guardar</button>
    </div>
  </div>
</div>

<!-- Message Detail Modal -->
<div class="modal-overlay" id="messageModal">
  <div class="modal">
    <div class="modal-header">
      <h3>Mensaje de contacto</h3>
      <button class="modal-close" data-close="messageModal"><i class="fa fa-xmark"></i></button>
    </div>
    <div class="modal-body" id="messageModalBody"></div>
    <div class="modal-footer">
      <button class="btn btn-ghost" data-close="messageModal">Cerrar</button>
    </div>
  </div>
</div>

<!-- Toast container -->
<div class="toast-container" id="toastContainer"></div>

<?php endif; ?>

<script src="js/admin.js"></script>
</body>
</html>
