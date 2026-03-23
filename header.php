<?php
require_once __DIR__ . '/config.php';
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$base = APP_URL;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?php echo isset($pageTitle) ? $pageTitle . ' — Katy & Woof' : 'Katy & Woof | Retratos Artísticos de Mascotas'; ?></title>
  <meta name="description" content="<?php echo isset($pageDesc) ? $pageDesc : 'Retratos artísticos coloridos de mascotas. Arte con propósito desde La Serena, Chile.'; ?>"/>
  <link rel="icon" type="image/svg+xml" href="<?php echo $base; ?>/img/favicon.svg"/>
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
      <a href="<?php echo $base; ?>/" class="logo">KATY<span>&</span>WOOF</a>
      <nav class="nav" id="mainNav">
        <a href="<?php echo $base; ?>/"            class="<?php echo $currentPage==='index'   ?'active':''; ?>">Inicio</a>
        <a href="<?php echo $base; ?>/catalogo.php" class="<?php echo $currentPage==='catalogo'?'active':''; ?>">Catálogo</a>
        <a href="<?php echo $base; ?>/nosotros.php" class="<?php echo $currentPage==='nosotros'?'active':''; ?>">Nosotros</a>
        <a href="<?php echo $base; ?>/contacto.php" class="<?php echo $currentPage==='contacto'?'active':''; ?>">Contacto</a>
      </nav>
      <div class="header-actions">
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
