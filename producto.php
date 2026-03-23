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
    <div style="display:flex;align-items:center;gap:0.5rem;font-size:0.8rem;color:var(--mid);margin-bottom:2.5rem">
      <a href="<?php echo $base; ?>/" style="color:var(--mid);transition:color 0.2s" onmouseover="this.style.color='var(--white)'" onmouseout="this.style.color='var(--mid)'">Inicio</a>
      <span>/</span>
      <a href="<?php echo $base; ?>/catalogo.php" style="color:var(--mid);transition:color 0.2s" onmouseover="this.style.color='var(--white)'" onmouseout="this.style.color='var(--mid)'">Catálogo</a>
      <span>/</span>
      <span style="color:var(--white)"><?php echo htmlspecialchars($p['name']); ?></span>
    </div>

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
        <div style="display:flex;gap:0.8rem;margin-top:1rem;overflow-x:auto;padding-bottom:0.5rem">
          <div style="width:72px;height:72px;border-radius:0.5rem;overflow:hidden;cursor:pointer;border:2px solid var(--accent);flex-shrink:0"
               onclick="setImg('<?php echo $p['image_url']; ?>', this)">
            <img src="<?php echo $p['image_url']; ?>" style="width:100%;height:100%;object-fit:cover"/>
          </div>
          <?php foreach($gallery as $img): ?>
          <div style="width:72px;height:72px;border-radius:0.5rem;overflow:hidden;cursor:pointer;border:2px solid transparent;flex-shrink:0;transition:border-color 0.2s"
               onclick="setImg('<?php echo $img; ?>', this)">
            <img src="<?php echo $img; ?>" style="width:100%;height:100%;object-fit:cover"/>
          </div>
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
        <div style="margin-bottom:1.5rem">
          <label>Cantidad</label>
          <div style="display:flex;align-items:center;gap:0.8rem;margin-top:0.4rem">
            <button onclick="changeQty(-1)" style="width:40px;height:40px;border-radius:50%;background:var(--dark2);border:1px solid rgba(255,255,255,0.1);color:var(--white);font-size:1.2rem;cursor:pointer;transition:all 0.2s" onmouseover="this.style.borderColor='var(--accent)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.1)'">−</button>
            <span id="qtyDisplay" style="font-family:'Space Mono',monospace;font-size:1.1rem;font-weight:700;min-width:30px;text-align:center">1</span>
            <button onclick="changeQty(1)"  style="width:40px;height:40px;border-radius:50%;background:var(--dark2);border:1px solid rgba(255,255,255,0.1);color:var(--white);font-size:1.2rem;cursor:pointer;transition:all 0.2s" onmouseover="this.style.borderColor='var(--accent)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.1)'">+</button>
            <?php if($p['stock_quantity'] > 0): ?>
            <span style="font-size:0.78rem;color:var(--mid)"><?php echo $p['stock_quantity']; ?> disponibles</span>
            <?php endif; ?>
          </div>
        </div>

        <!-- BOTONES -->
        <div style="display:flex;gap:1rem;flex-wrap:wrap;margin-bottom:2rem">
          <button class="btn btn-primary btn-lg" onclick="addToCartProduct()" style="flex:1;justify-content:center">
            <i class="fa-solid fa-bag-shopping"></i> Agregar al carrito
          </button>
          <a href="https://wa.me/56976886481?text=Hola!%20Me%20interesa%20el%20producto:%20<?php echo urlencode($p['name']); ?>" target="_blank" class="btn btn-outline">
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
    <div>
      <h2 style="font-size:1.5rem;margin-bottom:2rem">También te puede <span class="accent">interesar</span></h2>
      <div class="grid-4">
        <?php foreach($related as $r): ?>
        <div class="card product-card" onclick="window.location='<?php echo $base; ?>/producto.php?id=<?php echo $r['id']; ?>'">
          <div class="product-card-img">
            <img src="<?php echo $r['image_url'] ?: $base.'/uploads/placeholder-product.svg'; ?>" alt="<?php echo htmlspecialchars($r['name']); ?>" loading="lazy"/>
            <div class="product-card-overlay">
              <button class="btn btn-ghost btn-sm" onclick="event.stopPropagation();CartManager.add(<?php echo $r['id']; ?>)">
                <i class="fa-solid fa-bag-shopping"></i> Agregar
              </button>
            </div>
          </div>
          <div class="product-card-body">
            <div class="product-card-name"><?php echo htmlspecialchars($r['name']); ?></div>
            <div class="product-card-footer">
              <span class="product-card-price"><?php echo fmtP($r['sale_price'] ?: $r['price']); ?></span>
              <button class="add-cart-btn" onclick="event.stopPropagation();CartManager.add(<?php echo $r['id']; ?>)">+</button>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</main>

<script>
let qty = 1;
const productId = <?php echo $p['id']; ?>;

function changeQty(delta) {
  qty = Math.max(1, qty + delta);
  document.getElementById('qtyDisplay').textContent = qty;
}
function addToCartProduct() {
  CartManager.add(productId, qty);
}
function setImg(src, el) {
  document.getElementById('mainImg').src = src;
  document.querySelectorAll('[onclick^="setImg"]').forEach(e => e.style.borderColor = 'transparent');
  el.style.borderColor = 'var(--accent)';
}
</script>

<?php include 'footer.php'; ?>
