// ── AJUSTES ──
const loadSettings = async () => {
  try {
    const data = await fetch(`${API}?action=get_settings&auth=${AKEY}`).then(r=>r.json());
    data.forEach(s => {
      const input = document.querySelector(`[name="${s.setting_key}"]`);
      if(input) {
        if(input.type !== 'file') {
          input.value = s.setting_value || '';
        } else {
          // Si es un archivo, intentar mostrar preview si hay valor (URL)
          const prevId = 'prev_' + s.setting_key;
          const prevImg = document.getElementById(prevId);
          const icon = document.getElementById('icon_' + s.setting_key);
          if(prevImg && s.setting_value && !s.setting_value.includes('placeholder')) {
            prevImg.src = s.setting_value;
            prevImg.style.display = 'block';
            if(icon) icon.style.display = 'none';
          }
        }
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

window.saveIndividualSetting = async (key) => {
  const input = document.querySelector(`[name="${key}"]`);
  if (!input) return;

  const fd = new FormData();
  fd.append('auth', AKEY);
  
  if (input.type === 'file') {
    if (!input.files[0]) return toast('Selecciona un archivo primero', 'error');
    fd.append(key, input.files[0]);
  } else {
    fd.append(key, input.value);
  }

  try {
    const data = await fetch(`${API}?action=save_settings`, {method:'POST', body:fd}).then(r=>r.json());
    if(data.success) {
      toast('Ajuste guardado', 'success');
      if (input.type === 'file') loadSettings(); // Recargar para ver URL final
    } else {
      toast(data.error||'Error', 'error');
    }
  } catch(e) { toast('Error guardando ajuste', 'error'); }
};

document.getElementById('settingsForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  const fd = new FormData(e.target);
  fd.append('auth', AKEY);
  try {
    const data = await fetch(`${API}?action=save_settings`, {method:'POST', body:fd}).then(r=>r.json());
    if(data.success) toast('Todos los ajustes guardados', 'success');
    else toast(data.error||'Error', 'error');
  } catch(e) { toast('Error guardando ajustes', 'error'); }
});
