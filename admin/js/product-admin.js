class ProductAdmin {
    constructor() {
        this.currentProduct = null;
        this.products = [];
        this.categories = [];
    }

    async init() {
        await this.loadCategories();
        await this.loadProducts();
        this.setupEventListeners();
    }

    async loadCategories() {
        try {
            const response = await AdminAPI.fetch('ecommerce/categories');
            this.categories = response || [];
            this.renderCategorySelect();
        } catch (error) {
            console.error('Error loading categories:', error);
            AdminUI.showToast('Error al cargar categorías', 'error');
        }
    }

    async loadProducts() {
        try {
            AdminUI.toggleLoading(true);
            const response = await AdminAPI.fetch('ecommerce/products');
            this.products = response || [];
            this.renderProductsList();
        } catch (error) {
            console.error('Error loading products:', error);
            AdminUI.showToast('Error al cargar productos', 'error');
        } finally {
            AdminUI.toggleLoading(false);
        }
    }

    renderCategorySelect() {
        const select = document.getElementById('product-category');
        const filterSelect = document.getElementById('filter-category');

        if (!select || !filterSelect) return;

        const options = this.categories.map(cat =>
            `<option value="${cat.id}">${cat.name}</option>`
        ).join('');

        select.innerHTML = '<option value="">Seleccionar categoría</option>' + options;
        filterSelect.innerHTML = '<option value="">Todas las categorías</option>' + options;
    }

    renderProductsList() {
        const container = document.getElementById('products-admin-list');
        if (!container) return;

        if (this.products.length === 0) {
            container.innerHTML = '<p class="text-center text-stone-400 py-8">No hay productos</p>';
            return;
        }

        container.innerHTML = this.products.map(product => `
            <div class="product-admin-item flex items-center justify-between p-4 bg-stone-50 rounded-xl">
                <div class="flex items-center gap-4">
                    <img src="${product.image_url || 'img/placeholder.jpg'}" class="w-12 h-12 object-cover rounded-lg" onerror="this.src='img/placeholder.jpg'">
                    <div>
                        <h4 class="font-bold">${product.name}</h4>
                        <p class="text-sm text-stone-500">${product.sku || 'Sin SKU'}</p>
                        <p class="text-sm text-stone-600">$${product.price}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-1 text-xs rounded-full ${
                        product.status === 'publish' ? 'bg-green-100 text-green-800' :
                        product.status === 'draft' ? 'bg-yellow-100 text-yellow-800' :
                        'bg-red-100 text-red-800'
                    }">
                        ${product.status === 'publish' ? 'Publicado' :
                          product.status === 'draft' ? 'Borrador' : 'Papelera'}
                    </span>
                    <span class="px-2 py-1 text-xs rounded-full ${
                        product.stock_status === 'instock' ? 'bg-green-100 text-green-800' :
                        product.stock_status === 'outofstock' ? 'bg-red-100 text-red-800' :
                        'bg-blue-100 text-blue-800'
                    }">
                        ${product.stock_status === 'instock' ? 'En stock' :
                          product.stock_status === 'outofstock' ? 'Agotado' : 'Por encargo'}
                    </span>
                    <button onclick="ProductAdmin.edit(${product.id})" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg" title="Editar">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </button>
                    <button onclick="ProductAdmin.delete(${product.id})" class="p-2 text-red-600 hover:bg-red-50 rounded-lg" title="Eliminar">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>
            </div>
        `).join('');
    }

    async save() {
        const formData = new FormData();

        // Basic product data
        const productData = {
            name: document.getElementById('product-name').value,
            sku: document.getElementById('product-sku').value,
            description: document.getElementById('product-description').value,
            short_description: document.getElementById('product-short-description').value,
            price: parseFloat(document.getElementById('product-price').value),
            sale_price: document.getElementById('product-sale-price').value ?
                      parseFloat(document.getElementById('product-sale-price').value) : null,
            stock_quantity: parseInt(document.getElementById('product-stock').value) || 0,
            stock_status: document.getElementById('product-stock-status').value,
            category_id: document.getElementById('product-category').value,
            status: 'publish'
        };

        // Add to FormData
        Object.keys(productData).forEach(key => {
            if (productData[key] !== null && productData[key] !== undefined) {
                formData.append(key, productData[key]);
            }
        });

        // Add product ID if editing
        const productId = document.getElementById('product-id').value;
        if (productId) {
            formData.append('id', productId);
        }

        // Add product image
        const imageFile = document.getElementById('product-image').files[0];
        if (imageFile) {
            formData.append('image', imageFile);
        }

        try {
            AdminUI.toggleLoading(true);

            const endpoint = productId ? `ecommerce/products/${productId}` : 'ecommerce/products';
            const method = productId ? 'PUT' : 'POST';

            const response = await fetch(`/api/${endpoint}`, {
                method: method,
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                AdminUI.showToast('Producto guardado exitosamente', 'success');
                this.resetForm();
                await this.loadProducts();
            } else {
                throw new Error(result.message || 'Error al guardar producto');
            }

        } catch (error) {
            console.error('Error saving product:', error);
            AdminUI.showToast(error.message || 'Error al guardar producto', 'error');
        } finally {
            AdminUI.toggleLoading(false);
        }
    }

    edit(productId) {
        const product = this.products.find(p => p.id === productId);
        if (!product) return;

        this.currentProduct = product;

        // Populate form
        document.getElementById('product-id').value = product.id;
        document.getElementById('product-name').value = product.name;
        document.getElementById('product-sku').value = product.sku || '';
        document.getElementById('product-description').value = product.description || '';
        document.getElementById('product-short-description').value = product.short_description || '';
        document.getElementById('product-price').value = product.price;
        document.getElementById('product-sale-price').value = product.sale_price || '';
        document.getElementById('product-stock').value = product.stock_quantity || 0;
        document.getElementById('product-stock-status').value = product.stock_status;
        document.getElementById('product-category').value = product.category_id;

        // Update form title and button
        document.getElementById('product-form-title').textContent = 'Editar Producto';
        document.getElementById('product-submit-btn').textContent = 'Actualizar Producto';
        document.getElementById('product-cancel-btn').classList.remove('hidden');

        // Scroll to form
        document.querySelector('.lg\\:col-span-1').scrollIntoView({ behavior: 'smooth' });
    }

    cancelEdit() {
        this.resetForm();
    }

    resetForm() {
        this.currentProduct = null;

        // Clear form
        document.getElementById('product-form').reset();
        document.getElementById('product-id').value = '';

        // Reset UI
        document.getElementById('product-form-title').textContent = 'Nuevo Producto';
        document.getElementById('product-submit-btn').textContent = 'Crear Producto';
        document.getElementById('product-cancel-btn').classList.add('hidden');

        // Clear image preview
        document.getElementById('product-image-preview').classList.add('hidden');
    }

    async delete(productId) {
        if (!confirm('¿Estás seguro de que quieres eliminar este producto?')) return;

        try {
            AdminUI.toggleLoading(true);

            const response = await fetch(`/api/ecommerce/products/${productId}`, {
                method: 'DELETE'
            });

            const result = await response.json();

            if (result.success) {
                AdminUI.showToast('Producto eliminado', 'success');
                await this.loadProducts();
            } else {
                throw new Error(result.message || 'Error al eliminar producto');
            }

        } catch (error) {
            console.error('Error deleting product:', error);
            AdminUI.showToast(error.message || 'Error al eliminar producto', 'error');
        } finally {
            AdminUI.toggleLoading(false);
        }
    }

    filterProducts() {
        const categoryId = document.getElementById('filter-category').value;
        // Implement filtering logic
        this.renderProductsList();
    }

    searchProducts() {
        const searchTerm = document.getElementById('search-products').value.toLowerCase();
        // Implement search logic
        this.renderProductsList();
    }

    addAttribute() {
        // Implement attribute addition logic
        AdminUI.showToast('Funcionalidad de atributos próximamente', 'info');
    }

    setupEventListeners() {
        // Image preview
        const imageInput = document.getElementById('product-image');
        if (imageInput) {
            imageInput.addEventListener('change', (e) => {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        document.getElementById('product-image-thumb').src = e.target.result;
                        document.getElementById('product-image-preview').classList.remove('hidden');
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
    }
}

// Exponer instancia global para uso desde handlers inline y carga diferida de tabs
window.ProductAdmin = window.ProductAdmin || new ProductAdmin();

// Inicializar solo si el tab ya est\u00e1 presente al cargar el DOM
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('tab-products') && typeof window.ProductAdmin.init === 'function') {
        window.ProductAdmin.init();
    }
});