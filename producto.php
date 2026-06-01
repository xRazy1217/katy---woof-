<?php
require_once 'config.php';
$base = APP_URL;

$id = intval($_GET['id'] ?? 0);
if (!$id) { header('Location: '.$base.'/catalogo.php'); exit; }

try {
  $pdo = getDBConnection();
  $stmt = $pdo->prepare("SELECT p.*, c.name as cat_name FROM products p LEFT JOIN product_categories c ON p.category_id=c.id WHERE p.id=? AND p.status='publish'");
  $stmt->execute([$id]);
  $p = $stmt->fetch();
  if (!$p) { header('Location: '.$base.'/catalogo.php'); exit; }

  // Relacionados
  $related = $pdo->prepare("SELECT * FROM products WHERE status='publish' AND id!=? AND category_id=? LIMIT 4");
  $related->execute([$id, $p['category_id']]);
  $related = $related->fetchAll();
  if (!$related) {
    $related = $pdo->prepare("SELECT * FROM products WHERE status='publish' AND id!=? LIMIT 4");
    $related->execute([$id]);
    $related = $related->fetchAll();
  }
} catch(Exception $e) { header('Location: '.$base.'/catalogo.php'); exit; }

function fmtP($n) { return '$' . number_format($n, 0, ',', '.'); }

$gallery = json_decode($p['gallery_images'] ?? '[]', true) ?: [];
$price   = $p['sale_price'] ?: $p['price'];

$pageTitle = htmlspecialchars($p['name']);
$pageDesc  = htmlspecialchars(strip_tags($p['short_description'] ?? $p['description'] ?? ''));
include 'header.php';
?>

<main style="padding-top:5rem;min-height:100vh">
  <div class="container" style="padding-top:3rem;padding-bottom:5rem">

    <!-- BREADCRUMB -->
    <nav aria-label="Breadcrumb" style="margin-bottom:2.5rem">
      <ol style="display:flex;align-items:center;gap:0.5rem;font-size:0.8rem;color:var(--mid);list-style:none;padding:0;margin:0">
        <li><a href="<?php echo $base; ?>/" class="breadcrumb-link">Inicio</a></li>
        <li aria-hidden="true">/</li>
        <li><a href="<?php echo $base; ?>/catalogo.php" class="breadcrumb-link">Catálogo</a></li>
        <li aria-hidden="true">/</li>
        <li><span style="color:var(--white)"><?php echo htmlspecialchars($p['name']); ?></span></li>
      </ol>
    </nav>

    <!-- PRODUCTO PRINCIPAL -->
    <div class="grid-2" style="gap:4rem;margin-bottom:5rem">

      <!-- GALERÍA -->
      <div>
        <div style="border-radius:var(--radius);overflow:hidden;background:var(--dark2);aspect-ratio:1;position:relative;border:1px solid rgba(255,255,255,0.06)" id="mainImgWrap">
          <img id="mainImg"
               src="<?php echo $p['image_url'] ?: $base.'/uploads/placeholder-product.svg'; ?>"
               alt="<?php echo htmlspecialchars($p['name']); ?>"
               style="width:100%;height:100%;object-fit:cover"/>
          <?php if($p['sale_price']): ?>
          <div style="position:absolute;top:1rem;left:1rem"><span class="badge badge-accent">Oferta</span></div>
          <?php endif; ?>
        </div>
        <?php if($gallery): ?>
        <div id="galleryThumbnails" style="display:flex;gap:0.8rem;margin-top:1rem;overflow-x:auto;padding-bottom:0.5rem" role="group" aria-label="Product gallery">
          <button class="gallery-thumbnail gallery-thumbnail--active"
                  data-src="<?php echo $p['image_url']; ?>"
                  style="width:72px;height:72px;border-radius:0.5rem;overflow:hidden;cursor:pointer;border:2px solid var(--accent);flex-shrink:0;background:none;padding:0;margin:0"
                  aria-label="View main product image">
            <img src="<?php echo $p['image_url']; ?>" style="width:100%;height:100%;object-fit:cover" alt=""/>
          </button>
          <?php foreach($gallery as $idx => $img): ?>
          <button class="gallery-thumbnail"
                  data-src="<?php echo $img; ?>"
                  style="width:72px;height:72px;border-radius:0.5rem;overflow:hidden;cursor:pointer;border:2px solid transparent;flex-shrink:0;transition:border-color 0.2s;background:none;padding:0;margin:0"
                  aria-label="View product image <?php echo $idx + 2; ?>">
            <img src="<?php echo $img; ?>" style="width:100%;height:100%;object-fit:cover" alt=""/>
          </button>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>

      <!-- INFO -->
      <div>
        <div style="margin-bottom:0.5rem">
          <span class="badge badge-accent"><?php echo htmlspecialchars($p['cat_name'] ?? 'Retrato'); ?></span>
          <?php if($p['stock_status']==='instock'): ?>
          <span class="badge" style="background:rgba(34,197,94,0.1);color:#22c55e;border:1px solid rgba(34,197,94,0.2);margin-left:0.4rem">En stock</span>
          <?php elseif($p['stock_status']==='outofstock'): ?>
          <span class="badge badge-dark" style="margin-left:0.4rem">Agotado</span>
          <?php endif; ?>
        </div>

        <h1 style="font-size:clamp(1.8rem,3vw,2.5rem);margin:0.8rem 0 1rem"><?php echo htmlspecialchars($p['name']); ?></h1>

        <?php if($p['short_description']): ?>
        <p style="color:var(--mid);line-height:1.8;margin-bottom:1.5rem"><?php echo htmlspecialchars($p['short_description']); ?></p>
        <?php endif; ?>

        <!-- PRECIO -->
        <div style="display:flex;align-items:baseline;gap:1rem;margin-bottom:2rem">
          <span style="font-family:'Space Mono',monospace;font-size:2.2rem;font-weight:700;color:var(--accent)"><?php echo fmtP($price); ?></span>
          <?php if($p['sale_price']): ?>
          <span style="font-family:'Space Mono',monospace;font-size:1.2rem;color:var(--mid);text-decoration:line-through"><?php echo fmtP($p['price']); ?></span>
          <span class="badge badge-accent"><?php echo round((1-$p['sale_price']/$p['price'])*100); ?>% OFF</span>
          <?php endif; ?>
        </div>

        <!-- CANTIDAD -->
        <?php if($p['stock_status'] !== 'outofstock'): ?>
        <fieldset id="quantitySelector" style="margin-bottom:1.5rem;border:none;padding:0;margin:0">
          <legend style="font-size:0.9rem;font-weight:500;color:var(--white);margin-bottom:0.4rem">Cantidad</legend>
          <div style="display:flex;align-items:center;gap:0.8rem;margin-top:0.4rem">
            <button id="qtyDecrement"
                    data-action="decrease"
                    class="qty-btn-product"
                    style="width:40px;height:40px;border-radius:50%;background:var(--dark2);border:1px solid rgba(255,255,255,0.1);color:var(--white);font-size:1.2rem;cursor:pointer;transition:all 0.2s"
                    aria-label="Decrease quantity">−</button>
            <span id="qtyDisplay"
                  style="font-family:'Space Mono',monospace;font-size:1.1rem;font-weight:700;min-width:30px;text-align:center"
                  aria-live="polite"
                  aria-atomic="true">1</span>
            <button id="qtyIncrement"
                    data-action="increase"
                    class="qty-btn-product"
                    style="width:40px;height:40px;border-radius:50%;background:var(--dark2);border:1px solid rgba(255,255,255,0.1);color:var(--white);font-size:1.2rem;cursor:pointer;transition:all 0.2s"
                    aria-label="Increase quantity">+</button>
            <?php if($p['stock_quantity'] > 0): ?>
            <span style="font-size:0.78rem;color:var(--mid)" aria-label="Stock available"><?php echo $p['stock_quantity']; ?> disponibles</span>
            <?php endif; ?>
          </div>
        </fieldset>

        <!-- BOTONES -->
        <div style="display:flex;gap:1rem;flex-wrap:wrap;margin-bottom:2rem">
          <button id="addToCartBtn"
                  data-product-id="<?php echo $p['id']; ?>"
                  data-qty-display="qtyDisplay"
                  class="btn btn-primary btn-lg"
                  style="flex:1;justify-content:center">
            <i class="fa-solid fa-bag-shopping"></i> Agregar al carrito
          </button>
          <a href="https://wa.me/56976886481?text=Hola!%20Me%20interesa%20el%20producto:%20<?php echo urlencode($p['name']); ?>"
             target="_blank"
             rel="noopener noreferrer"
             class="btn btn-outline"
             aria-label="Contact us on WhatsApp about this product">
            <i class="fa-brands fa-whatsapp"></i>
          </a>
        </div>
        <?php else: ?>
        <div class="glass" style="padding:1rem 1.5rem;margin-bottom:2rem;border-color:rgba(255,255,255,0.05)">
          <p style="font-size:0.88rem;color:var(--mid)">Este producto está agotado. Contáctanos para consultar disponibilidad.</p>
          <a href="https://wa.me/56976886481" target="_blank" class="btn btn-outline btn-sm" style="margin-top:0.8rem">
            <i class="fa-brands fa-whatsapp"></i> Consultar
          </a>
        </div>
        <?php endif; ?>

        <!-- META -->
        <div class="divider"></div>
        <div style="display:flex;flex-direction:column;gap:0.6rem;font-size:0.82rem;color:var(--mid)">
          <?php if($p['sku']): ?>
          <div>SKU: <span style="color:var(--light)"><?php echo htmlspecialchars($p['sku']); ?></span></div>
          <?php endif; ?>
          <?php if($p['cat_name']): ?>
          <div>Categoría: <a href="<?php echo $base; ?>/catalogo.php?cat=<?php echo $p['category_id']; ?>" style="color:var(--accent)"><?php echo htmlspecialchars($p['cat_name']); ?></a></div>
          <?php endif; ?>
          <div>Envío: <span style="color:var(--light)">A todo Chile</span></div>
        </div>
      </div>
    </div>

    <!-- DESCRIPCIÓN -->
    <?php if($p['description']): ?>
    <div style="margin-bottom:5rem">
      <div style="border-bottom:1px solid rgba(255,255,255,0.06);margin-bottom:2rem;padding-bottom:1rem">
        <span style="font-size:0.85rem;font-weight:600;color:var(--white);border-bottom:2px solid var(--accent);padding-bottom:1rem">Descripción</span>
      </div>
      <div style="max-width:720px;color:var(--mid);line-height:1.9;font-size:0.95rem">
        <?php echo nl2br(htmlspecialchars($p['description'])); ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- RELACIONADOS -->
    <?php if($related): ?>
    <section aria-labelledby="relatedProductsTitle">
      <h2 id="relatedProductsTitle" style="font-size:1.5rem;margin-bottom:2rem">También te puede <span class="accent">interesar</span></h2>
      <div class="grid-4">
        <?php foreach($related as $r): ?>
        <article class="card product-card">
          <div class="product-card-img">
            <a href="<?php echo $base; ?>/producto.php?id=<?php echo $r['id']; ?>"
               class="product-card-link"
               aria-label="View <?php echo htmlspecialchars($r['name']); ?>">
              <img src="<?php echo $r['image_url'] ?: $base.'/uploads/placeholder-product.svg'; ?}"
                   alt="<?php echo htmlspecialchars($r['name']); ?>"
                   loading="lazy"/>
            </a>
            <div class="product-card-overlay">
              <button class="btn btn-ghost btn-sm related-add-cart"
                      data-product-id="<?php echo $r['id']; ?>"
                      aria-label="Add <?php echo htmlspecialchars($r['name']); ?> to cart">
                <i class="fa-solid fa-bag-shopping"></i> Agregar
              </button>
            </div>
          </div>
          <div class="product-card-body">
            <div class="product-card-name"><?php echo htmlspecialchars($r['name']); ?></div>
            <div class="product-card-footer">
              <span class="product-card-price"><?php echo fmtP($r['sale_price'] ?: $r['price']); ?></span>
              <button class="add-cart-btn related-add-cart"
                      data-product-id="<?php echo $r['id']; ?>"
                      aria-label="Quick add <?php echo htmlspecialchars($r['name']); ?>">+</button>
            </div>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>
  </div>
</main>

<style>
.breadcrumb-link {
  color: var(--mid);
  transition: color var(--transition-base);
}

.breadcrumb-link:hover,
.breadcrumb-link:focus-visible {
  color: var(--white);
}

.qty-btn-product {
  transition: border-color var(--transition-base);
}

.qty-btn-product:hover,
.qty-btn-product:focus-visible {
  border-color: var(--accent);
  outline: none;
}

.gallery-thumbnail {
  transition: border-color var(--transition-base);
}

.gallery-thumbnail:focus-visible {
  outline: 2px solid var(--accent);
  outline-offset: 2px;
}

.product-card-link {
  display: block;
  width: 100%;
  height: 100%;
  cursor: pointer;
}

.product-card-link:focus-visible {
  outline: 2px solid var(--accent);
}
</style>

<script>
/* ═══════════════════════════════════════════════════════════════════════════
   Product Page - Event Handlers
   ═══════════════════════════════════════════════════════════════════════════ */

let qty = 1;
const productId = <?php echo $p['id']; ?>;

// Gallery image selection
const galleryThumbnails = document.getElementById('galleryThumbnails');
if (galleryThumbnails) {
  galleryThumbnails.addEventListener('click', (e) => {
    const btn = e.target.closest('.gallery-thumbnail');
    if (!btn) return;

    // Update main image
    const src = btn.dataset.src;
    document.getElementById('mainImg').src = src;

    // Update active state
    document.querySelectorAll('.gallery-thumbnail').forEach(t => {
      t.style.borderColor = 'transparent';
      t.classList.remove('gallery-thumbnail--active');
    });
    btn.style.borderColor = 'var(--accent)';
    btn.classList.add('gallery-thumbnail--active');
  });
}

// Quantity selector
const qtyDecrement = document.getElementById('qtyDecrement');
const qtyIncrement = document.getElementById('qtyIncrement');
const qtyDisplay = document.getElementById('qtyDisplay');

if (qtyDecrement) {
  qtyDecrement.addEventListener('click', () => {
    qty = Math.max(1, qty - 1);
    qtyDisplay.textContent = qty;
  });
}

if (qtyIncrement) {
  qtyIncrement.addEventListener('click', () => {
    qty = Math.max(1, qty + 1);
    qtyDisplay.textContent = qty;
  });
}

// Add to cart - main button
const addToCartBtn = document.getElementById('addToCartBtn');
if (addToCartBtn) {
  addToCartBtn.addEventListener('click', () => {
    CartManager.add(productId, qty);
  });
}

// Add to cart - related products
document.querySelectorAll('.related-add-cart').forEach(btn => {
  btn.addEventListener('click', (e) => {
    e.stopPropagation();
    const productId = parseInt(btn.dataset.productId, 10);
    CartManager.add(productId, 1);
  });
});
</script>

<?php include 'footer.php'; ?>
