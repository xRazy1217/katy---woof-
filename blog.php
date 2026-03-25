<?php
require_once 'config.php';
$pageTitle = 'Blog';
$pageDesc  = 'Artículos sobre arte, mascotas y el mundo de Katy & Woof.';
$base = APP_URL;

try {
    $pdo = getDBConnection();
    $posts = $pdo->query("SELECT * FROM blog_posts ORDER BY created_at DESC")->fetchAll();
} catch(Exception $e) { $posts = []; }

include 'header.php';
?>

<main style="padding-top:5rem;min-height:100vh">

  <div style="background:var(--dark);padding:4rem 0 3rem;border-bottom:1px solid rgba(255,255,255,0.06)">
    <div class="container">
      <span class="label reveal">Nuestro Blog</span>
      <h1 style="margin-top:0.8rem" class="reveal delay-1">Historias & <span class="accent">Consejos</span></h1>
      <p style="color:var(--mid);margin-top:0.5rem" class="reveal delay-2">Arte, mascotas y todo lo que nos inspira.</p>
    </div>
  </div>

  <section class="section">
    <div class="container">
      <?php if($posts): ?>
      <div class="grid-3">
        <?php foreach($posts as $i => $p): ?>
        <a href="<?php echo $base; ?>/blog-post.php?id=<?php echo $p['id']; ?>"
           class="card reveal" style="transition-delay:<?php echo ($i%3)*0.1; ?>s;text-decoration:none;display:block">
          <div style="aspect-ratio:16/9;overflow:hidden;border-radius:0.5rem 0.5rem 0 0;background:var(--dark2)">
            <img src="<?php echo htmlspecialchars($p['img_url']); ?>" alt="<?php echo htmlspecialchars($p['title']); ?>"
                 style="width:100%;height:100%;object-fit:cover;transition:transform 0.4s ease"
                 onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
          </div>
          <div style="padding:1.5rem">
            <div style="display:flex;align-items:center;gap:0.8rem;margin-bottom:0.8rem">
              <span class="badge badge-accent"><?php echo htmlspecialchars($p['category'] ?? 'General'); ?></span>
              <span style="font-size:0.72rem;color:var(--mid)"><?php echo date('d M Y', strtotime($p['created_at'])); ?></span>
            </div>
            <h3 style="font-size:1rem;margin-bottom:0.6rem;color:var(--white)"><?php echo htmlspecialchars($p['title']); ?></h3>
            <p style="font-size:0.83rem;color:var(--mid);line-height:1.6">
              <?php echo mb_substr(strip_tags($p['content'] ?? ''), 0, 120) . '...'; ?>
            </p>
            <div style="margin-top:1rem;font-size:0.78rem;color:var(--accent);font-weight:600">
              Leer más <i class="fa-solid fa-arrow-right" style="font-size:0.7rem"></i>
            </div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
      <?php else: ?>
      <div style="text-align:center;padding:5rem;color:var(--mid)">
        <i class="fa-solid fa-pen-nib" style="font-size:3rem;opacity:0.2;display:block;margin-bottom:1rem"></i>
        <p>Próximamente publicaremos artículos aquí.</p>
      </div>
      <?php endif; ?>
    </div>
  </section>

</main>

<?php include 'footer.php'; ?>
