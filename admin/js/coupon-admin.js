class CouponAdmin {
    constructor() {
        this.coupons = [];
        this.currentCoupon = null;
    }

    async init() {
        await this.loadCoupons();
        this.setupEventListeners();
    }

    async loadCoupons() {
        try {
            AdminUI.toggleLoading(true);
            const response = await AdminAPI.fetch('ecommerce/coupons');
            this.coupons = response || [];
            this.renderCouponsList();
        } catch (error) {
            console.error('Error loading coupons:', error);
            AdminUI.showToast('Error al cargar cupones', 'error');
        } finally {
            AdminUI.toggleLoading(false);
        }
    }

    renderCouponsList() {
        const container = document.getElementById('coupons-admin-list');
        if (!container) return;

        if (this.coupons.length === 0) {
            container.innerHTML = '<p class="text-center text-stone-400 py-8">No hay cupones</p>';
            return;
        }

        container.innerHTML = this.coupons.map(coupon => `
            <div class="coupon-admin-item flex items-center justify-between p-4 bg-stone-50 rounded-xl">
                <div class="flex items-center gap-4">
                    <div>
                        <h4 class="font-bold">${coupon.code}</h4>
                        <p class="text-sm text-stone-500">${coupon.description || 'Sin descripción'}</p>
                        <p class="text-sm text-stone-600">
                            ${coupon.discount_type === 'fixed' ? `$${coupon.discount_value}` : `${coupon.discount_value}%`}
                            ${coupon.usage_limit ? ` - Límite: ${coupon.usage_count}/${coupon.usage_limit}` : ''}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-1 text-xs rounded-full ${
                        coupon.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                    }">
                        ${coupon.status === 'active' ? 'Activo' : 'Inactivo'}
                    </span>
                    ${coupon.expiry_date ? `
                        <span class="px-2 py-1 text-xs rounded-full ${
                            new Date(coupon.expiry_date) > new Date() ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800'
                        }">
                            ${new Date(coupon.expiry_date) > new Date() ? 'Vigente' : 'Expirado'}
                        </span>
                    ` : ''}
                    <button onclick="CouponAdmin.edit(${coupon.id})" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg" title="Editar">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </button>
                    <button onclick="CouponAdmin.delete(${coupon.id})" class="p-2 text-red-600 hover:bg-red-50 rounded-lg" title="Eliminar">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>
            </div>
        `).join('');
    }

    async save() {
        const couponData = {
            code: document.getElementById('coupon-code').value.toUpperCase(),
            description: document.getElementById('coupon-description').value,
            discount_type: document.getElementById('coupon-type').value,
            discount_value: parseFloat(document.getElementById('coupon-value').value),
            usage_limit: document.getElementById('coupon-usage-limit').value ?
                       parseInt(document.getElementById('coupon-usage-limit').value) : null,
            minimum_amount: document.getElementById('coupon-min-amount').value ?
                          parseFloat(document.getElementById('coupon-min-amount').value) : null,
            expiry_date: document.getElementById('coupon-expiry').value || null,
            status: 'active'
        };

        // Add coupon ID if editing
        const couponId = document.getElementById('coupon-id').value;
        if (couponId) {
            couponData.id = couponId;
        }

        try {
            AdminUI.toggleLoading(true);

            const endpoint = couponId ? `ecommerce/coupons/${couponId}` : 'ecommerce/coupons';
            const method = couponId ? 'PUT' : 'POST';

            const response = await fetch(`/api/${endpoint}`, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(couponData)
            });

            const result = await response.json();

            if (result.success) {
                AdminUI.showToast('Cupón guardado exitosamente', 'success');
                this.resetForm();
                await this.loadCoupons();
            } else {
                throw new Error(result.message || 'Error al guardar cupón');
            }

        } catch (error) {
            console.error('Error saving coupon:', error);
            AdminUI.showToast(error.message || 'Error al guardar cupón', 'error');
        } finally {
            AdminUI.toggleLoading(false);
        }
    }

    edit(couponId) {
        const coupon = this.coupons.find(c => c.id === couponId);
        if (!coupon) return;

        this.currentCoupon = coupon;

        // Populate form
        document.getElementById('coupon-id').value = coupon.id;
        document.getElementById('coupon-code').value = coupon.code;
        document.getElementById('coupon-description').value = coupon.description || '';
        document.getElementById('coupon-type').value = coupon.discount_type;
        document.getElementById('coupon-value').value = coupon.discount_value;
        document.getElementById('coupon-usage-limit').value = coupon.usage_limit || '';
        document.getElementById('coupon-min-amount').value = coupon.minimum_amount || '';
        document.getElementById('coupon-expiry').value = coupon.expiry_date ?
            new Date(coupon.expiry_date).toISOString().split('T')[0] : '';

        // Update form title and button
        document.getElementById('coupon-form-title').textContent = 'Editar Cupón';
        document.getElementById('coupon-submit-btn').textContent = 'Actualizar Cupón';
        document.getElementById('coupon-cancel-btn').classList.remove('hidden');

        // Scroll to form
        document.querySelector('.lg\\:col-span-1').scrollIntoView({ behavior: 'smooth' });
    }

    cancelEdit() {
        this.resetForm();
    }

    resetForm() {
        this.currentCoupon = null;

        // Clear form
        document.getElementById('coupon-form').reset();
        document.getElementById('coupon-id').value = '';

        // Reset UI
        document.getElementById('coupon-form-title').textContent = 'Nuevo Cupón';
        document.getElementById('coupon-submit-btn').textContent = 'Crear Cupón';
        document.getElementById('coupon-cancel-btn').classList.add('hidden');
    }

    async delete(couponId) {
        if (!confirm('¿Estás seguro de que quieres eliminar este cupón?')) return;

        try {
            AdminUI.toggleLoading(true);

            const response = await fetch(`/api/ecommerce/coupons/${couponId}`, {
                method: 'DELETE'
            });

            const result = await response.json();

            if (result.success) {
                AdminUI.showToast('Cupón eliminado', 'success');
                await this.loadCoupons();
            } else {
                throw new Error(result.message || 'Error al eliminar cupón');
            }

        } catch (error) {
            console.error('Error deleting coupon:', error);
            AdminUI.showToast(error.message || 'Error al eliminar cupón', 'error');
        } finally {
            AdminUI.toggleLoading(false);
        }
    }

    updateTypeFields() {
        const type = document.getElementById('coupon-type').value;
        const valueInput = document.getElementById('coupon-value');

        // Update placeholder based on type
        if (type === 'fixed') {
            valueInput.placeholder = 'Valor en pesos (ej: 5000)';
        } else {
            valueInput.placeholder = 'Porcentaje (ej: 10)';
        }
    }

    setupEventListeners() {
        // Add any additional event listeners here
    }
}

// Exponer instancia global para uso desde handlers inline y carga diferida de tabs
window.CouponAdmin = window.CouponAdmin || new CouponAdmin();

// Inicializar solo si el tab ya est\u00e1 presente al cargar el DOM
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('tab-coupons') && typeof window.CouponAdmin.init === 'function') {
        window.CouponAdmin.init();
    }
});