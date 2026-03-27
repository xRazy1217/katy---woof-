// ── PRODUCTOS ──
let allProducts = [];
let allCategories = [];

const loadProducts = async () => {
  try {
    const [pRes, cRes] = await Promise.all([
      fetch(`${API}?action=get_products&auth=${AKEY}`).then(r=>r.json()),
      fetch(`${API}?action=get_categories&auth=${AKEY}`).then(r=>r.json())
    ]);
    allProducts   = pRes.data  || [];
    allCategories = cRes.data  || [];
    renderProducts(allProducts);
    const sel = document.getElementById('productCategory');
    sel.innerHTML = '<option value="">Sin categoría</option>' +
      allCategories.map(c => `<option value="${c.id}">${c.name}</option>`).join('');
  } catch(e) { toast('Error cargando productos', 'error'); }
};

const renderProducts = (products) => {
  document.querySelector('#productsTable tbody').innerHTML =
    products.map(p => `<tr>
      <td><img src="${p.image_url||'/uploads/placeholder-product.svg'}" style="width:40px;height:40px;object-fit:cover;border-radius:4px"></td>
      <td>${p.name}</td>
      <td>${p.category_name||'—'}</td>
      <td>${fmt(p.price)}</td>
      <td>${p.stock_quantity}</td>
      <td><span class="badge badge-${p.status}">${p.status}</span></td>
      <td>
        <button class="btn btn-ghost btn-icon btn-sm" onclick="editProduct(${p.id})"><i class="fa fa-pen"></i></button>
        <button class="btn btn-ghost btn-icon btn-sm" onclick="deleteProduct(${p.id})"><i class="fa fa-trash"></i></button>
      </td>
    </tr>`).join('') || '<tr><td colspan="7" class="table-empty">Sin productos</td></tr>';
};

document.getElementById('productSearch').addEventListener('input', e => {
  renderProducts(allProducts.filter(p => p.name.toLowerCase().includes(e.target.value.toLowerCase())));
});

document.getElementById('btnNewProduct').addEventListener('click', () => {
  document.getElementById('productModalTitle').textContent = 'Nuevo Producto';
  ['productId','productName','productSku','productDesc','productPrice','productSalePrice','productImgUrl'].forEach(id => document.getElementById(id).value = '');
  document.getElementById('productStock').value = '0';
  document.getElementById('productStatus').value = 'publish';
  document.getElementById('productImgPreview').style.display = 'none';
  document.getElementById('productImgIcon').style.display = 'block';
  document.getElementById('productImg').value = '';
  openModal('productModal');
});

window.editProduct = (id) => {
  const p = allProducts.find(x => x.id == id);
  if(!p) return;
  document.getElementById('productModalTitle').textContent = 'Editar Producto';
  document.getElementById('productId').value        = p.id;
  document.getElementById('productName').value      = p.name;
  document.getElementById('productSku').value       = p.sku || '';
  document.getElementById('productDesc').value      = p.description || '';
  document.getElementById('productPrice').value     = p.price;
  document.getElementById('productSalePrice').value = p.sale_price || '';
  document.getElementById('productStock').value     = p.stock_quantity;
  document.getElementById('productCategory').value  = p.category_id || '';
  document.getElementById('productStatus').value    = p.status;
  document.getElementById('productImgUrl').value    = p.image_url || '';
  if(p.image_url) {
    document.getElementById('productImgPreview').src = p.image_url;
    document.getElementById('productImgPreview').style.display = 'block';
    document.getElementById('productImgIcon').style.display = 'none';
  }
  openModal('productModal');
};

window.deleteProduct = (id) => {
  confirmDelete('¿Eliminar este producto?', async () => {
    const fd = new FormData(); fd.append('auth', AKEY); fd.append('id', id);
    const data = await fetch(`${API}?action=delete_product`, {method:'POST', body:fd}).then(r=>r.json());
    if(data.success) { toast('Producto eliminado', 'success'); loadProducts(); }
    else toast(data.error||'Error', 'error');
  });
};

document.getElementById('btnSaveProduct').addEventListener('click', async () => {
  const id    = document.getElementById('productId').value;
  const name  = document.getElementById('productName').value.trim();
  const price = document.getElementById('productPrice').value;
  if(!name || !price) return toast('Nombre y precio son requeridos', 'error');
  const fd = new FormData();
  fd.append('auth', AKEY); fd.append('name', name);
  fd.append('sku', document.getElementById('productSku').value);
  fd.append('description', document.getElementById('productDesc').value);
  fd.append('price', price);
  fd.append('sale_price', document.getElementById('productSalePrice').value || 0);
  fd.append('stock_quantity', document.getElementById('productStock').value);
  fd.append('category_id', document.getElementById('productCategory').value);
  fd.append('status', document.getElementById('productStatus').value);
  const imgFile = document.getElementById('productImg').files[0];
  if(imgFile) fd.append('image', imgFile);
  if(id) fd.append('id', id);
  const data = await fetch(`${API}?action=${id?'update_product':'create_product'}`, {method:'POST', body:fd}).then(r=>r.json());
  if(data.success) { toast(id?'Producto actualizado':'Producto creado', 'success'); closeModal('productModal'); loadProducts(); }
  else toast(data.error||'Error', 'error');
});

document.getElementById('productImg').addEventListener('change', e => {
  const file = e.target.files[0];
  if(!file) return;
  const reader = new FileReader();
  reader.onload = ev => {
    document.getElementById('productImgPreview').src = ev.target.result;
    document.getElementById('productImgPreview').style.display = 'block';
    document.getElementById('productImgIcon').style.display = 'none';
  };
  reader.readAsDataURL(file);
});

// ── CATEGORÍAS ──
const loadCategories = async () => {
  try {
    const data = await fetch(`${API}?action=get_categories&auth=${AKEY}`).then(r=>r.json());
    allCategories = data.data || [];
    document.querySelector('#categoriesTable tbody').innerHTML =
      allCategories.map(c => `<tr>
        <td>${c.name}</td><td>${c.slug}</td><td>${c.description||'—'}</td>
        <td>
          <button class="btn btn-ghost btn-icon btn-sm" onclick="editCategory(${c.id})"><i class="fa fa-pen"></i></button>
          <button class="btn btn-ghost btn-icon btn-sm" onclick="deleteCategory(${c.id})"><i class="fa fa-trash"></i></button>
        </td>
      </tr>`).join('') || '<tr><td colspan="4" class="table-empty">Sin categorías</td></tr>';
  } catch(e) { toast('Error cargando categorías', 'error'); }
};

document.getElementById('btnNewCategory').addEventListener('click', () => {
  document.getElementById('categoryModalTitle').textContent = 'Nueva Categoría';
  ['categoryId','categoryName','categorySlug','categoryDesc'].forEach(id => document.getElementById(id).value = '');
  openModal('categoryModal');
});

window.editCategory = (id) => {
  const c = allCategories.find(x => x.id == id);
  if(!c) return;
  document.getElementById('categoryModalTitle').textContent = 'Editar Categoría';
  document.getElementById('categoryId').value   = c.id;
  document.getElementById('categoryName').value = c.name;
  document.getElementById('categorySlug').value = c.slug;
  document.getElementById('categoryDesc').value = c.description || '';
  openModal('categoryModal');
};

window.deleteCategory = (id) => {
  confirmDelete('¿Eliminar esta categoría?', async () => {
    const fd = new FormData(); fd.append('auth', AKEY); fd.append('id', id);
    const data = await fetch(`${API}?action=delete_category`, {method:'POST', body:fd}).then(r=>r.json());
    if(data.success) { toast('Categoría eliminada', 'success'); loadCategories(); }
    else toast(data.error||'Error', 'error');
  });
};

document.getElementById('btnSaveCategory').addEventListener('click', async () => {
  const id   = document.getElementById('categoryId').value;
  const name = document.getElementById('categoryName').value.trim();
  if(!name) return toast('Nombre es requerido', 'error');
  const body = { name, slug: document.getElementById('categorySlug').value, description: document.getElementById('categoryDesc').value };
  if(id) body.id = id;
  const data = await fetch(`${API}?action=${id?'update_category':'create_category'}&auth=${AKEY}`, {
    method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(body)
  }).then(r=>r.json());
  if(data.success) { toast(id?'Categoría actualizada':'Categoría creada', 'success'); closeModal('categoryModal'); loadCategories(); }
  else toast(data.error||'Error', 'error');
});
