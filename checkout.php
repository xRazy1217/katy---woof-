<?php
require_once 'config.php';
$pageTitle = 'Checkout';
$pageDesc  = 'Completa tu compra de forma segura.';
include 'header.php';
?>

<main style="padding-top:5rem;min-height:100vh;background:var(--dark)">
  <div class="container" style="padding-top:3rem;padding-bottom:5rem;max-width:1000px">

    <div style="margin-bottom:2.5rem">
      <span class="label">Compra segura</span>
      <h1 style="font-size:clamp(1.8rem,3vw,2.5rem);margin-top:0.6rem">Checkout</h1>
    </div>

    <!-- STEPS -->
    <div style="display:flex;align-items:center;margin-bottom:3rem" id="checkoutSteps">
      <?php foreach([['1','Carrito'],['2','Datos'],['3','Pago']] as $i => [$num,$lbl]): ?>
      <div style="display:flex;align-items:center;gap:0.6rem;<?php echo $i>0?'flex:1;':''; ?>">
        <?php if($i > 0): ?>
        <div style="flex:1;height:1px;background:rgba(255,255,255,0.08)"></div>
        <?php endif; ?>
        <div style="display:flex;align-items:center;gap:0.5rem">
          <div class="step-circle" data-step="<?php echo $num; ?>" style="width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:0.78rem;font-weight:700;border:1px solid rgba(255,255,255,0.1);color:var(--mid);transition:all 0.3s">
            <?php echo $num; ?>
          </div>
          <span class="step-label-text" data-step="<?php echo $num; ?>" style="font-size:0.8rem;color:var(--mid);transition:color 0.3s"><?php echo $lbl; ?></span>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <div style="display:grid;grid-template-columns:1fr 340px;gap:2.5rem;align-items:start">

      <!-- PASOS -->
      <div>
        <!-- PASO 1 -->
        <div id="step1" class="checkout-panel">
          <div class="glass" style="padding:2rem">
            <h3 style="margin-bottom:1.5rem;font-size:1.1rem">Revisar carrito</h3>
            <div id="checkoutCartItems">
              <div style="text-align:center;padding:2rem;color:var(--mid)">
                <i class="fa-solid fa-spinner fa-spin" style="font-size:1.5rem;display:block;margin-bottom:0.8rem"></i>
                Cargando carrito...
              </div>
            </div>
            <div style="margin-top:1.5rem;display:flex;justify-content:flex-end">
              <button class="btn btn-primary" onclick="Checkout.goStep(2)">
                Continuar <i class="fa-solid fa-arrow-right"></i>
              </button>
            </div>
          </div>
        </div>

        <!-- PASO 2 -->
        <div id="step2" class="checkout-panel" style="display:none">
          <div class="glass" style="padding:2rem">
            <h3 style="margin-bottom:1.5rem;font-size:1.1rem">Información de contacto y envío</h3>
            <form id="checkoutForm" style="display:flex;flex-direction:column;gap:1.2rem">
              <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
                <div><label>Nombre *</label><input type="text" name="first_name" class="input" required placeholder="Katherine"/></div>
                <div><label>Apellido *</label><input type="text" name="last_name" class="input" required placeholder="Rojas"/></div>
              </div>
              <div><label>Correo electrónico *</label><input type="email" name="email" class="input" required placeholder="tu@correo.cl"/></div>
              <div><label>Teléfono *</label><input type="tel" name="phone" class="input" required placeholder="+56 9 1234 5678"/></div>
              <div class="divider"></div>
              <h4 style="font-size:0.9rem;color:var(--light)">Dirección de envío</h4>
              <div><label>Dirección *</label><input type="text" name="address" class="input" required placeholder="Calle, número, depto"/></div>
              <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
                <div><label>Ciudad *</label><input type="text" name="city" class="input" required placeholder="La Serena"/></div>
                <div>
                  <label>Región *</label>
                  <select name="region" class="select" required>
                    <option value="">Seleccionar...</option>
                    <?php foreach(['Arica y Parinacota','Tarapacá','Antofagasta','Atacama','Coquimbo','Valparaíso','Metropolitana',"O'Higgins",'Maule','Ñuble','Biobío','La Araucanía','Los Ríos','Los Lagos','Aysén','Magallanes'] as $r): ?>
                    <option <?php echo $r==='Coquimbo'?'selected':''; ?>><?php echo $r; ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
              <div><label>Notas (opcional)</label><textarea name="notes" class="textarea" rows="2" placeholder="Instrucciones especiales..."></textarea></div>
            </form>
            <div style="display:flex;gap:1rem;margin-top:1.5rem;justify-content:space-between">
              <button class="btn btn-outline" onclick="Checkout.goStep(1)"><i class="fa-solid fa-arrow-left"></i> Volver</button>
              <button class="btn btn-primary" onclick="Checkout.goStep(3)">Continuar <i class="fa-solid fa-arrow-right"></i></button>
            </div>
          </div>
        </div>

        <!-- PASO 3 -->
        <div id="step3" class="checkout-panel" style="display:none">
          <div class="glass" style="padding:2rem">
            <h3 style="margin-bottom:1.5rem;font-size:1.1rem">Método de pago</h3>
            <div style="border:1px solid rgba(232,57,154,0.3);border-radius:var(--radius);padding:1.5rem;background:var(--accent-dim);margin-bottom:1.5rem">
              <div style="display:flex;align-items:center;gap:1rem">
                <div style="width:48px;height:48px;border-radius:50%;background:var(--accent);display:flex;align-items:center;justify-content:center">
                  <i class="fa-solid fa-lock" style="color:white"></i>
                </div>
                <div>
                  <div style="font-weight:600">Pago seguro con Flow</div>
                  <div style="font-size:0.8rem;color:var(--mid)">Tarjeta de crédito, débito, transferencia bancaria</div>
                </div>
              </div>
            </div>
            <div id="orderSummaryConfirm" style="margin-bottom:1.5rem"></div>
            <div style="display:flex;gap:1rem;justify-content:space-between">
              <button class="btn btn-outline" onclick="Checkout.goStep(2)"><i class="fa-solid fa-arrow-left"></i> Volver</button>
              <button class="btn btn-primary btn-lg" onclick="Checkout.processPayment()" id="btnPay" style="flex:1;justify-content:center;max-width:280px">
                <i class="fa-solid fa-lock"></i> Pagar ahora
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- RESUMEN -->
      <div style="position:sticky;top:6rem">
        <div class="glass" style="padding:1.8rem">
          <h3 style="font-size:1rem;margin-bottom:1.5rem">Resumen del pedido</h3>
          <div id="summaryItems" style="margin-bottom:1.2rem"></div>
          <div class="divider"></div>
          <div style="display:flex;flex-direction:column;gap:0.6rem;margin-top:1rem">
            <div style="display:flex;justify-content:space-between;font-size:0.85rem;color:var(--mid)"><span>Subtotal</span><span id="summarySubtotal">—</span></div>
            <div style="display:flex;justify-content:space-between;font-size:0.85rem;color:var(--mid)"><span>Envío</span><span id="summaryShipping">—</span></div>
            <div class="divider" style="margin:0.4rem 0"></div>
            <div style="display:flex;justify-content:space-between;font-size:1rem;font-weight:700">
              <span>Total</span>
              <span style="font-family:'Space Mono',monospace;color:var(--accent)" id="summaryTotal">—</span>
            </div>
          </div>
          <div style="margin-top:1.5rem;padding:0.8rem;background:rgba(34,197,94,0.05);border:1px solid rgba(34,197,94,0.1);border-radius:var(--radius-sm);display:flex;align-items:center;gap:0.6rem">
            <i class="fa-solid fa-shield-halved" style="color:#22c55e"></i>
            <span style="font-size:0.75rem;color:var(--mid)">Compra 100% segura y protegida</span>
          </div>
        </div>
      </div>

    </div>
  </div>
</main>

<?php
$extraJs = '<script src="' . APP_URL . '/js/checkout.js"></script>';
include 'footer.php';
?>
