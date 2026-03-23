<?php
require_once 'config.php';
$base = APP_URL;
$pageTitle = 'Orden Confirmada';
include 'header.php';
?>
<main style="padding-top:5rem;min-height:100vh;display:flex;align-items:center">
  <div class="container" style="text-align:center;padding:5rem 0">
    <div style="width:80px;height:80px;border-radius:50%;background:rgba(34,197,94,0.1);border:1px solid rgba(34,197,94,0.3);display:flex;align-items:center;justify-content:center;margin:0 auto 2rem;font-size:2rem">✓</div>
    <span class="label">Pago procesado</span>
    <h1 style="margin:1rem 0 1.5rem;font-size:clamp(2rem,4vw,3rem)">¡Gracias por tu <span class="accent">compra</span>!</h1>
    <p style="color:var(--mid);max-width:480px;margin:0 auto 2.5rem;line-height:1.8">
      Hemos recibido tu pedido. Te enviaremos un correo de confirmación con los detalles. Si tienes dudas, escríbenos por WhatsApp.
    </p>
    <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap">
      <a href="https://wa.me/56976886481" target="_blank" class="btn btn-primary btn-lg">
        <i class="fa-brands fa-whatsapp"></i> Contactar por WhatsApp
      </a>
      <a href="<?php echo $base; ?>/catalogo.php" class="btn btn-outline btn-lg">Seguir comprando</a>
    </div>
  </div>
</main>
<?php include 'footer.php'; ?>
