// ── AJUSTES ──
let quillEditors = {};

const loadSettings = async () => {
  try {
    const data = await fetch(`${API}?action=get_settings&auth=${AKEY}`).then(r=>r.json());
    
    // Inicializar editores Quill
    document.querySelectorAll('.rich-editor').forEach(el => {
      const key = el.dataset.key;
      if (!quillEditors[key]) {
        quillEditors[key] = new Quill(el, {
          theme: 'snow',
          modules: {
            toolbar: [
              ['bold', 'italic', 'underline'],
              [{ 'list': 'ordered'}, { 'list': 'bullet' }],
              ['clean']
            ]
          }
        });
      }
    });

    data.forEach(s => {
      if (quillEditors[s.setting_key]) {
        quillEditors[s.setting_key].root.innerHTML = s.setting_value || '';
        return;
      }
      const input = document.querySelector(`[name="${s.setting_key}"]`);
      if(input) {
        if(input.type !== 'file') {
          input.value = s.setting_value || '';
        } else {
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
  } catch(e) { console.error(e); toast('Error cargando ajustes', 'error'); }
};

window.saveRichSetting = async (key) => {
  const editor = quillEditors[key];
  if (!editor) return;
  const fd = new FormData();
  fd.append('auth', AKEY);
  fd.append(key, editor.root.innerHTML);
  try {
    const data = await fetch(`${API}?action=save_settings`, {method:'POST', body:fd}).then(r=>r.json());
    if(data.success) toast('Ajuste guardado', 'success');
    else toast(data.error||'Error', 'error');
  } catch(e) { toast('Error guardando ajuste', 'error'); }
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
