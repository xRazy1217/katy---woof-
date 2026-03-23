<?php
require_once 'config.php';
require_once 'api/database.php';
Database::runSetup();
$settings = getSiteSettings();
$base = APP_URL;

// Últimos productos destacados
try {
  $pdo = getDBConnection();
  $featured = $pdo->query("SELECT * FROM products WHERE status='publish' AND featured=1 ORDER BY created_at DESC LIMIT 4")->fetchAll();
  if (!$featured) $featured = $pdo->query("SELECT * FROM products WHERE status='publish' ORDER BY created_at DESC LIMIT 4")->fetchAll();
} catch(Exception $e) { $featured = []; }

$pageTitle = 'Inicio';
$pageDesc  = 'Retratos artísticos coloridos de mascotas. Arte con propósito desde La Serena, Chile.';
include 'header.php';
?>

<!-- ══ HERO ══ -->
<section class="hero noise">
  <div class="hero-bg"></div>
  <div class="hero-grid-lines"></div>
  <div class="container">
    <div class="grid-2" style="min-height:100vh;padding:8rem 0 4rem">
      <div class="hero-content">
        <div class="hero-eyebrow reveal">
          <div class="hero-eyebrow-line"></div>
          <span class="label">Arte · Mascotas · Chile</span>
        </div>
        <h1 class="reveal delay-1">
          Tu mascota,<br><em>eternizada</em><br>en arte.
        </h1>
        <p class="hero-desc reveal delay-2">
          Retratos artísticos coloridos que capturan la esencia, la mirada y la alegría de tu compañero más fiel. Hechos con amor desde La Serena.
        </p>
        <div class="hero-actions reveal delay-3">
          <a href="<?php echo $base; ?>/catalogo.php" class="btn btn-primary btn-lg">
            Ver Catálogo <i class="fa-solid fa-arrow-right"></i>
          </a>
          <a href="<?php echo $base; ?>/nosotros.php" class="btn btn-outline btn-lg">
            Nuestra Historia
          </a>
        </div>
        <div class="hero-stats reveal delay-4">
          <div>
            <div class="hero-stat-num">10+</div>
            <div class="hero-stat-label">Años de experiencia</div>
          </div>
          <div>
            <div class="hero-stat-num">500+</div>
            <div class="hero-stat-label">Retratos creados</div>
          </div>
          <div>
            <div class="hero-stat-num">100%</div>
            <div class="hero-stat-label">Personalizado</div>
          </div>
        </div>
      </div>
      <div class="hero-image-wrap reveal delay-2">
        <div class="hero-image-frame animate-float">
          <img src="<?php echo $base; ?>/img/placeholder.svg" alt="Retrato artístico de mascota" id="heroImg"/>
          <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;z-index:2">
            <div style="text-align:center;color:rgba(255,255,255,0.2)">
              <i class="fa-solid fa-paw" style="font-size:4rem"></i>
              <p style="margin-top:1rem;font-size:0.8rem;letter-spacing:0.1em">TU LOGO AQUÍ</p>
            </div>
          </div>
        </div>
        <div class="hero-badge">
          <div class="hero-badge-num">♥</div>
          <div class="hero-badge-text">Arte con propósito</div>
        </div>
        <div class="hero-dot"></div>
      </div>
    </div>
  </div>
</section>

<!-- ══ PROPÓSITO ══ -->
<section class="section" style="background:var(--dark)">
  <div class="container">
    <div class="grid-2" style="gap:5rem">
      <div>
        <div class="reveal">
          <span class="label">Nuestra historia</span>
          <h2 style="margin-top:1rem;margin-bottom:1.5rem">
            Nace desde el<br><span class="accent">amor y la resiliencia</span>
          </h2>
        </div>
        <p class="reveal delay-1" style="margin-bottom:1.2rem;line-height:1.9">
          KATY & WOOF nace desde una historia de amor, resiliencia y conexión con los animales. Durante una etapa muy difícil de mi vida enfrenté dos cánceres. En ese proceso, mis mascotas estuvieron siempre a mi lado, acompañándome con su cariño incondicional.
        </p>
        <p class="reveal delay-2" style="margin-bottom:2rem;line-height:1.9">
          A partir de esa experiencia nació la necesidad de agradecer y honrar el amor de los animales a través del arte. Así comencé a pintar retratos coloridos de mascotas, capturando su esencia y la alegría que entregan a nuestras vidas.
        </p>
        <div class="reveal delay-3" style="display:flex;gap:1rem;flex-wrap:wrap">
          <div class="glass" style="padding:1.2rem 1.8rem;text-align:center">
            <div style="font-family:'Space Mono',monospace;font-size:1.6rem;font-weight:700;color:var(--accent)">Katherine</div>
            <div style="font-size:0.75rem;color:var(--mid);margin-top:0.2rem;letter-spacing:0.05em">Fundadora · Katy</div>
          </div>
          <div class="glass" style="padding:1.2rem 1.8rem;text-align:center">
            <div style="font-family:'Space Mono',monospace;font-size:1.6rem;font-weight:700;color:var(--accent)">10 años</div>
            <div style="font-size:0.75rem;color:var(--mid);margin-top:0.2rem;letter-spacing:0.05em">De trayectoria</div>
          </div>
        </div>
        <div class="reveal delay-4" style="margin-top:2rem">
          <a href="<?php echo $base; ?>/nosotros.php" class="btn btn-outline">Conocer más <i class="fa-solid fa-arrow-right"></i></a>
        </div>
      </div>
      <div class="reveal delay-2">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
          <div class="card" style="aspect-ratio:3/4;background:var(--dark2);display:flex;align-items:center;justify-content:center;color:var(--gray2)">
            <i class="fa-solid fa-paw" style="font-size:2rem"></i>
          </div>
          <div style="display:flex;flex-direction:column;gap:1rem">
            <div class="card" style="flex:1;background:var(--dark2);display:flex;align-items:center;justify-content:center;color:var(--gray2)">
              <i class="fa-solid fa-palette" style="font-size:1.5rem"></i>
            </div>
            <div class="card" style="flex:1;background:var(--accent-dim);border-color:rgba(232,57,154,0.2);padding:1.5rem">
              <div style="font-size:0.75rem;color:var(--accent);letter-spacing:0.1em;text-transform:uppercase;margin-bottom:0.5rem">Misión</div>
              <p style="font-size:0.85rem;line-height:1.6;color:var(--light)">Crear arte que celebre el vínculo entre las personas y los animales.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ══ PROCESO ══ -->
<section class="section">
  <div class="container">
    <div style="text-align:center;margin-bottom:4rem">
      <span class="label reveal">Cómo funciona</span>
      <h2 style="margin-top:1rem" class="reveal delay-1">Proceso <span class="accent">simple</span> y transparente</h2>
    </div>
    <div class="grid-4" style="position:relative">
      <?php
      $steps = [
        ['icon'=>'fa-image',          'num'=>'01', 'title'=>'Envío de fotos', 'desc'=>'Envíanos hasta 3 fotos de tu mascota al correo o WhatsApp.'],
        ['icon'=>'fa-magnifying-glass','num'=>'02', 'title'=>'Selección',     'desc'=>'Elegimos la mejor fotografía para crear el retrato.'],
        ['icon'=>'fa-paintbrush',     'num'=>'03', 'title'=>'Creación',       'desc'=>'Realizamos un retrato artístico colorido y personalizado.'],
        ['icon'=>'fa-box-open',       'num'=>'04', 'title'=>'Entrega',        'desc'=>'Recibe tu retrato digital, en lienzo físico o ambas opciones.'],
      ];
      foreach($steps as $i => $s): ?>
      <div class="reveal" style="transition-delay:<?php echo $i*0.1; ?>s">
        <div class="glass" style="padding:2rem;height:100%;position:relative">
          <div style="font-family:'Space Mono',monospace;font-size:0.65rem;color:var(--accent);letter-spacing:0.2em;margin-bottom:1rem"><?php echo $s['num']; ?></div>
          <div style="width:48px;height:48px;border-radius:50%;background:var(--accent-dim);border:1px solid rgba(232,57,154,0.2);display:flex;align-items:center;justify-content:center;margin-bottom:1.2rem">
            <i class="fa-solid <?php echo $s['icon']; ?>" style="color:var(--accent)"></i>
          </div>
          <h3 style="font-size:1rem;margin-bottom:0.6rem"><?php echo $s['title']; ?></h3>
          <p style="font-size:0.85rem;line-height:1.7;color:var(--mid)"><?php echo $s['desc']; ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="reveal" style="text-align:center;margin-top:2.5rem">
      <div class="glass" style="display:inline-flex;align-items:center;gap:0.8rem;padding:0.8rem 1.8rem">
        <i class="fa-regular fa-clock" style="color:var(--accent)"></i>
        <span style="font-size:0.85rem;color:var(--light)">Tiempo total estimado: <strong style="color:var(--white)">hasta 7 días hábiles</strong></span>
      </div>
    </div>
  </div>
</section>

<!-- ══ PRODUCTOS DESTACADOS ══ -->
<section class="section" style="background:var(--dark)">
  <div class="container">
    <div style="display:flex;align-items:flex-end;justify-content:space-between;margin-bottom:3rem;flex-wrap:wrap;gap:1rem">
      <div>
        <span class="label reveal">Tienda</span>
        <h2 style="margin-top:0.8rem" class="reveal delay-1">Productos <span class="accent">destacados</span></h2>
      </div>
      <a href="<?php echo $base; ?>/catalogo.php" class="btn btn-outline reveal">Ver todo <i class="fa-solid fa-arrow-right"></i></a>
    </div>
    <?php if($featured): ?>
    <div class="grid-4">
      <?php foreach($featured as $i => $p): ?>
      <div class="card product-card reveal" style="transition-delay:<?php echo $i*0.1; ?>s" onclick="window.location='<?php echo $base; ?>/producto.php?id=<?php echo $p['id']; ?>'">
        <div class="product-card-img">
          <img src="<?php echo $p['image_url'] ?: $base.'/uploads/placeholder-product.svg'; ?>" alt="<?php echo htmlspecialchars($p['name']); ?>"/>
          <?php if($p['sale_price']): ?>
          <div style="position:absolute;top:0.8rem;left:0.8rem"><span class="badge badge-accent">Oferta</span></div>
          <?php endif; ?>
          <div class="product-card-overlay">
            <button class="btn btn-ghost btn-sm" onclick="event.stopPropagation();CartManager.add(<?php echo $p['id']; ?>)">
              <i class="fa-solid fa-bag-shopping"></i> Agregar
            </button>
          </div>
        </div>
        <div class="product-card-body">
          <div class="product-card-cat"><?php echo htmlspecialchars($p['service_category'] ?? 'Retrato'); ?></div>
          <div class="product-card-name"><?php echo htmlspecialchars($p['name']); ?></div>
          <div class="product-card-footer">
            <div>
              <span class="product-card-price"><?php echo formatPricePHP($p['sale_price'] ?: $p['price']); ?></span>
              <?php if($p['sale_price']): ?>
              <span class="product-card-price-old"><?php echo formatPricePHP($p['price']); ?></span>
              <?php endif; ?>
            </div>
            <button class="add-cart-btn" onclick="event.stopPropagation();CartManager.add(<?php echo $p['id']; ?>)">+</button>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div style="text-align:center;padding:4rem;color:var(--mid)">
      <i class="fa-solid fa-paw" style="font-size:3rem;opacity:0.2;margin-bottom:1rem;display:block"></i>
      <p>Próximamente productos disponibles</p>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- ══ CTA ══ -->
<section class="section-sm" style="background:linear-gradient(135deg,rgba(232,57,154,0.08) 0%,transparent 60%)">
  <div class="container" style="text-align:center">
    <span class="label reveal">¿Listo para comenzar?</span>
    <h2 style="margin:1rem 0 1.5rem" class="reveal delay-1">Crea el retrato de<br><span class="accent">tu mascota hoy</span></h2>
    <p style="max-width:480px;margin:0 auto 2.5rem;color:var(--mid)" class="reveal delay-2">
      Contáctanos por WhatsApp o correo y comenzamos el proceso. Envío a todo Chile.
    </p>
    <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap" class="reveal delay-3">
      <a href="https://wa.me/56976886481?text=Hola!%20Quiero%20un%20retrato%20de%20mi%20mascota" target="_blank" class="btn btn-primary btn-lg">
        <i class="fa-brands fa-whatsapp"></i> WhatsApp
      </a>
      <a href="<?php echo $base; ?>/catalogo.php" class="btn btn-outline btn-lg">
        Ver Catálogo
      </a>
    </div>
  </div>
</section>

<?php
function formatPricePHP($n) {
  return '$' . number_format($n, 0, ',', '.');
}
include 'footer.php';
?>
