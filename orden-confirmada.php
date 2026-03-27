<?php
require_once 'config.php';
$base = APP_URL;

// Verificar estado real del pago
$token       = $_GET['token'] ?? '';
$orderNumber = $_GET['commerceOrder'] ?? '';
$success     = false;
$cancelled   = false;
$orderData   = null;

if ($token) {
    // Consultar estado a Flow
    $apiKey    = defined('FLOW_API_KEY')    ? FLOW_API_KEY    : '';
    $secretKey = defined('FLOW_SECRET_KEY') ? FLOW_SECRET_KEY : '';
    $sandbox   = defined('FLOW_SANDBOX')    ? FLOW_SANDBOX    : true;
    $baseUrl   = $sandbox ? 'https://sandbox.flow.cl/api' : 'https://www.flow.cl/api';

    $params = ['apiKey' => $apiKey, 'token' => $token];
    ksort($params);
    $toSign = '';
    foreach ($params as $k => $v) $toSign .= $k . $v;
    $params['s'] = hash_hmac('sha256', $toSign, $secretKey);

    $ch = curl_init($baseUrl . '/payment/getStatus?' . http_build_query($params));
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 10, CURLOPT_SSL_VERIFYPEER => !$sandbox]);
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response) {
        $data = json_decode($response, true);
        $flowStatus = intval($data['status'] ?? 0);
        // Flow: 1=pendiente, 2=pagado, 3=rechazado, 4=anulado
        if ($flowStatus === 2) {
            $success = true;
        } elseif (in_array($flowStatus, [3, 4])) {
            $cancelled = true;
        }
        $orderNumber = $data['commerceOrder'] ?? $orderNumber;

        // Actualizar orden en BD
        if ($orderNumber) {
            try {
                $pdo = getDBConnection();
                if ($flowStatus === 2) {
                    $pdo->prepare("UPDATE orders SET status='processing', payment_status='paid', transaction_id=? WHERE order_number=?")
                        ->execute([$data['flowOrder'] ?? '', $orderNumber]);
                } elseif (in_array($flowStatus, [3, 4])) {
                    $pdo->prepare("UPDATE orders SET status='cancelled', payment_status='failed' WHERE order_number=?")
                        ->execute([$orderNumber]);
                }
                $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_number=?");
                $stmt->execute([$orderNumber]);
                $orderData = $stmt->fetch();
            } catch (Exception $e) {}
        }
    }
} elseif ($orderNumber) {
    // Sin token — puede ser redirección directa sin pago
    $cancelled = true;
    try {
        $pdo = getDBConnection();
        $pdo->prepare("UPDATE orders SET status='cancelled', payment_status='failed' WHERE order_number=? AND payment_status='pending'")
            ->execute([$orderNumber]);
    } catch (Exception $e) {}
}

$pageTitle = $success ? 'Orden Confirmada' : 'Pago no completado';
include 'header.php';
?>

<main style="padding-top:5rem;min-height:100vh;display:flex;align-items:center">
  <div class="container" style="text-align:center;padding:5rem 0;max-width:600px">

    <?php if ($success): ?>
      <div style="width:80px;height:80px;border-radius:50%;background:rgba(34,197,94,0.1);border:1px solid rgba(34,197,94,0.3);display:flex;align-items:center;justify-content:center;margin:0 auto 2rem;font-size:2rem">✓</div>
      <span class="label">Pago procesado</span>
      <h1 style="margin:1rem 0 1.5rem;font-size:clamp(2rem,4vw,3rem)">¡Gracias por tu <span class="accent">compra</span>!</h1>
      <p style="color:var(--mid);max-width:480px;margin:0 auto 1rem;line-height:1.8">
        Hemos recibido tu pedido correctamente.
        <?php if ($orderNumber): ?>
          Tu número de orden es <strong style="color:var(--white)"><?php echo htmlspecialchars($orderNumber); ?></strong>.
        <?php endif; ?>
      </p>
      <p style="color:var(--mid);max-width:480px;margin:0 auto 2.5rem;line-height:1.8">
        Te contactaremos pronto. Si tienes dudas, escríbenos por WhatsApp.
      </p>
      <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap">
        <a href="https://wa.me/56976886481" target="_blank" class="btn btn-primary btn-lg">
          <i class="fa-brands fa-whatsapp"></i> Contactar por WhatsApp
        </a>
        <a href="<?php echo $base; ?>/catalogo.php" class="btn btn-outline btn-lg">Seguir comprando</a>
      </div>

    <?php else: ?>
      <div style="width:80px;height:80px;border-radius:50%;background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);display:flex;align-items:center;justify-content:center;margin:0 auto 2rem;font-size:2rem">✕</div>
      <span class="label" style="color:#ef4444">Pago no completado</span>
      <h1 style="margin:1rem 0 1.5rem;font-size:clamp(2rem,4vw,3rem)">Tu pago fue <span style="color:#ef4444">cancelado</span></h1>
      <p style="color:var(--mid);max-width:480px;margin:0 auto 2.5rem;line-height:1.8">
        No se realizó ningún cargo. Puedes volver al catálogo e intentarlo nuevamente cuando quieras.
      </p>
      <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap">
        <a href="<?php echo $base; ?>/catalogo.php" class="btn btn-primary btn-lg">
          <i class="fa-solid fa-arrow-left"></i> Volver al catálogo
        </a>
        <a href="https://wa.me/56976886481" target="_blank" class="btn btn-outline btn-lg">
          <i class="fa-brands fa-whatsapp"></i> Contactar soporte
        </a>
      </div>
    <?php endif; ?>

  </div>
</main>

<?php include 'footer.php'; ?>
