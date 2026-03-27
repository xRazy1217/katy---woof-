// ── CLIENTES ──
const loadCustomers = async () => {
  try {
    const data = await fetch(`${API}?action=get_customers&auth=${AKEY}`).then(r=>r.json());
    document.querySelector('#customersTable tbody').innerHTML =
      (data.data||[]).map(c => `<tr>
        <td>${c.name||'—'}</td>
        <td>${c.email}</td>
        <td>${c.phone||'—'}</td>
        <td>${c.total_orders}</td>
        <td>${fmt(c.total_spent||0)}</td>
        <td>${new Date(c.last_order).toLocaleDateString('es-CL')}</td>
      </tr>`).join('') || '<tr><td colspan="6" class="table-empty">Sin clientes aún</td></tr>';
  } catch(e) { toast('Error cargando clientes', 'error'); }
};

// ── MENSAJES ──
let allMessages = [];

const checkUnreadMessages = async () => {
  try {
    const data = await fetch(`${API}?action=get_messages&auth=${AKEY}`).then(r=>r.json());
    const unread = (data.data||[]).filter(m => !m.read_at).length;
    const badge  = document.getElementById('msgBadge');
    if(unread > 0) { badge.textContent = unread; badge.style.display = 'inline-block'; }
    else badge.style.display = 'none';
  } catch(e) {}
};

const loadMessages = async () => {
  try {
    const data = await fetch(`${API}?action=get_messages&auth=${AKEY}`).then(r=>r.json());
    allMessages = data.data || [];
    const unread = allMessages.filter(m => !m.read_at).length;
    const badge  = document.getElementById('msgBadge');
    if(unread > 0) { badge.textContent = unread; badge.style.display = 'inline-block'; }
    else badge.style.display = 'none';

    document.querySelector('#messagesTable tbody').innerHTML =
      allMessages.map(m => `<tr style="${!m.read_at?'font-weight:600':'opacity:0.7'}">
        <td>${m.name}</td>
        <td>${m.email}</td>
        <td>${m.subject||'—'}</td>
        <td>${new Date(m.created_at).toLocaleDateString('es-CL')}</td>
        <td>${m.read_at?'<span class="badge badge-gray">Leído</span>':'<span class="badge badge-accent">Nuevo</span>'}</td>
        <td>
          <button class="btn btn-ghost btn-icon btn-sm" onclick="viewMessage(${m.id})"><i class="fa fa-eye"></i></button>
          <button class="btn btn-ghost btn-icon btn-sm" onclick="deleteMessage(${m.id})"><i class="fa fa-trash"></i></button>
        </td>
      </tr>`).join('') || '<tr><td colspan="6" class="table-empty">Sin mensajes</td></tr>';
  } catch(e) { toast('Error cargando mensajes', 'error'); }
};

window.viewMessage = async (id) => {
  const m = allMessages.find(x => x.id == id);
  if(!m) return;
  document.getElementById('messageModalBody').innerHTML = `
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem">
      <div><div style="font-size:0.7rem;color:var(--mid);margin-bottom:0.2rem">NOMBRE</div><div>${m.name}</div></div>
      <div><div style="font-size:0.7rem;color:var(--mid);margin-bottom:0.2rem">EMAIL</div><div>${m.email}</div></div>
      <div><div style="font-size:0.7rem;color:var(--mid);margin-bottom:0.2rem">TELÉFONO</div><div>${m.phone||'—'}</div></div>
      <div><div style="font-size:0.7rem;color:var(--mid);margin-bottom:0.2rem">ASUNTO</div><div>${m.subject||'—'}</div></div>
    </div>
    <div style="font-size:0.7rem;color:var(--mid);margin-bottom:0.5rem">MENSAJE</div>
    <div style="background:var(--dark2);border-radius:0.5rem;padding:1rem;line-height:1.7;font-size:0.88rem">${m.message.replace(/\n/g,'<br>')}</div>
    <div style="margin-top:1rem;font-size:0.75rem;color:var(--mid)">${new Date(m.created_at).toLocaleString('es-CL')}</div>
    <a href="mailto:${m.email}?subject=Re: ${encodeURIComponent(m.subject||'')}" class="btn btn-primary btn-sm" style="margin-top:1rem">
      <i class="fa fa-reply"></i> Responder
    </a>`;
  openModal('messageModal');
  if(!m.read_at) {
    await fetch(`${API}?action=mark_message_read&id=${id}&auth=${AKEY}`);
    m.read_at = new Date().toISOString();
    loadMessages();
  }
};

window.deleteMessage = (id) => {
  confirmDelete('¿Eliminar este mensaje?', async () => {
    const data = await fetch(`${API}?action=delete_message&id=${id}&auth=${AKEY}`).then(r=>r.json());
    if(data.success) { toast('Mensaje eliminado', 'success'); loadMessages(); }
    else toast(data.error||'Error', 'error');
  });
};
