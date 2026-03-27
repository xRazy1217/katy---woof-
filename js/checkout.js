const Checkout = {
  currentStep: 1,
  cartItems: [],

  async init() {
    await this.loadCart();
    this.goStep(1);
  },

  async loadCart() {
    try {
      const sid = localStorage.getItem('kw_session') || '';
      const data = await fetch(`${BASE}/api.php?action=cart_get`, {
        headers: { 'X-Session-ID': sid }
      }).then(r => r.json());
      this.cartItems = data.items || [];
      if (!this.cartItems.length) { window.location = BASE + '/catalogo.php'; return; }
      this.renderCartStep();
      this.renderSummary();
    } catch(e) { Toast.show('Error al cargar el carrito', 'error'); }
  },

  renderCartStep() {
    const el = document.getElementById('checkoutCartItems');
    if (!this.cartItems.length) { el.innerHTML = '<p style="color:var(--mid);text-align:center">Carrito vacío</p>'; return; }
    el.innerHTML = this.cartItems.map(item => `
      <div style="display:flex;gap:1rem;align-items:center;padding:0.8rem 0;border-bottom:1px solid rgba(255,255,255,0.05)">
        <img src="${item.image_url || BASE+'/uploads/placeholder-product.svg'}" style="width:56px;height:56px;border-radius:0.5rem;object-fit:cover"/>
        <div style="flex:1">
          <div style="font-size:0.88rem;font-weight:600">${item.name}</div>
          <div style="font-size:0.78rem;color:var(--mid)">Cant: ${item.quantity}</div>
        </div>
        <div style="font-family:'Space Mono',monospace;font-size:0.9rem;color:var(--accent)">${formatPrice(item.price * item.quantity)}</div>
      </div>`).join('');
  },

  renderSummary() {
    const subtotal = this.cartItems.reduce((s,i) => s + i.price * i.quantity, 0);
    const shipping = subtotal >= 50000 ? 0 : 5000;
    const total    = subtotal + shipping;

    document.getElementById('summarySubtotal').textContent = formatPrice(subtotal);
    document.getElementById('summaryShipping').textContent = shipping === 0 ? 'Gratis' : formatPrice(shipping);
    document.getElementById('summaryTotal').textContent    = formatPrice(total);
    document.getElementById('summaryItems').innerHTML = this.cartItems.map(i =>
      `<div style="display:flex;justify-content:space-between;font-size:0.82rem;color:var(--mid);margin-bottom:0.4rem">
        <span>${i.name} ×${i.quantity}</span><span>${formatPrice(i.price * i.quantity)}</span>
      </div>`).join('');
    document.getElementById('orderSummaryConfirm').innerHTML = `
      <div style="background:var(--dark2);border-radius:var(--radius-sm);padding:1rem">
        <div style="font-size:0.8rem;color:var(--mid);margin-bottom:0.5rem">Total a pagar</div>
        <div style="font-family:'Space Mono',monospace;font-size:1.8rem;font-weight:700;color:var(--accent)">${formatPrice(total)}</div>
      </div>`;
  },

  goStep(n) {
    if (n === 2 && !this.cartItems.length) { Toast.show('Tu carrito está vacío', 'error'); return; }
    if (n === 3 && !this.validateForm()) return;
    this.currentStep = n;
    [1,2,3].forEach(s => {
      const panel  = document.getElementById('step' + s);
      const circle = document.querySelector(`.step-circle[data-step="${s}"]`);
      const label  = document.querySelector(`.step-label-text[data-step="${s}"]`);
      if (panel)  panel.style.display       = s === n ? 'block' : 'none';
      if (circle) {
        circle.style.background  = s <= n ? 'var(--accent)' : 'transparent';
        circle.style.borderColor = s <= n ? 'var(--accent)' : 'rgba(255,255,255,0.1)';
        circle.style.color       = s <= n ? 'white' : 'var(--mid)';
      }
      if (label) label.style.color = s <= n ? 'var(--white)' : 'var(--mid)';
    });
  },

  validateForm() {
    const form = document.getElementById('checkoutForm');
    if (!form) return true;
    let valid = true;
    form.querySelectorAll('[required]').forEach(el => {
      if (!el.value.trim()) { el.style.borderColor = '#ef4444'; valid = false; }
      else el.style.borderColor = '';
    });
    if (!valid) Toast.show('Completa todos los campos requeridos', 'error');
    return valid;
  },

  async processPayment() {
    const btn = document.getElementById('btnPay');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Procesando...';
    const customer = {};
    new FormData(document.getElementById('checkoutForm')).forEach((v,k) => customer[k] = v);
    const sid = localStorage.getItem('kw_session') || '';
    try {
      const data = await fetch(`${BASE}/api.php?action=checkout_create`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Session-ID': sid },
        body: JSON.stringify({ customer })
      }).then(r => r.json());
      if (data.success && data.flow_url) {
        window.location.href = data.flow_url;
      } else {
        throw new Error(data.error || 'Error al procesar el pago');
      }
    } catch(e) {
      Toast.show(e.message, 'error');
      btn.disabled = false;
      btn.innerHTML = '<i class="fa-solid fa-lock"></i> Pagar ahora';
    }
  }
};

document.addEventListener('DOMContentLoaded', () => Checkout.init());
