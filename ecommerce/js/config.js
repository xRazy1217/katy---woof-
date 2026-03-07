// =========================================
// Katy & Woof E-commerce - Configuración y Utilidades
// Frontend Vanilla JS - Compatible con SiteGround
// =========================================

// Configuración global
const CONFIG = {
  API_BASE: window.location.origin + '/api',
  SITE_URL: window.location.origin,
  CURRENCY: 'COP',
  CURRENCY_SYMBOL: '$',
  ITEMS_PER_PAGE: 12,
  CSRF_TOKEN: null,
  DEBUG: false
};

// Utilidades generales
const Utils = {
  // Formatear precio
  formatPrice: function(price) {
    return new Intl.NumberFormat('es-CO', {
      style: 'currency',
      currency: CONFIG.CURRENCY,
      minimumFractionDigits: 0
    }).format(price);
  },

  // Formatear fecha
  formatDate: function(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('es-CO', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
  },

  // Sanitizar HTML
  sanitizeHTML: function(html) {
    const temp = document.createElement('div');
    temp.textContent = html;
    return temp.innerHTML;
  },

  // Truncar texto
  truncateText: function(text, maxLength) {
    if (text.length <= maxLength) return text;
    return text.substring(0, maxLength) + '...';
  },

  // Debounce function
  debounce: function(func, wait) {
    let timeout;
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout);
        func(...args);
      };
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
    };
  },

  // Mostrar notificación
  showNotification: function(message, type = 'info', duration = 3000) {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
      <div class="notification-content">
        <span class="notification-message">${message}</span>
        <button class="notification-close" onclick="this.parentElement.parentElement.remove()">&times;</button>
      </div>
    `;

    document.body.appendChild(notification);

    // Auto-remover después de la duración
    if (duration > 0) {
      setTimeout(() => {
        if (notification.parentElement) {
          notification.remove();
        }
      }, duration);
    }

    return notification;
  },

  // Mostrar loading
  showLoading: function(element, text = 'Cargando...') {
    element.innerHTML = `
      <div class="loading-spinner">
        <div class="spinner"></div>
        <span>${text}</span>
      </div>
    `;
  },

  // Ocultar loading
  hideLoading: function(element) {
    element.innerHTML = '';
  },

  // Validar email
  validateEmail: function(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
  },

  // Validar teléfono colombiano
  validatePhone: function(phone) {
    const re = /^(\+57|57)?[0-9]{10}$/;
    return re.test(phone.replace(/\s+/g, ''));
  },

  // Generar slug
  slugify: function(text) {
    return text
      .toString()
      .toLowerCase()
      .trim()
      .replace(/\s+/g, '-')
      .replace(/[^\w\-]+/g, '')
      .replace(/\-\-+/g, '-')
      .replace(/^-+/, '')
      .replace(/-+$/, '');
  },

  // Obtener parámetro URL
  getURLParameter: function(name) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(name);
  },

  // Actualizar URL sin recargar
  updateURL: function(params) {
    const url = new URL(window.location);
    Object.keys(params).forEach(key => {
      if (params[key] === null || params[key] === undefined) {
        url.searchParams.delete(key);
      } else {
        url.searchParams.set(key, params[key]);
      }
    });
    window.history.pushState({}, '', url);
  },

  // Copiar al portapapeles
  copyToClipboard: async function(text) {
    try {
      await navigator.clipboard.writeText(text);
      this.showNotification('Copiado al portapapeles', 'success');
    } catch (err) {
      // Fallback para navegadores antiguos
      const textArea = document.createElement('textarea');
      textArea.value = text;
      document.body.appendChild(textArea);
      textArea.select();
      document.execCommand('copy');
      document.body.removeChild(textArea);
      this.showNotification('Copiado al portapapeles', 'success');
    }
  },

  // Detectar si es móvil
  isMobile: function() {
    return window.innerWidth <= 768;
  },

  // Scroll suave
  smoothScroll: function(element, to, duration = 300) {
    const start = element.scrollTop;
    const change = to - start;
    const startTime = performance.now();

    function animateScroll(currentTime) {
      const elapsed = currentTime - startTime;
      const progress = Math.min(elapsed / duration, 1);

      element.scrollTop = start + change * easeInOutQuad(progress);

      if (elapsed < duration) {
        requestAnimationFrame(animateScroll);
      }
    }

    function easeInOutQuad(t) {
      return t < 0.5 ? 2 * t * t : -1 + (4 - 2 * t) * t;
    }

    requestAnimationFrame(animateScroll);
  }
};

// API Client
const API = {
  // Headers base
  getHeaders: function(includeCSRF = true) {
    const headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    };

    if (includeCSRF && CONFIG.CSRF_TOKEN) {
      headers['X-CSRF-Token'] = CONFIG.CSRF_TOKEN;
    }

    return headers;
  },

  // Manejar respuesta
  handleResponse: async function(response) {
    if (!response.ok) {
      const error = await response.json().catch(() => ({ message: 'Error en la solicitud' }));
      throw new Error(error.message || `HTTP ${response.status}`);
    }

    const data = await response.json();
    return data;
  },

  // GET request
  get: async function(endpoint, params = {}) {
    const url = new URL(CONFIG.API_BASE + endpoint);
    Object.keys(params).forEach(key => {
      if (params[key] !== null && params[key] !== undefined) {
        url.searchParams.append(key, params[key]);
      }
    });

    if (CONFIG.DEBUG) console.log('API GET:', url.toString());

    const response = await fetch(url, {
      method: 'GET',
      headers: this.getHeaders(false)
    });

    return this.handleResponse(response);
  },

  // POST request
  post: async function(endpoint, data = {}) {
    const url = CONFIG.API_BASE + endpoint;

    if (CONFIG.DEBUG) console.log('API POST:', url, data);

    const response = await fetch(url, {
      method: 'POST',
      headers: this.getHeaders(),
      body: JSON.stringify(data)
    });

    return this.handleResponse(response);
  },

  // PUT request
  put: async function(endpoint, data = {}) {
    const url = CONFIG.API_BASE + endpoint;

    if (CONFIG.DEBUG) console.log('API PUT:', url, data);

    const response = await fetch(url, {
      method: 'PUT',
      headers: this.getHeaders(),
      body: JSON.stringify(data)
    });

    return this.handleResponse(response);
  },

  // DELETE request
  delete: async function(endpoint) {
    const url = CONFIG.API_BASE + endpoint;

    if (CONFIG.DEBUG) console.log('API DELETE:', url);

    const response = await fetch(url, {
      method: 'DELETE',
      headers: this.getHeaders()
    });

    return this.handleResponse(response);
  }
};

// Gestión del carrito
const Cart = {
  items: [],
  total: 0,
  itemCount: 0,

  // Inicializar carrito
  init: function() {
    this.loadFromStorage();
    this.updateUI();
    this.bindEvents();
  },

  // Cargar desde localStorage
  loadFromStorage: function() {
    try {
      const saved = localStorage.getItem('katy_cart');
      if (saved) {
        this.items = JSON.parse(saved);
        this.calculateTotals();
      }
    } catch (error) {
      console.warn('Error loading cart from storage:', error);
      this.items = [];
    }
  },

  // Guardar en localStorage
  saveToStorage: function() {
    try {
      localStorage.setItem('katy_cart', JSON.stringify(this.items));
    } catch (error) {
      console.warn('Error saving cart to storage:', error);
    }
  },

  // Calcular totales
  calculateTotals: function() {
    this.total = this.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    this.itemCount = this.items.reduce((sum, item) => sum + item.quantity, 0);
  },

  // Agregar producto
  addItem: function(product, quantity = 1) {
    const existingItem = this.items.find(item => item.id === product.id);

    if (existingItem) {
      existingItem.quantity += quantity;
    } else {
      this.items.push({
        id: product.id,
        name: product.name,
        price: parseFloat(product.regular_price || product.price || 0),
        image: product.images?.[0]?.src || '/placeholder-product.jpg',
        quantity: quantity,
        maxStock: product.stock_quantity || 999
      });
    }

    this.calculateTotals();
    this.saveToStorage();
    this.updateUI();

    Utils.showNotification(`${product.name} agregado al carrito`, 'success');
  },

  // Remover producto
  removeItem: function(productId) {
    this.items = this.items.filter(item => item.id !== productId);
    this.calculateTotals();
    this.saveToStorage();
    this.updateUI();
  },

  // Actualizar cantidad
  updateQuantity: function(productId, quantity) {
    const item = this.items.find(item => item.id === productId);
    if (item) {
      item.quantity = Math.max(1, Math.min(quantity, item.maxStock));
      this.calculateTotals();
      this.saveToStorage();
      this.updateUI();
    }
  },

  // Limpiar carrito
  clear: function() {
    this.items = [];
    this.total = 0;
    this.itemCount = 0;
    this.saveToStorage();
    this.updateUI();
  },

  // Actualizar UI
  updateUI: function() {
    // Actualizar contador del carrito
    const cartCount = document.querySelector('.cart-count');
    if (cartCount) {
      cartCount.textContent = this.itemCount;
      cartCount.style.display = this.itemCount > 0 ? 'inline' : 'none';
    }

    // Actualizar total del carrito
    const cartTotal = document.querySelector('.cart-total');
    if (cartTotal) {
      cartTotal.textContent = Utils.formatPrice(this.total);
    }

    // Disparar evento de actualización
    window.dispatchEvent(new CustomEvent('cartUpdated', {
      detail: { items: this.items, total: this.total, itemCount: this.itemCount }
    }));
  },

  // Vincular eventos
  bindEvents: function() {
    // Event listeners para botones de agregar al carrito
    document.addEventListener('click', (e) => {
      if (e.target.matches('.add-to-cart-btn')) {
        e.preventDefault();
        const productId = e.target.dataset.productId;
        const quantity = parseInt(e.target.dataset.quantity) || 1;

        // Buscar datos del producto en el DOM
        const productCard = e.target.closest('.product-card');
        if (productCard) {
          const product = {
            id: productId,
            name: productCard.querySelector('.product-name')?.textContent || 'Producto',
            regular_price: productCard.dataset.price || 0,
            images: [{ src: productCard.querySelector('.product-image')?.src || '/placeholder-product.jpg' }],
            stock_quantity: parseInt(productCard.dataset.stock) || 999
          };
          this.addItem(product, quantity);
        }
      }
    });
  },

  // Obtener resumen para checkout
  getCheckoutData: function() {
    return {
      items: this.items,
      total: this.total,
      itemCount: this.itemCount
    };
  }
};

// Gestión de productos
const Products = {
  currentPage: 1,
  totalPages: 1,
  filters: {
    search: '',
    category: null,
    min_price: null,
    max_price: null,
    orderby: 'date',
    per_page: CONFIG.ITEMS_PER_PAGE
  },

  // Cargar productos
  loadProducts: async function(page = 1, append = false) {
    try {
      const params = {
        page: page,
        per_page: this.filters.per_page,
        ...this.filters
      };

      const response = await API.get('/products.php', params);

      if (response.success) {
        if (append) {
          this.appendProducts(response.data);
        } else {
          this.displayProducts(response.data);
        }

        this.currentPage = page;
        this.totalPages = response.pagination?.total_pages || 1;

        // Actualizar controles de paginación
        this.updatePagination(response.pagination);
      } else {
        Utils.showNotification('Error al cargar productos', 'error');
      }
    } catch (error) {
      console.error('Error loading products:', error);
      Utils.showNotification('Error al cargar productos', 'error');
    }
  },

  // Mostrar productos
  displayProducts: function(products) {
    const container = document.querySelector('.products-grid');
    if (!container) return;

    if (products.length === 0) {
      container.innerHTML = `
        <div class="no-products">
          <div class="no-products-icon">📦</div>
          <h3>No se encontraron productos</h3>
          <p>Intenta ajustar tus filtros de búsqueda</p>
        </div>
      `;
      return;
    }

    container.innerHTML = products.map(product => this.createProductCard(product)).join('');
  },

  // Agregar productos (para paginación infinita)
  appendProducts: function(products) {
    const container = document.querySelector('.products-grid');
    if (!container) return;

    const fragment = document.createDocumentFragment();
    products.forEach(product => {
      const card = document.createElement('div');
      card.innerHTML = this.createProductCard(product);
      fragment.appendChild(card.firstElementChild);
    });

    container.appendChild(fragment);
  },

  // Crear tarjeta de producto
  createProductCard: function(product) {
    const price = parseFloat(product.regular_price || product.price || 0);
    const salePrice = product.on_sale ? parseFloat(product.sale_price) : null;
    const finalPrice = salePrice || price;
    const inCart = Cart.items.some(item => item.id === product.id);
    const cartItem = Cart.items.find(item => item.id === product.id);

    return `
      <div class="product-card" data-product-id="${product.id}" data-price="${price}" data-stock="${product.stock_quantity || 999}">
        <div class="product-image-container">
          <img src="${product.images?.[0]?.src || '/placeholder-product.jpg'}" alt="${product.name}" class="product-image" loading="lazy">
          ${product.featured ? '<span class="product-badge badge-featured">Destacado</span>' : ''}
          ${product.on_sale ? '<span class="product-badge badge-sale">Oferta</span>' : ''}
          ${!product.in_stock ? '<span class="product-badge badge-out">Agotado</span>' : ''}
          <div class="product-overlay">
            <button class="btn-icon btn-quick-view" data-product-id="${product.id}">
              <span class="icon">👁</span>
            </button>
            <button class="btn-icon btn-wishlist" data-product-id="${product.id}">
              <span class="icon">♥</span>
            </button>
          </div>
        </div>
        <div class="product-info">
          ${product.categories?.[0] ? `<span class="product-category">${product.categories[0].name}</span>` : ''}
          <h3 class="product-name">${product.name}</h3>
          ${product.average_rating ? `
            <div class="product-rating">
              ${this.createStarRating(product.average_rating)}
              <span class="rating-count">(${product.rating_count || 0})</span>
            </div>
          ` : ''}
          <div class="product-price">
            ${salePrice ? `
              <span class="price-regular">${Utils.formatPrice(price)}</span>
              <span class="price-sale">${Utils.formatPrice(salePrice)}</span>
              <span class="price-discount">-${Math.round(((price - salePrice) / price) * 100)}%</span>
            ` : `
              <span class="price-current">${Utils.formatPrice(finalPrice)}</span>
            `}
          </div>
          <div class="product-actions">
            ${inCart ? `
              <div class="cart-status">
                <span class="in-cart-text">✓ En carrito (${cartItem.quantity})</span>
              </div>
            ` : `
              <div class="quantity-selector">
                <button class="quantity-btn" onclick="changeQuantity(this, -1)">−</button>
                <input type="number" class="quantity-input" value="1" min="1" max="${product.stock_quantity || 999}">
                <button class="quantity-btn" onclick="changeQuantity(this, 1)">+</button>
              </div>
              <button class="btn btn-primary add-to-cart-btn" data-product-id="${product.id}" ${!product.in_stock ? 'disabled' : ''}>
                ${!product.in_stock ? 'Agotado' : 'Agregar al carrito'}
              </button>
            `}
          </div>
        </div>
      </div>
    `;
  },

  // Crear rating de estrellas
  createStarRating: function(rating) {
    const stars = [];
    const fullStars = Math.floor(rating);
    const hasHalfStar = rating % 1 !== 0;

    for (let i = 0; i < 5; i++) {
      if (i < fullStars) {
        stars.push('<span class="star filled">★</span>');
      } else if (i === fullStars && hasHalfStar) {
        stars.push('<span class="star half">★</span>');
      } else {
        stars.push('<span class="star">☆</span>');
      }
    }

    return stars.join('');
  },

  // Actualizar paginación
  updatePagination: function(pagination) {
    const paginationContainer = document.querySelector('.pagination');
    if (!paginationContainer) return;

    // Implementar paginación si es necesario
  },

  // Aplicar filtros
  applyFilters: function(newFilters) {
    this.filters = { ...this.filters, ...newFilters, page: 1 };
    Utils.updateURL(this.filters);
    this.loadProducts(1, false);
  },

  // Buscar productos
  search: function(query) {
    this.applyFilters({ search: query });
  }
};

// Inicialización cuando el DOM está listo
document.addEventListener('DOMContentLoaded', function() {
  // Inicializar carrito
  Cart.init();

  // Inicializar productos si estamos en la página de productos
  if (document.querySelector('.products-grid')) {
    Products.loadProducts();
  }

  // Configurar CSRF token si existe
  const csrfToken = document.querySelector('meta[name="csrf-token"]');
  if (csrfToken) {
    CONFIG.CSRF_TOKEN = csrfToken.getAttribute('content');
  }

  if (CONFIG.DEBUG) {
    console.log('E-commerce initialized', { CONFIG, Cart, Products });
  }
});

// Funciones globales para usar desde HTML
window.changeQuantity = function(button, delta) {
  const input = button.parentElement.querySelector('.quantity-input');
  const currentValue = parseInt(input.value);
  const newValue = Math.max(1, currentValue + delta);
  input.value = newValue;

  // Actualizar data-quantity del botón agregar al carrito
  const addBtn = button.closest('.product-actions').querySelector('.add-to-cart-btn');
  if (addBtn) {
    addBtn.dataset.quantity = newValue;
  }
};

window.addToCart = function(productId, quantity = 1) {
  // Esta función se implementará cuando tengamos los datos del producto
  console.log('Add to cart:', productId, quantity);
};

// Exportar para uso global
window.KatyEcommerce = {
  CONFIG,
  Utils,
  API,
  Cart,
  Products
};