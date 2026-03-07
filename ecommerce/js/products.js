// =========================================
// Katy & Woof E-commerce - Gestión de Productos
// Frontend Vanilla JS - Compatible con SiteGround
// =========================================

// Gestión específica de productos
window.KatyEcommerce = window.KatyEcommerce || {};

window.KatyEcommerce.ProductManager = {
  currentPage: 1,
  hasMorePages: true,
  isLoading: false,
  filters: {
    search: '',
    category: null,
    min_price: null,
    max_price: null,
    orderby: 'date',
    per_page: 12,
    in_stock: null,
    featured: null
  },

  // Inicializar
  init: function() {
    this.loadProducts();
    this.loadCategories();
    this.bindEvents();
  },

  // Vincular eventos
  bindEvents: function() {
    // Evento de scroll infinito
    window.addEventListener('scroll', Utils.debounce(() => {
      if (this.shouldLoadMore()) {
        this.loadMoreProducts();
      }
    }, 200));

    // Evento de carrito actualizado
    window.addEventListener('cartUpdated', (e) => {
      this.updateProductCards();
    });
  },

  // Verificar si debe cargar más productos
  shouldLoadMore: function() {
    if (this.isLoading || !this.hasMorePages) return false;

    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
    const windowHeight = window.innerHeight;
    const documentHeight = document.documentElement.scrollHeight;

    return scrollTop + windowHeight >= documentHeight - 500; // 500px antes del final
  },

  // Cargar productos
  loadProducts: async function(page = 1, append = false) {
    if (this.isLoading) return;

    try {
      this.isLoading = true;
      const grid = document.getElementById('products-grid');

      if (!append) {
        grid.innerHTML = '<div class="loading-spinner"><div class="spinner"></div><span>Cargando productos...</span></div>';
      }

      const params = {
        page: page,
        per_page: this.filters.per_page,
        ...this.filters
      };

      // Remover parámetros undefined
      Object.keys(params).forEach(key => {
        if (params[key] === null || params[key] === undefined) {
          delete params[key];
        }
      });

      const response = await API.get('/api/products.php', params);

      if (response.success && response.data) {
        if (append) {
          this.appendProducts(response.data);
        } else {
          this.displayProducts(response.data);
        }

        this.currentPage = page;
        this.hasMorePages = response.pagination && response.pagination.has_next;

        // Actualizar contador de resultados
        this.updateResultsCount(response.pagination?.total || 0);

        Utils.showNotification(`Se encontraron ${response.pagination?.total || 0} productos`, 'info', 2000);
      } else {
        this.showNoProducts();
        Utils.showNotification(response.message || 'No se encontraron productos', 'warning');
      }
    } catch (error) {
      console.error('Error loading products:', error);
      this.showError('Error al cargar productos');
      Utils.showNotification('Error al cargar productos', 'error');
    } finally {
      this.isLoading = false;
    }
  },

  // Cargar más productos
  loadMoreProducts: function() {
    if (!this.isLoading && this.hasMorePages) {
      this.loadProducts(this.currentPage + 1, true);
    }
  },

  // Mostrar productos
  displayProducts: function(products) {
    const grid = document.getElementById('products-grid');

    if (products.length === 0) {
      this.showNoProducts();
      return;
    }

    grid.innerHTML = products.map(product => this.createProductCard(product)).join('');
    this.updateProductCards();
  },

  // Agregar productos
  appendProducts: function(products) {
    const grid = document.getElementById('products-grid');
    const fragment = document.createDocumentFragment();

    products.forEach(product => {
      const card = document.createElement('div');
      card.innerHTML = this.createProductCard(product);
      fragment.appendChild(card.firstElementChild);
    });

    grid.appendChild(fragment);
    this.updateProductCards();
  },

  // Crear tarjeta de producto
  createProductCard: function(product) {
    const price = parseFloat(product.regular_price || product.price || 0);
    const salePrice = product.on_sale ? parseFloat(product.sale_price) : null;
    const finalPrice = salePrice || price;
    const inCart = Cart.items.some(item => item.id === product.id);
    const cartItem = Cart.items.find(item => item.id === product.id);

    return `
      <div class="product-card fade-in" data-product-id="${product.id}" data-price="${price}" data-stock="${product.stock_quantity || 999}">
        <div class="product-image-container">
          <img src="${product.images?.[0]?.src || '/uploads/placeholder-product.jpg'}" alt="${product.name}" class="product-image" loading="lazy" onerror="this.src='/uploads/placeholder-product.jpg'">
          ${product.featured ? '<span class="product-badge badge-featured">Destacado</span>' : ''}
          ${product.on_sale ? '<span class="product-badge badge-sale">Oferta</span>' : ''}
          ${!product.in_stock ? '<span class="product-badge badge-out">Agotado</span>' : ''}
          <div class="product-overlay">
            <button class="btn-icon btn-quick-view" data-product-id="${product.id}" title="Vista rápida">
              👁
            </button>
            <button class="btn-icon btn-wishlist" data-product-id="${product.id}" title="Agregar a favoritos">
              ${product.is_wishlisted ? '❤️' : '🤍'}
            </button>
          </div>
        </div>
        <div class="product-info">
          ${product.categories?.[0] ? `<span class="product-category">${product.categories[0].name}</span>` : ''}
          <h3 class="product-name" title="${product.name}">${product.name}</h3>
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
                <button class="quantity-btn" onclick="changeQuantity(this, -1, ${product.id})" title="Disminuir cantidad">−</button>
                <input type="number" class="quantity-input quantity-${product.id}" value="1" min="1" max="${product.stock_quantity || 999}" onchange="updateQuantityInput(${product.id}, this.value)">
                <button class="quantity-btn" onclick="changeQuantity(this, 1, ${product.id})" title="Aumentar cantidad">+</button>
              </div>
              <button class="btn btn-primary add-to-cart-btn" data-product-id="${product.id}" data-quantity="1" ${!product.in_stock ? 'disabled' : ''}>
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

  // Actualizar tarjetas de productos
  updateProductCards: function() {
    document.querySelectorAll('.product-card').forEach(card => {
      const productId = card.dataset.productId;
      const inCart = Cart.items.some(item => item.id === productId);
      const cartItem = Cart.items.find(item => item.id === productId);
      const actionsDiv = card.querySelector('.product-actions');

      if (inCart && cartItem) {
        actionsDiv.innerHTML = `
          <div class="cart-status">
            <span class="in-cart-text">✓ En carrito (${cartItem.quantity})</span>
          </div>
        `;
      } else if (!card.querySelector('.quantity-selector')) {
        const stock = parseInt(card.dataset.stock) || 999;
        const inStock = stock > 0;

        actionsDiv.innerHTML = `
          <div class="quantity-selector">
            <button class="quantity-btn" onclick="changeQuantity(this, -1, ${productId})" title="Disminuir cantidad">−</button>
            <input type="number" class="quantity-input quantity-${productId}" value="1" min="1" max="${stock}" onchange="updateQuantityInput(${productId}, this.value)">
            <button class="quantity-btn" onclick="changeQuantity(this, 1, ${productId})" title="Aumentar cantidad">+</button>
          </div>
          <button class="btn btn-primary add-to-cart-btn" data-product-id="${productId}" data-quantity="1" ${!inStock ? 'disabled' : ''}>
            ${!inStock ? 'Agotado' : 'Agregar al carrito'}
          </button>
        `;
      }
    });
  },

  // Cargar categorías
  loadCategories: async function() {
    try {
      const response = await API.get('/api/products.php', { action: 'categories' });

      if (response.success && response.data) {
        this.renderCategories(response.data);
      }
    } catch (error) {
      console.error('Error loading categories:', error);
    }
  },

  // Renderizar categorías
  renderCategories: function(categories) {
    const categoriesList = document.getElementById('categories-list');
    if (!categoriesList) return;

    // Limpiar categorías existentes excepto "Todas"
    const allCategoriesOption = categoriesList.querySelector('input[value=""]');
    categoriesList.innerHTML = '';
    if (allCategoriesOption) {
      categoriesList.appendChild(allCategoriesOption.parentElement);
    }

    categories.forEach(category => {
      const label = document.createElement('label');
      label.className = 'filter-option';
      label.innerHTML = `
        <input type="radio" name="category" value="${category.id}">
        <span>${category.name}</span>
        ${category.count ? `<small>(${category.count})</small>` : ''}
      `;
      categoriesList.appendChild(label);
    });

    // Event listeners para categorías
    document.querySelectorAll('input[name="category"]').forEach(input => {
      input.addEventListener('change', () => {
        this.applyFilters({ category: input.value || null, page: 1 });
      });
    });
  },

  // Aplicar filtros
  applyFilters: function(newFilters) {
    this.filters = { ...this.filters, ...newFilters };
    Utils.updateURL(this.filters);
    this.loadProducts(1, false);
  },

  // Buscar productos
  search: function(query) {
    this.applyFilters({ search: query, page: 1 });
  },

  // Actualizar contador de resultados
  updateResultsCount: function(total) {
    const resultsCount = document.querySelector('.results-count');
    if (resultsCount) {
      resultsCount.textContent = `${total} producto${total !== 1 ? 's' : ''} encontrado${total !== 1 ? 's' : ''}`;
    }
  },

  // Mostrar sin productos
  showNoProducts: function() {
    const grid = document.getElementById('products-grid');
    grid.innerHTML = `
      <div class="no-products">
        <div class="no-products-icon">📦</div>
        <h3>No se encontraron productos</h3>
        <p>Intenta ajustar tus filtros de búsqueda o <a href="#" onclick="clearAllFilters()">limpia los filtros</a></p>
      </div>
    `;
  },

  // Mostrar error
  showError: function(message) {
    const grid = document.getElementById('products-grid');
    grid.innerHTML = `
      <div class="error-state">
        <div class="error-icon">⚠️</div>
        <h3>Error al cargar productos</h3>
        <p>${message}</p>
        <button class="btn btn-primary" onclick="window.KatyEcommerce.ProductManager.loadProducts()">Reintentar</button>
      </div>
    `;
  },

  // Vista rápida de producto
  showQuickView: async function(productId) {
    try {
      const response = await API.get('/api/products.php', { id: productId });

      if (response.success && response.data) {
        this.renderQuickView(response.data);
        document.getElementById('quick-view-modal').style.display = 'flex';
      }
    } catch (error) {
      console.error('Error loading product details:', error);
      Utils.showNotification('Error al cargar detalles del producto', 'error');
    }
  },

  // Renderizar vista rápida
  renderQuickView: function(product) {
    const modal = document.getElementById('quick-view-modal');
    const content = document.getElementById('quick-view-content');

    const price = parseFloat(product.regular_price || product.price || 0);
    const salePrice = product.on_sale ? parseFloat(product.sale_price) : null;
    const finalPrice = salePrice || price;

    content.innerHTML = `
      <div class="quick-view-product">
        <div class="quick-view-image">
          <img src="${product.images?.[0]?.src || '/uploads/placeholder-product.jpg'}" alt="${product.name}" onerror="this.src='/uploads/placeholder-product.jpg'">
        </div>
        <div class="quick-view-details">
          <h2>${product.name}</h2>
          ${product.categories?.[0] ? `<span class="product-category">${product.categories[0].name}</span>` : ''}

          ${product.average_rating ? `
            <div class="product-rating">
              ${this.createStarRating(product.average_rating)}
              <span class="rating-count">(${product.rating_count || 0} reseñas)</span>
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

          ${product.short_description ? `
            <div class="product-description">
              ${product.short_description}
            </div>
          ` : ''}

          <div class="product-meta">
            ${product.weight ? `<span>Peso: ${product.weight}kg</span>` : ''}
            ${product.dimensions ? `<span>Dimensiones: ${product.dimensions}</span>` : ''}
            <span>Stock: ${product.stock_quantity || 0} unidades</span>
          </div>

          <div class="quick-view-actions">
            <div class="quantity-selector">
              <button class="quantity-btn" onclick="changeModalQuantity(-1)">−</button>
              <input type="number" id="modal-quantity" class="quantity-input" value="1" min="1" max="${product.stock_quantity || 999}">
              <button class="quantity-btn" onclick="changeModalQuantity(1)">+</button>
            </div>
            <button class="btn btn-primary" onclick="addToCartFromModal(${product.id})" ${!product.in_stock ? 'disabled' : ''}>
              ${!product.in_stock ? 'Agotado' : 'Agregar al carrito'}
            </button>
          </div>
        </div>
      </div>
    `;

    // Guardar producto actual para el modal
    modal.dataset.productId = product.id;
  }
};

// Funciones globales para eventos
window.changeQuantity = function(button, delta, productId) {
  const input = document.querySelector(`.quantity-${productId}`);
  if (!input) return;

  const currentValue = parseInt(input.value);
  const maxValue = parseInt(input.max) || 999;
  const newValue = Math.max(1, Math.min(maxValue, currentValue + delta));
  input.value = newValue;

  // Actualizar data-quantity del botón agregar al carrito
  const card = button.closest('.product-card');
  const addBtn = card.querySelector('.add-to-cart-btn');
  if (addBtn) {
    addBtn.dataset.quantity = newValue;
  }
};

window.updateQuantityInput = function(productId, value) {
  const card = document.querySelector(`[data-product-id="${productId}"]`);
  const addBtn = card.querySelector('.add-to-cart-btn');
  if (addBtn) {
    addBtn.dataset.quantity = value;
  }
};

window.changeModalQuantity = function(delta) {
  const input = document.getElementById('modal-quantity');
  const currentValue = parseInt(input.value);
  const maxValue = parseInt(input.max) || 999;
  const newValue = Math.max(1, Math.min(maxValue, currentValue + delta));
  input.value = newValue;
};

window.addToCartFromModal = function(productId) {
  const quantity = parseInt(document.getElementById('modal-quantity').value);
  const productCard = document.querySelector(`[data-product-id="${productId}"]`);

  if (productCard) {
    const product = {
      id: productId,
      name: productCard.querySelector('.product-name')?.textContent || 'Producto',
      regular_price: productCard.dataset.price || 0,
      images: [{ src: productCard.querySelector('.product-image')?.src || '/uploads/placeholder-product.jpg' }],
      stock_quantity: parseInt(productCard.dataset.stock) || 999
    };

    Cart.addItem(product, quantity);
    document.getElementById('quick-view-modal').style.display = 'none';
  }
};

window.clearAllFilters = function() {
  window.KatyEcommerce.ProductManager.applyFilters({
    search: '',
    category: null,
    min_price: null,
    max_price: null,
    in_stock: null,
    featured: null,
    page: 1
  });

  // Limpiar inputs
  document.getElementById('product-search').value = '';
  document.getElementById('min-price').value = '';
  document.getElementById('max-price').value = '';
  document.getElementById('in-stock-only').checked = false;
  document.getElementById('featured-only').checked = false;
  document.querySelector('input[name="category"][value=""]').checked = true;
};

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
  if (document.getElementById('products-grid')) {
    window.KatyEcommerce.ProductManager.init();
  }
});