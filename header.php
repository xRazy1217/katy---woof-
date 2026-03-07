<?php $settings = getSiteSettings(); ?>
<!-- Minimalist Luxury Header -->
<link rel="stylesheet" href="header.css?v=<?php echo time(); ?>">
<header class="site-header fixed top-0 left-0 w-full">
  <!-- Exact Multicolor Stripe from Image -->
  <div class="multicolor-stripe h-1.5 w-full flex">
    <div class="flex-1 bg-[#1E2B3E]"></div>
    <div class="flex-1 bg-[#5F85BB]"></div>
    <div class="flex-1 bg-[#9ED8D8]"></div>
    <div class="flex-1 bg-[#B88181]"></div>
    <div class="flex-1 bg-[#000000]"></div>
  </div>
  
  <!-- Main Bar -->
  <div class="header-main-bar h-24 px-6 shadow-sm">
    <div class="max-w-7xl mx-auto h-full flex items-center justify-between">
      
      <!-- Logo Group -->
      <a href="index.php" class="flex items-center gap-6 group hover:opacity-80 transition-opacity">
        <!-- Icon -->
        <div class="w-10 h-10 flex items-center justify-center">
          <img src="logo-icon.png" alt="K" class="h-8 w-auto">
        </div>
        
        <!-- Vertical Divider -->
        <div class="h-10 w-[1px] bg-stone-200"></div>
        
        <!-- Text -->
        <div class="flex flex-col">
          <span class="font-serif italic text-2xl font-bold text-[#1E2B3E] leading-tight">Katy & Woof</span>
          <span class="text-[9px] uppercase tracking-[0.3em] font-bold text-stone-400">Fine Art Pet Studio</span>
        </div>
      </a>

      <!-- Desktop Nav -->
      <nav class="hidden lg:flex items-center gap-12">
        <a href="galeria.php" class="header-nav-link">Portafolio</a>
        <a href="servicios.php" class="header-nav-link">Servicios</a>
        <a href="como-funciona.php" class="header-nav-link">Proceso</a>
        <a href="blog.php" class="header-nav-link">Blog</a>
        <a href="index.php#nosotros" class="header-nav-link">Nosotros</a>
        <a href="contacto.php" class="header-nav-link">Contacto</a>
      </nav>

      <!-- Hamburger -->
      <div id="hamburger-toggle" class="lg:hidden cursor-pointer flex flex-col justify-between w-6 h-4">
        <span class="h-[2px] w-full bg-[#1E2B3E] rounded-full transition-all"></span>
        <span class="h-[2px] w-full bg-[#1E2B3E] rounded-full transition-all"></span>
        <span class="h-[2px] w-full bg-[#1E2B3E] rounded-full transition-all"></span>
      </div>

    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const btn = document.getElementById('hamburger-toggle');
      const nav = document.getElementById('mobile-nav');
      if (btn && nav) {
        btn.onclick = () => {
          const active = nav.classList.toggle('is-visible');
          document.body.style.overflow = active ? 'hidden' : '';
        };
        nav.querySelectorAll('a').forEach(link => {
          link.onclick = () => {
            nav.classList.remove('is-visible');
            document.body.style.overflow = '';
          };
        });
      }
    });
  </script>
</header>

<!-- Mobile Overlay -->
<div id="mobile-nav" class="fixed inset-0 z-[900] opacity-0 pointer-events-none transition-opacity duration-500 flex flex-col items-center justify-center">
  <nav class="flex flex-col items-center space-y-10">
    <a href="galeria.php" class="mobile-link">Portafolio</a>
    <a href="servicios.php" class="mobile-link">Servicios</a>
    <a href="como-funciona.php" class="mobile-link">Proceso</a>
    <a href="blog.php" class="mobile-link">Blog</a>
    <a href="index.php#nosotros" class="mobile-link">Nosotros</a>
    <a href="contacto.php" class="px-12 py-5 bg-white text-[#0F172A] rounded-full font-black uppercase text-xs">Contacto</a>
  </nav>
</div>