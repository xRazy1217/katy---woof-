<?php 
require_once 'config.php'; 
$settings = getSiteSettings();
?>
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Servicios | Katy & Woof</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="variables.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="main.css?v=<?php echo time(); ?>">
    <link rel="icon" type="image/x-icon" href="<?php echo $settings['site_favicon']; ?>">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;900&family=Lora:ital,wght@0,600;1,400&display=swap" rel="stylesheet">
  </head>
  <body class="pt-32 antialiased">
    <?php include 'header.php'; ?>

    <main class="max-w-7xl mx-auto py-24 px-8 min-h-screen">
        <header class="text-center mb-16 reveal">
            <h1 class="text-5xl md:text-7xl font-bold serif italic mb-6">Servicios de <span class="text-[var(--soft-blue)]">Autor</span></h1>
            <p class="text-xl text-stone-500 max-w-xl mx-auto italic">Experiencias creativas diseñadas para elevar el vínculo con tu mascota.</p>
        </header>
        
        <div id="filters-container" class="flex flex-wrap justify-center gap-4 mb-20 reveal"></div>
        <div id="services-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-12"></div>
    </main>

    <div id="service-modal" class="fixed inset-0 bg-black/60 z-[200] flex items-center justify-center p-4 md:p-8 overflow-y-auto opacity-0 pointer-events-none transition-opacity duration-500">
      <div id="modal-inner" class="bg-white rounded-[3rem] shadow-2xl w-full max-w-6xl flex flex-col lg:flex-row overflow-hidden relative my-auto transform scale-95 opacity-0 transition-all duration-300">
          <button id="close-modal" class="absolute top-6 right-6 text-stone-400 hover:text-midnight text-4xl font-light z-50 p-2 leading-none">&times;</button>
          <!-- Imagen Container: Mejorado para mejor visualización -->
          <div class="w-full lg:w-1/2 bg-stone-100 min-h-[350px] lg:min-h-[600px] overflow-hidden flex items-center justify-center">
              <img id="modal-main-img" src="" class="w-full h-full object-contain p-6 lg:p-8" referrerPolicy="no-referrer" alt="Servicio">
          </div>
          <!-- Contenido: Información del servicio -->
          <div class="w-full lg:w-1/2 p-8 lg:p-16 flex flex-col justify-center bg-white">
              <span class="text-[var(--pink-deep)] font-black uppercase tracking-[0.4em] text-[10px] mb-6 block">Katy & Woof Atelier</span>
              <h2 id="modal-title" class="text-3xl lg:text-5xl serif font-bold mb-8 leading-tight text-midnight"></h2>
              <div id="modal-description" class="text-base lg:text-lg italic font-light text-stone-600 mb-12 space-y-4 prose max-w-none"></div>
              <div class="pt-8 border-t border-stone-50">
                  <a id="modal-whatsapp" href="#" target="_blank" class="w-full inline-flex items-center justify-center gap-4 py-5 lg:py-6 bg-[#25d366] text-white rounded-full font-black uppercase tracking-[0.3em] text-[9px] lg:text-[10px] shadow-xl hover:bg-[#20ba5a] transition-all duration-300">
                    📱 Consultar este Servicio
                  </a>
              </div>
          </div>
      </div>
    </div>

    <?php include 'footer.php'; ?>
    <script src="whatsapp.js?v=<?php echo time(); ?>"></script>
    <script>
        // Configuración global para JavaScript
        window.siteSettings = <?php echo json_encode($settings); ?>;
    </script>
    <script src="js/services-page.js?v=<?php echo time(); ?>"></script>
  </body>
</html>