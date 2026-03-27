// ── AJUSTES ──
const loadSettings = async () => {
  try {
    const data = await fetch(`${API}?action=get_settings&auth=${AKEY}`).then(r=>r.json());
    const imageKeys = ['site_logo','site_favicon','hero_image','nosotros_image'];
    data.forEach(s => {
      const input = document.querySelector(`[name="${s.setting_key}"]`);
      if(input && input.type !== 'file') input.value = s.setting_value || '';
      if(imageKeys.includes(s.setting_key) && s.setting_value && !s.setting_value.includes('placeholder')) {
        const prev = document.getElementById('prev_' + s.setting_key);
        const icon = document.getElementById('icon_' + s.setting_key);
        if(prev) { prev.src = s.setting_value; prev.style.display = 'block'; }
        if(icon) icon.style.display = 'none';
      }
    });
  } catch(e) { toast('Error cargando ajustes', 'error'); }
};

window.previewSettingImg = (input, previewId) => {
  const file = input.files[0];
  if(!file) return;
  const reader = new FileReader();
  reader.onload = ev => {
    const prev = document.getElementById(previewId);
    const icon = document.getElementById('icon_' + input.name);
    if(prev) { prev.src = ev.target.result; prev.style.display = 'block'; }
    if(icon) icon.style.display = 'none';
  };
  reader.readAsDataURL(file);
};

document.getElementById('settingsForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  const fd = new FormData(e.target);
  fd.append('auth', AKEY);
  try {
    const data = await fetch(`${API}?action=save_settings`, {method:'POST', body:fd}).then(r=>r.json());
    if(data.success) toast('Ajustes guardados', 'success');
    else toast(data.error||'Error', 'error');
  } catch(e) { toast('Error guardando ajustes', 'error'); }
});
