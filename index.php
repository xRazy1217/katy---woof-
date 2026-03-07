<?php 
require_once 'config.php'; 
$settings = getSiteSettings();
?>
<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Katy & Woof | Retratos Artísticos de Autor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="variables.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="main.css?v=<?php echo time(); ?>">
    <link rel="icon" type="image/x-icon" href="<?php echo $settings['site_favicon']; ?>">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;900&family=Lora:ital,wght@0,400;0,600;1,400;1,600&display=swap" rel="stylesheet">
  </head>
  <body class="antialiased overflow-x-hidden">
    <?php include 'header.php'; ?>

    <section id="hero-section" class="relative pt-48 pb-32 px-8 flex flex-col items-center text-center min-h-[90vh] justify-center">
      <div class="absolute inset-0 z-[-1] opacity-10 bg-cover bg-center" style="background-image: url('<?php echo $settings['hero_image']; ?>')"></div>
      <div class="max-w-4xl reveal">
        <span class="inline-block text-[var(--pink-deep)] font-black uppercase tracking-[0.5em] text-xs mb-6">Fine Art Tradition</span>
        <h1 class="hero-title font-bold serif mb-8"><?php echo $settings['hero_title']; ?></h1>
        <p class="text-xl md:text-2xl text-stone-600 font-light max-w-2xl mx-auto mb-12 leading-relaxed italic"><?php echo $settings['hero_description']; ?></p>
        <div class="flex flex-col sm:flex-row gap-6 justify-center">
            <a href="contacto.php" class="px-12 py-5 bg-[var(--midnight)] text-white rounded-full font-black uppercase tracking-[0.2em] text-xs shadow-2xl hover:scale-105 transition-all">Encargar Retrato</a>
            <a href="galeria.php" class="px-12 py-5 border-2 border-stone-200 bg-white rounded-full font-black uppercase tracking-[0.2em] text-xs text-stone-800 hover:border-black transition-all">Ver Portafolio</a>
        </div>
      </div>
    </section>

    <section id="nosotros" class="py-32 bg-[#F9F7F5] px-8">
        <div class="max-w-7xl mx-auto grid lg:grid-cols-2 gap-24 items-center">
            <div class="reveal">
                <span class="text-[var(--pink-deep)] font-black uppercase tracking-[0.5em] text-[10px] mb-8 block">Nuestra Historia</span>
                <h2 class="text-6xl font-bold serif italic leading-tight mb-10 text-[#1E2B3E]"><?php echo $settings['nosotros_title']; ?></h2>
                <div class="space-y-8 text-stone-500 font-light leading-relaxed italic text-xl max-w-xl">
                    <?php echo nl2br($settings['our_history']); ?>
                </div>
            </div>
            <div class="reveal relative">
                <div class="aspect-[4/5] rounded-[5rem] overflow-hidden shadow-2xl">
                    <img src="<?php echo $settings['nosotros_image']; ?>" class="w-full h-full object-cover" alt="Katy & Woof Studio" referrerPolicy="no-referrer">
                </div>
            </div>
        </div>
    </section>

    <?php include 'footer.php'; ?>
    <script src="whatsapp.js?v=<?php echo time(); ?>"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => { if(entry.isIntersecting) entry.target.classList.add('active'); });
            }, { threshold: 0.1 });
            document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
        });
    </script>
  </body>
</html>