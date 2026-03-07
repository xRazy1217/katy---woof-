/**
 * Katy & Woof - Base Content Manager v6.0
 * Clase base para gestión de contenido con lógica común
 */

class BaseContentManager {
    constructor(entityName, apiAction, listId, formId) {
        this.entityName = entityName;
        this.apiAction = apiAction;
        this.listId = listId;
        this.formId = formId;
        this.dataKey = `${entityName}Data`;
    }

    /**
     * Carga datos desde la API y actualiza la lista
     */
    async load() {
        try {
            const data = await AdminAPI.fetch(this.apiAction);
            window[this.dataKey] = data;
            this.renderList(data);
        } catch (e) {
            AdminUI.showToast(`Error al cargar ${this.entityName}`, 'error');
            console.error(`load${this.entityName} error:`, e);
        }
    }

    /**
     * Guarda un elemento (crear o actualizar)
     * @param {Object} formData - Datos del formulario
     * @param {File} file - Archivo opcional
     */
    async save(formData, file = null) {
        // Validar imagen si hay archivo
        if (file) {
            const validation = await ContentValidator.validateImage(file);
            if (!validation.valid) {
                AdminUI.showToast(validation.error, 'error');
                return;
            }
        }

        const fd = new FormData();
        const id = formData.id;
        if (id) fd.append('id', id);

        // Agregar campos específicos (implementado en subclases)
        this.populateFormData(fd, formData);

        if (file) fd.append(this.getFileFieldName(), file);

        AdminUI.toggleLoading(true);
        try {
            await AdminAPI.post(`save_${this.entityName}`, fd);
            await this.load();
            this.cancelEdit();
            AdminUI.showToast(id ? "Actualizado" : "Guardado", 'success');
            AdminUI.showFormMessage(this.formId, id ? 'Actualizado ✓' : 'Guardado ✓', 'success');
        } catch (e) {
            AdminUI.showToast(e.message || "Error al guardar", 'error');
            console.error(`save${this.entityName} error:`, e);
        }
        AdminUI.toggleLoading(false);
    }

    /**
     * Elimina un elemento
     * @param {number} id - ID del elemento a eliminar
     */
    async delete(id) {
        if (!confirm(`¿Eliminar ${this.getEntityDisplayName()}?`)) return;

        AdminUI.toggleLoading(true);
        try {
            await AdminAPI.delete(`delete_${this.entityName}`, id);
            await this.load();
            AdminUI.showToast("Eliminado", 'success');
        } catch (e) {
            AdminUI.showToast(e.message || "Error al eliminar", 'error');
        }
        AdminUI.toggleLoading(false);
    }

    /**
     * Edita un elemento existente
     * @param {number} id - ID del elemento
     */
    edit(id) {
        const item = window[this.dataKey].find(i => i.id == id);
        if (!item) return;

        this.populateEditForm(item);
        this.setEditMode(true);
        AdminUI.switchTab(this.entityName);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    /**
     * Cancela la edición y resetea el formulario
     */
    cancelEdit() {
        const form = document.getElementById(this.formId);
        if (form) form.reset();

        this.clearEditFields();
        this.setEditMode(false);
    }

    // === MÉTODOS ABSTRACTOS (implementados en subclases) ===

    /**
     * Renderiza la lista de elementos
     * @param {Array} data - Datos a renderizar
     */
    renderList(data) {
        throw new Error('renderList must be implemented by subclass');
    }

    /**
     * Pobla los datos del formulario en FormData
     * @param {FormData} fd - FormData a poblar
     * @param {Object} formData - Datos del formulario
     */
    populateFormData(fd, formData) {
        throw new Error('populateFormData must be implemented by subclass');
    }

    /**
     * Pobla el formulario para edición
     * @param {Object} item - Elemento a editar
     */
    populateEditForm(item) {
        throw new Error('populateEditForm must be implemented by subclass');
    }

    /**
     * Limpia campos específicos de edición
     */
    clearEditFields() {
        throw new Error('clearEditFields must be implemented by subclass');
    }

    /**
     * Establece el modo edición del formulario
     * @param {boolean} isEdit - Si está en modo edición
     */
    setEditMode(isEdit) {
        throw new Error('setEditMode must be implemented by subclass');
    }

    /**
     * Retorna el nombre del campo de archivo
     * @returns {string}
     */
    getFileFieldName() {
        throw new Error('getFileFieldName must be implemented by subclass');
    }

    /**
     * Retorna el nombre para mostrar de la entidad
     * @returns {string}
     */
    getEntityDisplayName() {
        return this.entityName;
    }
}