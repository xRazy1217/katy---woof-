<?php
/**
 * Webhook Flow Payment - E-commerce Katy & Woof
 * Fase 1: Procesamiento de notificaciones de pago
 */

require_once 'flow-payment.php';

// Configurar logging para webhooks
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/flow-webhook.log');

// Solo aceptar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Obtener datos del webhook
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Datos JSON inválidos']);
    exit;
}

// Log del webhook recibido
ecommerceLog('Webhook Flow recibido', 'INFO', [
    'data' => $input,
    'headers' => getallheaders(),
    'ip' => $_SERVER['REMOTE_ADDR'] ?? null
]);

try {
    $flow = getFlowPayment();
    $result = $flow->processWebhook($input);

    if ($result['success']) {
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Webhook procesado correctamente']);
    } else {
        http_response_code(400);
        echo json_encode($result);
    }

} catch (Exception $e) {
    ecommerceLog('Error procesando webhook: ' . $e->getMessage(), 'ERROR', [
        'webhook_data' => $input,
        'exception' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor',
        'error' => getenv('APP_ENV') === 'development' ? $e->getMessage() : null
    ]);
}

?>