<?php
require_once __DIR__ . '/config.php';
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$base = APP_URL;
// Verificar sesión de usuario
if (session_status() === PHP_SESSION_NONE) session_start();
$loggedUser = null;
if (!empty($_SESSION['kw_user_id'])) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT id, name, email FROM users WHERE id=?");
        $stmt->execute([$_SESSION['kw_user_id']]);
        $loggedUser = $stmt->fetch();
    } catch(Exception $e) {}
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?php echo isset($pageTitle) ? $pageTitle . ' — Katy & Woof' : 'Katy & Woof | Retratos Artísticos de Mascotas'; ?></title>
  <meta name="description" content="<?php echo isset($pageDesc) ? $pageDesc : 'Retratos artísticos coloridos de mascotas. Arte con propósito desde La Serena, Chile.'; ?>"/>
  <link rel="icon" type="image/svg+xml" href="<?php echo getSetting('site_favicon', $base.'/img/favicon.svg'); ?>"/>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <link rel="stylesheet" href="<?php echo $base; ?>/css/app.css"/>
  <?php if(isset($extraCss)) echo $extraCss; ?>
</head>
<body>

<!-- PAGE LOADER -->
<div class="page-loader" id="pageLoader"><div class="loader-ring"></div></div>

<!-- CART OVERLAY -->
<div class="cart-overlay" id="cartOverlay" onclick="CartManager.close()"></div>

<!-- CART DRAWER -->
<div class="cart-drawer" id="cartDrawer">
  <div class="cart-drawer-header">
    <h3>Tu Carrito</h3>
    <button class="cart-close" onclick="CartManager.close()">✕</button>
  </div>
  <div class="cart-items-list" id="cartItemsList">
    <div class="cart-empty">
      <div class="cart-empty-icon">🛒</div>
      <p>Tu carrito está vacío</p>
    </div>
  </div>
  <div class="cart-drawer-footer" id="cartFooter" style="display:none">
    <div class="cart-total-row"><span>Subtotal</span><span id="cartSubtotal">$0</span></div>
    <div class="cart-total-row"><span>Envío</span><span id="cartShipping">Por calcular</span></div>
    <div class="cart-total-row total"><span>Total</span><span id="cartTotal">$0</span></div>
    <a href="<?php echo $base; ?>/checkout.php" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:1.2rem">
      Ir al Checkout <i class="fa-solid fa-arrow-right"></i>
    </a>
  </div>
</div>

<!-- TOAST CONTAINER -->
<div class="toast-container" id="toastContainer"></div>

<!-- Pasar APP_URL a JS -->
<script>const BASE = '<?php echo $base; ?>';</script>

<!-- HEADER -->
<header class="site-header" id="siteHeader">
  <div class="container">
    <div class="header-inner">
      <a href="<?php echo $base; ?>/" class="logo">
        <img src="<?php echo getSetting('site_logo', $base.'/Logo_KW.png'); ?>" alt="Katy &amp; Woof" style="height:38px;width:auto;object-fit:contain">
      </a>
      <nav class="nav" id="mainNav">
        <a href="<?php echo $base; ?>/"            class="<?php echo $currentPage==='index'   ?'active':''; ?>">Inicio</a>
        <a href="<?php echo $base; ?>/catalogo.php" class="<?php echo $currentPage==='catalogo'?'active':''; ?>">Catálogo</a>
        <a href="<?php echo $base; ?>/blog.php"     class="<?php echo $currentPage==='blog'    ?'active':''; ?>">Blog</a>
        <a href="<?php echo $base; ?>/nosotros.php" class="<?php echo $currentPage==='nosotros'?'active':''; ?>">Nosotros</a>
        <a href="<?php echo $base; ?>/contacto.php" class="<?php echo $currentPage==='contacto'?'active':''; ?>">Contacto</a>
      </nav>
      <div class="header-actions">
        <button class="theme-toggle" id="themeToggle" title="Cambiar tema" aria-label="Cambiar tema" onclick="ThemeManager.toggle()">
          <i class="fa-solid fa-moon" id="themeIcon"></i>
        </button>
        <?php if ($loggedUser): ?>
        <div class="user-menu-wrap" id="userMenuWrap">
          <button class="user-menu-btn" onclick="toggleUserMenu()">
            <div class="user-avatar"><?php echo strtoupper(substr($loggedUser['name'],0,1)); ?></div>
            <span class="user-name-short"><?php echo htmlspecialchars(explode(' ',$loggedUser['name'])[0]); ?></span>
            <i class="fa-solid fa-chevron-down" style="font-size:0.65rem"></i>
          </button>
          <div class="user-dropdown" id="userDropdown">
            <div style="padding:0.8rem 1rem;border-bottom:1px solid rgba(255,255,255,0.06)">
              <div style="font-size:0.82rem;font-weight:600;color:var(--white)"><?php echo htmlspecialchars($loggedUser['name']); ?></div>
              <div style="font-size:0.72rem;color:var(--mid)"><?php echo htmlspecialchars($loggedUser['email']); ?></div>
            </div>
            <a href="<?php echo $base; ?>/mi-perfil.php" class="user-dropdown-item"><i class="fa-solid fa-receipt"></i> Mis compras</a>
            <a href="<?php echo $base; ?>/mi-perfil.php" class="user-dropdown-item"><i class="fa-solid fa-user"></i> Mi perfil</a>
            <a href="<?php echo $base; ?>/contacto.php" class="user-dropdown-item"><i class="fa-solid fa-envelope"></i> Contactar con Katy & Woof</a>
            <div style="border-top:1px solid rgba(255,255,255,0.06);margin-top:0.3rem;padding-top:0.3rem">
              <button class="user-dropdown-item" style="width:100%;text-align:left;color:#ef4444" onclick="logoutUser()"><i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión</button>
            </div>
          </div>
        </div>
        <?php else: ?>
        <a href="<?php echo $base; ?>/cuenta.php" class="btn btn-ghost btn-sm">
          <i class="fa-solid fa-user"></i> Entrar
        </a>
        <?php endif; ?>
        <button class="cart-btn" onclick="CartManager.open()">
          <i class="fa-solid fa-bag-shopping"></i>
          <span>Carrito</span>
          <span class="cart-count" id="cartCount">0</span>
        </button>
        <div class="hamburger" id="hamburger" onclick="toggleNav()">
          <span></span><span></span><span></span>
        </div>
      </div>
    </div>
  </div>
</header>
