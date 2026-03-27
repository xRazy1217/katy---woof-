// ── SISTEMA ──
const loadSystem = async () => {
  try {
    const data = await fetch(`${API}?action=get_db_status&auth=${AKEY}`).then(r=>r.json());
    if(data.success && data.data) {
      document.getElementById('sys-tables').textContent = data.data.table_count || '—';
      document.getElementById('sys-size').textContent   = (data.data.size_mb||0) + ' MB';
      document.getElementById('sys-php').textContent    = data.data.php_version || '—';
    }
  } catch(e) { toast('Error cargando sistema', 'error'); }
};

const sysLog = (msg) => { document.getElementById('sysLog').textContent += msg + '\n'; };

document.getElementById('btnTestConn').addEventListener('click', async () => {
  document.getElementById('sysLog').textContent = 'Probando conexión...\n';
  try {
    const data = await fetch(`${API}?action=test_connection&auth=${AKEY}`).then(r=>r.json());
    if(data.success) {
      sysLog(`✓ Conexión exitosa`);
      sysLog(`✓ PHP: ${data.php_version}`);
      sysLog(`✓ Tablas: ${data.tables?.length||0}`);
      sysLog(`✓ Upload dir: ${data.upload_dir_writable?'Escribible':'No escribible'}`);
      toast('Conexión exitosa', 'success');
    } else { sysLog(`✗ Error: ${data.error||'Desconocido'}`); toast('Error en conexión', 'error'); }
  } catch(e) { sysLog(`✗ ${e.message}`); toast('Error', 'error'); }
});

document.getElementById('btnSyncDB').addEventListener('click', async () => {
  document.getElementById('sysLog').textContent = 'Sincronizando BD...\n';
  try {
    const data = await fetch(`${API}?action=sync_database&auth=${AKEY}`, {method:'POST'}).then(r=>r.json());
    if(data.success) { sysLog(`✓ ${data.message||'Exitosa'}`); toast('BD sincronizada', 'success'); loadSystem(); }
    else { sysLog(`✗ ${data.error||'Error'}`); toast('Error sincronizando', 'error'); }
  } catch(e) { sysLog(`✗ ${e.message}`); toast('Error', 'error'); }
});

document.getElementById('btnInitEcommerce').addEventListener('click', async () => {
  document.getElementById('sysLog').textContent = 'Inicializando e-commerce...\n';
  try {
    const data = await fetch(`${API}?action=ecommerce_init&auth=${AKEY}`, {method:'POST'}).then(r=>r.json());
    if(data.success) { sysLog(`✓ ${data.message||'Exitoso'}`); toast('E-commerce inicializado', 'success'); loadSystem(); }
    else { sysLog(`✗ ${data.error||'Error'}`); toast('Error', 'error'); }
  } catch(e) { sysLog(`✗ ${e.message}`); toast('Error', 'error'); }
});

document.getElementById('btnRepairDB').addEventListener('click', async () => {
  if(!confirm('¿Reparar la base de datos?')) return;
  document.getElementById('sysLog').textContent = 'Reparando BD...\n';
  try {
    const data = await fetch(`${API}?action=repair_database&auth=${AKEY}`, {method:'POST'}).then(r=>r.json());
    if(data.success) { sysLog(`✓ ${data.message||'Exitosa'}`); toast('BD reparada', 'success'); loadSystem(); }
    else { sysLog(`✗ ${data.error||'Error'}`); toast('Error reparando', 'error'); }
  } catch(e) { sysLog(`✗ ${e.message}`); toast('Error', 'error'); }
});
