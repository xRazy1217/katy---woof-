<?php
require_once __DIR__ . '/../config.php';

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

$logged   = !empty($_SESSION['kw_admin']);
$env      = APP_ENV;
$apiBase  = APP_URL . '/api/router.php';
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
  <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
  <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
  <script>
    const API  = <?php echo json_encode($apiBase, JSON_UNESCAPED_SLASHES); ?>;
    const AKEY = <?php echo json_encode($adminKey); ?>;
    const ENV  = <?php echo json_encode($env); ?>;
  </script>
</head>
<body>

<?php if (!$logged): ?>
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
<div class="admin-layout">

  <?php include __DIR__ . '/partials/sidebar.php'; ?>

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
      <?php include __DIR__ . '/partials/panels.php'; ?>
    </div>
  </main>

</div>

<?php include __DIR__ . '/partials/modals.php'; ?>
<?php endif; ?>

<script src="js/admin.core.js"></script>
<script src="js/admin.products.js"></script>
<script src="js/admin.orders.js"></script>
<script src="js/admin.blog.js"></script>
<script src="js/admin.settings.js"></script>
<script src="js/admin.system.js"></script>
<script src="js/admin.customers.js"></script>
</body>
</html>
