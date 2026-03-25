<?php
require_once 'config.php';
$base = APP_URL;
$id = intval($_GET['id'] ?? 0);

if(!$id) { header("Location: $base/blog.php"); exit; }

try {
    $pdo = getDBConnection();
    $post = $pdo->prepare("SELECT * FROM blog_posts WHERE id = ?");
    $post->execute([$id]);
    $post = $post->fetch();
    if(!$post) { header("Location: $base/blog.php"); exit; }

    $related = $pdo->prepare("SELECT * FROM blog_posts WHERE id != ? ORDER BY created_at DESC LIMIT 3");
    $related->execute([$id]);
    $related = $related->fetchAll();
} catch(Exception $e) { header("Location: $base/blog.php"); exit; }

$pageTitle = htmlspecialchars($post['title']);
$pageDesc  = mb_substr(strip_tags($post['content'] ?? ''), 0, 160);
include 'header.php';
?>

<main style="padding-top:5rem;min-height:100vh">

  <div style="background:var(--dark);padding:4rem 0 3rem;border-bottom:1px solid rgba(255,255,255,0.06)">
    <div class="container" style="max-width:800px">
      <a href="<?php echo $base; ?>/blog.php" style="font-size:0.8rem;color:var(--mid);display:inline-flex;align-items:center;gap:0.4rem;margin-bottom:1.5rem">
        <i class="fa-solid fa-arrow-left"></i> Volver al blog
      </a>
      <div style="display:flex;align-items:center;gap:0.8rem;margin-bottom:1rem">
        <span class="badge badge-accent"><?php echo htmlspecialchars($post['category'] ?? 'General'); ?></span>
        <span style="font-size:0.75rem;color:var(--mid)"><?php echo date('d \d\e F \d\e Y', strtotime($post['created_at'])); ?></span>
      </div>
      <h1 class="reveal" style="font-size:clamp(1.6rem,4vw,2.4rem);line-height:1.2"><?php echo htmlspecialchars($post['title']); ?></h1>
    </div>
  </div>

  <section class="section">
    <div class="container" style="max-width:800px">
      <?php if($post['img_url'] && !str_contains($post['img_url'], 'placeholder')): ?>
      <div style="border-radius:var(--radius);overflow:hidden;margin-bottom:2.5rem;aspect-ratio:16/7">
        <img src="<?php echo htmlspecialchars($post['img_url']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>"
             style="width:100%;height:100%;object-fit:cover">
      </div>
      <?php endif; ?>

      <div class="blog-content reveal" style="line-height:1.9;font-size:1rem;color:var(--light)">
        <?php echo nl2br(htmlspecialchars($post['content'] ?? '')); ?>
      </div>

      <?php if($related): ?>
      <div style="margin-top:4rem;padding-top:3rem;border-top:1px solid rgba(255,255,255,0.06)">
        <h3 style="margin-bottom:1.5rem">Más artículos</h3>
        <div class="grid-3">
          <?php foreach($related as $r): ?>
          <a href="<?php echo $base; ?>/blog-post.php?id=<?php echo $r['id']; ?>"
             class="card" style="text-decoration:none;display:block">
            <div style="aspect-ratio:16/9;overflow:hidden;border-radius:0.5rem 0.5rem 0 0;background:var(--dark2)">
              <img src="<?php echo htmlspecialchars($r['img_url']); ?>" alt="<?php echo htmlspecialchars($r['title']); ?>"
                   style="width:100%;height:100%;object-fit:cover">
            </div>
            <div style="padding:1rem">
              <div style="font-size:0.7rem;color:var(--mid);margin-bottom:0.4rem"><?php echo date('d M Y', strtotime($r['created_at'])); ?></div>
              <div style="font-size:0.88rem;font-weight:600;color:var(--white)"><?php echo htmlspecialchars($r['title']); ?></div>
            </div>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </section>

</main>

<?php include 'footer.php'; ?>
