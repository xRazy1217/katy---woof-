# 🛒 **PLAN DETALLADO - Sistema de Comercio Electrónico Katy & Woof**

## 📋 **RESUMEN EJECUTIVO**

**Proyecto:** Incorporación de E-commerce con Flow Payment  
**Fecha de Planificación:** Marzo 2026  
**Estado:** 📋 **PLAN DETALLADO COMPLETO**  
**Objetivo:** Sistema completo de venta online integrado con Flow

---

## 🎯 **OBJETIVOS DEL SISTEMA**

### **Funcionalidades Principales**
- ✅ **Catálogo de productos** con categorías y filtros
- ✅ **Carrito de compras** persistente
- ✅ **Proceso de checkout** seguro con Flow
- ✅ **Gestión de pedidos** completa
- ✅ **Panel de administración** de productos
- ✅ **Sistema de inventario** en tiempo real
- ✅ **Gestión de clientes** y perfiles
- ✅ **Reportes de ventas** y analytics

### **Requisitos Técnicos**
- 🔧 **Integración Flow:** API completa de pagos
- 🔒 **Seguridad PCI DSS:** Cumplimiento de estándares
- 📱 **Responsive Design:** Móvil y desktop
- ⚡ **Performance:** Carga rápida y optimizada
- 🔄 **Escalabilidad:** Arquitectura modular extensible

---

## 🏗️ **ARQUITECTURA DEL SISTEMA**

### **1. Estructura de Base de Datos**

#### **Nuevas Tablas Requeridas**
```sql
-- Productos y catálogo
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    short_description VARCHAR(500),
    price DECIMAL(10,2) NOT NULL,
    sale_price DECIMAL(10,2) NULL,
    sku VARCHAR(100) UNIQUE,
    stock_quantity INT DEFAULT 0,
    stock_status ENUM('instock', 'outofstock', 'onbackorder') DEFAULT 'instock',
    weight DECIMAL(5,2) NULL,
    dimensions VARCHAR(100),
    category_id INT,
    image_url VARCHAR(500),
    gallery_images JSON,
    attributes JSON, -- color, tamaño, etc.
    status ENUM('publish', 'draft', 'trash') DEFAULT 'publish',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES product_categories(id)
);

-- Categorías de productos
CREATE TABLE product_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE,
    description TEXT,
    parent_id INT NULL,
    image_url VARCHAR(500),
    display_order INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES product_categories(id)
);

-- Carrito de compras
CREATE TABLE cart_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    session_id VARCHAR(255),
    user_id INT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    variation_data JSON, -- atributos seleccionados
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Pedidos
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_number VARCHAR(50) UNIQUE,
    user_id INT NULL,
    customer_email VARCHAR(255) NOT NULL,
    customer_data JSON, -- nombre, dirección, teléfono
    status ENUM('pending', 'processing', 'completed', 'cancelled', 'refunded') DEFAULT 'pending',
    currency VARCHAR(3) DEFAULT 'CLP',
    subtotal DECIMAL(10,2) NOT NULL,
    tax_total DECIMAL(10,2) DEFAULT 0,
    shipping_total DECIMAL(10,2) DEFAULT 0,
    discount_total DECIMAL(10,2) DEFAULT 0,
    total DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50) DEFAULT 'flow',
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    flow_transaction_id VARCHAR(255) NULL,
    shipping_method VARCHAR(100),
    shipping_address JSON,
    order_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Items de pedidos
CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    product_sku VARCHAR(100),
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    line_total DECIMAL(10,2) NOT NULL,
    variation_data JSON,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Usuarios/Clientes (extensión de sistema existente)
ALTER TABLE users ADD COLUMN (
    billing_address JSON,
    shipping_address JSON,
    phone VARCHAR(50),
    date_of_birth DATE,
    gender ENUM('M', 'F', 'O'),
    marketing_consent BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Configuraciones de e-commerce
CREATE TABLE ecommerce_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE,
    setting_value TEXT,
    setting_group VARCHAR(50) DEFAULT 'general',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Cupones de descuento
CREATE TABLE coupons (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(50) UNIQUE,
    description TEXT,
    discount_type ENUM('fixed', 'percentage') DEFAULT 'fixed',
    discount_value DECIMAL(10,2) NOT NULL,
    usage_limit INT NULL,
    usage_count INT DEFAULT 0,
    expiry_date DATE NULL,
    minimum_amount DECIMAL(10,2) NULL,
    product_ids JSON, -- productos específicos
    category_ids JSON, -- categorías específicas
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### **2. API Endpoints Nuevos**

#### **Endpoints de Productos**
```php
// api/ecommerce/products.php
GET  /api/ecommerce/products - Listar productos con filtros
GET  /api/ecommerce/products/{id} - Detalles de producto
POST /api/ecommerce/products - Crear producto (admin)
PUT  /api/ecommerce/products/{id} - Actualizar producto (admin)
DELETE /api/ecommerce/products/{id} - Eliminar producto (admin)

// api/ecommerce/categories.php
GET  /api/ecommerce/categories - Listar categorías
POST /api/ecommerce/categories - Crear categoría (admin)
PUT  /api/ecommerce/categories/{id} - Actualizar categoría (admin)
DELETE /api/ecommerce/categories/{id} - Eliminar categoría (admin)
```

#### **Endpoints de Carrito**
```php
// api/ecommerce/cart.php
GET  /api/ecommerce/cart - Obtener carrito actual
POST /api/ecommerce/cart/add - Agregar producto al carrito
PUT  /api/ecommerce/cart/update - Actualizar cantidad
DELETE /api/ecommerce/cart/remove/{item_id} - Remover item
DELETE /api/ecommerce/cart/clear - Vaciar carrito
```

#### **Endpoints de Checkout**
```php
// api/ecommerce/checkout.php
POST /api/ecommerce/checkout/init - Iniciar checkout
POST /api/ecommerce/checkout/process - Procesar pago con Flow
GET  /api/ecommerce/checkout/status/{order_id} - Estado del pedido
POST /api/ecommerce/checkout/confirm - Confirmar pedido
```

#### **Endpoints de Pedidos**
```php
// api/ecommerce/orders.php
GET  /api/ecommerce/orders - Listar pedidos (admin/cliente)
GET  /api/ecommerce/orders/{id} - Detalles de pedido
PUT  /api/ecommerce/orders/{id}/status - Actualizar estado (admin)
POST /api/ecommerce/orders/{id}/refund - Procesar reembolso
```

### **3. Integración Flow Payment**

#### **Configuración Flow**
```php
// config/flow-config.php
define('FLOW_API_KEY', 'your_api_key_here');
define('FLOW_SECRET_KEY', 'your_secret_key_here');
define('FLOW_ENVIRONMENT', 'sandbox'); // 'production' para producción
define('FLOW_CURRENCY', 'CLP');
define('FLOW_SUCCESS_URL', 'https://katywoof.com/checkout/success');
define('FLOW_FAILURE_URL', 'https://katywoof.com/checkout/failure');
define('FLOW_PENDING_URL', 'https://katywoof.com/checkout/pending');
```

#### **Clase Flow Integration**
```php
// includes/FlowPayment.php
class FlowPayment {
    private $apiKey;
    private $secretKey;
    private $environment;

    public function __construct() {
        $this->apiKey = FLOW_API_KEY;
        $this->secretKey = FLOW_SECRET_KEY;
        $this->environment = FLOW_ENVIRONMENT;
    }

    public function createPayment($orderData) {
        // Crear pago en Flow
        $paymentData = [
            'commerceOrder' => $orderData['order_number'],
            'subject' => 'Compra Katy & Woof',
            'currency' => 'CLP',
            'amount' => $orderData['total'],
            'email' => $orderData['customer_email'],
            'urlConfirmation' => BASE_URL . '/api/ecommerce/flow/confirm',
            'urlReturn' => BASE_URL . '/checkout/complete'
        ];

        return $this->callFlowAPI('payment/create', $paymentData);
    }

    public function getPaymentStatus($flowOrder) {
        return $this->callFlowAPI('payment/getStatus', ['flowOrder' => $flowOrder]);
    }

    public function refundPayment($flowOrder, $amount = null) {
        $data = ['flowOrder' => $flowOrder];
        if ($amount) $data['amount'] = $amount;
        return $this->callFlowAPI('refund/create', $data);
    }

    private function callFlowAPI($endpoint, $data) {
        // Implementación de llamada a API Flow
        // ...
    }
}
```

---

## 🎨 **COMPONENTES DEL FRONTEND**

### **1. Páginas Nuevas**

#### **Catálogo de Productos** (`productos.php`)
```html
<!-- Página principal de productos -->
<div class="product-catalog">
    <!-- Filtros y búsqueda -->
    <div class="filters-sidebar">
        <div class="price-filter">
            <h3>Rango de Precio</h3>
            <input type="range" id="price-min" min="0" max="500000">
            <input type="range" id="price-max" min="0" max="500000">
        </div>
        <div class="category-filter">
            <h3>Categorías</h3>
            <div id="category-list"></div>
        </div>
        <div class="attribute-filters">
            <!-- Filtros dinámicos por atributos -->
        </div>
    </div>

    <!-- Grid de productos -->
    <div class="products-grid" id="products-container">
        <!-- Productos cargados dinámicamente -->
    </div>

    <!-- Paginación -->
    <div class="pagination" id="pagination-container"></div>
</div>
```

#### **Página de Producto** (`producto.php?id=123`)
```html
<!-- Página de detalle de producto -->
<div class="product-detail">
    <div class="product-gallery">
        <div class="main-image" id="main-product-image"></div>
        <div class="thumbnail-gallery" id="product-thumbnails"></div>
    </div>

    <div class="product-info">
        <h1 id="product-title"></h1>
        <div class="product-price" id="product-price"></div>
        <div class="product-description" id="product-description"></div>

        <!-- Variaciones del producto -->
        <div class="product-variations" id="product-variations"></div>

        <!-- Selector de cantidad -->
        <div class="quantity-selector">
            <button onclick="changeQuantity(-1)">-</button>
            <input type="number" id="quantity" value="1" min="1">
            <button onclick="changeQuantity(1)">+</button>
        </div>

        <!-- Botón agregar al carrito -->
        <button onclick="addToCart()" class="add-to-cart-btn" id="add-to-cart-btn">
            Agregar al Carrito
        </button>

        <!-- Información adicional -->
        <div class="product-meta">
            <span id="stock-status"></span>
            <span id="sku"></span>
        </div>
    </div>
</div>
```

#### **Carrito de Compras** (`carrito.php`)
```html
<!-- Página del carrito -->
<div class="cart-page">
    <div class="cart-items" id="cart-items">
        <!-- Items del carrito -->
    </div>

    <div class="cart-summary">
        <div class="cart-totals" id="cart-totals">
            <div class="subtotal">Subtotal: $<span id="subtotal"></span></div>
            <div class="shipping">Envío: $<span id="shipping"></span></div>
            <div class="tax">IVA: $<span id="tax"></span></div>
            <div class="total">Total: $<span id="total"></span></div>
        </div>

        <div class="cart-actions">
            <a href="productos.php" class="continue-shopping">Continuar Comprando</a>
            <button onclick="proceedToCheckout()" class="checkout-btn">
                Proceder al Pago
            </button>
        </div>
    </div>
</div>
```

#### **Checkout** (`checkout.php`)
```html
<!-- Página de checkout -->
<div class="checkout-page">
    <div class="checkout-steps">
        <div class="step active" data-step="1">Carrito</div>
        <div class="step" data-step="2">Envío</div>
        <div class="step" data-step="3">Pago</div>
        <div class="step" data-step="4">Confirmación</div>
    </div>

    <form id="checkout-form" class="checkout-form">
        <!-- Paso 1: Revisar carrito -->
        <div class="checkout-step" id="step-1">
            <h2>Revisar tu pedido</h2>
            <div id="checkout-cart-items"></div>
        </div>

        <!-- Paso 2: Información de envío -->
        <div class="checkout-step hidden" id="step-2">
            <h2>Información de envío</h2>
            <div class="form-row">
                <input type="text" name="first_name" placeholder="Nombre" required>
                <input type="text" name="last_name" placeholder="Apellido" required>
            </div>
            <input type="email" name="email" placeholder="Email" required>
            <input type="tel" name="phone" placeholder="Teléfono" required>
            <input type="text" name="address" placeholder="Dirección" required>
            <input type="text" name="city" placeholder="Ciudad" required>
            <select name="region" required>
                <option value="">Seleccionar Región</option>
                <!-- Opciones de regiones chilenas -->
            </select>
        </div>

        <!-- Paso 3: Método de pago -->
        <div class="checkout-step hidden" id="step-3">
            <h2>Método de pago</h2>
            <div class="payment-methods">
                <div class="payment-method active" data-method="flow">
                    <div class="flow-logo">
                        <img src="img/flow-logo.png" alt="Flow">
                    </div>
                    <p>Pago seguro con Flow</p>
                </div>
            </div>
            <div class="order-summary" id="order-summary"></div>
        </div>

        <!-- Paso 4: Confirmación -->
        <div class="checkout-step hidden" id="step-4">
            <h2>¡Pedido confirmado!</h2>
            <div id="order-confirmation"></div>
        </div>

        <div class="checkout-actions">
            <button type="button" onclick="prevStep()" id="prev-btn" class="hidden">Anterior</button>
            <button type="button" onclick="nextStep()" id="next-btn">Siguiente</button>
            <button type="submit" id="place-order-btn" class="hidden">Realizar Pedido</button>
        </div>
    </form>
</div>
```

### **2. Componentes JavaScript**

#### **ProductManager** (`js/product-manager.js`)
```javascript
class ProductManager {
    constructor() {
        this.products = [];
        this.filters = {};
        this.currentPage = 1;
        this.itemsPerPage = 12;
    }

    async loadProducts(filters = {}) {
        this.filters = filters;
        const queryParams = new URLSearchParams({
            ...filters,
            page: this.currentPage,
            limit: this.itemsPerPage
        });

        try {
            const response = await fetch(`/api/ecommerce/products?${queryParams}`);
            const data = await response.json();
            this.products = data.products;
            this.totalPages = data.totalPages;
            this.renderProducts();
            this.renderPagination();
        } catch (error) {
            console.error('Error loading products:', error);
        }
    }

    renderProducts() {
        const container = document.getElementById('products-container');
        container.innerHTML = this.products.map(product => `
            <div class="product-card" onclick="ProductManager.viewProduct(${product.id})">
                <div class="product-image">
                    <img src="${product.image_url}" alt="${product.name}">
                    ${product.sale_price ? '<div class="sale-badge">OFERTA</div>' : ''}
                </div>
                <div class="product-info">
                    <h3>${product.name}</h3>
                    <div class="product-price">
                        ${product.sale_price ?
                            `<span class="original-price">$${product.price}</span>
                             <span class="sale-price">$${product.sale_price}</span>` :
                            `<span class="price">$${product.price}</span>`
                        }
                    </div>
                    <div class="product-stock ${product.stock_status}">
                        ${product.stock_status === 'instock' ? 'En stock' :
                          product.stock_status === 'outofstock' ? 'Agotado' : 'Por encargo'}
                    </div>
                </div>
            </div>
        `).join('');
    }

    static viewProduct(productId) {
        window.location.href = `producto.php?id=${productId}`;
    }
}
```

#### **CartManager** (`js/cart-manager.js`)
```javascript
class CartManager {
    constructor() {
        this.cart = [];
        this.sessionId = this.getSessionId();
        this.init();
    }

    init() {
        this.loadCart();
        this.updateCartUI();
    }

    getSessionId() {
        let sessionId = localStorage.getItem('kw_cart_session');
        if (!sessionId) {
            sessionId = 'cart_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            localStorage.setItem('kw_cart_session', sessionId);
        }
        return sessionId;
    }

    async loadCart() {
        try {
            const response = await fetch('/api/ecommerce/cart', {
                headers: {
                    'X-Cart-Session': this.sessionId
                }
            });
            const data = await response.json();
            this.cart = data.items || [];
            this.updateCartUI();
        } catch (error) {
            console.error('Error loading cart:', error);
        }
    }

    async addToCart(productId, quantity = 1, variations = {}) {
        try {
            const response = await fetch('/api/ecommerce/cart/add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Cart-Session': this.sessionId
                },
                body: JSON.stringify({
                    product_id: productId,
                    quantity: quantity,
                    variations: variations
                })
            });

            const data = await response.json();
            if (data.success) {
                this.cart = data.cart;
                this.updateCartUI();
                this.showAddedNotification();
            }
        } catch (error) {
            console.error('Error adding to cart:', error);
        }
    }

    async updateQuantity(itemId, quantity) {
        if (quantity <= 0) {
            this.removeItem(itemId);
            return;
        }

        try {
            const response = await fetch('/api/ecommerce/cart/update', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Cart-Session': this.sessionId
                },
                body: JSON.stringify({
                    item_id: itemId,
                    quantity: quantity
                })
            });

            const data = await response.json();
            if (data.success) {
                this.cart = data.cart;
                this.updateCartUI();
            }
        } catch (error) {
            console.error('Error updating cart:', error);
        }
    }

    updateCartUI() {
        // Update cart counter in header
        const cartCount = document.getElementById('cart-count');
        if (cartCount) {
            const totalItems = this.cart.reduce((sum, item) => sum + item.quantity, 0);
            cartCount.textContent = totalItems;
            cartCount.style.display = totalItems > 0 ? 'block' : 'none';
        }

        // Update cart dropdown/sidebar if visible
        this.updateCartDisplay();
    }

    updateCartDisplay() {
        const cartContainer = document.getElementById('cart-items');
        if (!cartContainer) return;

        if (this.cart.length === 0) {
            cartContainer.innerHTML = '<p class="empty-cart">Tu carrito está vacío</p>';
            return;
        }

        cartContainer.innerHTML = this.cart.map(item => `
            <div class="cart-item">
                <img src="${item.product.image_url}" alt="${item.product.name}">
                <div class="item-details">
                    <h4>${item.product.name}</h4>
                    <div class="item-price">$${item.price}</div>
                    <div class="quantity-controls">
                        <button onclick="CartManager.updateQuantity(${item.id}, ${item.quantity - 1})">-</button>
                        <span>${item.quantity}</span>
                        <button onclick="CartManager.updateQuantity(${item.id}, ${item.quantity + 1})">+</button>
                    </div>
                </div>
                <button onclick="CartManager.removeItem(${item.id})" class="remove-item">×</button>
            </div>
        `).join('');

        this.updateCartTotals();
    }

    updateCartTotals() {
        const totals = this.calculateTotals();
        const totalsContainer = document.getElementById('cart-totals');
        if (totalsContainer) {
            totalsContainer.innerHTML = `
                <div class="cart-total-row">
                    <span>Subtotal:</span>
                    <span>$${totals.subtotal.toLocaleString()}</span>
                </div>
                <div class="cart-total-row">
                    <span>Envío:</span>
                    <span>$${totals.shipping.toLocaleString()}</span>
                </div>
                <div class="cart-total-row total">
                    <span>Total:</span>
                    <span>$${totals.total.toLocaleString()}</span>
                </div>
            `;
        }
    }

    calculateTotals() {
        const subtotal = this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        const shipping = subtotal > 50000 ? 0 : 5000; // Envío gratis sobre $50.000
        const total = subtotal + shipping;

        return {
            subtotal: Math.round(subtotal),
            shipping: shipping,
            total: Math.round(total)
        };
    }

    showAddedNotification() {
        // Show toast notification
        const toast = document.createElement('div');
        toast.className = 'toast toast-success';
        toast.textContent = 'Producto agregado al carrito';
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.remove();
        }, 3000);
    }
}

// Initialize cart manager
const CartManager = new CartManager();
```

#### **CheckoutManager** (`js/checkout-manager.js`)
```javascript
class CheckoutManager {
    constructor() {
        this.currentStep = 1;
        this.orderData = {};
        this.init();
    }

    init() {
        this.loadCartForCheckout();
        this.setupEventListeners();
    }

    setupEventListeners() {
        const form = document.getElementById('checkout-form');
        if (form) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.processOrder();
            });
        }
    }

    async loadCartForCheckout() {
        try {
            const response = await fetch('/api/ecommerce/cart');
            const data = await response.json();

            if (data.items && data.items.length > 0) {
                this.renderCheckoutCart(data.items);
                this.updateOrderSummary(data);
            } else {
                window.location.href = 'productos.php';
            }
        } catch (error) {
            console.error('Error loading cart for checkout:', error);
        }
    }

    renderCheckoutCart(items) {
        const container = document.getElementById('checkout-cart-items');
        container.innerHTML = items.map(item => `
            <div class="checkout-item">
                <img src="${item.product.image_url}" alt="${item.product.name}">
                <div class="item-details">
                    <h4>${item.product.name}</h4>
                    <div class="item-price">$${item.price} x ${item.quantity}</div>
                    <div class="item-total">$${(item.price * item.quantity).toLocaleString()}</div>
                </div>
            </div>
        `).join('');
    }

    updateOrderSummary(cartData) {
        const summary = this.calculateOrderSummary(cartData);
        const container = document.getElementById('order-summary');

        container.innerHTML = `
            <div class="order-summary-row">
                <span>Subtotal:</span>
                <span>$${summary.subtotal.toLocaleString()}</span>
            </div>
            <div class="order-summary-row">
                <span>Envío:</span>
                <span>$${summary.shipping.toLocaleString()}</span>
            </div>
            <div class="order-summary-row total">
                <span>Total:</span>
                <span>$${summary.total.toLocaleString()}</span>
            </div>
        `;

        this.orderData = {
            ...this.orderData,
            ...summary
        };
    }

    calculateOrderSummary(cartData) {
        const subtotal = cartData.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        const shipping = subtotal > 50000 ? 0 : 5000;
        const tax = Math.round(subtotal * 0.19); // IVA 19%
        const total = subtotal + shipping + tax;

        return {
            subtotal: Math.round(subtotal),
            shipping: shipping,
            tax: tax,
            total: Math.round(total),
            items: cartData.items
        };
    }

    nextStep() {
        if (this.validateCurrentStep()) {
            this.currentStep++;
            this.showStep(this.currentStep);
        }
    }

    prevStep() {
        this.currentStep--;
        this.showStep(this.currentStep);
    }

    showStep(stepNumber) {
        // Hide all steps
        document.querySelectorAll('.checkout-step').forEach(step => {
            step.classList.add('hidden');
        });

        // Show current step
        document.getElementById(`step-${stepNumber}`).classList.remove('hidden');

        // Update step indicators
        document.querySelectorAll('.checkout-steps .step').forEach((step, index) => {
            if (index + 1 === stepNumber) {
                step.classList.add('active');
            } else {
                step.classList.remove('active');
            }
        });

        // Update navigation buttons
        this.updateNavigationButtons();
    }

    updateNavigationButtons() {
        const prevBtn = document.getElementById('prev-btn');
        const nextBtn = document.getElementById('next-btn');
        const placeOrderBtn = document.getElementById('place-order-btn');

        prevBtn.classList.toggle('hidden', this.currentStep === 1);
        nextBtn.classList.toggle('hidden', this.currentStep === 4);
        placeOrderBtn.classList.toggle('hidden', this.currentStep !== 3);
    }

    validateCurrentStep() {
        switch (this.currentStep) {
            case 1:
                return true; // Cart review always valid
            case 2:
                return this.validateShippingForm();
            case 3:
                return this.validatePaymentForm();
            default:
                return true;
        }
    }

    validateShippingForm() {
        const requiredFields = ['first_name', 'last_name', 'email', 'phone', 'address', 'city', 'region'];
        let isValid = true;

        requiredFields.forEach(field => {
            const element = document.querySelector(`[name="${field}"]`);
            if (!element.value.trim()) {
                element.classList.add('error');
                isValid = false;
            } else {
                element.classList.remove('error');
            }
        });

        return isValid;
    }

    validatePaymentForm() {
        // For Flow payment, we mainly need to ensure terms are accepted
        const termsCheckbox = document.getElementById('accept-terms');
        if (termsCheckbox && !termsCheckbox.checked) {
            alert('Debes aceptar los términos y condiciones');
            return false;
        }
        return true;
    }

    async processOrder() {
        // Collect all form data
        const formData = new FormData(document.getElementById('checkout-form'));
        const customerData = {
            first_name: formData.get('first_name'),
            last_name: formData.get('last_name'),
            email: formData.get('email'),
            phone: formData.get('phone'),
            address: formData.get('address'),
            city: formData.get('city'),
            region: formData.get('region')
        };

        this.orderData.customer = customerData;

        try {
            // Show loading
            document.getElementById('place-order-btn').disabled = true;
            document.getElementById('place-order-btn').textContent = 'Procesando...';

            // Create order
            const response = await fetch('/api/ecommerce/checkout/process', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(this.orderData)
            });

            const result = await response.json();

            if (result.success) {
                // Redirect to Flow payment page
                window.location.href = result.flow_url;
            } else {
                throw new Error(result.message || 'Error al procesar el pedido');
            }

        } catch (error) {
            console.error('Error processing order:', error);
            alert('Error al procesar el pedido: ' + error.message);

            // Reset button
            document.getElementById('place-order-btn').disabled = false;
            document.getElementById('place-order-btn').textContent = 'Realizar Pedido';
        }
    }
}

// Initialize checkout manager when on checkout page
if (document.getElementById('checkout-form')) {
    const CheckoutManager = new CheckoutManager();
}
```

---

## 🔧 **PANEL DE ADMINISTRACIÓN E-COMMERCE**

### **1. Nuevos Tabs en Admin**

#### **Tab de Productos** (`admin/tab-products.html`)
```html
<!-- Admin Products Tab Component -->
<div id="tab-products" class="tab-content hidden">
    <div class="grid lg:grid-cols-4 gap-8">
        <!-- Product Form -->
        <div class="lg:col-span-1 space-y-6">
            <div class="glass-card p-6 rounded-[2rem]">
                <h2 id="product-form-title" class="text-xl serif font-bold mb-6">Nuevo Producto</h2>
                <form id="product-form" class="space-y-4">
                    <input type="hidden" id="product-id">
                    <input type="text" id="product-name" placeholder="Nombre del producto" class="form-input" required>
                    <input type="text" id="product-sku" placeholder="SKU" class="form-input">
                    <textarea id="product-description" rows="3" placeholder="Descripción" class="form-input"></textarea>
                    <textarea id="product-short-description" rows="2" placeholder="Descripción corta" class="form-input"></textarea>

                    <div class="grid grid-cols-2 gap-2">
                        <input type="number" id="product-price" placeholder="Precio" class="form-input" step="0.01" required>
                        <input type="number" id="product-sale-price" placeholder="Precio oferta" class="form-input" step="0.01">
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <input type="number" id="product-stock" placeholder="Stock" class="form-input" min="0">
                        <select id="product-stock-status" class="form-input">
                            <option value="instock">En stock</option>
                            <option value="outofstock">Agotado</option>
                            <option value="onbackorder">Por encargo</option>
                        </select>
                    </div>

                    <select id="product-category" class="form-input">
                        <option value="">Seleccionar categoría</option>
                    </select>

                    <input type="file" id="product-image" class="form-input" accept="image/*">
                    <div id="product-image-preview" class="hidden">
                        <img id="product-image-thumb" class="w-full h-32 object-cover rounded-lg">
                    </div>

                    <div class="flex gap-2">
                        <button type="button" id="product-submit-btn" onclick="ProductAdmin.save()" class="flex-1 py-3 btn-magic rounded-xl font-bold uppercase text-[10px]">
                            Crear Producto
                        </button>
                        <button type="button" id="product-cancel-btn" onclick="ProductAdmin.cancelEdit()" class="hidden py-3 bg-stone-100 text-stone-500 rounded-xl font-bold uppercase text-[9px]">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>

            <!-- Product Attributes -->
            <div class="glass-card p-6 rounded-[2rem]">
                <h3 class="text-sm font-bold uppercase tracking-widest mb-4">Atributos del Producto</h3>
                <div id="product-attributes" class="space-y-3">
                    <!-- Dynamic attributes will be loaded here -->
                </div>
                <button onclick="ProductAdmin.addAttribute()" class="w-full mt-4 py-2 bg-stone-100 hover:bg-stone-200 rounded-lg font-bold uppercase text-[9px]">
                    + Agregar Atributo
                </button>
            </div>
        </div>

        <!-- Products List -->
        <div class="lg:col-span-3">
            <div class="glass-card p-6 rounded-[2rem]">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl serif font-bold">Productos</h2>
                    <div class="flex gap-2">
                        <select id="filter-category" class="form-input text-xs py-2" onchange="ProductAdmin.filterProducts()">
                            <option value="">Todas las categorías</option>
                        </select>
                        <input type="text" id="search-products" placeholder="Buscar productos..." class="form-input text-xs py-2" onkeyup="ProductAdmin.searchProducts()">
                    </div>
                </div>
                <div id="products-admin-list" class="space-y-3 max-h-[600px] overflow-y-auto">
                    <!-- Products will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>
```

#### **Tab de Pedidos** (`admin/tab-orders.html`)
```html
<!-- Admin Orders Tab Component -->
<div id="tab-orders" class="tab-content hidden">
    <div class="space-y-8">
        <!-- Orders Stats -->
        <div class="grid md:grid-cols-4 gap-6">
            <div class="glass-card p-6 rounded-2xl text-center">
                <div class="text-3xl font-black text-blue-600" id="orders-total">-</div>
                <div class="text-xs uppercase tracking-widest text-stone-400">Total Pedidos</div>
            </div>
            <div class="glass-card p-6 rounded-2xl text-center">
                <div class="text-3xl font-black text-green-600" id="orders-completed">-</div>
                <div class="text-xs uppercase tracking-widest text-stone-400">Completados</div>
            </div>
            <div class="glass-card p-6 rounded-2xl text-center">
                <div class="text-3xl font-black text-yellow-600" id="orders-pending">-</div>
                <div class="text-xs uppercase tracking-widest text-stone-400">Pendientes</div>
            </div>
            <div class="glass-card p-6 rounded-2xl text-center">
                <div class="text-3xl font-black text-red-600" id="orders-cancelled">-</div>
                <div class="text-xs uppercase tracking-widest text-stone-400">Cancelados</div>
            </div>
        </div>

        <!-- Orders List -->
        <div class="glass-card p-6 rounded-[2rem]">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl serif font-bold">Pedidos</h2>
                <div class="flex gap-2">
                    <select id="filter-status" class="form-input text-xs py-2" onchange="OrderAdmin.filterOrders()">
                        <option value="">Todos los estados</option>
                        <option value="pending">Pendientes</option>
                        <option value="processing">Procesando</option>
                        <option value="completed">Completados</option>
                        <option value="cancelled">Cancelados</option>
                    </select>
                    <input type="text" id="search-orders" placeholder="Buscar por número..." class="form-input text-xs py-2" onkeyup="OrderAdmin.searchOrders()">
                </div>
            </div>
            <div id="orders-admin-list" class="space-y-3">
                <!-- Orders will be loaded here -->
            </div>
        </div>

        <!-- Order Detail Modal -->
        <div id="order-detail-modal" class="fixed inset-0 bg-black/50 hidden z-50">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="bg-white rounded-[2rem] max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                    <div class="p-8">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-2xl serif font-bold">Pedido #<span id="order-detail-number"></span></h2>
                            <button onclick="OrderAdmin.closeOrderDetail()" class="text-2xl">&times;</button>
                        </div>
                        <div id="order-detail-content">
                            <!-- Order details will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
```

#### **Tab de Cupones** (`admin/tab-coupons.html`)
```html
<!-- Admin Coupons Tab Component -->
<div id="tab-coupons" class="tab-content hidden">
    <div class="grid lg:grid-cols-3 gap-8">
        <!-- Coupon Form -->
        <div class="lg:col-span-1 space-y-6">
            <div class="glass-card p-6 rounded-[2rem]">
                <h2 id="coupon-form-title" class="text-xl serif font-bold mb-6">Nuevo Cupón</h2>
                <form id="coupon-form" class="space-y-4">
                    <input type="hidden" id="coupon-id">
                    <input type="text" id="coupon-code" placeholder="Código del cupón" class="form-input" required>
                    <textarea id="coupon-description" rows="2" placeholder="Descripción" class="form-input"></textarea>

                    <select id="coupon-type" class="form-input" onchange="CouponAdmin.updateTypeFields()">
                        <option value="fixed">Descuento fijo</option>
                        <option value="percentage">Descuento porcentual</option>
                    </select>

                    <input type="number" id="coupon-value" placeholder="Valor del descuento" class="form-input" step="0.01" required>

                    <div class="grid grid-cols-2 gap-2">
                        <input type="number" id="coupon-usage-limit" placeholder="Límite de uso" class="form-input" min="1">
                        <input type="number" id="coupon-min-amount" placeholder="Monto mínimo" class="form-input" step="0.01" min="0">
                    </div>

                    <input type="date" id="coupon-expiry" class="form-input">

                    <div class="flex gap-2">
                        <button type="button" id="coupon-submit-btn" onclick="CouponAdmin.save()" class="flex-1 py-3 btn-magic rounded-xl font-bold uppercase text-[10px]">
                            Crear Cupón
                        </button>
                        <button type="button" id="coupon-cancel-btn" onclick="CouponAdmin.cancelEdit()" class="hidden py-3 bg-stone-100 text-stone-500 rounded-xl font-bold uppercase text-[9px]">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Coupons List -->
        <div class="lg:col-span-2">
            <div class="glass-card p-6 rounded-[2rem]">
                <h2 class="text-xl serif font-bold mb-6">Cupones de Descuento</h2>
                <div id="coupons-admin-list" class="space-y-3">
                    <!-- Coupons will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>
```

### **2. Managers de Administración**

#### **ProductAdmin** (`admin/js/product-admin.js`)
```javascript
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
            this.categories = response;
            this.renderCategorySelect();
        } catch (error) {
            console.error('Error loading categories:', error);
        }
    }

    async loadProducts() {
        try {
            const response = await AdminAPI.fetch('ecommerce/products');
            this.products = response;
            this.renderProductsList();
        } catch (error) {
            console.error('Error loading products:', error);
        }
    }

    renderCategorySelect() {
        const select = document.getElementById('product-category');
        const filterSelect = document.getElementById('filter-category');

        const options = this.categories.map(cat =>
            `<option value="${cat.id}">${cat.name}</option>`
        ).join('');

        select.innerHTML = '<option value="">Seleccionar categoría</option>' + options;
        filterSelect.innerHTML = '<option value="">Todas las categorías</option>' + options;
    }

    renderProductsList() {
        const container = document.getElementById('products-admin-list');

        if (this.products.length === 0) {
            container.innerHTML = '<p class="text-center text-stone-400 py-8">No hay productos</p>';
            return;
        }

        container.innerHTML = this.products.map(product => `
            <div class="product-admin-item flex items-center justify-between p-4 bg-stone-50 rounded-xl">
                <div class="flex items-center gap-4">
                    <img src="${product.image_url || 'img/placeholder.jpg'}" class="w-12 h-12 object-cover rounded-lg">
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
                    <button onclick="ProductAdmin.edit(${product.id})" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </button>
                    <button onclick="ProductAdmin.delete(${product.id})" class="p-2 text-red-600 hover:bg-red-50 rounded-lg">
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

            const response = await AdminAPI.fetch(`ecommerce/products/${productId}`, {}, 'DELETE');
            // Note: AdminAPI.fetch might need to be modified to support DELETE method

            if (response.success) {
                AdminUI.showToast('Producto eliminado', 'success');
                await this.loadProducts();
            } else {
                throw new Error(response.message || 'Error al eliminar producto');
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
    }

    searchProducts() {
        const searchTerm = document.getElementById('search-products').value.toLowerCase();
        // Implement search logic
    }

    setupEventListeners() {
        // Image preview
        document.getElementById('product-image').addEventListener('change', (e) => {
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

// Initialize when admin panel loads
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('tab-products')) {
        window.ProductAdmin = new ProductAdmin();
        window.ProductAdmin.init();
    }
});
```

---

## 🔐 **SEGURIDAD Y VALIDACIONES**

### **1. Validaciones de Seguridad**

#### **Validación de Pagos**
```php
// includes/PaymentSecurity.php
class PaymentSecurity {
    public static function validatePaymentData($paymentData) {
        // Validar que el monto no haya sido alterado
        $expectedAmount = self::calculateExpectedAmount($paymentData['order_id']);
        if ($paymentData['amount'] !== $expectedAmount) {
            throw new Exception('Monto de pago alterado');
        }

        // Validar que el pedido existe y está en estado correcto
        $order = self::getOrder($paymentData['order_id']);
        if (!$order || $order['status'] !== 'pending') {
            throw new Exception('Pedido inválido o ya procesado');
        }

        return true;
    }

    public static function validateFlowWebhook($webhookData, $signature) {
        // Validar firma de Flow
        $expectedSignature = hash_hmac('sha256', json_encode($webhookData), FLOW_SECRET_KEY);
        if (!hash_equals($expectedSignature, $signature)) {
            throw new Exception('Firma de webhook inválida');
        }

        return true;
    }
}
```

#### **Protección CSRF**
```php
// includes/CSRFProtection.php
class CSRFProtection {
    public static function generateToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function validateToken($token) {
        if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            throw new Exception('Token CSRF inválido');
        }
        return true;
    }
}
```

### **2. Validaciones de Datos**

#### **Validación de Productos**
```javascript
// js/ProductValidation.js
const ProductValidation = {
    validateProductData(data) {
        const errors = [];

        // Validar nombre
        if (!data.name || data.name.trim().length < 3) {
            errors.push('El nombre debe tener al menos 3 caracteres');
        }

        // Validar precio
        if (!data.price || data.price <= 0) {
            errors.push('El precio debe ser mayor a 0');
        }

        // Validar precio de oferta
        if (data.sale_price && data.sale_price >= data.price) {
            errors.push('El precio de oferta debe ser menor al precio regular');
        }

        // Validar SKU único
        if (data.sku && !this.isSkuUnique(data.sku, data.id)) {
            errors.push('El SKU ya existe');
        }

        return {
            valid: errors.length === 0,
            errors: errors
        };
    },

    async isSkuUnique(sku, excludeId = null) {
        try {
            const response = await fetch(`/api/ecommerce/products/check-sku?sku=${encodeURIComponent(sku)}${excludeId ? `&exclude=${excludeId}` : ''}`);
            const result = await response.json();
            return result.available;
        } catch (error) {
            console.error('Error checking SKU:', error);
            return false;
        }
    }
};
```

---

## 📊 **REPORTES Y ANALYTICS**

### **1. Dashboard de Ventas**

#### **Métricas Principales**
```php
// api/ecommerce/analytics.php
class EcommerceAnalytics {
    public static function getSalesMetrics($period = '30_days') {
        $metrics = [
            'total_sales' => self::getTotalSales($period),
            'total_orders' => self::getTotalOrders($period),
            'average_order_value' => self::getAverageOrderValue($period),
            'conversion_rate' => self::getConversionRate($period),
            'top_products' => self::getTopProducts($period, 10),
            'sales_by_day' => self::getSalesByDay($period),
            'payment_methods' => self::getPaymentMethodsDistribution($period)
        ];

        return $metrics;
    }

    private static function getTotalSales($period) {
        $sql = "SELECT SUM(total) as total FROM orders WHERE created_at >= DATE_SUB(NOW(), INTERVAL $period)";
        // Execute query and return result
    }

    private static function getTopProducts($period, $limit) {
        $sql = "
            SELECT p.name, SUM(oi.quantity) as total_sold, SUM(oi.line_total) as total_revenue
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            JOIN products p ON oi.product_id = p.id
            WHERE o.created_at >= DATE_SUB(NOW(), INTERVAL $period)
            GROUP BY p.id
            ORDER BY total_sold DESC
            LIMIT $limit
        ";
        // Execute query and return results
    }
}
```

#### **Dashboard Frontend**
```html
<!-- admin/tab-analytics.html -->
<div id="tab-analytics" class="tab-content hidden">
    <div class="space-y-8">
        <!-- Key Metrics -->
        <div class="grid md:grid-cols-4 gap-6">
            <div class="glass-card p-6 rounded-2xl text-center">
                <div class="text-3xl font-black text-green-600" id="total-sales">-$</div>
                <div class="text-xs uppercase tracking-widest text-stone-400">Ventas Totales</div>
                <div class="text-xs text-stone-500 mt-1" id="sales-change">+12% vs mes anterior</div>
            </div>
            <div class="glass-card p-6 rounded-2xl text-center">
                <div class="text-3xl font-black text-blue-600" id="total-orders">-</div>
                <div class="text-xs uppercase tracking-widest text-stone-400">Total Pedidos</div>
                <div class="text-xs text-stone-500 mt-1" id="orders-change">+8% vs mes anterior</div>
            </div>
            <div class="glass-card p-6 rounded-2xl text-center">
                <div class="text-3xl font-black text-purple-600" id="avg-order-value">-$</div>
                <div class="text-xs uppercase tracking-widest text-stone-400">Valor Promedio</div>
                <div class="text-xs text-stone-500 mt-1" id="avg-change">+5% vs mes anterior</div>
            </div>
            <div class="glass-card p-6 rounded-2xl text-center">
                <div class="text-3xl font-black text-orange-600" id="conversion-rate">-%</div>
                <div class="text-xs uppercase tracking-widest text-stone-400">Tasa Conversión</div>
                <div class="text-xs text-stone-500 mt-1" id="conversion-change">+3% vs mes anterior</div>
            </div>
        </div>

        <!-- Sales Chart -->
        <div class="glass-card p-6 rounded-[2rem]">
            <h2 class="text-xl serif font-bold mb-6">Ventas por Día</h2>
            <canvas id="sales-chart" width="400" height="200"></canvas>
        </div>

        <!-- Top Products -->
        <div class="grid md:grid-cols-2 gap-8">
            <div class="glass-card p-6 rounded-[2rem]">
                <h2 class="text-xl serif font-bold mb-6">Productos Más Vendidos</h2>
                <div id="top-products-list" class="space-y-3">
                    <!-- Top products will be loaded here -->
                </div>
            </div>

            <div class="glass-card p-6 rounded-[2rem]">
                <h2 class="text-xl serif font-bold mb-6">Métodos de Pago</h2>
                <canvas id="payment-methods-chart" width="200" height="200"></canvas>
            </div>
        </div>
    </div>
</div>
```

---

## 🚀 **PLAN DE IMPLEMENTACIÓN**

### **Fase 1: Base de Datos y API (Semana 1-2)**
- [ ] Crear todas las tablas de e-commerce
- [ ] Implementar API endpoints básicos
- [ ] Configurar integración Flow
- [ ] Crear esquemas de validación

### **Fase 2: Catálogo de Productos (Semana 3-4)**
- [ ] Página de catálogo de productos
- [ ] Página de detalle de producto
- [ ] Sistema de categorías
- [ ] Gestión de imágenes de productos

### **Fase 3: Carrito y Checkout (Semana 5-6)**
- [ ] Sistema de carrito de compras
- [ ] Página de checkout multi-paso
- [ ] Integración completa con Flow
- [ ] Gestión de direcciones de envío

### **Fase 4: Panel de Administración (Semana 7-8)**
- [ ] Gestión de productos en admin
- [ ] Gestión de pedidos
- [ ] Sistema de cupones
- [ ] Reportes y analytics

### **Fase 5: Testing y Optimización (Semana 9-10)**
- [ ] Testing completo del flujo de compra
- [ ] Optimización de performance
- [ ] Testing de seguridad
- [ ] Documentación completa

### **Fase 6: Lanzamiento y Monitoreo (Semana 11-12)**
- [ ] Configuración de producción
- [ ] Testing con datos reales
- [ ] Monitoreo post-lanzamiento
- [ ] Optimizaciones basadas en uso real

---

## 📋 **REQUISITOS PREVIOS**

### **Dependencias Técnicas**
- ✅ PHP 8.0+ con extensiones MySQLi, JSON, Fileinfo
- ✅ MySQL 8.0+ con soporte JSON
- ✅ Composer para gestión de dependencias PHP
- ✅ Node.js para assets y validaciones
- ✅ Cuenta activa en Flow.cl
- ✅ Certificado SSL para pagos

### **Dependencias de Software**
- ✅ SDK de Flow para PHP
- ✅ Librería de validación de imágenes
- ✅ Sistema de envío de emails (SMTP)
- ✅ Librería de PDF para facturas
- ✅ Sistema de logging avanzado

---

## 🔒 **CONSIDERACIONES LEGALES**

### **Cumplimiento Normativo**
- **Ley 19.628** - Protección de datos personales
- **Decreto 211** - Comercio electrónico en Chile
- **Normas del SII** - Facturación electrónica
- **Regulaciones Flow** - Procesamiento de pagos

### **Políticas del Sitio**
- **Política de privacidad** actualizada
- **Términos y condiciones** de compra
- **Política de reembolso** clara
- **Política de envío** detallada

---

## 📈 **MÉTRICAS DE ÉXITO**

### **Métricas de Performance**
- **Tiempo de carga** < 3 segundos
- **Tasa de conversión** > 2%
- **Tasa de abandono de carrito** < 70%
- **Satisfacción del cliente** > 4.5/5

### **Métricas de Negocio**
- **Ingresos mensuales** objetivo
- **Número de pedidos** por mes
- **Valor promedio de pedido**
- **Retención de clientes**

---

## 🎯 **PRÓXIMOS PASOS**

1. **Revisar y aprobar** este plan detallado
2. **Configurar entorno de desarrollo** para e-commerce
3. **Crear cuenta y configurar** Flow Payment
4. **Implementar base de datos** y esquemas iniciales
5. **Comenzar desarrollo** por fases según el plan

**¿El plan está aprobado para comenzar la implementación?**</content>
<parameter name="filePath">C:\Users\obal_\Downloads\katy-&-woof---creative-studio (12)-20260305T183150Z-3-001\katy-&-woof---creative-studio (12)\PLAN_ECOMMERCE_COMPLETO.md