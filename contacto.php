<?php
require_once 'config.php';
$pageTitle = 'Contacto';
$pageDesc  = 'Contáctanos para crear el retrato de tu mascota. La Serena, Chile.';
include 'header.php';
?>

<main style="padding-top:5rem;min-height:100vh">

  <div style="background:var(--dark);padding:4rem 0 3rem;border-bottom:1px solid rgba(255,255,255,0.06)">
    <div class="container">
      <span class="label reveal">Hablemos</span>
      <h1 style="margin-top:0.8rem" class="reveal delay-1">Contáctanos</h1>
      <p style="color:var(--mid);margin-top:0.5rem" class="reveal delay-2">Estamos aquí para ayudarte a crear el retrato perfecto de tu mascota.</p>
    </div>
  </div>

  <section class="section">
    <div class="container">
      <div class="grid-2" style="gap:4rem">

        <!-- INFO -->
        <div>
          <span class="label reveal">Información de contacto</span>
          <div style="display:flex;flex-direction:column;gap:1.2rem;margin-top:2rem">
            <?php
            $contacts = [
              ['fa-envelope','Correo','katy.woof.store@gmail.com','mailto:katy.woof.store@gmail.com'],
              ['fa-brands fa-whatsapp','WhatsApp','+56 9 7688 6481','https://wa.me/56976886481'],
              ['fa-location-dot','Ubicación','La Serena, Chile','#'],
              ['fa-clock','Horario','Lunes a Sábado, 09:00 – 18:00 hrs','#'],
              ['fa-globe','Sitio web','www.retratosdemascotas.cl','https://www.retratosdemascotas.cl'],
            ];
            foreach($contacts as $i => $c): ?>
            <a href="<?php echo $c[3]; ?>" <?php echo $c[3]!='#'?'target="_blank"':''; ?>
               class="glass reveal" style="padding:1.2rem 1.5rem;display:flex;align-items:center;gap:1rem;transition-delay:<?php echo $i*0.08; ?>s;text-decoration:none"
               onmouseover="this.style.borderColor='rgba(232,57,154,0.3)'" onmouseout="this.style.borderColor=''">
              <div style="width:40px;height:40px;border-radius:50%;background:var(--accent-dim);border:1px solid rgba(232,57,154,0.2);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <i class="fa-solid <?php echo $c[0]; ?>" style="color:var(--accent);font-size:0.9rem"></i>
              </div>
              <div>
                <div style="font-size:0.72rem;color:var(--mid);letter-spacing:0.05em;text-transform:uppercase;margin-bottom:0.2rem"><?php echo $c[1]; ?></div>
                <div style="font-size:0.9rem;font-weight:500;color:var(--white)"><?php echo $c[2]; ?></div>
              </div>
            </a>
            <?php endforeach; ?>
          </div>

          <div style="margin-top:2.5rem" class="reveal">
            <div style="font-size:0.75rem;color:var(--mid);letter-spacing:0.1em;text-transform:uppercase;margin-bottom:1rem">Redes sociales</div>
            <div style="display:flex;gap:0.8rem">
              <a href="https://www.instagram.com/katyandwoof/" target="_blank" class="social-link"><i class="fa-brands fa-instagram"></i></a>
              <a href="https://wa.me/56976886481" target="_blank" class="social-link"><i class="fa-brands fa-whatsapp"></i></a>
              <a href="mailto:katy.woof.store@gmail.com" class="social-link"><i class="fa-solid fa-envelope"></i></a>
            </div>
          </div>
        </div>

        <!-- FORMULARIO -->
        <div class="reveal delay-2">
          <div class="glass" style="padding:2.5rem">
            <h3 style="font-size:1.1rem;margin-bottom:1.8rem">Envíanos un mensaje</h3>
            <form id="contactForm" style="display:flex;flex-direction:column;gap:1.2rem">
              <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
                <div>
                  <label>Nombre *</label>
                  <input type="text" name="name" class="input" required placeholder="Tu nombre"/>
                </div>
                <div>
                  <label>Correo *</label>
                  <input type="email" name="email" class="input" required placeholder="tu@correo.cl"/>
                </div>
              </div>
              <div>
                <label>Teléfono</label>
                <input type="tel" name="phone" class="input" placeholder="+56 9 1234 5678"/>
              </div>
              <div>
                <label>Asunto *</label>
                <select name="subject" class="select" required>
                  <option value="">Seleccionar...</option>
                  <option>Quiero un retrato de mi mascota</option>
                  <option>Consulta sobre productos</option>
                  <option>Información de precios</option>
                  <option>Seguimiento de pedido</option>
                  <option>Otro</option>
                </select>
              </div>
              <div>
                <label>Mensaje *</label>
                <textarea name="message" class="textarea" required placeholder="Cuéntanos sobre tu mascota o tu consulta..."></textarea>
              </div>
              <button type="submit" class="btn btn-primary" style="justify-content:center" id="btnContact">
                <i class="fa-solid fa-paper-plane"></i> Enviar mensaje
              </button>
            </form>
            <div id="contactSuccess" style="display:none;text-align:center;padding:2rem">
              <div style="font-size:2.5rem;margin-bottom:1rem">✓</div>
              <h4 style="margin-bottom:0.5rem">¡Mensaje enviado!</h4>
              <p style="font-size:0.88rem;color:var(--mid)">Te responderemos a la brevedad. También puedes contactarnos directamente por WhatsApp.</p>
              <a href="https://wa.me/56976886481" target="_blank" class="btn btn-primary btn-sm" style="margin-top:1rem">
                <i class="fa-brands fa-whatsapp"></i> Ir a WhatsApp
              </a>
            </div>
          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- WHATSAPP CTA -->
  <section class="section-sm" style="background:var(--dark);border-top:1px solid rgba(255,255,255,0.06)">
    <div class="container" style="text-align:center">
      <h2 style="margin-bottom:1rem" class="reveal">¿Prefieres hablar<br><span class="accent">directamente</span>?</h2>
      <p style="color:var(--mid);margin-bottom:2rem" class="reveal delay-1">Escríbenos por WhatsApp y te respondemos al instante.</p>
      <a href="https://wa.me/56976886481?text=Hola!%20Quiero%20información%20sobre%20retratos%20de%20mascotas" target="_blank" class="btn btn-primary btn-lg reveal delay-2">
        <i class="fa-brands fa-whatsapp"></i> Abrir WhatsApp
      </a>
    </div>
  </section>

</main>

<script>
document.getElementById('contactForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  const btn = document.getElementById('btnContact');
  btn.disabled = true;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Enviando...';
  // Simulación — conectar con API de email si se desea
  await new Promise(r => setTimeout(r, 1200));
  document.getElementById('contactForm').style.display = 'none';
  document.getElementById('contactSuccess').style.display = 'block';
});
</script>

<?php include 'footer.php'; ?>
