// ── ADMIN THEME ──
const AdminTheme = {
  init() {
    this._apply(localStorage.getItem('kw_admin_theme') === 'light' ? 'light' : 'dark');
  },
  toggle() {
    const next = document.body.classList.contains('light-theme') ? 'dark' : 'light';
    localStorage.setItem('kw_admin_theme', next);
    this._apply(next);
  },
  _apply(theme) {
    document.body.classList.toggle('light-theme', theme === 'light');
    const icon = document.getElementById('adminThemeIcon');
    if(icon) icon.className = theme === 'light' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
  }
};

// ── UTILIDADES ──
const fmt = price => new Intl.NumberFormat('es-CL', {style:'currency', currency:'CLP'}).format(price);

const toast = (msg, type='info') => {
  const c = document.getElementById('toastContainer');
  const t = document.createElement('div');
  t.className = `toast toast-${type} show`;
  t.innerHTML = `<i class="fa fa-${type==='success'?'check-circle':type==='error'?'exclamation-circle':'info-circle'}"></i><span>${msg}</span>`;
  c.appendChild(t);
  setTimeout(() => t.classList.remove('show'), 3000);
  setTimeout(() => t.remove(), 3500);
};

const openModal  = id => document.getElementById(id)?.classList.add('open');
const closeModal = id => document.getElementById(id)?.classList.remove('open');

document.querySelectorAll('[data-close]').forEach(btn =>
  btn.addEventListener('click', () => closeModal(btn.dataset.close))
);
document.querySelectorAll('.modal-overlay').forEach(overlay =>
  overlay.addEventListener('click', e => { if(e.target === overlay) closeModal(overlay.id); })
);

const confirmDelete = (msg, onConfirm) => {
  document.getElementById('confirmMsg').textContent = msg;
  openModal('confirmModal');
  document.getElementById('btnConfirmDelete').onclick = () => { closeModal('confirmModal'); onConfirm(); };
};

// ── NAVEGACIÓN ──
document.querySelectorAll('.nav-item[data-panel]').forEach(item => {
  item.addEventListener('click', () => {
    const panel = item.dataset.panel;
    document.querySelectorAll('.nav-item').forEach(i => i.classList.remove('active'));
    item.classList.add('active');
    document.querySelectorAll('.panel').forEach(p => p.classList.remove('active'));
    document.getElementById('panel-' + panel)?.classList.add('active');
    document.getElementById('topbarTitle').textContent = item.textContent.trim();
    const loaders = {
      dashboard: loadDashboard, products: loadProducts, categories: loadCategories,
      orders: loadOrders, coupons: loadCoupons, settings: loadSettings,
      system: loadSystem, blog: loadBlog, customers: loadCustomers, messages: loadMessages
    };
    loaders[panel]?.();
  });
});

// ── DASHBOARD ──
const loadDashboard = async () => {
  try {
    const [products, categories, orders, coupons] = await Promise.all([
      fetch(`${API}?action=get_products&auth=${AKEY}`).then(r=>r.json()),
      fetch(`${API}?action=get_categories&auth=${AKEY}`).then(r=>r.json()),
      fetch(`${API}?action=get_orders&auth=${AKEY}`).then(r=>r.json()),
      fetch(`${API}?action=get_coupons&auth=${AKEY}`).then(r=>r.json())
    ]);
    document.getElementById('ds-products').textContent   = products.count  || 0;
    document.getElementById('ds-categories').textContent = categories.count || 0;
    document.getElementById('ds-orders').textContent     = orders.count    || 0;
    document.getElementById('ds-coupons').textContent    = coupons.count   || 0;

    document.querySelector('#dashOrdersTable tbody').innerHTML =
      (orders.data||[]).slice(0,5).map(o => `<tr>
        <td>#${o.order_number||o.id}</td>
        <td>${o.customer_name||o.customer_email||'—'}</td>
        <td>${fmt(o.total)}</td>
        <td><span class="badge badge-${o.status}">${o.status}</span></td>
      </tr>`).join('') || '<tr><td colspan="4" class="table-empty">Sin órdenes</td></tr>';

    document.querySelector('#dashProductsTable tbody').innerHTML =
      (products.data||[]).slice(0,5).map(p => `<tr>
        <td>${p.name}</td><td>${fmt(p.price)}</td><td>${p.stock_quantity}</td>
      </tr>`).join('') || '<tr><td colspan="3" class="table-empty">Sin productos</td></tr>';
  } catch(e) { toast('Error cargando dashboard', 'error'); }
};

// ── INIT ──
document.addEventListener('DOMContentLoaded', () => {
  AdminTheme.init();
  loadDashboard();
  checkUnreadMessages();
});
