// =========================================
// Katy & Woof E-commerce - Gestión del Carrito
// Frontend Vanilla JS - Compatible con SiteGround
// =========================================

// Gestión del carrito de compras
window.KatyEcommerce = window.KatyEcommerce || {};

window.KatyEcommerce.CartManager = {
  isOpen: false,

  // Inicializar
  init: function() {
    this.loadFromStorage();
    this.bindEvents();
    this.updateUI();
  },

  // Cargar desde localStorage
  loadFromStorage: function() {
    try {
      const saved = localStorage.getItem('katy_cart');
      if (saved) {
        Cart.items = JSON.parse(saved);
        Cart.calculateTotals();
      }
    } catch (error) {
      console.warn('Error loading cart from storage:', error);
      Cart.items = [];
    }
  },

  // Vincular eventos
  bindEvents: function() {
    // Botón del carrito
    const cartIcon = document.getElementById('cart-icon');
    if (cartIcon) {
      cartIcon.addEventListener('click', (e) => {
        e.preventDefault();
        this.toggleCart();
      });
    }

    // Cerrar carrito
    const cartClose = document.querySelector('.cart-close');
    if (cartClose) {
      cartClose.addEventListener('click', () => this.closeCart());
    }

    // Overlay del carrito
    const cartSidebar = document.getElementById('cart-sidebar');
    if (cartSidebar) {
      cartSidebar.addEventListener('click', (e) => {
        if (e.target === cartSidebar) {
          this.closeCart();
        }
      });
    }

    // Evento de carrito actualizado
    window.addEventListener('cartUpdated', () => {
      this.updateUI();
    });

    // Delegación de eventos para botones del carrito
    document.addEventListener('click', (e) => {
      const target = e.target;

      // Botón agregar al carrito
      if (target.matches('.add-to-cart-btn')) {
        e.preventDefault();
        this.addToCartFromButton(target);
      }

      // Botón remover del carrito
      if (target.matches('.cart-item-remove')) {
        e.preventDefault();
        const itemId = target.dataset.itemId;
        this.removeItem(itemId);
      }

      // Botón cambiar cantidad en el carrito
      if (target.matches('.cart-quantity-btn')) {
        const itemId = target.dataset.itemId;
        const delta = parseInt(target.dataset.delta);
        this.changeItemQuantity(itemId, delta);
      }

      // Input de cantidad en el carrito
      if (target.matches('.cart-item-quantity')) {
        target.addEventListener('change', (e) => {
          const itemId = e.target.dataset.itemId;
          const quantity = parseInt(e.target.value);
          this.updateItemQuantity(itemId, quantity);
        });
      }
    });
  },

  // Agregar al carrito desde botón
  addToCartFromButton: function(button) {
    const productId = button.dataset.productId;
    const quantity = parseInt(button.dataset.quantity) || 1;
    const productCard = button.closest('.product-card');

    if (!productCard) return;

    const product = {
      id: productId,
      name: productCard.querySelector('.product-name')?.textContent || 'Producto',
      regular_price: productCard.dataset.price || 0,
      images: [{ src: productCard.querySelector('.product-image')?.src || '/uploads/placeholder-product.jpg' }],
      stock_quantity: parseInt(productCard.dataset.stock) || 999
    };

    Cart.addItem(product, quantity);
  },

  // Remover item del carrito
  removeItem: function(itemId) {
    Cart.removeItem(itemId);
    Utils.showNotification('Producto removido del carrito', 'info');
  },

  // Cambiar cantidad de item
  changeItemQuantity: function(itemId, delta) {
    const item = Cart.items.find(item => item.id === itemId);
    if (item) {
      const newQuantity = Math.max(1, Math.min(item.maxStock, item.quantity + delta));
      Cart.updateQuantity(itemId, newQuantity);
    }
  },

  // Actualizar cantidad de item
  updateItemQuantity: function(itemId, quantity) {
    Cart.updateQuantity(itemId, quantity);
  },

  // Limpiar carrito
  clearCart: function() {
    if (confirm('¿Estás seguro de que quieres vaciar el carrito?')) {
      Cart.clear();
      Utils.showNotification('Carrito vaciado', 'info');
    }
  },

  // Toggle carrito
  toggleCart: function() {
    if (this.isOpen) {
      this.closeCart();
    } else {
      this.openCart();
    }
  },

  // Abrir carrito
  openCart: function() {
    const cartSidebar = document.getElementById('cart-sidebar');
    if (cartSidebar) {
      cartSidebar.style.display = 'flex';
      document.body.style.overflow = 'hidden';
      this.isOpen = true;
      this.updateUI();
    }
  },

  // Cerrar carrito
  closeCart: function() {
    const cartSidebar = document.getElementById('cart-sidebar');
    if (cartSidebar) {
      cartSidebar.style.display = 'none';
      document.body.style.overflow = '';
      this.isOpen = false;
    }
  },

  // Actualizar UI del carrito
  updateUI: function() {
    this.updateCartIcon();
    this.updateCartContent();
    this.updateCartTotal();
    this.updateCheckoutButton();
  },

  // Actualizar ícono del carrito
  updateCartIcon: function() {
    const cartCount = document.querySelector('.cart-count');
    if (cartCount) {
      cartCount.textContent = Cart.itemCount;
      cartCount.style.display = Cart.itemCount > 0 ? 'inline' : 'none';
    }
  },

  // Actualizar contenido del carrito
  updateCartContent: function() {
    const cartContent = document.getElementById('cart-content');
    if (!cartContent) return;

    if (Cart.items.length === 0) {
      cartContent.innerHTML = `
        <div class="empty-cart">
          <div class="empty-cart-icon">🛒</div>
          <p>Tu carrito está vacío</p>
          <a href="/ecommerce/products.html" class="btn btn-primary">Ver Productos</a>
        </div>
      `;
      return;
    }

    cartContent.innerHTML = `
      <div class="cart-items">
        ${Cart.items.map(item => this.createCartItem(item)).join('')}
      </div>
      <div class="cart-actions">
        <button class="btn btn-outline clear-cart-btn" onclick="window.KatyEcommerce.CartManager.clearCart()">
          Vaciar Carrito
        </button>
      </div>
    `;
  },

  // Crear item del carrito
  createCartItem: function(item) {
    const itemTotal = item.price * item.quantity;

    return `
      <div class="cart-item" data-item-id="${item.id}">
        <div class="cart-item-image">
          <img src="${item.image}" alt="${item.name}" onerror="this.src='/uploads/placeholder-product.jpg'">
        </div>
        <div class="cart-item-details">
          <h4 class="cart-item-name">${item.name}</h4>
          <div class="cart-item-price">${Utils.formatPrice(item.price)}</div>
          <div class="cart-item-controls">
            <div class="cart-quantity-selector">
              <button class="cart-quantity-btn" data-item-id="${item.id}" data-delta="-1">−</button>
              <input type="number" class="cart-item-quantity" data-item-id="${item.id}" value="${item.quantity}" min="1" max="${item.maxStock}">
              <button class="cart-quantity-btn" data-item-id="${item.id}" data-delta="1">+</button>
            </div>
            <button class="cart-item-remove" data-item-id="${item.id}" title="Remover">×</button>
          </div>
        </div>
        <div class="cart-item-total">${Utils.formatPrice(itemTotal)}</div>
      </div>
    `;
  },

  // Actualizar total del carrito
  updateCartTotal: function() {
    const cartTotal = document.querySelector('.cart-total-amount');
    if (cartTotal) {
      cartTotal.textContent = Utils.formatPrice(Cart.total);
    }
  },

  // Actualizar botón de checkout
  updateCheckoutButton: function() {
    const checkoutBtn = document.querySelector('.checkout-btn');
    if (checkoutBtn) {
      checkoutBtn.disabled = Cart.items.length === 0;
      checkoutBtn.textContent = Cart.items.length === 0 ? 'Carrito vacío' : 'Ir al Checkout';
    }
  },

  // Proceder al checkout
  proceedToCheckout: async function() {
    if (Cart.items.length === 0) {
      Utils.showNotification('Tu carrito está vacío', 'warning');
      return;
    }

    try {
      // Guardar datos del carrito en sessionStorage para el checkout
      sessionStorage.setItem('checkout_cart', JSON.stringify(Cart.getCheckoutData()));

      // Redirigir al checkout
      window.location.href = '/ecommerce/checkout.html';
    } catch (error) {
      console.error('Error proceeding to checkout:', error);
      Utils.showNotification('Error al proceder al checkout', 'error');
    }
  },

  // Agregar producto al carrito (método público)
  addProduct: function(product, quantity = 1) {
    Cart.addItem(product, quantity);
  },

  // Obtener resumen del carrito
  getSummary: function() {
    return {
      items: Cart.items,
      total: Cart.total,
      itemCount: Cart.itemCount
    };
  }
};

// Funciones globales para el carrito
window.addToCart = function(productId, quantity = 1) {
  // Esta función se usa desde los botones de productos
  const button = document.querySelector(`[data-product-id="${productId}"] .add-to-cart-btn`);
  if (button) {
    window.KatyEcommerce.CartManager.addToCartFromButton(button);
  }
};

window.removeFromCart = function(itemId) {
  window.KatyEcommerce.CartManager.removeItem(itemId);
};

window.updateCartQuantity = function(itemId, quantity) {
  window.KatyEcommerce.CartManager.updateItemQuantity(itemId, quantity);
};

window.proceedToCheckout = function() {
  window.KatyEcommerce.CartManager.proceedToCheckout();
};

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
  window.KatyEcommerce.CartManager.init();
});