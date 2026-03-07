// =========================================
// Katy & Woof E-commerce - Interfaz de Usuario
// Frontend Vanilla JS - Compatible con SiteGround
// =========================================

// Gestión de la interfaz de usuario
window.KatyEcommerce = window.KatyEcommerce || {};

window.KatyEcommerce.UIManager = {
  // Inicializar
  init: function() {
    this.bindEvents();
    this.initModals();
    this.initResponsive();
  },

  // Vincular eventos
  bindEvents: function() {
    // Navegación móvil
    this.initMobileNav();

    // Filtros móviles
    this.initMobileFilters();

    // Modales
    this.initModalEvents();

    // Scroll suave
    this.initSmoothScroll();

    // Lazy loading de imágenes
    this.initLazyLoading();

    // Tema oscuro/claro (si se implementa)
    this.initThemeToggle();
  },

  // Inicializar navegación móvil
  initMobileNav: function() {
    const navToggle = document.querySelector('.nav-toggle');
    const navMenu = document.querySelector('.nav-menu');

    if (navToggle && navMenu) {
      navToggle.addEventListener('click', () => {
        navMenu.classList.toggle('active');
        navToggle.classList.toggle('active');
      });

      // Cerrar menú al hacer click fuera
      document.addEventListener('click', (e) => {
        if (!navToggle.contains(e.target) && !navMenu.contains(e.target)) {
          navMenu.classList.remove('active');
          navToggle.classList.remove('active');
        }
      });
    }
  },

  // Inicializar filtros móviles
  initMobileFilters: function() {
    const filtersToggle = document.getElementById('filters-toggle');
    const filtersSidebar = document.getElementById('filters-sidebar');
    const filtersClose = document.getElementById('filters-close');

    if (filtersToggle && filtersSidebar) {
      filtersToggle.addEventListener('click', () => {
        filtersSidebar.classList.add('active');
        document.body.style.overflow = 'hidden';
      });

      if (filtersClose) {
        filtersClose.addEventListener('click', () => {
          filtersSidebar.classList.remove('active');
          document.body.style.overflow = '';
        });
      }

      // Cerrar filtros al hacer click fuera
      filtersSidebar.addEventListener('click', (e) => {
        if (e.target === filtersSidebar) {
          filtersSidebar.classList.remove('active');
          document.body.style.overflow = '';
        }
      });
    }
  },

  // Inicializar eventos de modales
  initModalEvents: function() {
    // Modal de vista rápida
    this.initQuickViewModal();

    // Modal de login/register (si se implementa)
    this.initAuthModal();

    // Modal de búsqueda avanzada (si se implementa)
    this.initSearchModal();
  },

  // Inicializar modal de vista rápida
  initQuickViewModal: function() {
    const modal = document.getElementById('quick-view-modal');
    const closeBtn = modal?.querySelector('.modal-close');

    if (modal) {
      // Cerrar modal
      if (closeBtn) {
        closeBtn.addEventListener('click', () => {
          modal.style.display = 'none';
        });
      }

      // Cerrar al hacer click fuera
      modal.addEventListener('click', (e) => {
        if (e.target === modal) {
          modal.style.display = 'none';
        }
      });

      // Cerrar con tecla Escape
      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal.style.display === 'flex') {
          modal.style.display = 'none';
        }
      });

      // Delegación de eventos para botones de vista rápida
      document.addEventListener('click', (e) => {
        if (e.target.matches('.btn-quick-view')) {
          e.preventDefault();
          const productId = e.target.dataset.productId;
          if (window.KatyEcommerce.ProductManager) {
            window.KatyEcommerce.ProductManager.showQuickView(productId);
          }
        }
      });
    }
  },

  // Inicializar modal de autenticación
  initAuthModal: function() {
    // Implementar si se necesita autenticación de usuarios
  },

  // Inicializar modal de búsqueda
  initSearchModal: function() {
    // Implementar si se necesita búsqueda avanzada
  },

  // Inicializar modales
  initModals: function() {
    // Crear modal de vista rápida si no existe
    if (!document.getElementById('quick-view-modal')) {
      this.createQuickViewModal();
    }

    // Crear modal de carrito si no existe
    if (!document.getElementById('cart-sidebar')) {
      this.createCartSidebar();
    }
  },

  // Crear modal de vista rápida
  createQuickViewModal: function() {
    const modal = document.createElement('div');
    modal.id = 'quick-view-modal';
    modal.className = 'modal';
    modal.style.display = 'none';
    modal.innerHTML = `
      <div class="modal-overlay"></div>
      <div class="modal-content quick-view-modal-content">
        <div class="modal-header">
          <h3>Vista Rápida del Producto</h3>
          <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body" id="quick-view-content">
          <div class="loading-spinner">
            <div class="spinner"></div>
            <span>Cargando producto...</span>
          </div>
        </div>
      </div>
    `;
    document.body.appendChild(modal);
  },

  // Crear sidebar del carrito
  createCartSidebar: function() {
    const sidebar = document.createElement('div');
    sidebar.id = 'cart-sidebar';
    sidebar.className = 'cart-sidebar';
    sidebar.style.display = 'none';
    sidebar.innerHTML = `
      <div class="cart-overlay"></div>
      <div class="cart-content">
        <div class="cart-header">
          <h3>Carrito de Compras</h3>
          <button class="cart-close">&times;</button>
        </div>
        <div class="cart-body" id="cart-content">
          <div class="empty-cart">
            <div class="empty-cart-icon">🛒</div>
            <p>Tu carrito está vacío</p>
          </div>
        </div>
        <div class="cart-footer">
          <div class="cart-total">
            <span>Total:</span>
            <span class="cart-total-amount">$0</span>
          </div>
          <button class="btn btn-primary checkout-btn" disabled onclick="proceedToCheckout()">
            Ir al Checkout
          </button>
        </div>
      </div>
    `;
    document.body.appendChild(sidebar);
  },

  // Inicializar scroll suave
  initSmoothScroll: function() {
    // Scroll suave para anclas
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', (e) => {
        e.preventDefault();
        const target = document.querySelector(anchor.getAttribute('href'));
        if (target) {
          Utils.smoothScroll(document.documentElement, target.offsetTop, 500);
        }
      });
    });
  },

  // Inicializar lazy loading
  initLazyLoading: function() {
    if ('IntersectionObserver' in window) {
      const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            const img = entry.target;
            img.src = img.dataset.src || img.src;
            img.classList.remove('lazy');
            observer.unobserve(img);
          }
        });
      });

      document.querySelectorAll('img[loading="lazy"]').forEach(img => {
        imageObserver.observe(img);
      });
    }
  },

  // Inicializar toggle de tema
  initThemeToggle: function() {
    // Implementar si se necesita soporte para tema oscuro
  },

  // Inicializar responsive
  initResponsive: function() {
    // Detectar cambios de tamaño de pantalla
    window.addEventListener('resize', Utils.debounce(() => {
      this.handleResize();
    }, 250));

    this.handleResize();
  },

  // Manejar cambios de tamaño
  handleResize: function() {
    const isMobile = Utils.isMobile();

    // Ajustar navegación
    const navMenu = document.querySelector('.nav-menu');
    if (navMenu) {
      if (isMobile) {
        navMenu.classList.remove('active');
      }
    }

    // Ajustar filtros
    const filtersSidebar = document.getElementById('filters-sidebar');
    if (filtersSidebar && !isMobile) {
      filtersSidebar.classList.remove('active');
      document.body.style.overflow = '';
    }
  },

  // Mostrar notificación
  showNotification: function(message, type = 'info', duration = 3000) {
    return Utils.showNotification(message, type, duration);
  },

  // Mostrar loading
  showLoading: function(element, text = 'Cargando...') {
    Utils.showLoading(element, text);
  },

  // Ocultar loading
  hideLoading: function(element) {
    Utils.hideLoading(element);
  },

  // Scroll to top
  scrollToTop: function() {
    Utils.smoothScroll(document.documentElement, 0, 500);
  },

  // Crear botón de scroll to top
  createScrollToTopButton: function() {
    const button = document.createElement('button');
    button.id = 'scroll-to-top';
    button.className = 'scroll-to-top-btn';
    button.innerHTML = '↑';
    button.title = 'Volver arriba';
    button.style.cssText = `
      position: fixed;
      bottom: 2rem;
      right: 2rem;
      width: 3rem;
      height: 3rem;
      border-radius: 50%;
      background: var(--primary-color);
      color: white;
      border: none;
      cursor: pointer;
      opacity: 0;
      visibility: hidden;
      transition: all 0.3s ease;
      z-index: 1000;
      font-size: 1.25rem;
    `;

    button.addEventListener('click', () => this.scrollToTop());

    document.body.appendChild(button);

    // Mostrar/ocultar botón basado en scroll
    window.addEventListener('scroll', () => {
      const scrolled = window.pageYOffset;
      const threshold = 300;

      if (scrolled > threshold) {
        button.style.opacity = '1';
        button.style.visibility = 'visible';
      } else {
        button.style.opacity = '0';
        button.style.visibility = 'hidden';
      }
    });
  },

  // Crear indicador de carga global
  createGlobalLoader: function() {
    const loader = document.createElement('div');
    loader.id = 'global-loader';
    loader.style.cssText = `
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(255, 255, 255, 0.9);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 9999;
    `;
    loader.innerHTML = `
      <div class="loading-spinner">
        <div class="spinner"></div>
        <span>Cargando...</span>
      </div>
    `;
    loader.style.display = 'none';
    document.body.appendChild(loader);

    return loader;
  },

  // Mostrar loader global
  showGlobalLoader: function() {
    let loader = document.getElementById('global-loader');
    if (!loader) {
      loader = this.createGlobalLoader();
    }
    loader.style.display = 'flex';
  },

  // Ocultar loader global
  hideGlobalLoader: function() {
    const loader = document.getElementById('global-loader');
    if (loader) {
      loader.style.display = 'none';
    }
  }
};

// Funciones de utilidad para la UI
window.UI = {
  // Mostrar modal
  showModal: function(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
      modal.style.display = 'flex';
      document.body.style.overflow = 'hidden';
    }
  },

  // Ocultar modal
  hideModal: function(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
      modal.style.display = 'none';
      document.body.style.overflow = '';
    }
  },

  // Toggle elemento
  toggle: function(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
      element.style.display = element.style.display === 'none' ? 'block' : 'none';
    }
  },

  // Agregar clase
  addClass: function(elementId, className) {
    const element = document.getElementById(elementId);
    if (element) {
      element.classList.add(className);
    }
  },

  // Remover clase
  removeClass: function(elementId, className) {
    const element = document.getElementById(elementId);
    if (element) {
      element.classList.remove(className);
    }
  },

  // Toggle clase
  toggleClass: function(elementId, className) {
    const element = document.getElementById(elementId);
    if (element) {
      element.classList.toggle(className);
    }
  },

  // Obtener valor de input
  getValue: function(inputId) {
    const input = document.getElementById(inputId);
    return input ? input.value : '';
  },

  // Establecer valor de input
  setValue: function(inputId, value) {
    const input = document.getElementById(inputId);
    if (input) {
      input.value = value;
    }
  },

  // Obtener HTML de elemento
  getHTML: function(elementId) {
    const element = document.getElementById(elementId);
    return element ? element.innerHTML : '';
  },

  // Establecer HTML de elemento
  setHTML: function(elementId, html) {
    const element = document.getElementById(elementId);
    if (element) {
      element.innerHTML = html;
    }
  },

  // Agregar evento
  addEvent: function(elementId, event, callback) {
    const element = document.getElementById(elementId);
    if (element) {
      element.addEventListener(event, callback);
    }
  }
};

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
  window.KatyEcommerce.UIManager.init();

  // Crear botón de scroll to top
  window.KatyEcommerce.UIManager.createScrollToTopButton();
});