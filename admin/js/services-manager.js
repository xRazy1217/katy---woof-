/**
 * Katy & Woof - Services Manager v6.0
 * Gestión específica de servicios
 */

class ServicesManager extends BaseContentManager {
    constructor() {
        super('services', 'get_services', 'services-list', 'service-form');
    }

    renderList(data) {
        const list = document.getElementById(this.listId);
        list.innerHTML = data.map(s => `
            <div class="bg-white p-4 rounded-xl flex justify-between items-center border border-stone-50 group hover:border-soft-blue transition-colors">
                <div class="flex flex-col">
                    <span class="text-xs font-bold serif text-midnight">${s.title}</span>
                </div>
                <div class="flex gap-2">
                    <button onclick="ServicesManager.edit(${s.id})" class="px-3 py-1 bg-stone-50 text-stone-500 rounded-md text-[8px] font-black uppercase tracking-widest hover:bg-midnight hover:text-white transition-all">Editar</button>
                    <button onclick="ServicesManager.delete(${s.id})" class="px-3 py-1 text-red-300 hover:text-red-500 text-lg">&times;</button>
                </div>
            </div>
        `).join('');
    }

    populateFormData(fd, formData) {
        fd.append('title', formData.title);
        fd.append('description', formData.description);
    }

    populateEditForm(item) {
        document.getElementById('service-id').value = item.id;
        document.getElementById('service-title').value = item.title;
        document.getElementById('service-desc').value = item.description;
    }

    clearEditFields() {
        document.getElementById('service-id').value = "";
    }

    setEditMode(isEdit) {
        const titleForm = document.getElementById('service-title-form');
        const submitBtn = document.getElementById('service-submit-btn');
        const cancelBtn = document.getElementById('service-cancel-btn');
        const formContainer = document.getElementById('services-form-container');

        if (isEdit) {
            titleForm.innerText = "Modificar Servicio";
            submitBtn.innerText = "Actualizar Servicio";
            cancelBtn.classList.remove('hidden');
            formContainer.classList.add('ring-4', 'ring-soft-blue/20', 'bg-white');
        } else {
            titleForm.innerText = "Nuevo Servicio";
            submitBtn.innerText = "Publicar Servicio";
            cancelBtn.classList.add('hidden');
            formContainer.classList.remove('ring-4', 'ring-soft-blue/20', 'bg-white');
        }
    }

    getFileFieldName() {
        return 'main_file';
    }

    getEntityDisplayName() {
        return 'servicio';
    }

    // === MÉTODOS PÚBLICOS ESTÁTICOS ===

    static edit(id) {
        ServicesManager.instance.edit(id);
    }

    static async save() {
        const formData = {
            id: document.getElementById('service-id').value,
            title: document.getElementById('service-title').value,
            description: document.getElementById('service-desc').value
        };
        const file = document.getElementById('service-file').files[0];
        await ServicesManager.instance.save(formData, file);
    }

    static async delete(id) {
        await ServicesManager.instance.delete(id);
    }

    static cancelEdit() {
        ServicesManager.instance.cancelEdit();
    }
}

// Instancia singleton
ServicesManager.instance = new ServicesManager();