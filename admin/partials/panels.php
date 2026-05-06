<!-- DASHBOARD -->
<div class="panel active" id="panel-dashboard">
  <div class="stats-row" id="dashStats">
    <div class="stat-card"><div class="stat-card-label">Productos</div><div class="stat-card-value" id="ds-products">—</div></div>
    <div class="stat-card"><div class="stat-card-label">Categorías</div><div class="stat-card-value" id="ds-categories">—</div></div>
    <div class="stat-card accent"><div class="stat-card-label">Órdenes</div><div class="stat-card-value" id="ds-orders">—</div></div>
    <div class="stat-card"><div class="stat-card-label">Cupones</div><div class="stat-card-value" id="ds-coupons">—</div></div>
  </div>
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
    <div class="table-wrap" style="padding:1.5rem">
      <h3 style="font-size:0.9rem;margin-bottom:1rem;color:var(--light)"><i class="fa fa-receipt" style="color:var(--accent);margin-right:0.4rem"></i>Últimas órdenes</h3>
      <table class="data-table" id="dashOrdersTable">
        <thead><tr><th>#</th><th>Cliente</th><th>Total</th><th>Estado</th></tr></thead>
        <tbody><tr><td colspan="4" class="table-empty">Cargando...</td></tr></tbody>
      </table>
    </div>
    <div class="table-wrap" style="padding:1.5rem">
      <h3 style="font-size:0.9rem;margin-bottom:1rem;color:var(--light)"><i class="fa fa-box" style="color:var(--accent);margin-right:0.4rem"></i>Productos recientes</h3>
      <table class="data-table" id="dashProductsTable">
        <thead><tr><th>Nombre</th><th>Precio</th><th>Stock</th></tr></thead>
        <tbody><tr><td colspan="3" class="table-empty">Cargando...</td></tr></tbody>
      </table>
    </div>
  </div>
</div>

<!-- PRODUCTOS -->
<div class="panel" id="panel-products">
  <div class="toolbar">
    <div class="toolbar-left">
      <h2>Productos</h2>
      <div class="search-input-wrap">
        <i class="fa fa-search"></i>
        <input type="text" class="input input-sm" id="productSearch" placeholder="Buscar..." style="width:200px">
      </div>
    </div>
    <button class="btn btn-primary btn-sm" id="btnNewProduct"><i class="fa fa-plus"></i> Nuevo producto</button>
  </div>
  <div class="table-wrap">
    <table class="data-table" id="productsTable">
      <thead><tr><th>Imagen</th><th>Nombre</th><th>Categoría</th><th>Precio</th><th>Stock</th><th>Estado</th><th>Acciones</th></tr></thead>
      <tbody><tr><td colspan="7" class="table-empty">Cargando...</td></tr></tbody>
    </table>
  </div>
</div>

<!-- CATEGORÍAS -->
<div class="panel" id="panel-categories">
  <div class="toolbar">
    <h2>Categorías</h2>
    <button class="btn btn-primary btn-sm" id="btnNewCategory"><i class="fa fa-plus"></i> Nueva categoría</button>
  </div>
  <div class="table-wrap">
    <table class="data-table" id="categoriesTable">
      <thead><tr><th>Nombre</th><th>Slug</th><th>Descripción</th><th>Acciones</th></tr></thead>
      <tbody><tr><td colspan="4" class="table-empty">Cargando...</td></tr></tbody>
    </table>
  </div>
</div>

<!-- ÓRDENES -->
<div class="panel" id="panel-orders">
  <div class="toolbar">
    <div class="toolbar-left">
      <h2>Órdenes</h2>
      <select class="select input-sm" id="orderStatusFilter" style="width:150px">
        <option value="">Todos los estados</option>
        <option value="pending">Pendiente</option>
        <option value="processing">Procesando</option>
        <option value="shipped">Enviado</option>
        <option value="completed">Completado</option>
        <option value="cancelled">Cancelado</option>
      </select>
    </div>
  </div>
  <div class="table-wrap">
    <table class="data-table" id="ordersTable">
      <thead><tr><th>#Orden</th><th>Cliente</th><th>Email</th><th>Total</th><th>Estado</th><th>Fecha</th><th>Acciones</th></tr></thead>
      <tbody><tr><td colspan="7" class="table-empty">Cargando...</td></tr></tbody>
    </table>
  </div>
</div>

<!-- CUPONES -->
<div class="panel" id="panel-coupons">
  <div class="toolbar">
    <h2>Cupones</h2>
    <button class="btn btn-primary btn-sm" id="btnNewCoupon"><i class="fa fa-plus"></i> Nuevo cupón</button>
  </div>
  <div class="table-wrap">
    <table class="data-table" id="couponsTable">
      <thead><tr><th>Código</th><th>Tipo</th><th>Descuento</th><th>Usos</th><th>Vencimiento</th><th>Estado</th><th>Acciones</th></tr></thead>
      <tbody><tr><td colspan="7" class="table-empty">Cargando...</td></tr></tbody>
    </table>
  </div>
</div>

<!-- BLOG -->
<div class="panel" id="panel-blog">
  <div class="toolbar">
    <h2>Blog</h2>
    <button class="btn btn-primary btn-sm" id="btnNewPost"><i class="fa fa-plus"></i> Nuevo post</button>
  </div>
  <div class="table-wrap">
    <table class="data-table" id="blogTable">
      <thead><tr><th>Imagen</th><th>Título</th><th>Categoría</th><th>Fecha</th><th>Acciones</th></tr></thead>
      <tbody><tr><td colspan="5" class="table-empty">Cargando...</td></tr></tbody>
    </table>
  </div>
</div>

<!-- CLIENTES -->
<div class="panel" id="panel-customers">
  <div class="toolbar"><h2>Clientes</h2></div>
  <div class="table-wrap">
    <table class="data-table" id="customersTable">
      <thead><tr><th>Nombre</th><th>Email</th><th>Teléfono</th><th>Órdenes</th><th>Total gastado</th><th>Última orden</th></tr></thead>
      <tbody><tr><td colspan="6" class="table-empty">Cargando...</td></tr></tbody>
    </table>
  </div>
</div>

<!-- MENSAJES -->
<div class="panel" id="panel-messages">
  <div class="toolbar"><h2>Mensajes de contacto</h2></div>
  <div class="table-wrap">
    <table class="data-table" id="messagesTable">
      <thead><tr><th>Nombre</th><th>Email</th><th>Asunto</th><th>Fecha</th><th>Estado</th><th>Acciones</th></tr></thead>
      <tbody><tr><td colspan="6" class="table-empty">Cargando...</td></tr></tbody>
    </table>
  </div>
</div>

<!-- AJUSTES -->
<div class="panel" id="panel-settings">
  <div class="toolbar"><h2>Ajustes del Sitio</h2></div>
  <form id="settingsForm">
    <div class="settings-grid">
      <div class="settings-card">
        <h3><i class="fa fa-star"></i> Banner Principal (Hero)</h3>
        <div class="form-group">
          <label>Título Hero</label>
          <div class="input-with-action">
            <div id="editor_hero_title" class="rich-editor" data-key="hero_title"></div>
            <button type="button" class="btn btn-primary btn-icon btn-sm" onclick="saveRichSetting('hero_title')" title="Guardar"><i class="fa fa-save"></i></button>
          </div>
        </div>
        <div class="form-group">
          <label>Descripción Hero</label>
          <div class="input-with-action">
            <div id="editor_hero_description" class="rich-editor" data-key="hero_description"></div>
            <button type="button" class="btn btn-primary btn-icon btn-sm" onclick="saveRichSetting('hero_description')" title="Guardar"><i class="fa fa-save"></i></button>
          </div>
        </div>
      </div>
      <div class="settings-card">
        <h3><i class="fa fa-globe"></i> General</h3>
        <div class="form-group">
          <label>Email de contacto</label>
          <div class="input-with-action">
            <input type="email" name="contact_email" class="input" placeholder="email@ejemplo.com">
            <button type="button" class="btn btn-primary btn-icon btn-sm" onclick="saveIndividualSetting('contact_email')" title="Guardar"><i class="fa fa-save"></i></button>
          </div>
        </div>
        <div class="form-group">
          <label>WhatsApp</label>
          <div class="input-with-action">
            <input type="text" name="contact_whatsapp" class="input" placeholder="+56 9 XXXX XXXX">
            <button type="button" class="btn btn-primary btn-icon btn-sm" onclick="saveIndividualSetting('contact_whatsapp')" title="Guardar"><i class="fa fa-save"></i></button>
          </div>
        </div>
        <div class="form-group">
          <label>Dirección</label>
          <div class="input-with-action">
            <input type="text" name="contact_address" class="input" placeholder="Ciudad, País">
            <button type="button" class="btn btn-primary btn-icon btn-sm" onclick="saveIndividualSetting('contact_address')" title="Guardar"><i class="fa fa-save"></i></button>
          </div>
        </div>
        <div class="form-group">
          <label>Instagram URL</label>
          <div class="input-with-action">
            <input type="text" name="social_instagram" class="input" placeholder="https://instagram.com/...">
            <button type="button" class="btn btn-primary btn-icon btn-sm" onclick="saveIndividualSetting('social_instagram')" title="Guardar"><i class="fa fa-save"></i></button>
          </div>
        </div>
        <div class="form-group">
          <label>Filosofía Footer</label>
          <div class="input-with-action">
            <div id="editor_footer_philosophy" class="rich-editor" data-key="footer_philosophy"></div>
            <button type="button" class="btn btn-primary btn-icon btn-sm" onclick="saveRichSetting('footer_philosophy')" title="Guardar"><i class="fa fa-save"></i></button>
          </div>
        </div>
        <div class="form-group">
          <label>Horario de atención</label>
          <div class="input-with-action">
            <input type="text" name="contact_hours" class="input" placeholder="Lun–Sáb 09:00–18:00">
            <button type="button" class="btn btn-primary btn-icon btn-sm" onclick="saveIndividualSetting('contact_hours')" title="Guardar"><i class="fa fa-save"></i></button>
          </div>
        </div>
      </div>
      <div class="settings-card">
        <h3><i class="fa fa-info-circle"></i> Sección Nosotros</h3>
        <div class="form-group">
          <label>Etiqueta Superior</label>
          <div class="input-with-action">
            <input type="text" name="nosotros_label" class="input" placeholder="Nuestra historia">
            <button type="button" class="btn btn-primary btn-icon btn-sm" onclick="saveIndividualSetting('nosotros_label')" title="Guardar"><i class="fa fa-save"></i></button>
          </div>
        </div>
        <div class="form-group">
          <label>Título Principal</label>
          <div class="input-with-action">
            <div id="editor_nosotros_title" class="rich-editor" data-key="nosotros_title"></div>
            <button type="button" class="btn btn-primary btn-icon btn-sm" onclick="saveRichSetting('nosotros_title')" title="Guardar"><i class="fa fa-save"></i></button>
          </div>
        </div>
        <div class="form-group">
          <label>Descripción Hero</label>
          <div class="input-with-action">
            <textarea name="nosotros_hero_desc" class="textarea" placeholder="Katy & Woof es más que..."></textarea>
            <button type="button" class="btn btn-primary btn-icon btn-sm" onclick="saveIndividualSetting('nosotros_hero_desc')" title="Guardar"><i class="fa fa-save"></i></button>
          </div>
        </div>
        <div class="form-group">
          <label>Historia Párrafo 1</label>
          <div class="input-with-action">
            <div id="editor_our_history_detailed_p1" class="rich-editor" data-key="our_history_detailed_p1"></div>
            <button type="button" class="btn btn-primary btn-icon btn-sm" onclick="saveRichSetting('our_history_detailed_p1')" title="Guardar"><i class="fa fa-save"></i></button>
          </div>
        </div>
        <div class="form-group">
          <label>Nombre Fundadora</label>
          <div class="input-with-action">
            <input type="text" name="nosotros_founder_name" class="input" placeholder="Katherine Rojas Labrín">
            <button type="button" class="btn btn-primary btn-icon btn-sm" onclick="saveIndividualSetting('nosotros_founder_name')" title="Guardar"><i class="fa fa-save"></i></button>
          </div>
        </div>
        <div class="form-group">
          <label>Detalles Fundadora</label>
          <div class="input-with-action">
            <input type="text" name="nosotros_founder_details" class="input" placeholder="Artista · La Serena, Chile">
            <button type="button" class="btn btn-primary btn-icon btn-sm" onclick="saveIndividualSetting('nosotros_founder_details')" title="Guardar"><i class="fa fa-save"></i></button>
          </div>
        </div>
        <div class="form-group">
          <label>Misión</label>
          <div class="input-with-action">
            <div id="editor_nosotros_mision" class="rich-editor" data-key="nosotros_mision"></div>
            <button type="button" class="btn btn-primary btn-icon btn-sm" onclick="saveRichSetting('nosotros_mision')" title="Guardar"><i class="fa fa-save"></i></button>
          </div>
        </div>
        <div class="form-group">
          <label>Visión</label>
          <div class="input-with-action">
            <div id="editor_nosotros_vision" class="rich-editor" data-key="nosotros_vision"></div>
            <button type="button" class="btn btn-primary btn-icon btn-sm" onclick="saveRichSetting('nosotros_vision')" title="Guardar"><i class="fa fa-save"></i></button>
          </div>
        </div>
      </div>
      <div class="settings-card">
        <h3><i class="fa fa-image"></i> Imágenes del sitio</h3>
        <div class="form-group">
          <label>Favicon</label>
          <div class="img-upload-area">
            <input type="file" name="site_favicon" accept="image/x-icon,image/png,image/svg+xml" onchange="previewSettingImg(this,'prev_site_favicon')">
            <img id="prev_site_favicon" class="img-preview" src="" style="display:none">
            <i class="fa fa-star" id="icon_site_favicon"></i>
            <p>Ícono de pestaña (SVG/PNG)</p>
          </div>
          <button type="button" class="btn btn-primary btn-sm img-save-btn" onclick="saveIndividualSetting('site_favicon')"><i class="fa fa-save"></i> Guardar Favicon</button>
        </div>
        <div class="form-group">
          <label>Logo del sitio</label>
          <div class="img-upload-area">
            <input type="file" name="site_logo" accept="image/*" onchange="previewSettingImg(this,'prev_site_logo')">
            <img id="prev_site_logo" class="img-preview" src="" style="display:none">
            <i class="fa fa-cloud-arrow-up" id="icon_site_logo"></i>
            <p>Logo principal</p>
          </div>
          <button type="button" class="btn btn-primary btn-sm img-save-btn" onclick="saveIndividualSetting('site_logo')"><i class="fa fa-save"></i> Guardar Logo</button>
        </div>
        <div class="form-group">
          <label>Imagen Hero</label>
          <div class="img-upload-area">
            <input type="file" name="hero_image" accept="image/*" onchange="previewSettingImg(this,'prev_hero_image')">
            <img id="prev_hero_image" class="img-preview" src="" style="display:none">
            <i class="fa fa-image" id="icon_hero_image"></i>
            <p>Fondo banner principal</p>
          </div>
          <button type="button" class="btn btn-primary btn-sm img-save-btn" onclick="saveIndividualSetting('hero_image')"><i class="fa fa-save"></i> Guardar Imagen</button>
        </div>
        <div class="form-group">
          <label>Imagen "Nosotros"</label>
          <div class="img-upload-area">
            <input type="file" name="nosotros_image" accept="image/*" onchange="previewSettingImg(this,'prev_nosotros_image')">
            <img id="prev_nosotros_image" class="img-preview" src="" style="display:none">
            <i class="fa fa-users" id="icon_nosotros_image"></i>
            <p>Imagen sección historia</p>
          </div>
          <button type="button" class="btn btn-primary btn-sm img-save-btn" onclick="saveIndividualSetting('nosotros_image')"><i class="fa fa-save"></i> Guardar Imagen</button>
        </div>
      </div>
    </div>
    <div style="margin-top:1.5rem;display:flex;justify-content:flex-end">
      <button type="submit" class="btn btn-primary"><i class="fa fa-floppy-disk"></i> Guardar todos los ajustes</button>
    </div>
  </form>
</div>

<!-- SISTEMA -->
<div class="panel" id="panel-system">
  <div class="toolbar"><h2>Sistema / Base de Datos</h2></div>
  <div class="db-status-grid" id="dbStatusGrid">
    <div class="db-stat"><div class="db-stat-val" id="sys-tables">—</div><div class="db-stat-label">Tablas</div></div>
    <div class="db-stat"><div class="db-stat-val" id="sys-size">—</div><div class="db-stat-label">Tamaño BD</div></div>
    <div class="db-stat"><div class="db-stat-val" id="sys-php">—</div><div class="db-stat-label">PHP Version</div></div>
  </div>
  <div style="display:flex;gap:0.8rem;flex-wrap:wrap;margin-bottom:1.5rem">
    <button class="btn btn-outline btn-sm" id="btnTestConn"><i class="fa fa-plug"></i> Probar conexión</button>
    <button class="btn btn-outline btn-sm" id="btnSyncDB"><i class="fa fa-rotate"></i> Sincronizar BD</button>
    <button class="btn btn-outline btn-sm" id="btnInitEcommerce"><i class="fa fa-store"></i> Inicializar E-commerce</button>
    <button class="btn btn-danger btn-sm" id="btnRepairDB"><i class="fa fa-wrench"></i> Reparar BD</button>
  </div>
  <div id="sysLog" class="table-wrap" style="padding:1.2rem;font-family:'Space Mono',monospace;font-size:0.75rem;color:var(--mid);min-height:120px;white-space:pre-wrap">
    Listo. Usa los botones de arriba para ejecutar operaciones.
  </div>
</div>
