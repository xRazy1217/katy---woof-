<?php 
require_once 'config.php'; 
$settings = getSiteSettings();
?>
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Proceso Creativo | Katy & Woof</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="variables.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="main.css?v=<?php echo time(); ?>">
    <link rel="icon" type="image/x-icon" href="<?php echo $settings['site_favicon']; ?>">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;900&family=Lora:ital,wght@0,600;1,400&display=swap" rel="stylesheet">
  </head>
  <body class="pt-32 antialiased">
    <?php include 'header.php'; ?>

    <section class="max-w-7xl mx-auto py-24 px-8 min-h-screen">
      <header class="text-center mb-32 reveal">
        <h1 class="text-6xl md:text-8xl font-bold serif italic mb-10 leading-none">Del alma al <br /><span class="text-[var(--pink-deep)]">Lienzo.</span></h1>
        <p class="text-2xl text-stone-500 font-light max-w-2xl mx-auto italic">Un viaje de paciencia, observación y arte puro.</p>
      </header>

      <div id="dynamic-process-steps" class="space-y-48">
          <!-- JS Hydration -->
      </div>
    </section>

    <?php include 'footer.php'; ?>
    <script src="whatsapp.js?v=<?php echo time(); ?>"></script>
    <script>
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => { if(entry.isIntersecting) entry.target.classList.add('active'); });
        }, { threshold: 0.1 });

        async function loadProcess() {
            try {
                const res = await fetch(`api.php?action=get_process&v=${Date.now()}`);
                const steps = await res.json();
                const container = document.getElementById('dynamic-process-steps');
                
                if(steps.length === 0) {
                    container.innerHTML = "<p class='text-center italic text-stone-400'>Próximamente compartiremos nuestro proceso secreto.</p>";
                    return;
                }

                container.innerHTML = steps.map((s, index) => {
                    const isEven = index % 2 === 0;
                    const roman = ["I", "II", "III", "IV", "V", "VI"][index] || (index + 1);
                    return `
                        <div class="grid lg:grid-cols-2 gap-24 items-center reveal">
                            <img src="${s.img_url}" class="rounded-[5rem] shadow-2xl h-[500px] w-full object-cover ${!isEven ? 'order-1 lg:order-2' : ''}" referrerPolicy="no-referrer">
                            <div class="p-10 ${!isEven ? 'order-2 lg:order-1 text-right' : ''}">
                                <span class="text-9xl font-black text-stone-100 serif mb-8 block leading-none">${roman}</span>
                                <h2 class="text-4xl font-bold mb-10 serif">${s.title}</h2>
                                <p class="text-stone-600 leading-loose text-2xl italic font-light">"${s.description}"</p>
                            </div>
                        </div>
                    `;
                }).join('');
                document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
            } catch(e) { console.error(e); }
        }

        document.addEventListener('DOMContentLoaded', () => {
            loadProcess();
        });
    </script>
  </body>
</html>