/**
 * Katy & Woof - Blog Manager v6.0
 * Gestión específica del blog
 */

class BlogManager extends BaseContentManager {
    constructor() {
        super('blog', 'get_blog', 'blog-list', 'blog-form');
    }

    renderList(data) {
        const list = document.getElementById(this.listId);
        list.innerHTML = data.map(p => `
            <div class="bg-white p-4 rounded-xl flex justify-between items-center border border-stone-50">
                <div class="flex flex-col">
                    <span class="text-xs font-bold serif text-midnight">${p.title}</span>
                    <span class="text-[8px] uppercase tracking-widest text-stone-400">${p.category}</span>
                </div>
                <div class="flex gap-2">
                    <button onclick="BlogManager.edit(${p.id})" class="px-3 py-1 bg-stone-50 text-stone-500 rounded-md text-[8px] font-black uppercase tracking-widest hover:bg-midnight hover:text-white transition-all">Editar</button>
                    <button onclick="BlogManager.delete(${p.id})" class="px-3 py-1 text-red-300 hover:text-red-500 text-lg">&times;</button>
                </div>
            </div>
        `).join('');
    }

    populateFormData(fd, formData) {
        fd.append('title', formData.title);
        fd.append('category', formData.category);
        fd.append('content', formData.content);
    }

    populateEditForm(item) {
        document.getElementById('blog-id').value = item.id;
        document.getElementById('blog-title').value = item.title;
        document.getElementById('blog-category').value = item.category;
        document.getElementById('blog-content').value = item.content;
    }

    clearEditFields() {
        document.getElementById('blog-id').value = "";
    }

    setEditMode(isEdit) {
        const titleForm = document.getElementById('blog-title-form');
        const submitBtn = document.getElementById('blog-submit-btn');
        const cancelBtn = document.getElementById('blog-cancel-btn');
        const formContainer = document.getElementById('blog-form-container');

        if (isEdit) {
            titleForm.innerText = "Editar Historia";
            submitBtn.innerText = "Actualizar Blog";
            cancelBtn.classList.remove('hidden');
            formContainer.classList.add('ring-4', 'ring-soft-blue/20', 'bg-white');
        } else {
            titleForm.innerText = "Nueva Historia";
            submitBtn.innerText = "Publicar en Blog";
            cancelBtn.classList.add('hidden');
            formContainer.classList.remove('ring-4', 'ring-soft-blue/20', 'bg-white');
        }
    }

    getFileFieldName() {
        return 'file';
    }

    getEntityDisplayName() {
        return 'post';
    }

    // === MÉTODOS PÚBLICOS ESTÁTICOS ===

    static edit(id) {
        BlogManager.instance.edit(id);
    }

    static async save() {
        const formData = {
            id: document.getElementById('blog-id').value,
            title: document.getElementById('blog-title').value,
            category: document.getElementById('blog-category').value,
            content: document.getElementById('blog-content').value
        };
        const file = document.getElementById('blog-file').files[0];
        await BlogManager.instance.save(formData, file);
    }

    static async delete(id) {
        await BlogManager.instance.delete(id);
    }

    static cancelEdit() {
        BlogManager.instance.cancelEdit();
    }
}

// Instancia singleton
BlogManager.instance = new BlogManager();