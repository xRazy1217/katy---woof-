<?php
class CheckoutAPI {

    public static function create(array $body): array {
        try {
            $pdo      = getDBConnection();
            $sid      = $_SERVER['HTTP_X_SESSION_ID'] ?? '';
            $customer = $body['customer'] ?? [];

            // Validar campos requeridos
            $required = ['first_name','last_name','email','phone','address','city','region'];
            foreach ($required as $f) {
                if (empty($customer[$f])) return ['success' => false, 'error' => "Campo requerido: $f"];
            }

            // Obtener items del carrito
            $stmt = $pdo->prepare("
                SELECT ci.*, p.name, p.image_url
                FROM cart_items ci JOIN products p ON ci.product_id=p.id
                WHERE ci.session_id=?
            ");
            $stmt->execute([$sid]);
            $items = $stmt->fetchAll();
            if (!$items) return ['success' => false, 'error' => 'Carrito vacío'];

            // Calcular totales
            $subtotal = array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $items));
            $shipping = $subtotal >= 50000 ? 0 : 5000;
            $total    = $subtotal + $shipping;

            // Crear orden
            $orderNumber = 'KW-' . strtoupper(substr(uniqid(), -6));
            $pdo->prepare("
                INSERT INTO orders (order_number, customer_email, customer_data, status, subtotal, shipping_total, total, payment_method, payment_status)
                VALUES (?,?,?,?,?,?,?,?,?)
            ")->execute([
                $orderNumber,
                $customer['email'],
                json_encode($customer),
                'pending',
                $subtotal,
                $shipping,
                $total,
                'flow',
                'pending'
            ]);
            $orderId = $pdo->lastInsertId();

            // Insertar items de la orden
            $stmtItem = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, quantity, price, line_total) VALUES (?,?,?,?,?,?)");
            foreach ($items as $item) {
                $stmtItem->execute([$orderId, $item['product_id'], $item['name'], $item['quantity'], $item['price'], $item['price'] * $item['quantity']]);
            }

            // Crear pago en Flow
            $flowResult = self::createFlowPayment($orderNumber, $total, $customer['email']);
            if (!$flowResult['success']) {
                return ['success' => false, 'error' => 'Error al conectar con Flow: ' . ($flowResult['error'] ?? '')];
            }

            // Guardar flow_order_id
            $pdo->prepare("UPDATE orders SET flow_order_id=? WHERE id=?")->execute([$flowResult['flow_order'] ?? '', $orderId]);

            // Vaciar carrito
            $pdo->prepare("DELETE FROM cart_items WHERE session_id=?")->execute([$sid]);

            return ['success' => true, 'order_number' => $orderNumber, 'flow_url' => $flowResult['url']];

        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private static function createFlowPayment(string $orderNumber, float $amount, string $email): array {
        $apiKey    = $_ENV['FLOW_API_KEY']    ?? getenv('FLOW_API_KEY')    ?? '';
        $secretKey = $_ENV['FLOW_SECRET_KEY'] ?? getenv('FLOW_SECRET_KEY') ?? '';
        $sandbox   = ($_ENV['FLOW_SANDBOX']   ?? getenv('FLOW_SANDBOX')   ?? 'true') === 'true';

        if (!$apiKey || !$secretKey) {
            return ['success' => false, 'error' => 'Flow no configurado'];
        }

        $baseUrl = $sandbox
            ? 'https://sandbox.flow.cl/api'
            : 'https://www.flow.cl/api';

        $siteBase = defined('APP_URL') ? APP_URL : ((isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));

        $params = [
            'apiKey'          => $apiKey,
            'commerceOrder'   => $orderNumber,
            'subject'         => 'Compra Katy & Woof - ' . $orderNumber,
            'currency'        => 'CLP',
            'amount'          => intval($amount),
            'email'           => $email,
            'urlConfirmation' => $siteBase . '/api.php?action=checkout_confirm',
            'urlReturn'       => $siteBase . '/orden-confirmada.php',
        ];

        ksort($params);
        $toSign = '';
        foreach ($params as $k => $v) $toSign .= $k . $v;
        $params['s'] = hash_hmac('sha256', $toSign, $secretKey);

        $ch = curl_init($baseUrl . '/payment/create');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($params),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => !$sandbox,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (!$response) return ['success' => false, 'error' => 'Sin respuesta de Flow'];

        $data = json_decode($response, true);
        if ($httpCode !== 200 || empty($data['url']) || empty($data['token'])) {
            return ['success' => false, 'error' => $data['message'] ?? 'Error Flow'];
        }

        return [
            'success'     => true,
            'url'         => $data['url'] . '?token=' . $data['token'],
            'flow_order'  => $data['flowOrder'] ?? '',
        ];
    }

    public static function confirm(array $params): array {
        try {
            $token = $params['token'] ?? '';
            if (!$token) return ['success' => false, 'error' => 'Token inválido'];

            $apiKey    = $_ENV['FLOW_API_KEY']    ?? getenv('FLOW_API_KEY')    ?? '';
            $secretKey = $_ENV['FLOW_SECRET_KEY'] ?? getenv('FLOW_SECRET_KEY') ?? '';
            $sandbox   = ($_ENV['FLOW_SANDBOX']   ?? getenv('FLOW_SANDBOX')   ?? 'true') === 'true';
            $baseUrl   = $sandbox ? 'https://sandbox.flow.cl/api' : 'https://www.flow.cl/api';

            $p = ['apiKey' => $apiKey, 'token' => $token];
            ksort($p);
            $toSign = '';
            foreach ($p as $k => $v) $toSign .= $k . $v;
            $p['s'] = hash_hmac('sha256', $toSign, $secretKey);

            $ch = curl_init($baseUrl . '/payment/getStatus?' . http_build_query($p));
            curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 15, CURLOPT_SSL_VERIFYPEER => !$sandbox]);
            $response = curl_exec($ch);
            curl_close($ch);

            $data = json_decode($response, true);
            if (empty($data['commerceOrder'])) return ['success' => false, 'error' => 'Respuesta inválida de Flow'];

            $pdo    = getDBConnection();
            $status = intval($data['status'] ?? 0);
            $paymentStatus = $status === 2 ? 'paid' : ($status === 3 ? 'cancelled' : 'pending');
            $orderStatus   = $status === 2 ? 'processing' : ($status === 3 ? 'cancelled' : 'pending');

            $pdo->prepare("UPDATE orders SET payment_status=?, status=?, transaction_id=? WHERE order_number=?")
                ->execute([$paymentStatus, $orderStatus, $data['flowOrder'] ?? '', $data['commerceOrder']]);

            return ['success' => true, 'payment_status' => $paymentStatus];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
