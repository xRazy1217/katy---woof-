<?php 
require_once 'config.php'; 
$settings = getSiteSettings();
$pdo = getDBConnection();

$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE id = ?");
$stmt->execute([$post_id]);
$post = $stmt->fetch();

if (!$post) {
    header("Location: blog.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo $post['title']; ?> | Katy & Woof Blog</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="variables.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="main.css?v=<?php echo time(); ?>">
    <link rel="icon" type="image/x-icon" href="<?php echo $settings['site_favicon']; ?>">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&family=Lora:ital,wght@0,600;1,400&display=swap" rel="stylesheet">
  </head>
  <body class="pt-32 antialiased bg-[#F9F7F5]">
    <?php include 'header.php'; ?>

    <article class="max-w-5xl mx-auto px-6 py-24 min-h-screen reveal active">
      <header class="mb-20 text-center max-w-3xl mx-auto">
        <span class="text-[10px] font-black uppercase tracking-[0.5em] text-[var(--pink-deep)] mb-8 block"><?php echo $post['category']; ?></span>
        <h1 class="text-5xl md:text-7xl font-bold serif italic leading-tight mb-12 text-[#1E2B3E]"><?php echo $post['title']; ?></h1>
        <div class="flex items-center justify-center gap-4 text-[10px] font-black uppercase tracking-widest text-stone-400">
            <span>By Katy & Woof Atelier</span>
            <span class="w-1 h-1 bg-stone-200 rounded-full"></span>
            <span><?php echo date("F j, Y", strtotime($post['created_at'])); ?></span>
        </div>
      </header>

      <div class="rounded-[4rem] overflow-hidden shadow-2xl aspect-[16/9] mb-24 bg-stone-100">
        <img src="<?php echo $post['img_url']; ?>" class="w-full h-full object-cover" alt="<?php echo $post['title']; ?>" referrerPolicy="no-referrer">
      </div>
      
      <div class="max-w-3xl mx-auto">
          <div class="article-content text-stone-600 font-light italic text-2xl leading-relaxed space-y-10">
              <?php 
                $paragraphs = explode("\n", $post['content']);
                foreach($paragraphs as $p) {
                    if(trim($p)) echo "<p>".nl2br($p)."</p>";
                }
              ?>
          </div>

          <div class="mt-32 pt-16 border-t border-stone-200 flex flex-col items-center">
              <div class="w-16 h-16 bg-white rounded-full shadow-sm flex items-center justify-center mb-8 overflow-hidden">
                  <img src="<?php echo $settings['site_logo']; ?>" class="h-8 object-contain opacity-40" alt="K&W">
              </div>
              <p class="text-stone-400 italic serif text-lg mb-12 text-center">"Cada trazo es un tributo a la lealtad eterna."</p>
              <a href="blog.php" class="px-10 py-4 border border-stone-200 rounded-full text-[10px] font-black uppercase tracking-widest text-stone-500 hover:bg-midnight hover:text-white hover:border-midnight transition-all">
                Volver al Journal
              </a>
          </div>
      </div>
    </article>

    <!-- More Stories Section -->
    <section class="bg-white py-32 px-8 mt-24">
        <div class="max-w-7xl mx-auto">
            <div class="flex justify-between items-end mb-16">
                <div>
                    <span class="text-[var(--pink-deep)] font-black uppercase tracking-[0.5em] text-[10px] mb-4 block">Sigue Explorando</span>
                    <h2 class="text-4xl font-bold serif italic">Más Crónicas.</h2>
                </div>
                <a href="blog.php" class="text-[10px] font-black uppercase tracking-widest text-stone-400 hover:text-midnight transition-colors">Ver Todo &rarr;</a>
            </div>
            <div class="grid md:grid-cols-3 gap-12">
                <?php
                    $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE id != ? ORDER BY RAND() LIMIT 3");
                    $stmt->execute([$post_id]);
                    $related = $stmt->fetchAll();
                    foreach($related as $r):
                ?>
                <a href="articulo.php?id=<?php echo $r['id']; ?>" class="group">
                    <div class="aspect-square rounded-[2.5rem] overflow-hidden bg-stone-100 mb-6 shadow-sm">
                        <img src="<?php echo $r['img_url']; ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" referrerPolicy="no-referrer">
                    </div>
                    <span class="text-[8px] font-black uppercase tracking-widest text-stone-400 mb-2 block"><?php echo $r['category']; ?></span>
                    <h3 class="text-xl serif font-bold group-hover:text-midnight transition-colors"><?php echo $r['title']; ?></h3>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <?php include 'footer.php'; ?>
    <script src="whatsapp.js?v=<?php echo time(); ?>"></script>
  </body>
</html>