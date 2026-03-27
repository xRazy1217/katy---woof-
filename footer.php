<?php $base = APP_URL; ?>
<footer class="site-footer">
  <div class="container">
    <div class="footer-grid">
      <div class="footer-brand">
        <a href="<?php echo $base; ?>/" class="logo">
          <img src="<?php echo $base; ?>/Logo_KW.png" alt="Katy &amp; Woof" style="height:42px;width:auto;object-fit:contain">
        </a>
        <p>Arte con propósito. Retratos coloridos que celebran el vínculo entre las personas y sus mascotas. Desde La Serena, Chile.</p>
        <div class="social-links" style="margin-top:1.5rem">
          <a href="https://www.instagram.com/katyandwoof/" target="_blank" class="social-link"><i class="fa-brands fa-instagram"></i></a>
          <a href="https://wa.me/56976886481" target="_blank" class="social-link"><i class="fa-brands fa-whatsapp"></i></a>
          <a href="mailto:katy.woof.store@gmail.com" class="social-link"><i class="fa-solid fa-envelope"></i></a>
        </div>
      </div>
      <div class="footer-col">
        <h4>Tienda</h4>
        <a href="<?php echo $base; ?>/catalogo.php">Catálogo</a>
        <a href="<?php echo $base; ?>/catalogo.php?tipo=retrato">Retratos</a>
        <a href="<?php echo $base; ?>/catalogo.php?tipo=producto">Productos</a>
        <a href="<?php echo $base; ?>/checkout.php">Mi Carrito</a>
      </div>
      <div class="footer-col">
        <h4>Nosotros</h4>
        <a href="<?php echo $base; ?>/nosotros.php">Historia</a>
        <a href="<?php echo $base; ?>/nosotros.php#proceso">Proceso</a>
        <a href="<?php echo $base; ?>/nosotros.php#mision">Misión</a>
        <a href="<?php echo $base; ?>/contacto.php">Contacto</a>
      </div>
      <div class="footer-col">
        <h4>Contacto</h4>
        <a href="mailto:katy.woof.store@gmail.com">katy.woof.store@gmail.com</a>
        <a href="https://wa.me/56976886481">+56 9 7688 6481</a>
        <a href="#">La Serena, Chile</a>
        <a href="#">Lun–Sáb 09:00–18:00</a>
      </div>
    </div>
    <div class="footer-bottom">
      <p>© <?php echo date('Y'); ?> Katy & Woof. Todos los derechos reservados.</p>
      <p style="font-size:0.75rem;color:var(--gray2)">Hecho con ♥ en Chile</p>
    </div>
  </div>
</footer>

<script src="<?php echo $base; ?>/js/app.js"></script>
<?php if(isset($extraJs)) echo $extraJs; ?>
<script>
  window.addEventListener('load', () => {
    const loader = document.getElementById('pageLoader');
    if(loader) { loader.classList.add('hidden'); setTimeout(()=>loader.remove(), 600); }
  });
  const revealObserver = new IntersectionObserver((entries) => {
    entries.forEach(e => { if(e.isIntersecting) e.target.classList.add('visible'); });
  }, { threshold: 0.1 });
  document.querySelectorAll('.reveal').forEach(el => revealObserver.observe(el));
  // Cerrar menú usuario al hacer click fuera
  document.addEventListener('click', function(e) {
    const wrap = document.getElementById('userMenuWrap');
    if (wrap && !wrap.contains(e.target)) {
      document.getElementById('userDropdown')?.classList.remove('open');
    }
  });
</script>
</body>
</html>
