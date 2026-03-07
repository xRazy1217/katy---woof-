/**
 * Katy & Woof - Admin Taxonomy & Settings Module v6.0 (Isolated Context Saving)
 */
const AdminTaxonomy = {
    async loadLists() {
        const lists = await AdminAPI.fetch('get_lists');
        const populateSelect = (id, key) => {
            const el = document.getElementById(id); if(!el) return;
            const currentVal = el.value;
            const items = lists.filter(l => l.list_key === key);
            el.innerHTML = '<option value="">Seleccionar...</option>' + 
                items.map(i => `<option value="${i.item_value}">${i.item_value}</option>`).join('');
            if (currentVal && items.some(i => i.item_value === currentVal)) el.value = currentVal;
        };

        populateSelect('blog-category', 'blog_categories');

        ['art_styles', 'blog_categories'].forEach(key => {
            const listEl = document.getElementById(`list-${key}`); if(!listEl) return;
            let items = lists.filter(l => l.list_key === key);
            items.sort((a,b)=>a.item_value.localeCompare(b.item_value));
            if (items.length === 0) {
                listEl.innerHTML = '<p class="text-[9px] text-stone-300 italic text-center py-4 border border-dashed border-stone-100 rounded-xl">Sin categorías</p>';
                return;
            }
            listEl.innerHTML = items.map(i => `
                <div class="flex justify-between items-center bg-white p-2.5 rounded-xl border border-stone-100 text-[9px] group hover:border-midnight transition-all">
                    <span class="font-bold uppercase tracking-widest text-stone-500">${i.item_value}</span>
                    <button onclick="AdminTaxonomy.deleteListItem(${i.id})" class="text-stone-300 hover:text-red-500 transition-colors px-2 text-base leading-none">&times;</button>
                </div>
            `).join('');
        });
    },

    async loadSettings() {
        const settings = await AdminAPI.fetch('get_settings');
        settings.forEach(s => {
            const el = document.getElementById(`setting-${s.setting_key}`) || document.getElementById(`id-${s.setting_key}`);
            if (el) el.value = s.setting_value;
            const previewEl = document.getElementById(`preview-${s.setting_key}`);
            if (previewEl) previewEl.src = s.setting_value + '?v=' + Date.now();
        });
    },

    async saveIdentitySettings() {
        AdminUI.toggleLoading(true);
        const fd = new FormData();
        const textKeys = ['contact_email', 'contact_whatsapp', 'contact_address', 'social_instagram', 'footer_philosophy'];
        textKeys.forEach(k => {
            const el = document.getElementById(`id-${k}`);
            if(el) fd.append(k, el.value);
        });
        const fileInputs = {'site_logo': 'site-logo-input', 'site_favicon': 'site-favicon-input'};
        for (const [key, inputId] of Object.entries(fileInputs)) {
            const el = document.getElementById(inputId);
            if(el && el.files[0]) fd.append(key, el.files[0]);
        }
        try {
            await AdminAPI.post('save_settings', fd);
            await this.loadSettings();
            // limpiar inputs de archivo y previews
            document.getElementById('site-logo-input').value = '';
            document.getElementById('site-favicon-input').value = '';
            document.getElementById('site-logo-file-info').innerHTML = '';
            document.getElementById('site-favicon-file-info').innerHTML = '';
            AdminUI.showToast("Datos de identidad guardados", 'success');
            AdminUI.showFormMessage('settings-form','Guardado ✓','success');
        } catch (e) {
            AdminUI.showToast(e.message || "Error al guardar", 'error');
            console.error("saveIdentitySettings error:", e);
        }
        AdminUI.toggleLoading(false);
    },

    async saveVisualSettings() {
        AdminUI.toggleLoading(true);
        const fd = new FormData();
        const textKeys = ['hero_title', 'hero_description', 'nosotros_title'];
        textKeys.forEach(k => {
            const el = document.getElementById(`id-${k}`);
            if(el) fd.append(k, el.value);
        });
        const fileInputs = {'hero_image': 'hero-image-input', 'nosotros_image': 'nosotros-image-input'};
        for (const [key, inputId] of Object.entries(fileInputs)) {
            const el = document.getElementById(inputId);
            if(el && el.files[0]) fd.append(key, el.files[0]);
        }
        try {
            await AdminAPI.post('save_settings', fd);
            await this.loadSettings();
            AdminUI.showToast("Contenido visual actualizado", 'success');
        } catch (e) {
            AdminUI.showToast(e.message || "Error al guardar", 'error');
            console.error("saveVisualSettings error:", e);
        }
        AdminUI.toggleLoading(false);
    },

    async saveSettings() {
        AdminUI.toggleLoading(true);
        const fd = new FormData();
        fd.append('our_history', document.getElementById('setting-our_history').value);
        try {
            await AdminAPI.post('save_settings', fd);
            AdminUI.showToast("Textos de historia actualizados", 'success');
        } catch (e) {
            AdminUI.showToast(e.message || "Error al guardar", 'error');
            console.error("saveSettings error:", e);
        }
        AdminUI.toggleLoading(false);
    },

    async addListItem(key) {
        const input = document.getElementById(`new-${key}`);
        if (!input.value) return;
        
        const fd = new FormData();
        fd.append('list_key', key);
        fd.append('item_value', input.value);
        
        AdminUI.toggleLoading(true);
        try {
            await AdminAPI.post('save_list_item', fd);
            input.value = "";
            await this.loadLists();
            AdminUI.showToast("Añadido", 'success');
        } catch (e) {
            AdminUI.showToast(e.message || "Error al añadir", 'error');
        }
        AdminUI.toggleLoading(false);
    },

    async deleteListItem(id) {
        if (!confirm("¿Eliminar elemento?")) return;
        AdminUI.toggleLoading(true);
        try {
            await AdminAPI.delete('delete_list_item', id);
            await this.loadLists();
            AdminUI.showToast("Eliminado", 'success');
        } catch (e) {
            AdminUI.showToast(e.message || "Error al eliminar", 'error');
        }
        AdminUI.toggleLoading(false);
    }
};