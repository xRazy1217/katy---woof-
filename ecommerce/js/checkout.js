// =========================================
// Katy & Woof E-commerce - Gestión del Checkout
// Frontend Vanilla JS - Compatible con SiteGround
// =========================================

// Gestión del proceso de checkout
const CheckoutManager = {
  currentStep: 1,
  cartData: null,
  orderData: {
    billing: {},
    shipping: {},
    payment: {}
  },

  // Inicializar
  init: function() {
    this.loadCartData();
    this.bindEvents();
    this.updateOrderSummary();
    this.showStep(1);
  },

  // Cargar datos del carrito
  loadCartData: function() {
    try {
      const savedCart = sessionStorage.getItem('checkout_cart');
      if (savedCart) {
        this.cartData = JSON.parse(savedCart);
        this.renderCartReview();
      } else {
        // Si no hay datos, redirigir al carrito
        Utils.showNotification('No hay productos en el carrito', 'warning');
        setTimeout(() => {
          window.location.href = '/ecommerce/products.html';
        }, 2000);
      }
    } catch (error) {
      console.error('Error loading cart data:', error);
      Utils.showNotification('Error al cargar datos del carrito', 'error');
    }
  },

  // Vincular eventos
  bindEvents: function() {
    // Métodos de pago
    document.querySelectorAll('input[name="payment_method"]').forEach(input => {
      input.addEventListener('change', (e) => {
        this.selectPaymentMethod(e.target.value);
      });
    });

    // Formulario de envío
    const shippingForm = document.getElementById('shipping-form');
    if (shippingForm) {
      shippingForm.addEventListener('submit', (e) => {
        e.preventDefault();
        this.validateShippingForm();
      });
    }

    // Formatear número de tarjeta
    const cardNumber = document.getElementById('card_number');
    if (cardNumber) {
      cardNumber.addEventListener('input', (e) => {
        e.target.value = this.formatCardNumber(e.target.value);
      });
    }

    // Formatear fecha de expiración
    const expiryDate = document.getElementById('expiry_date');
    if (expiryDate) {
      expiryDate.addEventListener('input', (e) => {
        e.target.value = this.formatExpiryDate(e.target.value);
      });
    }

    // Formatear CVV
    const cvv = document.getElementById('cvv');
    if (cvv) {
      cvv.addEventListener('input', (e) => {
        e.target.value = e.target.value.replace(/\D/g, '').substring(0, 4);
      });
    }
  },

  // Mostrar paso específico
  showStep: function(stepNumber) {
    // Ocultar todos los pasos
    document.querySelectorAll('.checkout-step').forEach(step => {
      step.classList.remove('active');
    });

    // Mostrar el paso actual
    const targetStep = document.getElementById(`step-${stepNumber}`);
    if (targetStep) {
      targetStep.classList.add('active');
    }

    // Actualizar indicadores de pasos
    document.querySelectorAll('.step').forEach(step => {
      const stepNum = parseInt(step.dataset.step);
      step.classList.toggle('active', stepNum === stepNumber);
      step.classList.toggle('completed', stepNum < stepNumber);
    });

    this.currentStep = stepNumber;
  },

  // Avanzar al siguiente paso
  nextStep: function(stepNumber) {
    if (this.validateCurrentStep()) {
      this.showStep(stepNumber);
    }
  },

  // Retroceder al paso anterior
  previousStep: function(stepNumber) {
    this.showStep(stepNumber);
  },

  // Validar paso actual
  validateCurrentStep: function() {
    switch (this.currentStep) {
      case 1:
        return this.cartData && this.cartData.items.length > 0;
      case 2:
        return this.validateShippingForm();
      case 3:
        return this.validatePaymentForm();
      default:
        return true;
    }
  },

  // Renderizar revisión del carrito
  renderCartReview: function() {
    const cartReview = document.getElementById('cart-review');
    if (!cartReview || !this.cartData) return;

    const itemsHTML = this.cartData.items.map(item => `
      <div class="cart-item-review">
        <div class="item-image">
          <img src="${item.image}" alt="${item.name}" onerror="this.src='/uploads/placeholder-product.jpg'">
        </div>
        <div class="item-details">
          <h4>${item.name}</h4>
          <div class="item-price">${Utils.formatPrice(item.price)}</div>
          <div class="item-quantity">Cantidad: ${item.quantity}</div>
        </div>
        <div class="item-total">${Utils.formatPrice(item.price * item.quantity)}</div>
      </div>
    `).join('');

    cartReview.innerHTML = `
      <div class="cart-items-review">
        ${itemsHTML}
      </div>
      <div class="cart-totals-review">
        <div class="total-row">
          <span>Subtotal:</span>
          <span>${Utils.formatPrice(this.cartData.total)}</span>
        </div>
        <div class="total-row">
          <span>Envío:</span>
          <span>Por calcular</span>
        </div>
        <div class="total-row total-final">
          <span>Total:</span>
          <span>${Utils.formatPrice(this.cartData.total)}</span>
        </div>
      </div>
    `;
  },

  // Validar formulario de envío
  validateShippingForm: function() {
    const requiredFields = ['first_name', 'last_name', 'email', 'phone', 'address_1', 'city', 'state'];
    let isValid = true;

    requiredFields.forEach(fieldId => {
      const field = document.getElementById(fieldId);
      if (field) {
        const value = field.value.trim();
        if (!value) {
          this.showFieldError(field, 'Este campo es obligatorio');
          isValid = false;
        } else {
          this.clearFieldError(field);

          // Validaciones específicas
          if (fieldId === 'email' && !Utils.validateEmail(value)) {
            this.showFieldError(field, 'Correo electrónico inválido');
            isValid = false;
          } else if (fieldId === 'phone' && !Utils.validatePhone(value)) {
            this.showFieldError(field, 'Número de teléfono inválido');
            isValid = false;
          }
        }
      }
    });

    if (isValid) {
      this.saveShippingData();
    }

    return isValid;
  },

  // Validar formulario de pago
  validatePaymentForm: function() {
    const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;

    if (paymentMethod === 'card') {
      return this.validateCardForm();
    }

    return true;
  },

  // Validar formulario de tarjeta
  validateCardForm: function() {
    const cardNumber = document.getElementById('card_number').value.replace(/\s/g, '');
    const expiryDate = document.getElementById('expiry_date').value;
    const cvv = document.getElementById('cvv').value;
    const cardName = document.getElementById('card_name').value.trim();

    let isValid = true;

    // Validar número de tarjeta (longitud básica)
    if (cardNumber.length < 13 || cardNumber.length > 19) {
      this.showFieldError(document.getElementById('card_number'), 'Número de tarjeta inválido');
      isValid = false;
    } else {
      this.clearFieldError(document.getElementById('card_number'));
    }

    // Validar fecha de expiración
    if (!this.isValidExpiryDate(expiryDate)) {
      this.showFieldError(document.getElementById('expiry_date'), 'Fecha de expiración inválida');
      isValid = false;
    } else {
      this.clearFieldError(document.getElementById('expiry_date'));
    }

    // Validar CVV
    if (cvv.length < 3 || cvv.length > 4) {
      this.showFieldError(document.getElementById('cvv'), 'CVV inválido');
      isValid = false;
    } else {
      this.clearFieldError(document.getElementById('cvv'));
    }

    // Validar nombre
    if (!cardName) {
      this.showFieldError(document.getElementById('card_name'), 'Nombre es obligatorio');
      isValid = false;
    } else {
      this.clearFieldError(document.getElementById('card_name'));
    }

    return isValid;
  },

  // Mostrar error en campo
  showFieldError: function(field, message) {
    this.clearFieldError(field);
    field.classList.add('error');
    const errorDiv = document.createElement('div');
    errorDiv.className = 'field-error';
    errorDiv.textContent = message;
    field.parentNode.appendChild(errorDiv);
  },

  // Limpiar error de campo
  clearFieldError: function(field) {
    field.classList.remove('error');
    const errorDiv = field.parentNode.querySelector('.field-error');
    if (errorDiv) {
      errorDiv.remove();
    }
  },

  // Guardar datos de envío
  saveShippingData: function() {
    const formData = new FormData(document.getElementById('shipping-form'));
    const shippingData = {};

    for (let [key, value] of formData.entries()) {
      shippingData[key] = value;
    }

    this.orderData.shipping = shippingData;
  },

  // Seleccionar método de pago
  selectPaymentMethod: function(method) {
    // Ocultar todos los contenidos de métodos de pago
    document.querySelectorAll('.payment-method-content').forEach(content => {
      content.style.display = 'none';
    });

    // Remover clase active de todos los métodos
    document.querySelectorAll('.payment-method').forEach(methodEl => {
      methodEl.classList.remove('active');
    });

    // Mostrar contenido del método seleccionado
    const selectedMethod = document.querySelector(`[data-method="${method}"]`);
    if (selectedMethod) {
      selectedMethod.classList.add('active');
      const content = selectedMethod.querySelector('.payment-method-content');
      if (content) {
        content.style.display = 'block';
      }
    }
  },

  // Formatear número de tarjeta
  formatCardNumber: function(value) {
    const v = value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
    const matches = v.match(/\d{4,16}/g);
    const match = matches && matches[0] || '';
    const parts = [];
    for (let i = 0, len = match.length; i < len; i += 4) {
      parts.push(match.substring(i, i + 4));
    }
    if (parts.length) {
      return parts.join(' ');
    } else {
      return v;
    }
  },

  // Formatear fecha de expiración
  formatExpiryDate: function(value) {
    const v = value.replace(/\D/g, '');
    if (v.length >= 2) {
      return v.substring(0, 2) + '/' + v.substring(2, 4);
    }
    return v;
  },

  // Validar fecha de expiración
  isValidExpiryDate: function(value) {
    const match = value.match(/^(\d{2})\/(\d{2})$/);
    if (!match) return false;

    const month = parseInt(match[1]);
    const year = parseInt('20' + match[2]);

    if (month < 1 || month > 12) return false;

    const now = new Date();
    const expiry = new Date(year, month - 1);

    return expiry > now;
  },

  // Actualizar resumen del pedido
  updateOrderSummary: function() {
    const summary = document.getElementById('order-summary');
    if (!summary || !this.cartData) return;

    const itemsHTML = this.cartData.items.map(item => `
      <div class="summary-item">
        <div class="summary-item-info">
          <span class="summary-item-name">${item.name}</span>
          <span class="summary-item-quantity">×${item.quantity}</span>
        </div>
        <span class="summary-item-price">${Utils.formatPrice(item.price * item.quantity)}</span>
      </div>
    `).join('');

    summary.innerHTML = `
      <div class="summary-items">
        ${itemsHTML}
      </div>
      <div class="summary-totals">
        <div class="summary-row">
          <span>Subtotal:</span>
          <span>${Utils.formatPrice(this.cartData.total)}</span>
        </div>
        <div class="summary-row">
          <span>Envío:</span>
          <span>Por calcular</span>
        </div>
        <div class="summary-row summary-total">
          <span>Total:</span>
          <span>${Utils.formatPrice(this.cartData.total)}</span>
        </div>
      </div>
    `;
  },

  // Procesar pago
  processPayment: async function() {
    if (!this.validatePaymentForm()) {
      return;
    }

    try {
      Utils.showGlobalLoader();

      // Recopilar todos los datos del pedido
      const orderData = {
        ...this.orderData,
        items: this.cartData.items,
        total: this.cartData.total,
        payment_method: document.querySelector('input[name="payment_method"]:checked').value
      };

      // Agregar datos de tarjeta si es pago con tarjeta
      if (orderData.payment_method === 'card') {
        orderData.card = {
          number: document.getElementById('card_number').value.replace(/\s/g, ''),
          expiry: document.getElementById('expiry_date').value,
          cvv: document.getElementById('cvv').value,
          name: document.getElementById('card_name').value
        };
      }

      // Enviar pedido a la API
      const response = await API.post('/api/checkout.php', orderData);

      if (response.success) {
        // Limpiar carrito
        localStorage.removeItem('katy_cart');
        sessionStorage.removeItem('checkout_cart');

        // Mostrar confirmación
        this.showOrderConfirmation(response.data);
      } else {
        Utils.showNotification(response.message || 'Error al procesar el pago', 'error');
      }
    } catch (error) {
      console.error('Error processing payment:', error);
      Utils.showNotification('Error al procesar el pago', 'error');
    } finally {
      Utils.hideGlobalLoader();
    }
  },

  // Mostrar confirmación del pedido
  showOrderConfirmation: function(orderData) {
    this.showStep(4);

    const orderDetails = document.getElementById('order-details');
    if (orderDetails) {
      orderDetails.innerHTML = `
        <div class="order-info">
          <div class="order-number">
            <strong>Número de pedido:</strong> #${orderData.order_id || 'N/A'}
          </div>
          <div class="order-date">
            <strong>Fecha:</strong> ${new Date().toLocaleDateString('es-CO')}
          </div>
          <div class="order-email">
            <strong>Correo:</strong> ${this.orderData.shipping.email}
          </div>
        </div>
      `;
    }

    // Scroll to top
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
};

// Funciones globales para el checkout
function nextStep(step) {
  CheckoutManager.nextStep(step);
}

function previousStep(step) {
  CheckoutManager.previousStep(step);
}

function processPayment() {
  CheckoutManager.processPayment();
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
  // El init se llama desde el HTML
});