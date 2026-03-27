<?php
require_once 'config.php';
$base = APP_URL;
if (session_status() === PHP_SESSION_NONE) session_start();
if (!empty($_SESSION['kw_user_id'])) {
    header("Location: $base/mi-perfil.php");
    exit;
}
$pageTitle = 'Crear cuenta / Iniciar sesión';
include 'header.php';
?>

<main style="padding-top:5rem;min-height:100vh;display:flex;align-items:center">
  <div class="container" style="max-width:480px;padding:4rem 2rem">

    <!-- TABS -->
    <div style="display:flex;border-bottom:1px solid rgba(255,255,255,0.08);margin-bottom:2rem">
      <button class="auth-tab active" id="tabLogin" onclick="switchTab('login')">Iniciar sesión</button>
      <button class="auth-tab" id="tabRegister" onclick="switchTab('register')">Crear cuenta</button>
    </div>

    <!-- LOGIN -->
    <div id="formLogin">
      <h2 style="font-size:1.4rem;margin-bottom:1.5rem">Bienvenida de vuelta</h2>
      <form id="loginForm" style="display:flex;flex-direction:column;gap:1rem">
        <div class="form-group">
          <label>Correo electrónico</label>
          <input type="email" name="email" class="input" required placeholder="tu@correo.cl">
        </div>
        <div class="form-group">
          <label>Contraseña</label>
          <input type="password" name="password" class="input" required placeholder="••••••••">
        </div>
        <div id="loginError" style="display:none;color:#ef4444;font-size:0.82rem;padding:0.6rem 1rem;background:rgba(239,68,68,0.08);border-radius:0.5rem;border:1px solid rgba(239,68,68,0.2)"></div>
        <button type="submit" class="btn btn-primary" style="justify-content:center;margin-top:0.5rem" id="btnLogin">
          <i class="fa-solid fa-arrow-right-to-bracket"></i> Iniciar sesión
        </button>
      </form>
    </div>

    <!-- REGISTRO -->
    <div id="formRegister" style="display:none">
      <h2 style="font-size:1.4rem;margin-bottom:1.5rem">Crea tu cuenta</h2>
      <form id="registerForm" style="display:flex;flex-direction:column;gap:1rem">
        <div class="form-group">
          <label>Nombre completo</label>
          <input type="text" name="name" class="input" required placeholder="Katherine Rojas">
        </div>
        <div class="form-group">
          <label>Correo electrónico</label>
          <input type="email" name="email" class="input" required placeholder="tu@correo.cl">
        </div>
        <div class="form-group">
          <label>Teléfono (opcional)</label>
          <input type="tel" name="phone" class="input" placeholder="+56 9 1234 5678">
        </div>
        <div class="form-group">
          <label>Contraseña</label>
          <input type="password" name="password" class="input" required placeholder="Mínimo 6 caracteres">
        </div>
        <div id="registerError" style="display:none;color:#ef4444;font-size:0.82rem;padding:0.6rem 1rem;background:rgba(239,68,68,0.08);border-radius:0.5rem;border:1px solid rgba(239,68,68,0.2)"></div>
        <button type="submit" class="btn btn-primary" style="justify-content:center;margin-top:0.5rem" id="btnRegister">
          <i class="fa-solid fa-user-plus"></i> Crear cuenta
        </button>
      </form>
    </div>

  </div>
</main>

<style>
.auth-tab {
  flex:1; padding:0.8rem; background:none; border:none; color:var(--mid);
  font-family:'Space Grotesk',sans-serif; font-size:0.9rem; font-weight:500;
  cursor:pointer; border-bottom:2px solid transparent; transition:0.2s;
}
.auth-tab.active { color:var(--accent); border-bottom-color:var(--accent); }
</style>

<script>
function switchTab(tab) {
  document.getElementById('formLogin').style.display    = tab==='login'    ? 'block' : 'none';
  document.getElementById('formRegister').style.display = tab==='register' ? 'block' : 'none';
  document.getElementById('tabLogin').classList.toggle('active',    tab==='login');
  document.getElementById('tabRegister').classList.toggle('active', tab==='register');
}

document.getElementById('loginForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  const btn = document.getElementById('btnLogin');
  btn.disabled = true;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Entrando...';
  const err = document.getElementById('loginError');
  err.style.display = 'none';

  const body = Object.fromEntries(new FormData(this).entries());
  try {
    const res  = await fetch(`${BASE}/api.php?action=user_login`, {
      method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(body)
    });
    const data = await res.json();
    if (data.success) {
      window.location.href = `${BASE}/mi-perfil.php`;
    } else {
      err.textContent = data.error || 'Error al iniciar sesión';
      err.style.display = 'block';
      btn.disabled = false;
      btn.innerHTML = '<i class="fa-solid fa-arrow-right-to-bracket"></i> Iniciar sesión';
    }
  } catch(e) {
    err.textContent = 'Error de conexión';
    err.style.display = 'block';
    btn.disabled = false;
    btn.innerHTML = '<i class="fa-solid fa-arrow-right-to-bracket"></i> Iniciar sesión';
  }
});

document.getElementById('registerForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  const btn = document.getElementById('btnRegister');
  btn.disabled = true;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Creando cuenta...';
  const err = document.getElementById('registerError');
  err.style.display = 'none';

  const body = Object.fromEntries(new FormData(this).entries());
  try {
    const res  = await fetch(`${BASE}/api.php?action=user_register`, {
      method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(body)
    });
    const data = await res.json();
    if (data.success) {
      window.location.href = `${BASE}/mi-perfil.php`;
    } else {
      err.textContent = data.error || 'Error al crear cuenta';
      err.style.display = 'block';
      btn.disabled = false;
      btn.innerHTML = '<i class="fa-solid fa-user-plus"></i> Crear cuenta';
    }
  } catch(e) {
    err.textContent = 'Error de conexión';
    err.style.display = 'block';
    btn.disabled = false;
    btn.innerHTML = '<i class="fa-solid fa-user-plus"></i> Crear cuenta';
  }
});
</script>

<?php include 'footer.php'; ?>
