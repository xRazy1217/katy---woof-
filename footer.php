<?php $settings = getSiteSettings(); ?>
<!-- Minimalist Luxury Footer -->
<link rel="stylesheet" href="footer.css?v=<?php echo time(); ?>">
<footer class="site-footer-minimal">
  <div class="max-w-7xl mx-auto px-6">
    
    <div class="footer-grid-mini">
      <!-- Col 1: Brand -->
      <div class="footer-col-mini">
        <a href="index.php" class="footer-brand-mini">Katy & Woof</a>
        <span class="footer-tagline-mini">Fine Art Pet Studio</span>
        <p class="text-[12px] text-white/40 italic serif mt-6 leading-relaxed max-w-[200px]">
          Capturando la esencia eterna de tu compañero a través del arte.
        </p>
      </div>

      <!-- Col 2: Navigation -->
      <div class="footer-col-mini">
        <h4 class="text-[9px] uppercase tracking-[0.3em] text-white/20 font-bold mb-6">Explorar</h4>
        <nav>
          <a href="galeria.php" class="footer-link-mini">Portafolio</a>
          <a href="servicios.php" class="footer-link-mini">Servicios</a>
          <a href="como-funciona.php" class="footer-link-mini">Proceso</a>
          <a href="blog.php" class="footer-link-mini">Journal</a>
          <a href="index.php#nosotros" class="footer-link-mini">Nosotros</a>
        </nav>
      </div>

      <!-- Col 3: Contact -->
      <div class="footer-col-mini">
        <h4 class="text-[9px] uppercase tracking-[0.3em] text-white/20 font-bold mb-6">Atelier</h4>
        <div class="footer-contact-item">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
          <a href="https://wa.me/<?php echo preg_replace('/\s+/', '', $settings['contact_whatsapp']); ?>" class="hover:text-white transition-colors">
            <?php echo $settings['contact_whatsapp']; ?>
          </a>
        </div>
        <div class="footer-contact-item">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
          <a href="mailto:<?php echo $settings['contact_email']; ?>" class="hover:text-white transition-colors">
            <?php echo $settings['contact_email']; ?>
          </a>
        </div>
      </div>

      <!-- Col 4: Social -->
      <div class="footer-col-mini">
        <h4 class="text-[9px] uppercase tracking-[0.3em] text-white/20 font-bold mb-6">Conectar</h4>
        <div class="flex gap-4">
          <a href="https://www.instagram.com/katyandwoof/" target="_blank" class="text-white/40 hover:text-white transition-all hover:-translate-y-1">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="20" x="2" y="2" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/></svg>
          </a>
          <a href="contacto.php" class="text-white/40 hover:text-white transition-all hover:-translate-y-1">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          </a>
        </div>
      </div>
    </div>

    <!-- Bottom Bar -->
    <div class="footer-bottom-mini">
      <span class="copyright-mini">
        © <?php echo date("Y"); ?> Katy & Woof Studio. Todos los derechos reservados.
      </span>
      <div class="flex gap-6">
        <a href="#" class="copyright-mini hover:text-white transition-colors">Privacidad</a>
        <a href="#" class="copyright-mini hover:text-white transition-colors">Términos</a>
      </div>
    </div>

  </div>
</footer>