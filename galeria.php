<?php 
require_once 'config.php'; 
$settings = getSiteSettings();
?>
<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Portafolio | Katy & Woof</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/x-icon" href="<?php echo $settings['site_favicon']; ?>">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;900&family=Lora:ital,wght@0,600;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="variables.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="main.css?v=<?php echo time(); ?>">
</head>
<body class="antialiased pt-32">
    <?php include 'header.php'; ?>

    <section class="max-w-7xl mx-auto py-24 px-8 min-h-screen">
      <div class="text-center mb-24 reveal">
        <span class="text-[var(--pink-deep)] font-black uppercase tracking-[0.5em] text-[10px] mb-6 block">Archivo Curado</span>
        <h1 class="text-5xl md:text-7xl font-bold serif italic mb-8">Archivo de <span class="text-stone-300">Obra.</span></h1>
        <p class="text-xl text-stone-500 max-w-xl mx-auto italic">Explora una colección donde cada trazo cuenta una historia de lealtad.</p>
      </div>
      <div id="dynamic-gallery" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 md:gap-12">
          <!-- JS Hydration -->
      </div>
    </section>

    <?php include 'footer.php'; ?>
    <script src="whatsapp.js?v=<?php echo time(); ?>"></script>
    <script src="js/gallery-page.js?v=<?php echo time(); ?>"></script>
</body>
</html>