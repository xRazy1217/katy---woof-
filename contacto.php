<?php 
require_once 'config.php'; 
$settings = getSiteSettings();
?>
<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Contacto | Katy & Woof</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="variables.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="main.css?v=<?php echo time(); ?>">
    <link rel="icon" type="image/x-icon" href="<?php echo $settings['site_favicon']; ?>">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;900&family=Lora:ital,wght@0,600;1,400&display=swap" rel="stylesheet">
  </head>
  <body class="antialiased pt-32">
    <?php include 'header.php'; ?>

    <section class="max-w-7xl mx-auto py-24 px-8 min-h-screen">
      <div class="grid lg:grid-cols-2 gap-24 items-start">
        <div class="reveal">
          <span class="text-[var(--pink-deep)] font-black uppercase tracking-[0.5em] text-[10px] mb-6 block">Hablemos de Arte</span>
          <h1 class="text-5xl md:text-7xl font-bold serif italic mb-10 leading-none">Inicia un <span class="text-stone-300">proyecto.</span></h1>
          
          <div class="space-y-12 mt-16">
            <div class="flex items-center gap-8">
              <div class="w-14 h-14 bg-white rounded-2xl shadow-sm flex items-center justify-center text-2xl">📧</div>
              <div>
                <p class="text-[9px] font-black uppercase tracking-widest text-stone-500 mb-1">Email directo</p>
                <a href="mailto:<?php echo $settings['contact_email']; ?>" class="text-xl font-bold serif italic text-midnight hover:text-pink transition-colors">
                    <?php echo $settings['contact_email']; ?>
                </a>
              </div>
            </div>
            <div class="flex items-center gap-8">
              <div class="w-14 h-14 bg-white rounded-2xl shadow-sm flex items-center justify-center text-2xl">💬</div>
              <div>
                <p class="text-[9px] font-black uppercase tracking-widest text-stone-500 mb-1">WhatsApp Atelier</p>
                <a href="https://wa.me/<?php echo preg_replace('/\s+/', '', $settings['contact_whatsapp']); ?>" class="text-xl font-bold serif italic text-midnight hover:text-pink transition-colors">
                    <?php echo $settings['contact_whatsapp']; ?>
                </a>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white p-10 md:p-16 rounded-[3.5rem] shadow-2xl reveal">
          <form class="space-y-8" onsubmit="event.preventDefault(); alert('¡Gracias! Nos pondremos en contacto pronto.');">
            <div class="grid md:grid-cols-2 gap-8">
              <div class="space-y-2">
                <label class="text-[10px] font-black uppercase tracking-widest text-stone-500">Tu Nombre</label>
                <input required type="text" placeholder="Ej. Javier" class="w-full bg-stone-50 border-stone-100 border p-4 rounded-xl outline-none focus:border-midnight">
              </div>
              <div class="space-y-2">
                <label class="text-[10px] font-black uppercase tracking-widest text-stone-500">Compañero</label>
                <input type="text" placeholder="Nombre de tu mascota" class="w-full bg-stone-50 border-stone-100 border p-4 rounded-xl outline-none focus:border-midnight">
              </div>
            </div>
            <div class="space-y-2">
              <label class="text-[10px] font-black uppercase tracking-widest text-stone-500">Email de contacto</label>
              <input required type="email" placeholder="javier@ejemplo.com" class="w-full bg-stone-50 border-stone-100 border p-4 rounded-xl outline-none focus:border-midnight">
            </div>
            <div class="space-y-2">
              <label class="text-[10px] font-black uppercase tracking-widest text-stone-500">Tu Idea</label>
              <textarea rows="4" placeholder="¿Cómo te imaginas el retrato?" class="w-full bg-stone-50 border-stone-100 border p-4 rounded-xl outline-none focus:border-midnight"></textarea>
            </div>
            <button type="submit" class="w-full py-5 bg-midnight text-white rounded-full font-black uppercase tracking-[0.3em] text-[10px] shadow-xl hover:scale-[1.02] transition-all">Enviar Consulta</button>
          </form>
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