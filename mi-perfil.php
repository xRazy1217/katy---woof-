<?php
require_once 'config.php';
$base = APP_URL;
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['kw_user_id'])) {
    header("Location: $base/cuenta.php");
    exit;
}
$pdo  = getDBConnection();
$stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([$_SESSION['kw_user_id']]);
$user = $stmt->fetch();
if (!$user) { session_destroy(); header("Location: $base/cuenta.php"); exit; }

$pageTitle = 'Mi Perfil';
include 'header.php';
?>

<main style="padding-top:5rem;min-height:100vh">
  <div style="background:var(--dark);padding:3rem 0 2rem;border-bottom:1px solid rgba(255,255,255,0.06)">
    <div class="container">
      <span class="label">Mi cuenta</span>
      <h1 style="margin-top:0.6rem">Hola, <span class="accent"><?php echo htmlspecialchars(explode(' ',$user['name'])[0]); ?></span> 👋</h1>
    </div>
  </div>

  <section class="section">
    <div class="container" style="max-width:900px">
      <div style="display:grid;grid-template-columns:240px 1fr;gap:2rem;align-items:start">

        <!-- SIDEBAR -->
        <div>
          <div class="glass" style="padding:1.5rem">
            <div style="text-align:center;margin-bottom:1.5rem">
              <div style="width:64px;height:64px;border-radius:50%;background:var(--accent-dim);border:2px solid rgba(232,57,154,0.3);display:flex;align-items:center;justify-content:center;margin:0 auto 0.8rem;font-size:1.5rem;color:var(--accent)">
                <?php echo strtoupper(substr($user['name'],0,1)); ?>
              </div>
              <div style="font-weight:600;font-size:0.9rem"><?php echo htmlspecialchars($user['name']); ?></div>
              <div style="font-size:0.75rem;color:var(--mid)"><?php echo htmlspecialchars($user['email']); ?></div>
            </div>
            <nav style="display:flex;flex-direction:column;gap:0.3rem">
              <button class="profile-tab active" onclick="showTab('orders')" id="tab-orders"><i class="fa-solid fa-receipt"></i> Mis compras</button>
              <button class="profile-tab" onclick="showTab('profile')" id="tab-profile"><i class="fa-solid fa-user"></i> Mi perfil</button>
              <a href="<?php echo $base; ?>/contacto.php" class="profile-tab"><i class="fa-solid fa-envelope"></i> Contactar</a>
              <button class="profile-tab" onclick="logout()" style="color:#ef4444"><i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión</button>
            </nav>
          </div>
        </div>

        <!-- CONTENIDO -->
        <div>
          <!-- MIS COMPRAS -->
          <div id="panel-orders">
            <h3 style="margin-bottom:1.5rem">Mis compras</h3>
            <div id="ordersList">
              <div style="text-align:center;padding:2rem;color:var(--mid)"><i class="fa-solid fa-spinner fa-spin"></i></div>
            </div>
          </div>

          <!-- MI PERFIL -->
          <div id="panel-profile" style="display:none">
            <h3 style="margin-bottom:1.5rem">Mi perfil</h3>
            <form id="profileForm" style="display:flex;flex-direction:column;gap:1rem;max-width:480px">
              <div class="form-group">
                <label>Nombre completo</label>
                <input type="text" name="name" class="input" value="<?php echo htmlspecialchars($user['name']); ?>" required>
              </div>
              <div class="form-group">
                <label>Correo electrónico</label>
                <input type="email" class="input" value="<?php echo htmlspecialchars($user['email']); ?>" disabled style="opacity:0.5">
              </div>
              <div class="form-group">
                <label>Teléfono</label>
                <input type="tel" name="phone" class="input" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" placeholder="+56 9 1234 5678">
              </div>
              <div class="form-group">
                <label>Nueva contraseña <span style="color:var(--mid);font-weight:400">(dejar vacío para no cambiar)</span></label>
                <input type="password" name="password" class="input" placeholder="Mínimo 6 caracteres">
              </div>
              <div id="profileMsg" style="display:none;font-size:0.82rem;padding:0.6rem 1rem;border-radius:0.5rem"></div>
              <button type="submit" class="btn btn-primary" style="align-self:flex-start">
                <i class="fa-solid fa-floppy-disk"></i> Guardar cambios
              </button>
            </form>
          </div>
        </div>

      </div>
    </div>
  </section>
</main>

<style>
.profile-tab {
  display:flex; align-items:center; gap:0.6rem;
  padding:0.65rem 1rem; border-radius:0.5rem;
  background:none; border:none; color:var(--mid);
  font-family:'Space Grotesk',sans-serif; font-size:0.85rem; font-weight:500;
  cursor:pointer; transition:0.2s; text-decoration:none; width:100%; text-align:left;
}
.profile-tab:hover { background:rgba(255,255,255,0.04); color:var(--white); }
.profile-tab.active { background:var(--accent-dim); color:var(--accent); }
.order-row {
  display:flex; align-items:center; justify-content:space-between;
  padding:1rem 1.2rem; border-bottom:1px solid rgba(255,255,255,0.05);
  font-size:0.85rem;
}
.order-row:last-child { border-bottom:none; }
</style>

<script>
function showTab(tab) {
  ['orders','profile'].forEach(t => {
    document.getElementById('panel-'+t).style.display = t===tab ? 'block' : 'none';
    document.getElementById('tab-'+t)?.classList.toggle('active', t===tab);
  });
}

async function loadOrders() {
  const res  = await fetch(`${BASE}/api.php?action=user_orders`);
  const data = await res.json();
  const el   = document.getElementById('ordersList');
  if (!data.success || !data.data.length) {
    el.innerHTML = '<div class="glass" style="padding:2rem;text-align:center;color:var(--mid)"><i class="fa-solid fa-bag-shopping" style="font-size:2rem;opacity:0.2;display:block;margin-bottom:0.8rem"></i>Aún no tienes compras</div>';
    return;
  }
  const statusLabel = {pending:'Pendiente',processing:'En proceso',shipped:'Enviado',completed:'Completado',cancelled:'Cancelado'};
  const statusColor = {pending:'badge-yellow',processing:'badge-blue',shipped:'badge-blue',completed:'badge-green',cancelled:'badge-red'};
  el.innerHTML = '<div class="table-wrap">' + data.data.map(o => `
    <div class="order-row">
      <div>
        <div style="font-weight:600;color:var(--white)">#${o.order_number}</div>
        <div style="color:var(--mid);font-size:0.75rem">${new Date(o.created_at).toLocaleDateString('es-CL')}</div>
      </div>
      <span class="badge ${statusColor[o.status]||'badge-gray'}">${statusLabel[o.status]||o.status}</span>
      <div style="font-family:'Space Mono',monospace;color:var(--accent)">$${Number(o.total).toLocaleString('es-CL')}</div>
    </div>
  `).join('') + '</div>';
}

document.getElementById('profileForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  const msg  = document.getElementById('profileMsg');
  const body = Object.fromEntries(new FormData(this).entries());
  const res  = await fetch(`${BASE}/api.php?action=user_update`, {
    method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(body)
  });
  const data = await res.json();
  msg.style.display = 'block';
  if (data.success) {
    msg.style.background = 'rgba(34,197,94,0.08)';
    msg.style.border = '1px solid rgba(34,197,94,0.2)';
    msg.style.color = '#86efac';
    msg.textContent = 'Perfil actualizado correctamente';
  } else {
    msg.style.background = 'rgba(239,68,68,0.08)';
    msg.style.border = '1px solid rgba(239,68,68,0.2)';
    msg.style.color = '#fca5a5';
    msg.textContent = data.error || 'Error al guardar';
  }
});

async function logout() {
  await fetch(`${BASE}/api.php?action=user_logout`);
  window.location.href = `${BASE}/`;
}

loadOrders();
</script>

<?php include 'footer.php'; ?>
