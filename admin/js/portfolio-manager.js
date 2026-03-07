/**
 * Katy & Woof - Portfolio Manager v6.0
 * Gestión específica del portafolio
 */

class PortfolioManager extends BaseContentManager {
    constructor() {
        super('portfolio', 'get_portfolio', 'portfolio-list', 'portfolio-form');
    }

    renderList(data) {
        const list = document.getElementById(this.listId);
        list.innerHTML = data.map(i => `
            <div class="aspect-square bg-stone-100 rounded-lg overflow-hidden relative group">
                <img src="${addCacheBust(i.img_url)}" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-midnight/80 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col justify-center items-center gap-2 p-2">
                    <button onclick="PortfolioManager.edit(${i.id})" class="w-full py-2 bg-white text-midnight uppercase text-[8px] font-black rounded-md hover:bg-soft-blue transition-colors">Editar</button>
                    <button onclick="PortfolioManager.delete(${i.id})" class="w-full py-2 bg-red-500 text-white uppercase text-[8px] font-black rounded-md">Eliminar</button>
                </div>
            </div>
        `).join('');
    }

    populateFormData(fd, formData) {
        fd.append('name', formData.name);
        fd.append('description', formData.description);
    }

    populateEditForm(item) {
        document.getElementById('art-id').value = item.id;
        document.getElementById('art-name').value = item.name;
        document.getElementById('art-description').value = item.description || "";
    }

    clearEditFields() {
        document.getElementById('art-id').value = "";
        document.getElementById('art-description').value = "";
    }

    setEditMode(isEdit) {
        const titleForm = document.getElementById('portfolio-title-form');
        const submitBtn = document.getElementById('art-submit-btn');
        const cancelBtn = document.getElementById('art-cancel-btn');
        const formContainer = document.getElementById('portfolio-form-container');

        if (isEdit) {
            titleForm.innerText = "Editando Obra";
            submitBtn.innerText = "Actualizar en Archivo";
            cancelBtn.classList.remove('hidden');
            formContainer.classList.add('ring-4', 'ring-soft-blue/20', 'bg-white');
        } else {
            titleForm.innerText = "Archivar Obra";
            submitBtn.innerText = "Subir al Archivo";
            cancelBtn.classList.add('hidden');
            formContainer.classList.remove('ring-4', 'ring-soft-blue/20', 'bg-white');
        }
    }

    getFileFieldName() {
        return 'file';
    }

    getEntityDisplayName() {
        return 'obra';
    }

    // === MÉTODOS PÚBLICOS ESTÁTICOS ===

    static edit(id) {
        PortfolioManager.instance.edit(id);
    }

    static async save() {
        const formData = {
            id: document.getElementById('art-id').value,
            name: document.getElementById('art-name').value,
            description: document.getElementById('art-description').value
        };
        const file = document.getElementById('art-file').files[0];
        await PortfolioManager.instance.save(formData, file);
    }

    static async delete(id) {
        await PortfolioManager.instance.delete(id);
    }

    static cancelEdit() {
        PortfolioManager.instance.cancelEdit();
    }
}

// Instancia singleton
PortfolioManager.instance = new PortfolioManager();