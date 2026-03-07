<?php 
require_once 'config.php'; 
$settings = getSiteSettings();
?>
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Blog | Katy & Woof</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="variables.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="main.css?v=<?php echo time(); ?>">
    <link rel="icon" type="image/x-icon" href="<?php echo $settings['site_favicon']; ?>">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;900&family=Lora:ital,wght@0,600;1,400&display=swap" rel="stylesheet">
  </head>
  <body class="pt-32 antialiased">
    <?php include 'header.php'; ?>

    <main class="max-w-7xl mx-auto py-24 px-8 min-h-screen">
        <header class="text-center mb-32 reveal">
            <span class="text-[var(--pink-deep)] font-black uppercase tracking-[0.5em] text-[10px] mb-6 block">Katy & Woof Journal</span>
            <h1 class="text-6xl md:text-8xl font-bold serif italic mb-10 leading-none">Crónicas del <br /><span class="text-stone-300">Atelier.</span></h1>
            <p class="text-2xl text-stone-500 max-w-xl mx-auto italic font-light">Historias de lealtad, arte y el vínculo eterno que nos une a ellos.</p>
        </header>

        <!-- Featured Post Section -->
        <div id="featured-post" class="mb-32 reveal"></div>

        <!-- Grid Section -->
        <div id="blog-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-16"></div>
    </main>

    <?php include 'footer.php'; ?>
    <script src="whatsapp.js?v=<?php echo time(); ?>"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const loadBlog = async () => {
                try {
                    const res = await fetch(`api.php?action=get_blog&v=${Date.now()}`);
                    const data = await res.json();
                    
                    if (data.length === 0) {
                        document.getElementById('blog-grid').innerHTML = "<p class='col-span-full text-center italic text-stone-400 py-20'>El Journal está esperando nuevas historias...</p>";
                        return;
                    }

                    // Featured Post (Latest)
                    const featured = data[0];
                    document.getElementById('featured-post').innerHTML = `
                        <a href="articulo.php?id=${featured.id}" class="group block">
                            <div class="grid lg:grid-cols-2 gap-12 items-center">
                                <div class="aspect-[16/10] rounded-[4rem] overflow-hidden bg-stone-100 shadow-2xl">
                                    <img src="${featured.img_url}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-1000" referrerPolicy="no-referrer">
                                </div>
                                <div class="p-8">
                                    <span class="text-[10px] font-black uppercase tracking-[0.4em] text-[var(--pink-deep)] mb-6 block">${featured.category}</span>
                                    <h2 class="text-4xl md:text-5xl font-bold serif italic mb-8 group-hover:text-midnight transition-colors leading-tight">${featured.title}</h2>
                                    <p class="text-stone-500 text-lg italic mb-10 line-clamp-3 font-light leading-relaxed">${featured.content}</p>
                                    <span class="text-[10px] font-black uppercase tracking-widest text-midnight border-b-2 border-midnight pb-2">Leer Historia Completa</span>
                                </div>
                            </div>
                        </a>
                    `;

                    // Grid Posts (Rest)
                    const gridPosts = data.slice(1);
                    document.getElementById('blog-grid').innerHTML = gridPosts.map(p => `
                        <a href="articulo.php?id=${p.id}" class="group reveal">
                            <div class="aspect-[4/5] rounded-[3rem] overflow-hidden bg-stone-100 mb-8 shadow-lg">
                                <img src="${p.img_url}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000" referrerPolicy="no-referrer">
                            </div>
                            <span class="text-[9px] font-black uppercase tracking-[0.3em] text-stone-400 mb-4 block">${p.category}</span>
                            <h2 class="text-2xl serif font-bold mb-4 group-hover:text-[var(--pink-deep)] transition-colors leading-snug">${p.title}</h2>
                            <p class="text-stone-500 text-sm italic line-clamp-2 font-light mb-6">${p.content}</p>
                            <span class="text-[9px] font-black uppercase tracking-widest text-stone-300 group-hover:text-midnight transition-colors">Seguir leyendo &rarr;</span>
                        </a>
                    `).join('');

                    initReveal();
                } catch (e) {
                    console.error(e);
                }
            };

            function initReveal() {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => { if(entry.isIntersecting) entry.target.classList.add('active'); });
                }, { threshold: 0.1 });
                document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
            }

            loadBlog();
        });
    </script>
  </body>
</html>