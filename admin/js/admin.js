// ══════════════════════════════════════════════════════════════
// KATY & WOOF — Admin Panel JavaScript
// ══════════════════════════════════════════════════════════════

// ── ADMIN THEME ──
const AdminTheme = {
  init() {
    if(localStorage.getItem('kw_admin_theme') === 'light') this._apply('light');
    else this._apply('dark');
  },
  toggle() {
    const isLight = document.body.classList.contains('light-theme');
    const next = isLight ? 'dark' : 'light';
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

const openModal = id => document.getElementById(id).classList.add('open');
const closeModal = id => document.getElementById(id).classList.remove('open');

document.querySelectorAll('[data-close]').forEach(btn => {
  btn.addEventListener('click', () => closeModal(btn.dataset.close));
});

document.querySelectorAll('.modal-overlay').forEach(overlay => {
  overlay.addEventListener('click', e => {
    if(e.target === overlay) closeModal(overlay.id);
  });
});

const confirmDelete = (msg, onConfirm) => {
  document.getElementById('confirmMsg').textContent = msg;
  openModal('confirmModal');
  document.getElementById('btnConfirmDelete').onclick = () => {
    closeModal('confirmModal');
    onConfirm();
  };
};

// ── NAVEGACIÓN ──
document.querySelectorAll('.nav-item[data-panel]').forEach(item => {
  item.addEventListener('click', () => {
    const panel = item.dataset.panel;
    document.querySelectorAll('.nav-item').forEach(i => i.classList.remove('active'));
    item.classList.add('active');
    document.querySelectorAll('.panel').forEach(p => p.classList.remove('active'));
    document.getElementById('panel-'+panel).classList.add('active');
    document.getElementById('topbarTitle').textContent = item.textContent.trim();
    
    if(panel === 'dashboard') loadDashboard();
    if(panel === 'products') loadProducts();
    if(panel === 'categories') loadCategories();
    if(panel === 'orders') loadOrders();
    if(panel === 'coupons') loadCoupons();
    if(panel === 'settings') loadSettings();
    if(panel === 'system') loadSystem();
    if(panel === 'blog') loadBlog();
    if(panel === 'customers') loadCustomers();
    if(panel === 'messages') loadMessages();
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
    
    document.getElementById('ds-products').textContent = products.count || 0;
    document.getElementById('ds-categories').textContent = categories.count || 0;
    document.getElementById('ds-orders').textContent = orders.count || 0;
    document.getElementById('ds-coupons').textContent = coupons.count || 0;
    
    const ordersData = orders.data || [];
    const tbody = document.querySelector('#dashOrdersTable tbody');
    tbody.innerHTML = ordersData.slice(0,5).map(o => `
      <tr>
        <td>#${o.order_number || o.id}</td>
        <td>${o.customer_name || o.customer_email || '—'}</td>
        <td>${fmt(o.total)}</td>
        <td><span class="badge badge-${o.status}">${o.status}</span></td>
      </tr>
    `).join('') || '<tr><td colspan="4" class="table-empty">Sin órdenes</td></tr>';
    
    const productsData = products.data || [];
    const tbody2 = document.querySelector('#dashProductsTable tbody');
    tbody2.innerHTML = productsData.slice(0,5).map(p => `
      <tr>
        <td>${p.name}</td>
        <td>${fmt(p.price)}</td>
        <td>${p.stock_quantity}</td>
      </tr>
    `).join('') || '<tr><td colspan="3" class="table-empty">Sin productos</td></tr>';
  } catch(e) {
    toast('Error cargando dashboard', 'error');
  }
};

// ── PRODUCTOS ──
let allProducts = [];
let allCategories = [];

const loadProducts = async () => {
  try {
    const res = await fetch(`${API}?action=get_products&auth=${AKEY}`);
    const data = await res.json();
    allProducts = data.data || [];
    renderProducts(allProducts);
    
    const catRes = await fetch(`${API}?action=get_categories&auth=${AKEY}`);
    const catData = await catRes.json();
    allCategories = catData.data || [];
    
    const sel = document.getElementById('productCategory');
    sel.innerHTML = '<option value="">Sin categoría</option>' + allCategories.map(c => `<option value="${c.id}">${c.name}</option>`).join('');
  } catch(e) {
    toast('Error cargando productos', 'error');
  }
};

const renderProducts = (products) => {
  const tbody = document.querySelector('#productsTable tbody');
  tbody.innerHTML = products.map(p => `
    <tr>
      <td><img src="${p.image_url || '/uploads/placeholder-product.svg'}" style="width:40px;height:40px;object-fit:cover;border-radius:4px"></td>
      <td>${p.name}</td>
      <td>${p.category_name || '—'}</td>
      <td>${fmt(p.price)}</td>
      <td>${p.stock_quantity}</td>
      <td><span class="badge badge-${p.status}">${p.status}</span></td>
      <td>
        <button class="btn btn-ghost btn-icon btn-sm" onclick="editProduct(${p.id})"><i class="fa fa-pen"></i></button>
        <button class="btn btn-ghost btn-icon btn-sm" onclick="deleteProduct(${p.id})"><i class="fa fa-trash"></i></button>
      </td>
    </tr>
  `).join('') || '<tr><td colspan="7" class="table-empty">Sin productos</td></tr>';
};

document.getElementById('productSearch').addEventListener('input', e => {
  const q = e.target.value.toLowerCase();
  const filtered = allProducts.filter(p => p.name.toLowerCase().includes(q));
  renderProducts(filtered);
});

document.getElementById('btnNewProduct').addEventListener('click', () => {
  document.getElementById('productModalTitle').textContent = 'Nuevo Producto';
  document.getElementById('productId').value = '';
  document.getElementById('productName').value = '';
  document.getElementById('productSku').value = '';
  document.getElementById('productDesc').value = '';
  document.getElementById('productPrice').value = '';
  document.getElementById('productSalePrice').value = '';
  document.getElementById('productStock').value = '0';
  document.getElementById('productCategory').value = '';
  document.getElementById('productStatus').value = 'publish';
  document.getElementById('productImgUrl').value = '';
  document.getElementById('productImgPreview').style.display = 'none';
  document.getElementById('productImgIcon').style.display = 'block';
  document.getElementById('productImg').value = '';
  openModal('productModal');
});

window.editProduct = async (id) => {
  const p = allProducts.find(x => x.id == id);
  if(!p) return;
  
  document.getElementById('productModalTitle').textContent = 'Editar Producto';
  document.getElementById('productId').value = p.id;
  document.getElementById('productName').value = p.name;
  document.getElementById('productSku').value = p.sku || '';
  document.getElementById('productDesc').value = p.description || '';
  document.getElementById('productPrice').value = p.price;
  document.getElementById('productSalePrice').value = p.sale_price || '';
  document.getElementById('productStock').value = p.stock_quantity;
  document.getElementById('productCategory').value = p.category_id || '';
  document.getElementById('productStatus').value = p.status;
  document.getElementById('productImgUrl').value = p.image_url || '';
  
  if(p.image_url) {
    document.getElementById('productImgPreview').src = p.image_url;
    document.getElementById('productImgPreview').style.display = 'block';
    document.getElementById('productImgIcon').style.display = 'none';
  }
  
  openModal('productModal');
};

window.deleteProduct = (id) => {
  confirmDelete('¿Eliminar este producto?', async () => {
    try {
      const fd = new FormData();
      fd.append('auth', AKEY);
      fd.append('id', id);
      const res = await fetch(`${API}?action=delete_product`, {method:'POST', body:fd});
      const data = await res.json();
      if(data.success) {
        toast('Producto eliminado', 'success');
        loadProducts();
      } else {
        toast(data.error || 'Error', 'error');
      }
    } catch(e) {
      toast('Error eliminando producto', 'error');
    }
  });
};

document.getElementById('btnSaveProduct').addEventListener('click', async () => {
  const id = document.getElementById('productId').value;
  const name = document.getElementById('productName').value.trim();
  const price = document.getElementById('productPrice').value;
  
  if(!name || !price) return toast('Nombre y precio son requeridos', 'error');
  
  const fd = new FormData();
  fd.append('auth', AKEY);
  fd.append('name', name);
  fd.append('sku', document.getElementById('productSku').value);
  fd.append('description', document.getElementById('productDesc').value);
  fd.append('price', price);
  fd.append('sale_price', document.getElementById('productSalePrice').value || 0);
  fd.append('stock_quantity', document.getElementById('productStock').value);
  fd.append('category_id', document.getElementById('productCategory').value);
  fd.append('status', document.getElementById('productStatus').value);
  
  const imgFile = document.getElementById('productImg').files[0];
  if(imgFile) fd.append('image', imgFile);
  
  if(id) fd.append('id', id);
  
  try {
    const action = id ? 'update_product' : 'create_product';
    const res = await fetch(`${API}?action=${action}`, {method:'POST', body:fd});
    const data = await res.json();
    if(data.success) {
      toast(id ? 'Producto actualizado' : 'Producto creado', 'success');
      closeModal('productModal');
      loadProducts();
    } else {
      toast(data.error || 'Error', 'error');
    }
  } catch(e) {
    toast('Error guardando producto', 'error');
  }
});

document.getElementById('productImg').addEventListener('change', e => {
  const file = e.target.files[0];
  if(file) {
    const reader = new FileReader();
    reader.onload = ev => {
      document.getElementById('productImgPreview').src = ev.target.result;
      document.getElementById('productImgPreview').style.display = 'block';
      document.getElementById('productImgIcon').style.display = 'none';
    };
    reader.readAsDataURL(file);
  }
});

// ── CATEGORÍAS ──
const loadCategories = async () => {
  try {
    const res = await fetch(`${API}?action=get_categories&auth=${AKEY}`);
    const data = await res.json();
    const cats = data.data || [];
    
    const tbody = document.querySelector('#categoriesTable tbody');
    tbody.innerHTML = cats.map(c => `
      <tr>
        <td>${c.name}</td>
        <td>${c.slug}</td>
        <td>${c.description || '—'}</td>
        <td>—</td>
        <td>
          <button class="btn btn-ghost btn-icon btn-sm" onclick="editCategory(${c.id})"><i class="fa fa-pen"></i></button>
          <button class="btn btn-ghost btn-icon btn-sm" onclick="deleteCategory(${c.id})"><i class="fa fa-trash"></i></button>
        </td>
      </tr>
    `).join('') || '<tr><td colspan="5" class="table-empty">Sin categorías</td></tr>';
    
    allCategories = cats;
  } catch(e) {
    toast('Error cargando categorías', 'error');
  }
};

document.getElementById('btnNewCategory').addEventListener('click', () => {
  document.getElementById('categoryModalTitle').textContent = 'Nueva Categoría';
  document.getElementById('categoryId').value = '';
  document.getElementById('categoryName').value = '';
  document.getElementById('categorySlug').value = '';
  document.getElementById('categoryDesc').value = '';
  openModal('categoryModal');
});

window.editCategory = (id) => {
  const c = allCategories.find(x => x.id == id);
  if(!c) return;
  
  document.getElementById('categoryModalTitle').textContent = 'Editar Categoría';
  document.getElementById('categoryId').value = c.id;
  document.getElementById('categoryName').value = c.name;
  document.getElementById('categorySlug').value = c.slug;
  document.getElementById('categoryDesc').value = c.description || '';
  openModal('categoryModal');
};

window.deleteCategory = (id) => {
  confirmDelete('¿Eliminar esta categoría?', async () => {
    try {
      const fd = new FormData();
      fd.append('auth', AKEY);
      fd.append('id', id);
      const res = await fetch(`${API}?action=delete_category`, {method:'POST', body:fd});
      const data = await res.json();
      if(data.success) {
        toast('Categoría eliminada', 'success');
        loadCategories();
      } else {
        toast(data.error || 'Error', 'error');
      }
    } catch(e) {
      toast('Error eliminando categoría', 'error');
    }
  });
};

document.getElementById('btnSaveCategory').addEventListener('click', async () => {
  const id = document.getElementById('categoryId').value;
  const name = document.getElementById('categoryName').value.trim();
  
  if(!name) return toast('Nombre es requerido', 'error');
  
  const body = {
    name,
    slug: document.getElementById('categorySlug').value,
    description: document.getElementById('categoryDesc').value
  };
  
  try {
    const action = id ? 'update_category' : 'create_category';
    if(id) body.id = id;
    
    const res = await fetch(`${API}?action=${action}&auth=${AKEY}`, {
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body: JSON.stringify(body)
    });
    const data = await res.json();
    if(data.success) {
      toast(id ? 'Categoría actualizada' : 'Categoría creada', 'success');
      closeModal('categoryModal');
      loadCategories();
    } else {
      toast(data.error || 'Error', 'error');
    }
  } catch(e) {
    toast('Error guardando categoría', 'error');
  }
});

// ── ÓRDENES ──
let allOrders = [];
let currentOrderId = null;

const loadOrders = async () => {
  try {
    const res = await fetch(`${API}?action=get_orders&auth=${AKEY}`);
    const data = await res.json();
    allOrders = data.data || [];
    renderOrders(allOrders);
  } catch(e) {
    toast('Error cargando órdenes', 'error');
  }
};

const renderOrders = (orders) => {
  const tbody = document.querySelector('#ordersTable tbody');
  tbody.innerHTML = orders.map(o => `
    <tr>
      <td>#${o.order_number || o.id}</td>
      <td>${o.customer_name || '—'}</td>
      <td>${o.customer_email || '—'}</td>
      <td>${fmt(o.total)}</td>
      <td><span class="badge badge-${o.status}">${o.status}</span></td>
      <td>${new Date(o.created_at).toLocaleDateString('es-CL')}</td>
      <td>
        <button class="btn btn-ghost btn-icon btn-sm" onclick="viewOrder(${o.id})"><i class="fa fa-eye"></i></button>
      </td>
    </tr>
  `).join('') || '<tr><td colspan="7" class="table-empty">Sin órdenes</td></tr>';
};

document.getElementById('orderStatusFilter').addEventListener('change', e => {
  const status = e.target.value;
  const filtered = status ? allOrders.filter(o => o.status === status) : allOrders;
  renderOrders(filtered);
});

window.viewOrder = async (id) => {
  currentOrderId = id;
  const o = allOrders.find(x => x.id == id);
  if(!o) return;
  
  document.getElementById('orderModalTitle').textContent = `Orden #${o.order_number || o.id}`;
  document.getElementById('orderStatusSelect').value = o.status;
  
  let shipping = {};
  let billing = {};
  try { shipping = JSON.parse(o.shipping_address || '{}'); } catch(e) {}
  try { billing = JSON.parse(o.billing_address || '{}'); } catch(e) {}
  
  const html = `
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:1.5rem">
      <div>
        <h4 style="font-size:0.85rem;margin-bottom:0.5rem;color:var(--light)">Cliente</h4>
        <p style="margin:0.2rem 0">${o.customer_name || '—'}</p>
        <p style="margin:0.2rem 0;color:var(--mid)">${o.customer_email || '—'}</p>
        <p style="margin:0.2rem 0;color:var(--mid)">${o.customer_phone || '—'}</p>
      </div>
      <div>
        <h4 style="font-size:0.85rem;margin-bottom:0.5rem;color:var(--light)">Dirección de envío</h4>
        <p style="margin:0.2rem 0">${shipping.address || '—'}</p>
        <p style="margin:0.2rem 0;color:var(--mid)">${shipping.city || ''} ${shipping.region || ''}</p>
      </div>
    </div>
    <h4 style="font-size:0.85rem;margin-bottom:0.5rem;color:var(--light)">Productos</h4>
    <table class="data-table" style="margin-bottom:1rem">
      <thead><tr><th>Producto</th><th>Cantidad</th><th>Precio</th><th>Total</th></tr></thead>
      <tbody>
        ${(o.items || []).map(i => `
          <tr>
            <td>${i.product?.name || i.product_name || '—'}</td>
            <td>${i.quantity}</td>
            <td>${fmt(i.price)}</td>
            <td>${fmt(i.line_total || i.price * i.quantity)}</td>
          </tr>
        `).join('')}
      </tbody>
    </table>
    <div style="text-align:right">
      <p style="margin:0.3rem 0"><strong>Subtotal:</strong> ${fmt(o.subtotal)}</p>
      <p style="margin:0.3rem 0"><strong>Envío:</strong> ${fmt(o.shipping_total || 0)}</p>
      <p style="margin:0.3rem 0"><strong>Descuento:</strong> ${fmt(o.discount_total || 0)}</p>
      <p style="margin:0.5rem 0;font-size:1.1rem"><strong>Total:</strong> ${fmt(o.total)}</p>
    </div>
  `;
  
  document.getElementById('orderModalBody').innerHTML = html;
  openModal('orderModal');
};

document.getElementById('btnUpdateOrderStatus').addEventListener('click', async () => {
  const status = document.getElementById('orderStatusSelect').value;
  
  try {
    const res = await fetch(`${API}?action=update_order_status&auth=${AKEY}`, {
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body: JSON.stringify({id: currentOrderId, status})
    });
    const data = await res.json();
    if(data.success) {
      toast('Estado actualizado', 'success');
      closeModal('orderModal');
      loadOrders();
    } else {
      toast(data.error || 'Error', 'error');
    }
  } catch(e) {
    toast('Error actualizando estado', 'error');
  }
});

// ── CUPONES ──
let allCoupons = [];

const loadCoupons = async () => {
  try {
    const res = await fetch(`${API}?action=get_coupons&auth=${AKEY}`);
    const data = await res.json();
    allCoupons = data.data || [];
    
    const tbody = document.querySelector('#couponsTable tbody');
    tbody.innerHTML = allCoupons.map(c => `
      <tr>
        <td><strong>${c.code}</strong></td>
        <td>${c.discount_type === 'percentage' ? 'Porcentaje' : 'Fijo'}</td>
        <td>${c.discount_type === 'percentage' ? c.discount_value+'%' : fmt(c.discount_value)}</td>
        <td>${c.usage_count || 0} / ${c.usage_limit || '∞'}</td>
        <td>${c.expiry_date || '—'}</td>
        <td>
          <div class="toggle ${c.status === 'active' ? 'on' : ''}" onclick="toggleCoupon(${c.id})"></div>
        </td>
        <td>
          <button class="btn btn-ghost btn-icon btn-sm" onclick="editCoupon(${c.id})"><i class="fa fa-pen"></i></button>
          <button class="btn btn-ghost btn-icon btn-sm" onclick="deleteCoupon(${c.id})"><i class="fa fa-trash"></i></button>
        </td>
      </tr>
    `).join('') || '<tr><td colspan="7" class="table-empty">Sin cupones</td></tr>';
  } catch(e) {
    toast('Error cargando cupones', 'error');
  }
};

document.getElementById('btnNewCoupon').addEventListener('click', () => {
  document.getElementById('couponModalTitle').textContent = 'Nuevo Cupón';
  document.getElementById('couponId').value = '';
  document.getElementById('couponCode').value = '';
  document.getElementById('couponType').value = 'percentage';
  document.getElementById('couponValue').value = '';
  document.getElementById('couponMinSpend').value = '';
  document.getElementById('couponLimit').value = '';
  document.getElementById('couponExpiry').value = '';
  document.getElementById('couponDesc').value = '';
  document.getElementById('couponActiveToggle').classList.add('on');
  openModal('couponModal');
});

window.editCoupon = (id) => {
  const c = allCoupons.find(x => x.id == id);
  if(!c) return;
  
  document.getElementById('couponModalTitle').textContent = 'Editar Cupón';
  document.getElementById('couponId').value = c.id;
  document.getElementById('couponCode').value = c.code;
  document.getElementById('couponType').value = c.discount_type;
  document.getElementById('couponValue').value = c.discount_value;
  document.getElementById('couponMinSpend').value = c.minimum_amount || '';
  document.getElementById('couponLimit').value = c.usage_limit || '';
  document.getElementById('couponExpiry').value = c.expiry_date || '';
  document.getElementById('couponDesc').value = c.description || '';
  
  if(c.status === 'active') {
    document.getElementById('couponActiveToggle').classList.add('on');
  } else {
    document.getElementById('couponActiveToggle').classList.remove('on');
  }
  
  openModal('couponModal');
};

window.deleteCoupon = (id) => {
  confirmDelete('¿Eliminar este cupón?', async () => {
    try {
      const fd = new FormData();
      fd.append('auth', AKEY);
      fd.append('id', id);
      const res = await fetch(`${API}?action=delete_coupon`, {method:'POST', body:fd});
      const data = await res.json();
      if(data.success) {
        toast('Cupón eliminado', 'success');
        loadCoupons();
      } else {
        toast(data.error || 'Error', 'error');
      }
    } catch(e) {
      toast('Error eliminando cupón', 'error');
    }
  });
};

window.toggleCoupon = async (id) => {
  const c = allCoupons.find(x => x.id == id);
  if(!c) return;
  
  const newStatus = c.status === 'active' ? 'inactive' : 'active';
  
  try {
    const res = await fetch(`${API}?action=toggle_coupon&auth=${AKEY}`, {
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body: JSON.stringify({id, status: newStatus})
    });
    const data = await res.json();
    if(data.success) {
      toast('Estado actualizado', 'success');
      loadCoupons();
    } else {
      toast(data.error || 'Error', 'error');
    }
  } catch(e) {
    toast('Error actualizando cupón', 'error');
  }
};

document.getElementById('couponActiveToggle').addEventListener('click', function() {
  this.classList.toggle('on');
});

document.getElementById('btnSaveCoupon').addEventListener('click', async () => {
  const id = document.getElementById('couponId').value;
  const code = document.getElementById('couponCode').value.trim().toUpperCase();
  const value = document.getElementById('couponValue').value;
  
  if(!code || !value) return toast('Código y valor son requeridos', 'error');
  
  const body = {
    code,
    discount_type: document.getElementById('couponType').value,
    discount_value: value,
    minimum_amount: document.getElementById('couponMinSpend').value || 0,
    usage_limit: document.getElementById('couponLimit').value || 0,
    expiry_date: document.getElementById('couponExpiry').value || null,
    description: document.getElementById('couponDesc').value,
    status: document.getElementById('couponActiveToggle').classList.contains('on') ? 'active' : 'inactive'
  };
  
  try {
    const action = id ? 'update_coupon' : 'create_coupon';
    if(id) body.id = id;
    
    const res = await fetch(`${API}?action=${action}&auth=${AKEY}`, {
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body: JSON.stringify(body)
    });
    const data = await res.json();
    if(data.success) {
      toast(id ? 'Cupón actualizado' : 'Cupón creado', 'success');
      closeModal('couponModal');
      loadCoupons();
    } else {
      toast(data.error || 'Error', 'error');
    }
  } catch(e) {
    toast('Error guardando cupón', 'error');
  }
});

// ── AJUSTES ──
const loadSettings = async () => {
  try {
    const res = await fetch(`${API}?action=get_settings&auth=${AKEY}`);
    const data = await res.json();
    
    data.forEach(s => {
      const input = document.querySelector(`[name="${s.setting_key}"]`);
      if(input) input.value = s.setting_value || '';
    });
  } catch(e) {
    toast('Error cargando ajustes', 'error');
  }
};

document.getElementById('settingsForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  
  const fd = new FormData(e.target);
  fd.append('auth', AKEY);
  
  try {
    const res = await fetch(`${API}?action=save_settings`, {method:'POST', body:fd});
    const data = await res.json();
    if(data.success) {
      toast('Ajustes guardados', 'success');
    } else {
      toast(data.error || 'Error', 'error');
    }
  } catch(e) {
    toast('Error guardando ajustes', 'error');
  }
});

// ── SISTEMA ──
const loadSystem = async () => {
  try {
    const res = await fetch(`${API}?action=get_db_status&auth=${AKEY}`);
    const data = await res.json();
    
    if(data.success && data.data) {
      document.getElementById('sys-tables').textContent = data.data.table_count || '—';
      document.getElementById('sys-size').textContent = (data.data.size_mb || 0) + ' MB';
      document.getElementById('sys-php').textContent = data.data.php_version || '—';
    }
  } catch(e) {
    toast('Error cargando estado del sistema', 'error');
  }
};

document.getElementById('btnTestConn').addEventListener('click', async () => {
  const log = document.getElementById('sysLog');
  log.textContent = 'Probando conexión...\n';
  
  try {
    const res = await fetch(`${API}?action=test_connection&auth=${AKEY}`);
    const data = await res.json();
    
    if(data.success) {
      log.textContent += `✓ Conexión exitosa\n`;       log.textContent += `✓ PHP: ${data.php_version}\n`;
      log.textContent += `✓ Tablas: ${data.tables?.length || 0}\n`;
      log.textContent += `✓ Upload dir: ${data.upload_dir_writable ? 'Escribible' : 'No escribible'}\n`;
      log.textContent += `✓ WebP: ${data.webp_support ? 'Soportado' : 'No soportado'}\n`;
      toast('Conexión exitosa', 'success');
    } else {
      log.textContent += `✗ Error: ${data.error || 'Desconocido'}\n`;
      toast('Error en conexión', 'error');
    }
  } catch(e) {
    log.textContent += `✗ Error: ${e.message}\n`;
    toast('Error probando conexión', 'error');
  }
});

document.getElementById('btnSyncDB').addEventListener('click', async () => {
  const log = document.getElementById('sysLog');
  log.textContent = 'Sincronizando base de datos...\n';
  
  try {
    const res = await fetch(`${API}?action=sync_database&auth=${AKEY}`, {method:'POST'});
    const data = await res.json();
    
    if(data.success) {
      log.textContent += `✓ Sincronización exitosa\n`;
      log.textContent += data.message || '';
      if(data.changes) {
        log.textContent += `\n\nCambios aplicados:\n${JSON.stringify(data.changes, null, 2)}`;
      }
      toast('BD sincronizada', 'success');
      loadSystem();
    } else {
      log.textContent += `✗ Error: ${data.error || 'Desconocido'}\n`;
      toast('Error sincronizando BD', 'error');
    }
  } catch(e) {
    log.textContent += `✗ Error: ${e.message}\n`;
    toast('Error sincronizando BD', 'error');
  }
});

document.getElementById('btnInitEcommerce').addEventListener('click', async () => {
  const log = document.getElementById('sysLog');
  log.textContent = 'Inicializando e-commerce...\n';
  
  try {
    const res = await fetch(`${API}?action=ecommerce_init&auth=${AKEY}`, {method:'POST'});
    const data = await res.json();
    
    if(data.success) {
      log.textContent += `✓ E-commerce inicializado\n`;
      log.textContent += data.message || '';
      if(data.details) {
        log.textContent += `\n\n${JSON.stringify(data.details, null, 2)}`;
      }
      toast('E-commerce inicializado', 'success');
      loadSystem();
    } else {
      log.textContent += `✗ Error: ${data.error || 'Desconocido'}\n`;
      toast('Error inicializando e-commerce', 'error');
    }
  } catch(e) {
    log.textContent += `✗ Error: ${e.message}\n`;
    toast('Error inicializando e-commerce', 'error');
  }
});

document.getElementById('btnRepairDB').addEventListener('click', async () => {
  if(!confirm('¿Estás segura de reparar la base de datos? Esto puede tomar tiempo.')) return;
  
  const log = document.getElementById('sysLog');
  log.textContent = 'Reparando base de datos...\n';
  
  try {
    const res = await fetch(`${API}?action=repair_database&auth=${AKEY}`, {method:'POST'});
    const data = await res.json();
    
    if(data.success) {
      log.textContent += `✓ Reparación exitosa\n`;
      log.textContent += data.message || '';
      if(data.changes) {
        log.textContent += `\n\nCambios aplicados:\n${JSON.stringify(data.changes, null, 2)}`;
      }
      toast('BD reparada', 'success');
      loadSystem();
    } else {
      log.textContent += `✗ Error: ${data.error || 'Desconocido'}\n`;
      toast('Error reparando BD', 'error');
    }
  } catch(e) {
    log.textContent += `✗ Error: ${e.message}\n`;
    toast('Error reparando BD', 'error');
  }
});

// ── INIT ──
document.addEventListener('DOMContentLoaded', () => {
  AdminTheme.init();
  loadDashboard();
  checkUnreadMessages();
});

// ── BLOG ──
let allPosts = [];

const loadBlog = async () => {
  try {
    const res = await fetch(`${API}?action=get_blog&auth=${AKEY}`);
    const data = await res.json();
    allPosts = Array.isArray(data) ? data : (data.data || []);
    const tbody = document.querySelector('#blogTable tbody');
    tbody.innerHTML = allPosts.map(p => `
      <tr>
        <td><img src="${p.img_url || '/img/placeholder.svg'}" style="width:40px;height:40px;object-fit:cover;border-radius:4px"></td>
        <td>${p.title}</td>
        <td>${p.category || 'General'}</td>
        <td>${new Date(p.created_at).toLocaleDateString('es-CL')}</td>
        <td>
          <button class="btn btn-ghost btn-icon btn-sm" onclick="editPost(${p.id})"><i class="fa fa-pen"></i></button>
          <button class="btn btn-ghost btn-icon btn-sm" onclick="deletePost(${p.id})"><i class="fa fa-trash"></i></button>
        </td>
      </tr>
    `).join('') || '<tr><td colspan="5" class="table-empty">Sin posts</td></tr>';
  } catch(e) { toast('Error cargando blog', 'error'); }
};

document.getElementById('btnNewPost').addEventListener('click', () => {
  document.getElementById('blogModalTitle').textContent = 'Nuevo Post';
  document.getElementById('blogId').value = '';
  document.getElementById('blogTitle').value = '';
  document.getElementById('blogCategory').value = 'General';
  document.getElementById('blogContent').value = '';
  document.getElementById('blogImgPreview').style.display = 'none';
  document.getElementById('blogImgIcon').style.display = 'block';
  document.getElementById('blogImg').value = '';
  openModal('blogModal');
});

window.editPost = (id) => {
  const p = allPosts.find(x => x.id == id);
  if(!p) return;
  document.getElementById('blogModalTitle').textContent = 'Editar Post';
  document.getElementById('blogId').value = p.id;
  document.getElementById('blogTitle').value = p.title;
  document.getElementById('blogCategory').value = p.category || 'General';
  document.getElementById('blogContent').value = p.content || '';
  if(p.img_url && !p.img_url.includes('placeholder')) {
    document.getElementById('blogImgPreview').src = p.img_url;
    document.getElementById('blogImgPreview').style.display = 'block';
    document.getElementById('blogImgIcon').style.display = 'none';
  } else {
    document.getElementById('blogImgPreview').style.display = 'none';
    document.getElementById('blogImgIcon').style.display = 'block';
  }
  openModal('blogModal');
};

window.deletePost = (id) => {
  confirmDelete('¿Eliminar este post?', async () => {
    const fd = new FormData();
    fd.append('auth', AKEY); fd.append('id', id);
    const res = await fetch(`${API}?action=delete_blog`, {method:'POST', body:fd});
    const data = await res.json();
    if(data.success) { toast('Post eliminado', 'success'); loadBlog(); }
    else toast(data.error || 'Error', 'error');
  });
};

document.getElementById('blogImg').addEventListener('change', e => {
  const file = e.target.files[0];
  if(file) {
    const reader = new FileReader();
    reader.onload = ev => {
      document.getElementById('blogImgPreview').src = ev.target.result;
      document.getElementById('blogImgPreview').style.display = 'block';
      document.getElementById('blogImgIcon').style.display = 'none';
    };
    reader.readAsDataURL(file);
  }
});

document.getElementById('btnSavePost').addEventListener('click', async () => {
  const id = document.getElementById('blogId').value;
  const title = document.getElementById('blogTitle').value.trim();
  const content = document.getElementById('blogContent').value.trim();
  if(!title || !content) return toast('Título y contenido son requeridos', 'error');

  const fd = new FormData();
  fd.append('auth', AKEY);
  fd.append('title', title);
  fd.append('category', document.getElementById('blogCategory').value);
  fd.append('content', content);
  if(id) fd.append('id', id);
  const imgFile = document.getElementById('blogImg').files[0];
  if(imgFile) fd.append('file', imgFile);

  try {
    const res = await fetch(`${API}?action=save_blog`, {method:'POST', body:fd});
    const data = await res.json();
    if(data.success) { toast(id ? 'Post actualizado' : 'Post creado', 'success'); closeModal('blogModal'); loadBlog(); }
    else toast(data.error || 'Error', 'error');
  } catch(e) { toast('Error guardando post', 'error'); }
});

// ── CLIENTES ──
const loadCustomers = async () => {
  try {
    const res = await fetch(`${API}?action=get_customers&auth=${AKEY}`);
    const data = await res.json();
    const customers = data.data || [];
    const tbody = document.querySelector('#customersTable tbody');
    tbody.innerHTML = customers.map(c => `
      <tr>
        <td>${c.name || '—'}</td>
        <td>${c.email}</td>
        <td>${c.phone || '—'}</td>
        <td>${c.total_orders}</td>
        <td>${fmt(c.total_spent || 0)}</td>
        <td>${new Date(c.last_order).toLocaleDateString('es-CL')}</td>
      </tr>
    `).join('') || '<tr><td colspan="6" class="table-empty">Sin clientes aún</td></tr>';
  } catch(e) { toast('Error cargando clientes', 'error'); }
};

// ── MENSAJES ──
let allMessages = [];

const checkUnreadMessages = async () => {
  try {
    const res = await fetch(`${API}?action=get_messages&auth=${AKEY}`);
    const data = await res.json();
    const unread = (data.data || []).filter(m => !m.read_at).length;
    const badge = document.getElementById('msgBadge');
    if(unread > 0) { badge.textContent = unread; badge.style.display = 'inline-block'; }
    else badge.style.display = 'none';
  } catch(e) {}
};

const loadMessages = async () => {
  try {
    const res = await fetch(`${API}?action=get_messages&auth=${AKEY}`);
    const data = await res.json();
    allMessages = data.data || [];
    const unread = allMessages.filter(m => !m.read_at).length;
    const badge = document.getElementById('msgBadge');
    if(unread > 0) { badge.textContent = unread; badge.style.display = 'inline-block'; }
    else badge.style.display = 'none';

    const tbody = document.querySelector('#messagesTable tbody');
    tbody.innerHTML = allMessages.map(m => `
      <tr style="${!m.read_at ? 'font-weight:600' : 'opacity:0.7'}">
        <td>${m.name}</td>
        <td>${m.email}</td>
        <td>${m.subject || '—'}</td>
        <td>${new Date(m.created_at).toLocaleDateString('es-CL')}</td>
        <td>${m.read_at ? '<span class="badge badge-gray">Leído</span>' : '<span class="badge badge-accent">Nuevo</span>'}</td>
        <td>
          <button class="btn btn-ghost btn-icon btn-sm" onclick="viewMessage(${m.id})"><i class="fa fa-eye"></i></button>
          <button class="btn btn-ghost btn-icon btn-sm" onclick="deleteMessage(${m.id})"><i class="fa fa-trash"></i></button>
        </td>
      </tr>
    `).join('') || '<tr><td colspan="6" class="table-empty">Sin mensajes</td></tr>';
  } catch(e) { toast('Error cargando mensajes', 'error'); }
};

window.viewMessage = async (id) => {
  const m = allMessages.find(x => x.id == id);
  if(!m) return;
  document.getElementById('messageModalBody').innerHTML = `
    <div style="margin-bottom:1rem">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem">
        <div><div style="font-size:0.7rem;color:var(--mid);margin-bottom:0.2rem">NOMBRE</div><div>${m.name}</div></div>
        <div><div style="font-size:0.7rem;color:var(--mid);margin-bottom:0.2rem">EMAIL</div><div>${m.email}</div></div>
        <div><div style="font-size:0.7rem;color:var(--mid);margin-bottom:0.2rem">TELÉFONO</div><div>${m.phone || '—'}</div></div>
        <div><div style="font-size:0.7rem;color:var(--mid);margin-bottom:0.2rem">ASUNTO</div><div>${m.subject || '—'}</div></div>
      </div>
      <div style="font-size:0.7rem;color:var(--mid);margin-bottom:0.5rem">MENSAJE</div>
      <div style="background:var(--dark2);border-radius:0.5rem;padding:1rem;line-height:1.7;font-size:0.88rem">${m.message.replace(/\n/g,'<br>')}</div>
      <div style="margin-top:1rem;font-size:0.75rem;color:var(--mid)">${new Date(m.created_at).toLocaleString('es-CL')}</div>
    </div>
    <a href="mailto:${m.email}?subject=Re: ${encodeURIComponent(m.subject||'')}" class="btn btn-primary btn-sm">
      <i class="fa fa-reply"></i> Responder por email
    </a>
  `;
  openModal('messageModal');
  if(!m.read_at) {
    await fetch(`${API}?action=mark_message_read&id=${id}&auth=${AKEY}`);
    m.read_at = new Date().toISOString();
    loadMessages();
  }
};

window.deleteMessage = (id) => {
  confirmDelete('¿Eliminar este mensaje?', async () => {
    const res = await fetch(`${API}?action=delete_message&id=${id}&auth=${AKEY}`);
    const data = await res.json();
    if(data.success) { toast('Mensaje eliminado', 'success'); loadMessages(); }
    else toast(data.error || 'Error', 'error');
  });
};

   