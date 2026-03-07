<?php
/**
 * Integración Flow Payment - E-commerce Katy & Woof
 * Fase 1: Configuración y funciones básicas de pago
 */

require_once 'ecommerce-config.php';

class FlowPayment {
    private $apiKey;
    private $secretKey;
    private $apiUrl;
    private $isSandbox;

    public function __construct() {
        // Configuración de Flow - CAMBIAR en producción
        $this->apiKey = getenv('FLOW_API_KEY') ?: 'FLOW_API_KEY_AQUI'; // Reemplazar con tu API Key
        $this->secretKey = getenv('FLOW_SECRET_KEY') ?: 'FLOW_SECRET_KEY_AQUI'; // Reemplazar con tu Secret Key
        $this->isSandbox = getenv('FLOW_SANDBOX') ?: true;

        // URLs de Flow
        if ($this->isSandbox) {
            $this->apiUrl = 'https://sandbox.flow.cl/api';
        } else {
            $this->apiUrl = 'https://www.flow.cl/api';
        }
    }

    /**
     * Crear un pago con Flow
     */
    public function createPayment($orderData) {
        try {
            // Validar datos requeridos
            $this->validatePaymentData($orderData);

            // Preparar datos para Flow
            $paymentData = [
                'apiKey' => $this->apiKey,
                'commerceOrder' => $orderData['order_number'],
                'subject' => 'Compra en Katy & Woof Creative Studio',
                'currency' => 'CLP',
                'amount' => $orderData['total'],
                'email' => $orderData['customer_email'],
                'paymentMethod' => $orderData['payment_method'] ?? 9, // 9 = Todos los medios
                'urlConfirmation' => $this->getBaseUrl() . '/ecommerce-webhook.php',
                'urlReturn' => $this->getBaseUrl() . '/checkout/success?order=' . $orderData['order_number'],
                'optional' => json_encode([
                    'order_id' => $orderData['order_id'],
                    'customer_data' => $orderData['customer_data']
                ])
            ];

            // Crear firma
            $paymentData['s'] = $this->createSignature($paymentData);

            // Hacer petición a Flow
            $response = $this->makeRequest('/payment/create', $paymentData);

            if ($response && isset($response['url']) && isset($response['token'])) {
                return [
                    'success' => true,
                    'payment_url' => $response['url'],
                    'flow_token' => $response['token'],
                    'flow_order' => $response['flowOrder'] ?? $orderData['order_number']
                ];
            } else {
                throw new Exception('Respuesta inválida de Flow');
            }

        } catch (Exception $e) {
            ecommerceLog('Flow Payment Error: ' . $e->getMessage(), 'ERROR', [
                'order_number' => $orderData['order_number'] ?? null
            ]);

            return [
                'success' => false,
                'message' => 'Error al crear pago con Flow',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtener estado de un pago
     */
    public function getPaymentStatus($flowOrder) {
        try {
            $data = [
                'apiKey' => $this->apiKey,
                'flowOrder' => $flowOrder
            ];

            $data['s'] = $this->createSignature($data);

            $response = $this->makeRequest('/payment/getStatus', $data);

            if ($response) {
                return [
                    'success' => true,
                    'status' => $response['status'] ?? null,
                    'data' => $response
                ];
            } else {
                throw new Exception('No se pudo obtener el estado del pago');
            }

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener estado del pago',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Procesar webhook de Flow
     */
    public function processWebhook($webhookData) {
        try {
            // Verificar firma del webhook
            if (!$this->verifyWebhookSignature($webhookData)) {
                throw new Exception('Firma del webhook inválida');
            }

            $flowOrder = $webhookData['commerceOrder'] ?? null;
            $status = $webhookData['status'] ?? null;

            if (!$flowOrder) {
                throw new Exception('Orden de comercio no encontrada en webhook');
            }

            // Buscar orden en la base de datos
            $db = getEcommerceDB();
            $order = $db->selectOne(
                "SELECT * FROM orders WHERE order_number = ?",
                [$flowOrder]
            );

            if (!$order) {
                throw new Exception('Orden no encontrada: ' . $flowOrder);
            }

            // Mapear status de Flow a nuestro status
            $newStatus = $this->mapFlowStatus($status);

            if ($newStatus && $newStatus !== $order['payment_status']) {
                // Actualizar status del pago
                $db->update(
                    'orders',
                    [
                        'payment_status' => $newStatus,
                        'transaction_id' => $webhookData['paymentData']['payment_id'] ?? null,
                        'flow_order_id' => $webhookData['flowOrder'] ?? null,
                        'updated_at' => date('Y-m-d H:i:s')
                    ],
                    'id = ?',
                    [$order['id']]
                );

                // Si el pago fue exitoso, cambiar status de la orden
                if ($newStatus === 'paid') {
                    $db->update(
                        'orders',
                        ['status' => 'processing'],
                        'id = ?',
                        [$order['id']]
                    );

                    // Registrar en historial
                    $db->insert('order_status_history', [
                        'order_id' => $order['id'],
                        'old_status' => $order['status'],
                        'new_status' => 'processing',
                        'notes' => 'Pago confirmado por Flow'
                    ]);
                }

                ecommerceLog('Webhook procesado exitosamente', 'INFO', [
                    'order_id' => $order['id'],
                    'flow_order' => $flowOrder,
                    'status' => $newStatus
                ]);
            }

            return [
                'success' => true,
                'message' => 'Webhook procesado correctamente'
            ];

        } catch (Exception $e) {
            ecommerceLog('Webhook Error: ' . $e->getMessage(), 'ERROR', $webhookData);

            return [
                'success' => false,
                'message' => 'Error al procesar webhook',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Crear firma para requests a Flow
     */
    private function createSignature($data) {
        // Ordenar parámetros alfabéticamente
        ksort($data);

        // Crear string para firmar
        $signatureString = '';
        foreach ($data as $key => $value) {
            if ($key !== 's') { // Excluir la firma misma
                $signatureString .= $key . $value;
            }
        }

        // Agregar secret key
        $signatureString .= $this->secretKey;

        // Crear firma SHA256
        return hash('sha256', $signatureString);
    }

    /**
     * Verificar firma del webhook
     */
    private function verifyWebhookSignature($data) {
        if (!isset($data['s'])) {
            return false;
        }

        $receivedSignature = $data['s'];
        unset($data['s']); // Remover firma para verificar

        $calculatedSignature = $this->createSignature($data);

        return hash_equals($calculatedSignature, $receivedSignature);
    }

    /**
     * Mapear status de Flow a nuestros status
     */
    private function mapFlowStatus($flowStatus) {
        $statusMap = [
            1 => 'pending',      // Pendiente
            2 => 'paid',         // Pagado
            3 => 'failed',       // Rechazado
            4 => 'cancelled',    // Cancelado
            5 => 'refunded'      // Reembolsado
        ];

        return $statusMap[$flowStatus] ?? null;
    }

    /**
     * Hacer petición HTTP a la API de Flow
     */
    private function makeRequest($endpoint, $data) {
        $url = $this->apiUrl . $endpoint;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, !$this->isSandbox); // Solo verificar SSL en producción
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        if ($error) {
            throw new Exception('Error de conexión con Flow: ' . $error);
        }

        if ($httpCode !== 200) {
            throw new Exception('Error HTTP ' . $httpCode . ' de Flow API');
        }

        $result = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Respuesta JSON inválida de Flow');
        }

        return $result;
    }

    /**
     * Validar datos requeridos para crear pago
     */
    private function validatePaymentData($data) {
        $required = ['order_number', 'total', 'customer_email'];

        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new Exception("Campo requerido faltante: {$field}");
            }
        }

        if (!is_numeric($data['total']) || $data['total'] <= 0) {
            throw new Exception('El total debe ser un número positivo');
        }

        if (!validateEmail($data['customer_email'])) {
            throw new Exception('Email inválido');
        }
    }

    /**
     * Obtener URL base del sitio
     */
    private function getBaseUrl() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        return $protocol . '://' . $host;
    }

    /**
     * Verificar configuración de Flow
     */
    public function verifyConfiguration() {
        $issues = [];

        if ($this->apiKey === 'FLOW_API_KEY_AQUI') {
            $issues[] = 'API Key de Flow no configurada';
        }

        if ($this->secretKey === 'FLOW_SECRET_KEY_AQUI') {
            $issues[] = 'Secret Key de Flow no configurada';
        }

        if ($this->isSandbox) {
            $issues[] = 'Flow está en modo sandbox (cambiar para producción)';
        }

        return [
            'valid' => empty($issues),
            'issues' => $issues,
            'config' => [
                'api_key_configured' => $this->apiKey !== 'FLOW_API_KEY_AQUI',
                'secret_key_configured' => $this->secretKey !== 'FLOW_SECRET_KEY_AQUI',
                'sandbox_mode' => $this->isSandbox,
                'api_url' => $this->apiUrl
            ]
        ];
    }
}

// Función helper para obtener instancia de Flow
function getFlowPayment() {
    static $instance = null;
    if ($instance === null) {
        $instance = new FlowPayment();
    }
    return $instance;
}

?>