// ── ÓRDENES ──
let allOrders = [];
let currentOrderId = null;

const loadOrders = async () => {
  try {
    const data = await fetch(`${API}?action=get_orders&auth=${AKEY}`).then(r=>r.json());
    allOrders = data.data || [];
    renderOrders(allOrders);
  } catch(e) { toast('Error cargando órdenes', 'error'); }
};

const renderOrders = (orders) => {
  document.querySelector('#ordersTable tbody').innerHTML =
    orders.map(o => `<tr>
      <td>#${o.order_number||o.id}</td>
      <td>${o.customer_name||'—'}</td>
      <td>${o.customer_email||'—'}</td>
      <td>${fmt(o.total)}</td>
      <td><span class="badge badge-${o.status}">${o.status}</span></td>
      <td>${new Date(o.created_at).toLocaleDateString('es-CL')}</td>
      <td><button class="btn btn-ghost btn-icon btn-sm" onclick="viewOrder(${o.id})"><i class="fa fa-eye"></i></button></td>
    </tr>`).join('') || '<tr><td colspan="7" class="table-empty">Sin órdenes</td></tr>';
};

document.getElementById('orderStatusFilter').addEventListener('change', e => {
  renderOrders(e.target.value ? allOrders.filter(o => o.status === e.target.value) : allOrders);
});

window.viewOrder = (id) => {
  currentOrderId = id;
  const o = allOrders.find(x => x.id == id);
  if(!o) return;
  document.getElementById('orderModalTitle').textContent = `Orden #${o.order_number||o.id}`;
  document.getElementById('orderStatusSelect').value = o.status;
  let shipping = {}, billing = {};
  try { shipping = JSON.parse(o.shipping_address||'{}'); } catch(e) {}
  document.getElementById('orderModalBody').innerHTML = `
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:1.5rem">
      <div>
        <h4 style="font-size:0.85rem;margin-bottom:0.5rem">Cliente</h4>
        <p>${o.customer_name||'—'}</p>
        <p style="color:var(--mid)">${o.customer_email||'—'}</p>
        <p style="color:var(--mid)">${o.customer_phone||'—'}</p>
      </div>
      <div>
        <h4 style="font-size:0.85rem;margin-bottom:0.5rem">Dirección</h4>
        <p>${shipping.address||'—'}</p>
        <p style="color:var(--mid)">${shipping.city||''} ${shipping.region||''}</p>
      </div>
    </div>
    <div style="text-align:right;margin-top:1rem">
      <p><strong>Subtotal:</strong> ${fmt(o.subtotal)}</p>
      <p><strong>Envío:</strong> ${fmt(o.shipping_total||0)}</p>
      <p style="font-size:1.1rem"><strong>Total:</strong> ${fmt(o.total)}</p>
    </div>`;
  openModal('orderModal');
};

document.getElementById('btnUpdateOrderStatus').addEventListener('click', async () => {
  const status = document.getElementById('orderStatusSelect').value;
  const data = await fetch(`${API}?action=update_order_status&auth=${AKEY}`, {
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({id: currentOrderId, status})
  }).then(r=>r.json());
  if(data.success) { toast('Estado actualizado', 'success'); closeModal('orderModal'); loadOrders(); }
  else toast(data.error||'Error', 'error');
});

// ── CUPONES ──
let allCoupons = [];

const loadCoupons = async () => {
  try {
    const data = await fetch(`${API}?action=get_coupons&auth=${AKEY}`).then(r=>r.json());
    allCoupons = data.data || [];
    document.querySelector('#couponsTable tbody').innerHTML =
      allCoupons.map(c => `<tr>
        <td><strong>${c.code}</strong></td>
        <td>${c.discount_type==='percentage'?'Porcentaje':'Fijo'}</td>
        <td>${c.discount_type==='percentage'?c.discount_value+'%':fmt(c.discount_value)}</td>
        <td>${c.usage_count||0} / ${c.usage_limit||'∞'}</td>
        <td>${c.expiry_date||'—'}</td>
        <td><div class="toggle ${c.status==='active'?'on':''}" onclick="toggleCoupon(${c.id})"></div></td>
        <td>
          <button class="btn btn-ghost btn-icon btn-sm" onclick="editCoupon(${c.id})"><i class="fa fa-pen"></i></button>
          <button class="btn btn-ghost btn-icon btn-sm" onclick="deleteCoupon(${c.id})"><i class="fa fa-trash"></i></button>
        </td>
      </tr>`).join('') || '<tr><td colspan="7" class="table-empty">Sin cupones</td></tr>';
  } catch(e) { toast('Error cargando cupones', 'error'); }
};

document.getElementById('btnNewCoupon').addEventListener('click', () => {
  document.getElementById('couponModalTitle').textContent = 'Nuevo Cupón';
  ['couponId','couponCode','couponValue','couponMinSpend','couponLimit','couponExpiry','couponDesc'].forEach(id => document.getElementById(id).value = '');
  document.getElementById('couponType').value = 'percentage';
  document.getElementById('couponActiveToggle').classList.add('on');
  openModal('couponModal');
});

window.editCoupon = (id) => {
  const c = allCoupons.find(x => x.id == id);
  if(!c) return;
  document.getElementById('couponModalTitle').textContent = 'Editar Cupón';
  document.getElementById('couponId').value       = c.id;
  document.getElementById('couponCode').value     = c.code;
  document.getElementById('couponType').value     = c.discount_type;
  document.getElementById('couponValue').value    = c.discount_value;
  document.getElementById('couponMinSpend').value = c.minimum_amount||'';
  document.getElementById('couponLimit').value    = c.usage_limit||'';
  document.getElementById('couponExpiry').value   = c.expiry_date||'';
  document.getElementById('couponDesc').value     = c.description||'';
  document.getElementById('couponActiveToggle').classList.toggle('on', c.status==='active');
  openModal('couponModal');
};

window.deleteCoupon = (id) => {
  confirmDelete('¿Eliminar este cupón?', async () => {
    const fd = new FormData(); fd.append('auth', AKEY); fd.append('id', id);
    const data = await fetch(`${API}?action=delete_coupon`, {method:'POST', body:fd}).then(r=>r.json());
    if(data.success) { toast('Cupón eliminado', 'success'); loadCoupons(); }
    else toast(data.error||'Error', 'error');
  });
};

window.toggleCoupon = async (id) => {
  const c = allCoupons.find(x => x.id == id);
  if(!c) return;
  const data = await fetch(`${API}?action=toggle_coupon&auth=${AKEY}`, {
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({id, status: c.status==='active'?'inactive':'active'})
  }).then(r=>r.json());
  if(data.success) { toast('Estado actualizado', 'success'); loadCoupons(); }
  else toast(data.error||'Error', 'error');
};

document.getElementById('couponActiveToggle').addEventListener('click', function() { this.classList.toggle('on'); });

document.getElementById('btnSaveCoupon').addEventListener('click', async () => {
  const id   = document.getElementById('couponId').value;
  const code = document.getElementById('couponCode').value.trim().toUpperCase();
  const value = document.getElementById('couponValue').value;
  if(!code || !value) return toast('Código y valor son requeridos', 'error');
  const body = {
    code, discount_type: document.getElementById('couponType').value,
    discount_value: value, minimum_amount: document.getElementById('couponMinSpend').value||0,
    usage_limit: document.getElementById('couponLimit').value||0,
    expiry_date: document.getElementById('couponExpiry').value||null,
    description: document.getElementById('couponDesc').value,
    status: document.getElementById('couponActiveToggle').classList.contains('on')?'active':'inactive'
  };
  if(id) body.id = id;
  const data = await fetch(`${API}?action=${id?'update_coupon':'create_coupon'}&auth=${AKEY}`, {
    method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(body)
  }).then(r=>r.json());
  if(data.success) { toast(id?'Cupón actualizado':'Cupón creado', 'success'); closeModal('couponModal'); loadCoupons(); }
  else toast(data.error||'Error', 'error');
});
