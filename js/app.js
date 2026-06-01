/* ═══════════════════════════════════════════════════════════════════════════
   KATY & WOOF — app.js
   ═══════════════════════════════════════════════════════════════════════════
   Modular JavaScript application with event delegation and no inline handlers.
   BASE variable is injected by header.php
   ═════════════════════════════════════════════════════════════════════════════ */

/* ══════════════════════════════
   HEADER SCROLL
══════════════════════════════ */
const header = document.getElementById('siteHeader');
if (header) {
  window.addEventListener('scroll', () => {
    header.classList.toggle('scrolled', window.scrollY > 40);
  });
}

/* ══════════════════════════════
   NAV MOBILE
══════════════════════════════ */
function toggleNav() {
  const nav = document.getElementById('mainNav');
  const ham = document.getElementById('hamburger');
  nav.classList.toggle('open');
  ham.classList.toggle('open');
}

function toggleUserMenu() {
  document.getElementById('userDropdown')?.classList.toggle('open');
}

async function logoutUser() {
  await fetch(`${BASE}/api.php?action=user_logout`);
  window.location.reload();
}

/* ══════════════════════════════
   TOAST
══════════════════════════════ */
const Toast = {
  show(msg, type = 'info', duration = 3000) {
    const icons = { success: '✓', error: '✕', info: '◆' };
    const container = document.getElementById('toastContainer');
    if (!container) return;
    const el = document.createElement('div');
    el.className = `toast ${type}`;
    el.innerHTML = `<span>${icons[type]||'◆'}</span><span>${msg}</span>`;
    container.appendChild(el);
    setTimeout(() => { el.style.opacity = '0'; el.style.transform = 'translateY(10px)'; el.style.transition = '0.3s'; setTimeout(() => el.remove(), 300); }, duration);
  }
};

/* ══════════════════════════════
   FORMAT PRICE (CLP)
══════════════════════════════ */
function formatPrice(n) {
  return '$' + Number(n).toLocaleString('es-CL');
}

/* ══════════════════════════════
   THEME TOGGLE
══════════════════════════════ */
const ThemeManager = {
  init() {
    if(localStorage.getItem('kw_theme') === 'light') {
      document.body.classList.add('light-theme');
    }
    this._updateIcon();
  },
  toggle() {
    document.body.classList.toggle('light-theme');
    const isLight = document.body.classList.contains('light-theme');
    localStorage.setItem('kw_theme', isLight ? 'light' : 'dark');
    this._updateIcon();
  },
  _updateIcon() {
    const icon = document.getElementById('themeIcon');
    if(!icon) return;
    const isLight = document.body.classList.contains('light-theme');
    icon.className = isLight ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
  }
};

/* ══════════════════════════════
   CART MANAGER
══════════════════════════════ */
const CartManager = {
  sessionId: null,
  items: [],

  init() {
    this.sessionId = localStorage.getItem('kw_session');
    if (!this.sessionId) {
      this.sessionId = 'kw_' + Date.now() + '_' + Math.random().toString(36).slice(2, 9);
      localStorage.setItem('kw_session', this.sessionId);
    }
    this.load();
  },

  async load() {
    try {
      const res = await fetch(`${BASE}/api.php?action=cart_get`, {
        headers: { 'X-Session-ID': this.sessionId }
      });
      const data = await res.json();
      if (data.success) { this.items = data.items || []; this.render(); }
    } catch (e) { /* silent */ }
  },

  async add(productId, qty = 1) {
    try {
      const res = await fetch(`${BASE}/api.php?action=cart_add`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Session-ID': this.sessionId },
        body: JSON.stringify({ product_id: productId, quantity: qty })
      });
      const data = await res.json();
      if (data.success) {
        this.items = data.items || [];
        this.render();
        this.open();
        Toast.show('Producto agregado al carrito', 'success');
      } else {
        Toast.show(data.error || 'Error al agregar', 'error');
      }
    } catch (e) { Toast.show('Error de conexión', 'error'); }
  },

  async updateQty(itemId, qty) {
    try {
      const res = await fetch(`${BASE}/api.php?action=cart_update`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Session-ID': this.sessionId },
        body: JSON.stringify({ item_id: itemId, quantity: qty })
      });
      const data = await res.json();
      if (data.success) { this.items = data.items || []; this.render(); }
    } catch (e) { /* silent */ }
  },

  async remove(itemId) {
    try {
      const res = await fetch(`${BASE}/api.php?action=cart_remove`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Session-ID': this.sessionId },
        body: JSON.stringify({ item_id: itemId })
      });
      const data = await res.json();
      if (data.success) { this.items = data.items || []; this.render(); Toast.show('Producto eliminado', 'info'); }
    } catch (e) { /* silent */ }
  },

  render() {
    const list   = document.getElementById('cartItemsList');
    const footer = document.getElementById('cartFooter');
    const count  = document.getElementById('cartCount');
    if (!list) return;

    const totalItems = this.items.reduce((s, i) => s + i.quantity, 0);
    if (count) {
      count.textContent = totalItems;
      count.classList.toggle('visible', totalItems > 0);
    }

    if (!this.items.length) {
      list.innerHTML = `<div class="cart-empty"><div class="cart-empty-icon">🛒</div><p>Tu carrito está vacío</p></div>`;
      if (footer) footer.style.display = 'none';
      return;
    }

    list.innerHTML = this.items.map(item => `
      <div class="cart-item">
        <img class="cart-item-img" src="${item.image_url || BASE+'/uploads/placeholder-product.svg'}" alt="${item.name}"/>
        <div class="cart-item-info">
          <div class="cart-item-name">${item.name}</div>
          <div class="cart-item-price">${formatPrice(item.price)}</div>
          <div class="cart-item-qty">
            <button class="qty-btn qty-minus" data-item-id="${item.id}" data-action="decrease" aria-label="Decrease quantity">−</button>
            <span class="qty-num">${item.quantity}</span>
            <button class="qty-btn qty-plus" data-item-id="${item.id}" data-action="increase" aria-label="Increase quantity">+</button>
          </div>
        </div>
        <button class="cart-item-remove" data-item-id="${item.id}" data-action="remove" aria-label="Remove from cart">✕</button>
      </div>
    `).join('');

    const subtotal = this.items.reduce((s, i) => s + (i.price * i.quantity), 0);
    const shipping = subtotal >= 50000 ? 0 : 5000;
    const total    = subtotal + shipping;

    const sub  = document.getElementById('cartSubtotal');
    const ship = document.getElementById('cartShipping');
    const tot  = document.getElementById('cartTotal');
    if (sub)  sub.textContent  = formatPrice(subtotal);
    if (ship) ship.textContent = shipping === 0 ? 'Gratis' : formatPrice(shipping);
    if (tot)  tot.textContent  = formatPrice(total);
    if (footer) footer.style.display = 'block';

    this._attachCartItemEvents();
  },

  _attachCartItemEvents() {
    const list = document.getElementById('cartItemsList');
    if (!list) return;

    list.addEventListener('click', (e) => {
      const btn = e.target.closest('[data-action]');
      if (!btn) return;

      const itemId = parseInt(btn.dataset.itemId, 10);
      const action = btn.dataset.action;

      if (action === 'increase') {
        const item = this.items.find(i => i.id === itemId);
        if (item) this.updateQty(itemId, item.quantity + 1);
      } else if (action === 'decrease') {
        const item = this.items.find(i => i.id === itemId);
        if (item && item.quantity > 1) this.updateQty(itemId, item.quantity - 1);
      } else if (action === 'remove') {
        this.remove(itemId);
      }
    });
  },

  open() {
    document.getElementById('cartDrawer')?.classList.add('open');
    document.getElementById('cartOverlay')?.classList.add('open');
    document.body.style.overflow = 'hidden';
  },

  close() {
    document.getElementById('cartDrawer')?.classList.remove('open');
    document.getElementById('cartOverlay')?.classList.remove('open');
    document.body.style.overflow = '';
  },

  getItems()   { return this.items; },
  getTotal()   { return this.items.reduce((s, i) => s + (i.price * i.quantity), 0); },
  getSession() { return this.sessionId; }
};

/* ══════════════════════════════
   APP INITIALIZATION
══════════════════════════════ */
const App = {
  init() {
    ThemeManager.init();
    CartManager.init();
    this._setupEventDelegation();
  },

  _setupEventDelegation() {
    // Theme toggle
    const themeBtn = document.getElementById('themeToggle');
    if (themeBtn) {
      themeBtn.addEventListener('click', () => ThemeManager.toggle());
    }

    // Navigation
    const hamburger = document.getElementById('hamburger');
    if (hamburger) {
      hamburger.addEventListener('click', toggleNav);
    }

    const userMenuBtn = document.getElementById('userMenuBtn');
    if (userMenuBtn) {
      userMenuBtn.addEventListener('click', toggleUserMenu);
    }

    // Cart
    const cartBtn = document.getElementById('cartBtn');
    if (cartBtn) {
      cartBtn.addEventListener('click', () => CartManager.open());
    }

    const cartClose = document.getElementById('cartClose');
    if (cartClose) {
      cartClose.addEventListener('click', () => CartManager.close());
    }

    const cartOverlay = document.getElementById('cartOverlay');
    if (cartOverlay) {
      cartOverlay.addEventListener('click', () => CartManager.close());
    }
  }
};

// Auto-initialize when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => App.init());
} else {
  App.init();
}
