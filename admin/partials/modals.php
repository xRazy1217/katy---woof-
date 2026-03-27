<!-- Product Modal -->
<div class="modal-overlay" id="productModal">
  <div class="modal modal-lg">
    <div class="modal-header">
      <h3 id="productModalTitle">Nuevo Producto</h3>
      <button class="modal-close" data-close="productModal"><i class="fa fa-xmark"></i></button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="productId">
      <div class="form-row">
        <div class="form-group"><label>Nombre *</label><input type="text" id="productName" class="input" required></div>
        <div class="form-group"><label>SKU</label><input type="text" id="productSku" class="input" placeholder="Ej: KW-001"></div>
      </div>
      <div class="form-group"><label>Descripción</label><textarea id="productDesc" class="textarea"></textarea></div>
      <div class="form-row-3">
        <div class="form-group"><label>Precio (CLP) *</label><input type="number" id="productPrice" class="input" min="0"></div>
        <div class="form-group"><label>Precio oferta</label><input type="number" id="productSalePrice" class="input" min="0" placeholder="0 = sin oferta"></div>
        <div class="form-group"><label>Stock</label><input type="number" id="productStock" class="input" min="0" value="0"></div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Categoría</label>
          <select id="productCategory" class="select"><option value="">Sin categoría</option></select>
        </div>
        <div class="form-group">
          <label>Estado</label>
          <select id="productStatus" class="select">
            <option value="publish">Publicado</option>
            <option value="draft">Borrador</option>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label>Imagen del producto</label>
        <div class="img-upload-area" id="productImgArea">
          <input type="file" id="productImg" accept="image/*">
          <img id="productImgPreview" class="img-preview" src="" style="display:none">
          <i class="fa fa-cloud-arrow-up" id="productImgIcon"></i>
          <p>Haz clic o arrastra una imagen</p>
        </div>
        <input type="hidden" id="productImgUrl">
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" data-close="productModal">Cancelar</button>
      <button class="btn btn-primary" id="btnSaveProduct"><i class="fa fa-floppy-disk"></i> Guardar</button>
    </div>
  </div>
</div>

<!-- Category Modal -->
<div class="modal-overlay" id="categoryModal">
  <div class="modal">
    <div class="modal-header">
      <h3 id="categoryModalTitle">Nueva Categoría</h3>
      <button class="modal-close" data-close="categoryModal"><i class="fa fa-xmark"></i></button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="categoryId">
      <div class="form-group"><label>Nombre *</label><input type="text" id="categoryName" class="input" required></div>
      <div class="form-group"><label>Slug</label><input type="text" id="categorySlug" class="input" placeholder="se-genera-automático"></div>
      <div class="form-group"><label>Descripción</label><textarea id="categoryDesc" class="textarea" style="min-height:70px"></textarea></div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" data-close="categoryModal">Cancelar</button>
      <button class="btn btn-primary" id="btnSaveCategory"><i class="fa fa-floppy-disk"></i> Guardar</button>
    </div>
  </div>
</div>

<!-- Coupon Modal -->
<div class="modal-overlay" id="couponModal">
  <div class="modal">
    <div class="modal-header">
      <h3 id="couponModalTitle">Nuevo Cupón</h3>
      <button class="modal-close" data-close="couponModal"><i class="fa fa-xmark"></i></button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="couponId">
      <div class="form-row">
        <div class="form-group"><label>Código *</label><input type="text" id="couponCode" class="input" placeholder="DESCUENTO20" style="text-transform:uppercase"></div>
        <div class="form-group">
          <label>Tipo</label>
          <select id="couponType" class="select">
            <option value="percentage">Porcentaje (%)</option>
            <option value="fixed">Monto fijo (CLP)</option>
          </select>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group"><label>Valor *</label><input type="number" id="couponValue" class="input" min="0"></div>
        <div class="form-group"><label>Mínimo de compra</label><input type="number" id="couponMinSpend" class="input" min="0" placeholder="0 = sin mínimo"></div>
      </div>
      <div class="form-row">
        <div class="form-group"><label>Límite de usos</label><input type="number" id="couponLimit" class="input" min="0" placeholder="0 = ilimitado"></div>
        <div class="form-group"><label>Fecha de vencimiento</label><input type="date" id="couponExpiry" class="input"></div>
      </div>
      <div class="form-group"><label>Descripción</label><input type="text" id="couponDesc" class="input"></div>
      <div class="toggle-wrap">
        <div class="toggle on" id="couponActiveToggle"></div>
        <span class="toggle-label">Cupón activo</span>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" data-close="couponModal">Cancelar</button>
      <button class="btn btn-primary" id="btnSaveCoupon"><i class="fa fa-floppy-disk"></i> Guardar</button>
    </div>
  </div>
</div>

<!-- Order Modal -->
<div class="modal-overlay" id="orderModal">
  <div class="modal modal-lg">
    <div class="modal-header">
      <h3 id="orderModalTitle">Detalle de Orden</h3>
      <button class="modal-close" data-close="orderModal"><i class="fa fa-xmark"></i></button>
    </div>
    <div class="modal-body" id="orderModalBody">Cargando...</div>
    <div class="modal-footer">
      <label style="margin:0;font-size:0.8rem;color:var(--mid)">Cambiar estado:</label>
      <select class="select input-sm" id="orderStatusSelect" style="width:160px">
        <option value="pending">Pendiente</option>
        <option value="processing">Procesando</option>
        <option value="shipped">Enviado</option>
        <option value="completed">Completado</option>
        <option value="cancelled">Cancelado</option>
      </select>
      <button class="btn btn-primary btn-sm" id="btnUpdateOrderStatus"><i class="fa fa-check"></i> Actualizar</button>
      <button class="btn btn-ghost" data-close="orderModal">Cerrar</button>
    </div>
  </div>
</div>

<!-- Blog Modal -->
<div class="modal-overlay" id="blogModal">
  <div class="modal modal-lg">
    <div class="modal-header">
      <h3 id="blogModalTitle">Nuevo Post</h3>
      <button class="modal-close" data-close="blogModal"><i class="fa fa-xmark"></i></button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="blogId">
      <div class="form-group"><label>Título *</label><input type="text" id="blogTitle" class="input" required></div>
      <div class="form-group">
        <label>Categoría</label>
        <select id="blogCategory" class="select">
          <option>General</option><option>Consejos</option><option>Noticias</option><option>Arte</option><option>Mascotas</option>
        </select>
      </div>
      <div class="form-group"><label>Contenido *</label><textarea id="blogContent" class="textarea" style="min-height:160px" required></textarea></div>
      <div class="form-group">
        <label>Imagen</label>
        <div class="img-upload-area">
          <input type="file" id="blogImg" accept="image/*">
          <img id="blogImgPreview" class="img-preview" src="" style="display:none">
          <i class="fa fa-cloud-arrow-up" id="blogImgIcon"></i>
          <p>Haz clic o arrastra una imagen</p>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" data-close="blogModal">Cancelar</button>
      <button class="btn btn-primary" id="btnSavePost"><i class="fa fa-floppy-disk"></i> Guardar</button>
    </div>
  </div>
</div>

<!-- Message Modal -->
<div class="modal-overlay" id="messageModal">
  <div class="modal">
    <div class="modal-header">
      <h3>Mensaje de contacto</h3>
      <button class="modal-close" data-close="messageModal"><i class="fa fa-xmark"></i></button>
    </div>
    <div class="modal-body" id="messageModalBody"></div>
    <div class="modal-footer">
      <button class="btn btn-ghost" data-close="messageModal">Cerrar</button>
    </div>
  </div>
</div>

<!-- Confirm Modal -->
<div class="modal-overlay" id="confirmModal">
  <div class="modal" style="max-width:400px">
    <div class="modal-header">
      <h3>Confirmar eliminación</h3>
      <button class="modal-close" data-close="confirmModal"><i class="fa fa-xmark"></i></button>
    </div>
    <div class="modal-body">
      <p id="confirmMsg">¿Estás segura de que deseas eliminar este elemento?</p>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" data-close="confirmModal">Cancelar</button>
      <button class="btn btn-danger" id="btnConfirmDelete"><i class="fa fa-trash"></i> Eliminar</button>
    </div>
  </div>
</div>

<div class="toast-container" id="toastContainer"></div>
