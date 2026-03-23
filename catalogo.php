<?php
require_once 'config.php';
require_once 'api/database.php';
Database::runSetup();

// Filtros
$search   = trim($_GET['q'] ?? '');
$cat      = intval($_GET['cat'] ?? 0);
$tipo     = $_GET['tipo'] ?? '';
$sort     = $_GET['sort'] ?? 'newest';
$page     = max(1, intval($_GET['page'] ?? 1));
$perPage  = 12;
$offset   = ($page - 1) * $perPage;

try {
  $pdo = getDBConnection();

  // Categorías
  $cats = $pdo->query("SELECT * FROM product_categories WHERE status='active' ORDER BY display_order")->fetchAll();

  // Query base
  $where = ["p.status='publish'"];
  $params = [];

  if ($search) { $where[] = "(p.name LIKE ? OR p.description LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
  if ($cat)    { $where[] = "p.category_id = ?"; $params[] = $cat; }
  if ($tipo === 'servicio') { $where[] = "p.product_type = 'service'"; }
  if ($tipo === 'producto') { $where[] = "p.product_type = 'physical'"; }

  $whereStr = implode(' AND ', $where);

  $orderMap = ['newest'=>'p.created_at DESC','price_asc'=>'p.price ASC','price_desc'=>'p.price DESC','name'=>'p.name ASC'];
  $orderBy  = $orderMap[$sort] ?? 'p.created_at DESC';

  // Total
  $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM products p WHERE $whereStr");
  $stmtCount->execute($params);
  $total = $stmtCount->fetchColumn();
  $totalPages = ceil($total / $perPage);

  // Productos
  $stmt = $pdo->prepare("SELECT p.*, c.name as cat_name FROM products p LEFT JOIN product_categories c ON p.category_id=c.id WHERE $whereStr ORDER BY $orderBy LIMIT $perPage OFFSET $offset");
  $stmt->execute($params);
  $products = $stmt->fetchAll();

} catch(Exception $e) { $products = []; $cats = []; $total = 0; $totalPages = 1; }

function fmtPrice($n) { return '$' . number_format($n, 0, ',', '.'); }

$pageTitle = 'Catálogo';
$pageDesc  = 'Explora nuestros retratos artísticos y productos personalizados de mascotas.';
$base = APP_URL;
$extraCss  = '<link rel="stylesheet" href="'.$base.'/css/catalogo.css"/>';
include 'header.php';
?>

<main style="padding-top:5rem;min-height:100vh">

  <!-- HERO CATALOGO -->
  <div style="background:var(--dark);padding:4rem 0 3rem;border-bottom:1px solid rgba(255,255,255,0.06)">
    <div class="container">
      <span class="label">Tienda</span>
      <h1 style="font-size:clamp(2rem,4vw,3rem);margin-top:0.8rem">
        Catálogo <span class="accent">completo</span>
      </h1>
      <p style="color:var(--mid);margin-top:0.5rem;font-size:0.95rem">
        <?php echo $total; ?> producto<?php echo $total!=1?'s':''; ?> disponible<?php echo $total!=1?'s':''; ?>
      </p>
    </div>
  </div>

  <div class="container" style="padding-top:3rem;padding-bottom:5rem">
    <div style="display:grid;grid-template-columns:260px 1fr;gap:3rem;align-items:start">

      <!-- SIDEBAR FILTROS -->
      <aside class="catalog-sidebar" id="catalogSidebar">
        <div class="glass" style="padding:1.8rem;position:sticky;top:6rem">

          <!-- Búsqueda -->
          <div style="margin-bottom:1.8rem">
            <label>Buscar</label>
            <form method="GET" action="">
              <div style="position:relative">
                <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>" placeholder="Nombre del producto..." class="input" style="padding-right:2.5rem"/>
                <button type="submit" style="position:absolute;right:0.8rem;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--mid);cursor:pointer">
                  <i class="fa-solid fa-magnifying-glass"></i>
                </button>
              </div>
              <?php if($cat): ?><input type="hidden" name="cat" value="<?php echo $cat; ?>"/><?php endif; ?>
              <?php if($tipo): ?><input type="hidden" name="tipo" value="<?php echo $tipo; ?>"/><?php endif; ?>
              <?php if($sort): ?><input type="hidden" name="sort" value="<?php echo $sort; ?>"/><?php endif; ?>
            </form>
          </div>

          <div class="divider"></div>

          <!-- Tipo -->
          <div style="margin-bottom:1.8rem">
            <label style="margin-bottom:0.8rem">Tipo</label>
            <?php
            $tipos = [['','Todos'],['servicio','Retratos / Servicios'],['producto','Productos físicos']];
            foreach($tipos as [$val,$lbl]):
            ?>
            <a href="?<?php echo http_build_query(array_merge($_GET,['tipo'=>$val,'page'=>1])); ?>"
               style="display:flex;align-items:center;gap:0.6rem;padding:0.5rem 0;font-size:0.85rem;color:<?php echo $tipo===$val?'var(--accent)':'var(--mid)'; ?>;transition:color 0.2s">
              <span style="width:8px;height:8px;border-radius:50%;background:<?php echo $tipo===$val?'var(--accent)':'var(--gray2)'; ?>"></span>
              <?php echo $lbl; ?>
            </a>
            <?php endforeach; ?>
          </div>

          <div class="divider"></div>

          <!-- Categorías -->
          <?php if($cats): ?>
          <div style="margin-bottom:1.8rem">
            <label style="margin-bottom:0.8rem">Categorías</label>
            <a href="?<?php echo http_build_query(array_merge($_GET,['cat'=>0,'page'=>1])); ?>"
               style="display:flex;align-items:center;gap:0.6rem;padding:0.5rem 0;font-size:0.85rem;color:<?php echo !$cat?'var(--accent)':'var(--mid)'; ?>">
              <span style="width:8px;height:8px;border-radius:50%;background:<?php echo !$cat?'var(--accent)':'var(--gray2)'; ?>"></span>
              Todas
            </a>
            <?php foreach($cats as $c): ?>
            <a href="?<?php echo http_build_query(array_merge($_GET,['cat'=>$c['id'],'page'=>1])); ?>"
               style="display:flex;align-items:center;gap:0.6rem;padding:0.5rem 0;font-size:0.85rem;color:<?php echo $cat==$c['id']?'var(--accent)':'var(--mid)'; ?>">
              <span style="width:8px;height:8px;border-radius:50%;background:<?php echo $cat==$c['id']?'var(--accent)':'var(--gray2)'; ?>"></span>
              <?php echo htmlspecialchars($c['name']); ?>
            </a>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>

          <?php if($search || $cat || $tipo): ?>
          <a href="<?php echo $base; ?>/catalogo.php" class="btn btn-outline btn-sm" style="width:100%;justify-content:center">
            <i class="fa-solid fa-xmark"></i> Limpiar filtros
          </a>
          <?php endif; ?>
        </div>
      </aside>

      <!-- GRID PRODUCTOS -->
      <div>
        <!-- Toolbar -->
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:2rem;flex-wrap:wrap;gap:1rem">
          <p style="font-size:0.85rem;color:var(--mid)">
            Mostrando <strong style="color:var(--white)"><?php echo count($products); ?></strong> de <strong style="color:var(--white)"><?php echo $total; ?></strong>
          </p>
          <div style="display:flex;align-items:center;gap:0.8rem">
            <label style="margin:0;font-size:0.8rem;color:var(--mid)">Ordenar:</label>
            <select class="select" style="width:auto;padding:0.5rem 1rem;font-size:0.82rem" onchange="window.location='?<?php echo http_build_query(array_merge($_GET,['sort'=>''])); ?>&sort='+this.value">
              <option value="newest"     <?php echo $sort==='newest'?'selected':''; ?>>Más recientes</option>
              <option value="price_asc"  <?php echo $sort==='price_asc'?'selected':''; ?>>Precio: menor a mayor</option>
              <option value="price_desc" <?php echo $sort==='price_desc'?'selected':''; ?>>Precio: mayor a menor</option>
              <option value="name"       <?php echo $sort==='name'?'selected':''; ?>>Nombre A-Z</option>
            </select>
          </div>
        </div>

        <?php if($products): ?>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1.5rem" id="productsGrid">
          <?php foreach($products as $i => $p): ?>
          <div class="card product-card reveal" style="transition-delay:<?php echo ($i%3)*0.08; ?>s"
               onclick="window.location='<?php echo $base; ?>/producto.php?id=<?php echo $p['id']; ?>'">
            <div class="product-card-img">
              <img src="<?php echo $p['image_url'] ?: $base.'/uploads/placeholder-product.svg'; ?>"
                   alt="<?php echo htmlspecialchars($p['name']); ?>"
                   loading="lazy"/>
              <?php if($p['sale_price']): ?>
              <div style="position:absolute;top:0.8rem;left:0.8rem"><span class="badge badge-accent">Oferta</span></div>
              <?php endif; ?>
              <?php if($p['stock_status']==='outofstock'): ?>
              <div style="position:absolute;top:0.8rem;right:0.8rem"><span class="badge badge-dark">Agotado</span></div>
              <?php endif; ?>
              <div class="product-card-overlay">
                <button class="btn btn-ghost btn-sm" onclick="event.stopPropagation();CartManager.add(<?php echo $p['id']; ?>)">
                  <i class="fa-solid fa-bag-shopping"></i> Agregar
                </button>
              </div>
            </div>
            <div class="product-card-body">
              <div class="product-card-cat"><?php echo htmlspecialchars($p['cat_name'] ?? 'Retrato'); ?></div>
              <div class="product-card-name"><?php echo htmlspecialchars($p['name']); ?></div>
              <div class="product-card-footer">
                <div>
                  <span class="product-card-price"><?php echo fmtPrice($p['sale_price'] ?: $p['price']); ?></span>
                  <?php if($p['sale_price']): ?>
                  <span class="product-card-price-old"><?php echo fmtPrice($p['price']); ?></span>
                  <?php endif; ?>
                </div>
                <button class="add-cart-btn" onclick="event.stopPropagation();CartManager.add(<?php echo $p['id']; ?>)">+</button>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>

        <!-- PAGINACIÓN -->
        <?php if($totalPages > 1): ?>
        <div style="display:flex;justify-content:center;gap:0.5rem;margin-top:3rem">
          <?php for($i=1;$i<=$totalPages;$i++): ?>
          <a href="?<?php echo http_build_query(array_merge($_GET,['page'=>$i])); ?>"
             style="width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:0.85rem;font-weight:600;border:1px solid <?php echo $i==$page?'var(--accent)':'rgba(255,255,255,0.1)'; ?>;color:<?php echo $i==$page?'var(--accent)':'var(--mid)'; ?>;background:<?php echo $i==$page?'var(--accent-dim)':'transparent'; ?>;transition:all 0.2s">
            <?php echo $i; ?>
          </a>
          <?php endfor; ?>
        </div>
        <?php endif; ?>

        <?php else: ?>
        <div style="text-align:center;padding:5rem 2rem;color:var(--mid)">
          <i class="fa-solid fa-paw" style="font-size:3rem;opacity:0.15;display:block;margin-bottom:1.5rem"></i>
          <h3 style="color:var(--white);margin-bottom:0.5rem">No se encontraron productos</h3>
          <p style="font-size:0.9rem">Intenta con otros filtros o términos de búsqueda.</p>
          <a href="<?php echo $base; ?>/catalogo.php" class="btn btn-outline btn-sm" style="margin-top:1.5rem">Ver todos</a>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</main>

<?php include 'footer.php'; ?>
