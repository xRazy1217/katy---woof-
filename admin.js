/**
 * Katy & Woof - Admin Logic v1.0 (Clean Architecture)
 */
const AUTH_KEY = 'Asesor25';
const v = () => Date.now();

// --- AUTH & NAVIGATION ---
function attemptAuth() {
    if(document.getElementById('auth-input').value === AUTH_KEY) {
        localStorage.setItem('kw_admin', 'ok');
        unlock();
    } else alert("Acceso Denegado");
}
function unlock() {
    document.getElementById('login-portal').classList.add('hidden');
    document.getElementById('admin-content').classList.remove('hidden');
    setTimeout(() => document.getElementById('admin-content').style.opacity = '1', 100);
    loadAll();
}
if(localStorage.getItem('kw_admin') === 'ok') unlock();

function switchTab(id) {
    document.querySelectorAll('.tab-content').forEach(c => c.classList.add('hidden'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById(`tab-${id}`).classList.remove('hidden');
    document.querySelector(`[data-tab="${id}"]`).classList.add('active');
}

// --- UTILS ---
const toggleLoading = (show) => document.getElementById('loading-screen').style.display = show ? 'flex' : 'none';
const showToast = (m) => { const t=document.getElementById('toast'); t.innerText=m; t.classList.add('active'); setTimeout(()=>t.classList.remove('active'),3000); };

// --- CORE LOADERS ---
async function loadAll() {
    toggleLoading(true);
    await loadLists();
    await loadSettings();
    await Promise.all([loadPortfolio(), loadServices(), loadBlog()]);
    toggleLoading(false);
}

async function loadLists() {
    const res = await fetch(`api.php?action=get_lists&v=${v()}`);
    const lists = await res.json();
    
    const populate = (id, key) => {
        const el = document.getElementById(id); if(!el) return;
        const items = lists.filter(l => l.list_key === key);
        el.innerHTML = items.map(i => `<option value="${i.item_value}">${i.item_value}</option>`).join('');
    };
    
    populate('art-style', 'art_styles');
    populate('service-category', 'service_categories');
    populate('blog-category', 'blog_categories');

    ['art_styles', 'service_categories', 'blog_categories'].forEach(key => {
        const listEl = document.getElementById(`list-${key}`); if(!listEl) return;
        listEl.innerHTML = lists.filter(l => l.list_key === key).map(i => `
            <div class="flex justify-between items-center bg-white p-3 rounded-xl border border-stone-100 text-[10px]">
                <span class="font-bold uppercase">${i.item_value}</span>
                <button onclick="deleteListItem(${i.id})" class="text-red-400 hover:text-red-600">×</button>
            </div>
        `).join('');
    });
}

// --- TAXONOMY CRUD ---
async function addListItem(key) {
    const auth = localStorage.getItem('kw_admin_key') || '';
    const val = document.getElementById(`new-${key}`).value; if(!val) return;
    const fd = new FormData(); fd.append('list_key', key); fd.append('item_value', val);
    await fetch(`api.php?action=save_list_item&auth=${auth}`, { method: 'POST', body: fd });
    document.getElementById(`new-${key}`).value = '';
    await loadLists();
    showToast("Categoría Guardada");
}

async function deleteListItem(id) {
    const auth = localStorage.getItem('kw_admin_key') || '';
    if(!confirm("¿Eliminar?")) return;
    await fetch(`api.php?action=delete_list_item&id=${id}&auth=${auth}&force_delete=1`, { method: 'DELETE' });
    await loadLists();
    showToast("Eliminado");
}

// --- IDENTITY & SETTINGS ---
async function loadSettings() {
    const res = await fetch(`api.php?action=get_settings&v=${v()}`);
    const settings = await res.json();
    settings.forEach(s => {
        const el = document.getElementById(`setting-${s.setting_key}`) || document.getElementById(`id-${s.setting_key}`);
        if(el) el.value = s.setting_value;
        if(s.setting_key === 'site_logo') document.getElementById('preview-logo').src = s.setting_value + '?v=' + v();
        if(s.setting_key === 'site_favicon') document.getElementById('preview-favicon').src = s.setting_value + '?v=' + v();
    });
}

async function saveIdentity(e) {
    e.preventDefault(); toggleLoading(true);
    const auth = localStorage.getItem('kw_admin_key') || '';
    const fd = new FormData();
    ['contact_email', 'contact_whatsapp', 'contact_address'].forEach(k => fd.append(k, document.getElementById(`id-${k}`).value));
    const lIn = document.getElementById('site-logo-input'); if(lIn.files[0]) fd.append('site_logo', lIn.files[0]);
    const fIn = document.getElementById('site-favicon-input'); if(fIn.files[0]) fd.append('site_favicon', fIn.files[0]);
    await fetch(`api.php?action=save_settings&auth=${auth}`, { method: 'POST', body: fd });
    await loadSettings(); toggleLoading(false); showToast("Marca Sincronizada");
}

async function saveSettings(e) {
    e.preventDefault(); toggleLoading(true);
    const auth = localStorage.getItem('kw_admin_key') || '';
    const fd = new FormData(); fd.append('our_history', document.getElementById('setting-our_history').value);
    await fetch(`api.php?action=save_settings&auth=${auth}`, { method: 'POST', body: fd });
    toggleLoading(false); showToast("Textos Guardados");
}

// --- CONTENT CRUD ---
async function loadPortfolio() {
    const res = await fetch(`api.php?v=${v()}`); const data = await res.json();
    document.getElementById('portfolio-list').innerHTML = data.map(i => `
        <div class="aspect-square bg-stone-100 rounded-lg overflow-hidden relative group">
            <img src="${i.img_url}" class="w-full h-full object-cover">
            <button onclick="deleteArt(${i.id})" class="absolute inset-0 bg-red-500/80 text-white opacity-0 group-hover:opacity-100 transition-opacity uppercase text-[8px] font-black">Eliminar</button>
        </div>
    `).join('');
}
async function savePortfolio(e) {
    const auth = localStorage.getItem('kw_admin_key') || '';
    e.preventDefault(); const file = document.getElementById('art-file').files[0]; if(!file) return;
    toggleLoading(true); const fd = new FormData(); fd.append('name', document.getElementById('art-name').value); fd.append('style', document.getElementById('art-style').value); fd.append('file', file);
    await fetch(`api.php?auth=${auth}`, { method: 'POST', body: fd }); await loadPortfolio(); toggleLoading(false); document.getElementById('portfolio-form').reset();
}
async function deleteArt(id) { 
    const auth = localStorage.getItem('kw_admin_key') || '';
    await fetch(`api.php?action=delete_portfolio&id=${id}&auth=${auth}`); loadPortfolio(); 
}

async function loadServices() {
    const res = await fetch(`api.php?action=get_services&v=${v()}`); const data = await res.json();
    document.getElementById('services-list').innerHTML = data.map(s => `<div class="bg-white p-3 rounded-xl flex justify-between items-center text-[10px]"><span>${s.title}</span><button onclick="deleteService(${s.id})" class="text-red-400">×</button></div>`).join('');
}
async function saveService(e) {
    const auth = localStorage.getItem('kw_admin_key') || '';
    e.preventDefault(); toggleLoading(true); const fd = new FormData(); fd.append('title', document.getElementById('service-title').value); fd.append('category', document.getElementById('service-category').value); fd.append('description', document.getElementById('service-desc').value); const file = document.getElementById('service-file').files[0]; if(file) fd.append('main_file', file);
    await fetch(`api.php?action=save_service&auth=${auth}`, { method: 'POST', body: fd }); await loadServices(); toggleLoading(false); document.getElementById('service-form').reset();
}
async function deleteService(id) { 
    const auth = localStorage.getItem('kw_admin_key') || '';
    await fetch(`api.php?action=delete_service&id=${id}&auth=${auth}`); loadServices(); 
}

async function loadBlog() {
    const res = await fetch(`api.php?action=get_blog&v=${v()}`); const data = await res.json();
    document.getElementById('blog-list').innerHTML = data.map(p => `<div class="bg-white p-3 rounded-xl flex justify-between items-center text-[10px]"><span>${p.title}</span><button onclick="deleteBlog(${p.id})" class="text-red-400">×</button></div>`).join('');
}
async function saveBlog(e) {
    const auth = localStorage.getItem('kw_admin_key') || '';
    e.preventDefault(); toggleLoading(true); const fd = new FormData(); fd.append('title', document.getElementById('blog-title').value); fd.append('category', document.getElementById('blog-category').value); fd.append('content', document.getElementById('blog-content').value); const file = document.getElementById('blog-file').files[0]; if(file) fd.append('file', file);
    await fetch(`api.php?action=save_blog&auth=${auth}`, { method: 'POST', body: fd }); await loadBlog(); toggleLoading(false); document.getElementById('blog-form').reset();
}
async function deleteBlog(id) { 
    const auth = localStorage.getItem('kw_admin_key') || '';
    await fetch(`api.php?action=delete_blog&id=${id}&auth=${auth}`); loadBlog(); 
}