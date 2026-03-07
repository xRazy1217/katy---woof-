class OrderAdmin {
    constructor() {
        this.orders = [];
        this.filteredOrders = [];
        this.currentOrder = null;
    }

    async init() {
        await this.loadOrders();
        await this.loadStats();
        this.setupEventListeners();
    }

    async loadOrders() {
        try {
            AdminUI.toggleLoading(true);
            const response = await AdminAPI.fetch('ecommerce/orders');
            this.orders = response || [];
            this.filteredOrders = [...this.orders];
            this.renderOrdersList();
        } catch (error) {
            console.error('Error loading orders:', error);
            AdminUI.showToast('Error al cargar pedidos', 'error');
        } finally {
            AdminUI.toggleLoading(false);
        }
    }

    async loadStats() {
        try {
            const response = await AdminAPI.fetch('ecommerce/orders/stats');
            const stats = response || {};

            document.getElementById('orders-total').textContent = stats.total || 0;
            document.getElementById('orders-completed').textContent = stats.completed || 0;
            document.getElementById('orders-pending').textContent = stats.pending || 0;
            document.getElementById('orders-cancelled').textContent = stats.cancelled || 0;
        } catch (error) {
            console.error('Error loading stats:', error);
            // Set defaults
            document.getElementById('orders-total').textContent = '0';
            document.getElementById('orders-completed').textContent = '0';
            document.getElementById('orders-pending').textContent = '0';
            document.getElementById('orders-cancelled').textContent = '0';
        }
    }

    renderOrdersList() {
        const container = document.getElementById('orders-admin-list');
        if (!container) return;

        if (this.filteredOrders.length === 0) {
            container.innerHTML = '<p class="text-center text-stone-400 py-8">No hay pedidos</p>';
            return;
        }

        container.innerHTML = this.filteredOrders.map(order => `
            <div class="order-admin-item flex items-center justify-between p-4 bg-stone-50 rounded-xl">
                <div class="flex items-center gap-4">
                    <div>
                        <h4 class="font-bold">#${order.order_number}</h4>
                        <p class="text-sm text-stone-500">${order.customer_name}</p>
                        <p class="text-sm text-stone-600">$${order.total}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-1 text-xs rounded-full ${
                        order.status === 'completed' ? 'bg-green-100 text-green-800' :
                        order.status === 'processing' ? 'bg-blue-100 text-blue-800' :
                        order.status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                        'bg-red-100 text-red-800'
                    }">
                        ${order.status === 'completed' ? 'Completado' :
                          order.status === 'processing' ? 'Procesando' :
                          order.status === 'pending' ? 'Pendiente' : 'Cancelado'}
                    </span>
                    <span class="px-2 py-1 text-xs rounded-full ${
                        order.payment_status === 'paid' ? 'bg-green-100 text-green-800' :
                        order.payment_status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                        'bg-red-100 text-red-800'
                    }">
                        ${order.payment_status === 'paid' ? 'Pagado' :
                          order.payment_status === 'pending' ? 'Pendiente' : 'Fallido'}
                    </span>
                    <button onclick="OrderAdmin.viewOrderDetail(${order.id})" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg" title="Ver detalle">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                    <select onchange="OrderAdmin.updateOrderStatus(${order.id}, this.value)" class="text-xs py-1 px-2 rounded border">
                        <option value="pending" ${order.status === 'pending' ? 'selected' : ''}>Pendiente</option>
                        <option value="processing" ${order.status === 'processing' ? 'selected' : ''}>Procesando</option>
                        <option value="completed" ${order.status === 'completed' ? 'selected' : ''}>Completado</option>
                        <option value="cancelled" ${order.status === 'cancelled' ? 'selected' : ''}>Cancelado</option>
                    </select>
                </div>
            </div>
        `).join('');
    }

    async viewOrderDetail(orderId) {
        try {
            AdminUI.toggleLoading(true);
            const response = await AdminAPI.fetch(`ecommerce/orders/${orderId}`);
            this.currentOrder = response;

            this.renderOrderDetail();
            document.getElementById('order-detail-modal').classList.remove('hidden');
        } catch (error) {
            console.error('Error loading order detail:', error);
            AdminUI.showToast('Error al cargar detalle del pedido', 'error');
        } finally {
            AdminUI.toggleLoading(false);
        }
    }

    renderOrderDetail() {
        const container = document.getElementById('order-detail-content');
        if (!container || !this.currentOrder) return;

        document.getElementById('order-detail-number').textContent = this.currentOrder.order_number;

        container.innerHTML = `
            <div class="grid md:grid-cols-2 gap-8">
                <!-- Order Info -->
                <div class="space-y-6">
                    <div class="glass-card p-6 rounded-xl">
                        <h3 class="text-lg font-bold mb-4">Información del Pedido</h3>
                        <div class="space-y-2 text-sm">
                            <p><strong>Fecha:</strong> ${new Date(this.currentOrder.created_at).toLocaleDateString()}</p>
                            <p><strong>Estado:</strong> ${this.currentOrder.status}</p>
                            <p><strong>Pago:</strong> ${this.currentOrder.payment_status}</p>
                            <p><strong>Método:</strong> ${this.currentOrder.payment_method}</p>
                            ${this.currentOrder.flow_transaction_id ? `<p><strong>ID Transacción:</strong> ${this.currentOrder.flow_transaction_id}</p>` : ''}
                        </div>
                    </div>

                    <div class="glass-card p-6 rounded-xl">
                        <h3 class="text-lg font-bold mb-4">Información del Cliente</h3>
                        <div class="space-y-2 text-sm">
                            <p><strong>Nombre:</strong> ${this.currentOrder.customer_name}</p>
                            <p><strong>Email:</strong> ${this.currentOrder.customer_email}</p>
                            <p><strong>Teléfono:</strong> ${this.currentOrder.customer_phone || 'No especificado'}</p>
                        </div>
                    </div>

                    <div class="glass-card p-6 rounded-xl">
                        <h3 class="text-lg font-bold mb-4">Dirección de Envío</h3>
                        <div class="text-sm">
                            ${this.currentOrder.shipping_address ?
                                Object.values(JSON.parse(this.currentOrder.shipping_address)).join(', ') :
                                'No especificada'}
                        </div>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="space-y-6">
                    <div class="glass-card p-6 rounded-xl">
                        <h3 class="text-lg font-bold mb-4">Productos</h3>
                        <div class="space-y-3">
                            ${this.currentOrder.items.map(item => `
                                <div class="flex items-center gap-3 p-3 bg-stone-50 rounded-lg">
                                    <img src="${item.product.image_url || 'img/placeholder.jpg'}" class="w-12 h-12 object-cover rounded" onerror="this.src='img/placeholder.jpg'">
                                    <div class="flex-1">
                                        <h4 class="font-medium">${item.product.name}</h4>
                                        <p class="text-sm text-stone-500">SKU: ${item.product.sku || 'N/A'}</p>
                                        <p class="text-sm">Cant: ${item.quantity} x $${item.price}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold">$${(item.quantity * item.price).toLocaleString()}</p>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>

                    <div class="glass-card p-6 rounded-xl">
                        <h3 class="text-lg font-bold mb-4">Resumen</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span>Subtotal:</span>
                                <span>$${this.currentOrder.subtotal}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Envío:</span>
                                <span>$${this.currentOrder.shipping_total}</span>
                            </div>
                            ${this.currentOrder.discount_total > 0 ? `
                                <div class="flex justify-between text-green-600">
                                    <span>Descuento:</span>
                                    <span>-$${this.currentOrder.discount_total}</span>
                                </div>
                            ` : ''}
                            <div class="flex justify-between font-bold text-lg border-t pt-2">
                                <span>Total:</span>
                                <span>$${this.currentOrder.total}</span>
                            </div>
                        </div>
                    </div>

                    ${this.currentOrder.order_notes ? `
                        <div class="glass-card p-6 rounded-xl">
                            <h3 class="text-lg font-bold mb-4">Notas del Pedido</h3>
                            <p class="text-sm">${this.currentOrder.order_notes}</p>
                        </div>
                    ` : ''}
                </div>
            </div>
        `;
    }

    closeOrderDetail() {
        document.getElementById('order-detail-modal').classList.add('hidden');
        this.currentOrder = null;
    }

    async updateOrderStatus(orderId, newStatus) {
        try {
            AdminUI.toggleLoading(true);

            const response = await fetch(`/api/ecommerce/orders/${orderId}/status`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ status: newStatus })
            });

            const result = await response.json();

            if (result.success) {
                AdminUI.showToast('Estado del pedido actualizado', 'success');
                await this.loadOrders();
                await this.loadStats();
            } else {
                throw new Error(result.message || 'Error al actualizar estado');
            }

        } catch (error) {
            console.error('Error updating order status:', error);
            AdminUI.showToast(error.message || 'Error al actualizar estado', 'error');
        } finally {
            AdminUI.toggleLoading(false);
        }
    }

    filterOrders() {
        const statusFilter = document.getElementById('filter-status').value;
        if (!statusFilter) {
            this.filteredOrders = [...this.orders];
        } else {
            this.filteredOrders = this.orders.filter(order => order.status === statusFilter);
        }
        this.renderOrdersList();
    }

    searchOrders() {
        const searchTerm = document.getElementById('search-orders').value.toLowerCase();
        if (!searchTerm) {
            this.filteredOrders = [...this.orders];
        } else {
            this.filteredOrders = this.orders.filter(order =>
                order.order_number.toLowerCase().includes(searchTerm) ||
                order.customer_name.toLowerCase().includes(searchTerm) ||
                order.customer_email.toLowerCase().includes(searchTerm)
            );
        }
        this.renderOrdersList();
    }

    setupEventListeners() {
        // Add any additional event listeners here
    }
}

// Exponer instancia global para uso desde handlers inline y carga diferida de tabs
window.OrderAdmin = window.OrderAdmin || new OrderAdmin();

// Inicializar solo si el tab ya est\u00e1 presente al cargar el DOM
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('tab-orders') && typeof window.OrderAdmin.init === 'function') {
        window.OrderAdmin.init();
    }
});