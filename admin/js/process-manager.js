/**
 * Katy & Woof - Process Manager v6.0
 * Gestión específica de los pasos del proceso
 */

class ProcessManager extends BaseContentManager {
    constructor() {
        super('process', 'get_process', 'process-list', 'process-form');
    }

    renderList(data) {
        const list = document.getElementById(this.listId);
        list.innerHTML = data.map(s => `
            <div class="bg-white p-4 rounded-xl flex justify-between items-center border border-stone-100">
                <div class="flex items-center gap-4">
                    <span class="text-xl font-black serif">${s.step_number}</span>
                    <p class="text-[10px] font-black uppercase tracking-widest">${s.title}</p>
                </div>
                <div class="flex gap-2">
                    <button onclick="ProcessManager.edit(${s.id})" class="px-3 py-1 bg-stone-50 text-stone-500 rounded-md text-[8px] font-black uppercase tracking-widest hover:bg-midnight hover:text-white transition-all">Editar</button>
                    <button onclick="ProcessManager.delete(${s.id})" class="text-red-400 hover:text-red-600 px-2 text-xl">&times;</button>
                </div>
            </div>
        `).join('');
    }

    populateFormData(fd, formData) {
        fd.append('step_number', formData.step_number);
        fd.append('title', formData.title);
        fd.append('description', formData.description);
    }

    populateEditForm(item) {
        document.getElementById('process-id').value = item.id;
        document.getElementById('step-number').value = item.step_number;
        document.getElementById('step-title').value = item.title;
        document.getElementById('step-description').value = item.description;
    }

    clearEditFields() {
        document.getElementById('process-id').value = "";
    }

    setEditMode(isEdit) {
        const titleForm = document.getElementById('proceso-title-form');
        const submitBtn = document.getElementById('process-submit-btn');
        const cancelBtn = document.getElementById('process-cancel-btn');

        if (isEdit) {
            titleForm.innerText = "Editar Paso";
            submitBtn.innerText = "Actualizar Paso";
            cancelBtn.classList.remove('hidden');
        } else {
            titleForm.innerText = "Paso del Proceso";
            submitBtn.innerText = "Guardar Paso";
            cancelBtn.classList.add('hidden');
        }
    }

    getFileFieldName() {
        return 'file';
    }

    getEntityDisplayName() {
        return 'paso';
    }

    // === MÉTODOS PÚBLICOS ESTÁTICOS ===

    static edit(id) {
        ProcessManager.instance.edit(id);
    }

    static async save() {
        const formData = {
            id: document.getElementById('process-id').value,
            step_number: document.getElementById('step-number').value,
            title: document.getElementById('step-title').value,
            description: document.getElementById('step-description').value
        };
        const file = document.getElementById('step-file').files[0];
        await ProcessManager.instance.save(formData, file);
    }

    static async delete(id) {
        await ProcessManager.instance.delete(id);
    }

    static cancelEdit() {
        ProcessManager.instance.cancelEdit();
    }
}

// Instancia singleton
ProcessManager.instance = new ProcessManager();