// ── BLOG ──
let allPosts = [];

const loadBlog = async () => {
  try {
    const data = await fetch(`${API}?action=get_blog&auth=${AKEY}`).then(r=>r.json());
    allPosts = Array.isArray(data) ? data : (data.data || []);
    document.querySelector('#blogTable tbody').innerHTML =
      allPosts.map(p => `<tr>
        <td><img src="${p.img_url||'/img/placeholder.svg'}" style="width:40px;height:40px;object-fit:cover;border-radius:4px"></td>
        <td>${p.title}</td>
        <td>${p.category||'General'}</td>
        <td>${new Date(p.created_at).toLocaleDateString('es-CL')}</td>
        <td>
          <button class="btn btn-ghost btn-icon btn-sm" onclick="editPost(${p.id})"><i class="fa fa-pen"></i></button>
          <button class="btn btn-ghost btn-icon btn-sm" onclick="deletePost(${p.id})"><i class="fa fa-trash"></i></button>
        </td>
      </tr>`).join('') || '<tr><td colspan="5" class="table-empty">Sin posts</td></tr>';
  } catch(e) { toast('Error cargando blog', 'error'); }
};

document.getElementById('btnNewPost').addEventListener('click', () => {
  document.getElementById('blogModalTitle').textContent = 'Nuevo Post';
  ['blogId','blogTitle','blogContent'].forEach(id => document.getElementById(id).value = '');
  document.getElementById('blogCategory').value = 'General';
  document.getElementById('blogImgPreview').style.display = 'none';
  document.getElementById('blogImgIcon').style.display = 'block';
  document.getElementById('blogImg').value = '';
  openModal('blogModal');
});

window.editPost = (id) => {
  const p = allPosts.find(x => x.id == id);
  if(!p) return;
  document.getElementById('blogModalTitle').textContent = 'Editar Post';
  document.getElementById('blogId').value       = p.id;
  document.getElementById('blogTitle').value    = p.title;
  document.getElementById('blogCategory').value = p.category||'General';
  document.getElementById('blogContent').value  = p.content||'';
  const hasImg = p.img_url && !p.img_url.includes('placeholder');
  document.getElementById('blogImgPreview').src          = hasImg ? p.img_url : '';
  document.getElementById('blogImgPreview').style.display = hasImg ? 'block' : 'none';
  document.getElementById('blogImgIcon').style.display    = hasImg ? 'none'  : 'block';
  openModal('blogModal');
};

window.deletePost = (id) => {
  confirmDelete('¿Eliminar este post?', async () => {
    const fd = new FormData(); fd.append('auth', AKEY); fd.append('id', id);
    const data = await fetch(`${API}?action=delete_blog`, {method:'POST', body:fd}).then(r=>r.json());
    if(data.success) { toast('Post eliminado', 'success'); loadBlog(); }
    else toast(data.error||'Error', 'error');
  });
};

document.getElementById('blogImg').addEventListener('change', e => {
  const file = e.target.files[0];
  if(!file) return;
  const reader = new FileReader();
  reader.onload = ev => {
    document.getElementById('blogImgPreview').src = ev.target.result;
    document.getElementById('blogImgPreview').style.display = 'block';
    document.getElementById('blogImgIcon').style.display = 'none';
  };
  reader.readAsDataURL(file);
});

document.getElementById('btnSavePost').addEventListener('click', async () => {
  const id      = document.getElementById('blogId').value;
  const title   = document.getElementById('blogTitle').value.trim();
  const content = document.getElementById('blogContent').value.trim();
  if(!title || !content) return toast('Título y contenido son requeridos', 'error');
  const fd = new FormData();
  fd.append('auth', AKEY); fd.append('title', title);
  fd.append('category', document.getElementById('blogCategory').value);
  fd.append('content', content);
  if(id) fd.append('id', id);
  const imgFile = document.getElementById('blogImg').files[0];
  if(imgFile) fd.append('file', imgFile);
  const data = await fetch(`${API}?action=save_blog`, {method:'POST', body:fd}).then(r=>r.json());
  if(data.success) { toast(id?'Post actualizado':'Post creado', 'success'); closeModal('blogModal'); loadBlog(); }
  else toast(data.error||'Error', 'error');
});
