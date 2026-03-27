<aside class="sidebar" id="sidebar">
  <div class="sidebar-logo">
    KATY<span>&</span>WOOF
    <small>Admin Panel</small>
  </div>
  <nav class="sidebar-nav">
    <div class="nav-section-label">Principal</div>
    <div class="nav-item active" data-panel="dashboard"><i class="fa fa-chart-pie"></i> Dashboard</div>

    <div class="nav-section-label">Tienda</div>
    <div class="nav-item" data-panel="products"><i class="fa fa-box"></i> Productos</div>
    <div class="nav-item" data-panel="categories"><i class="fa fa-tags"></i> Categorías</div>
    <div class="nav-item" data-panel="orders"><i class="fa fa-receipt"></i> Órdenes</div>
    <div class="nav-item" data-panel="coupons"><i class="fa fa-ticket"></i> Cupones</div>

    <div class="nav-section-label">Contenido</div>
    <div class="nav-item" data-panel="blog"><i class="fa fa-pen-nib"></i> Blog</div>

    <div class="nav-section-label">Clientes</div>
    <div class="nav-item" data-panel="customers"><i class="fa fa-users"></i> Clientes</div>
    <div class="nav-item" data-panel="messages">
      <i class="fa fa-envelope"></i> Mensajes
      <span class="msg-badge" id="msgBadge" style="display:none;margin-left:auto;background:var(--accent);color:#fff;font-size:0.6rem;padding:0.1rem 0.45rem;border-radius:100px"></span>
    </div>

    <div class="nav-section-label">Configuración</div>
    <div class="nav-item" data-panel="settings"><i class="fa fa-sliders"></i> Ajustes del Sitio</div>
    <div class="nav-item" data-panel="system"><i class="fa fa-database"></i> Sistema / BD</div>
  </nav>
  <div class="sidebar-footer">
    <form method="POST" style="margin:0">
      <button type="submit" name="logout" class="nav-item" style="width:100%;background:none;border:none;cursor:pointer">
        <i class="fa fa-right-from-bracket"></i> Cerrar sesión
      </button>
    </form>
  </div>
</aside>
