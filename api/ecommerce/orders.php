<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../ecommerce-config.php';

// Get database connection
$conn = EcommerceDatabase::getInstance()->getConnection();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Get the request method and path
$method = $_SERVER['REQUEST_METHOD'];
$request = $_SERVER['REQUEST_URI'];

// Remove query string and decode
$path = parse_url($request, PHP_URL_PATH);
$path = str_replace('/api/ecommerce/orders', '', $path);
$path = str_replace('.php', '', $path);
$path = trim($path, '/');

// Check for parameters in query string
if (empty($path) && isset($_GET['id'])) {
    $path = $_GET['id'];
    if (isset($_GET['action']) && $_GET['action'] === 'status') {
        $path .= '/status';
    }
} elseif (empty($path) && isset($_GET['action'])) {
    $path = $_GET['action'];
}

// Handle different endpoints
if ($method === 'GET') {
    if (empty($path)) {
        // GET /api/ecommerce/orders - List all orders
        getOrders();
    } elseif (preg_match('/^(\d+)$/', $path, $matches)) {
        // GET /api/ecommerce/orders/{id} - Get single order
        getOrder($matches[1]);
    } elseif (preg_match('/^stats$/', $path)) {
        // GET /api/ecommerce/orders/stats - Get order statistics
        getOrderStats();
    }
} elseif ($method === 'PUT' && preg_match('/^(\d+)\/status$/', $path, $matches)) {
    // PUT /api/ecommerce/orders/{id}/status - Update order status
    updateOrderStatus($matches[1]);
} else {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Endpoint not found']);
}

function getOrders() {
    global $conn;

    try {
        $query = "SELECT
            o.*,
            CONCAT(
                JSON_UNQUOTE(JSON_EXTRACT(o.shipping_address, '$.first_name')),
                ' ',
                JSON_UNQUOTE(JSON_EXTRACT(o.shipping_address, '$.last_name'))
            ) as customer_name,
            JSON_UNQUOTE(JSON_EXTRACT(o.shipping_address, '$.email')) as customer_email,
            JSON_UNQUOTE(JSON_EXTRACT(o.shipping_address, '$.phone')) as customer_phone
            FROM orders o
            ORDER BY o.created_at DESC";

        $stmt = $conn->prepare($query);
        $stmt->execute();
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Add order items for each order
        foreach ($orders as &$order) {
            $order['items'] = getOrderItems($order['id']);
        }

        echo json_encode($orders);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error fetching orders: ' . $e->getMessage()]);
    }
}

function getOrder($id) {
    global $conn;

    try {
        $query = "SELECT
            o.*,
            CONCAT(
                JSON_UNQUOTE(JSON_EXTRACT(o.shipping_address, '$.first_name')),
                ' ',
                JSON_UNQUOTE(JSON_EXTRACT(o.shipping_address, '$.last_name'))
            ) as customer_name,
            JSON_UNQUOTE(JSON_EXTRACT(o.shipping_address, '$.email')) as customer_email,
            JSON_UNQUOTE(JSON_EXTRACT(o.shipping_address, '$.phone')) as customer_phone
            FROM orders o
            WHERE o.id = ?";

        $stmt = $conn->prepare($query);
        $stmt->execute([$id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Order not found']);
            return;
        }

        // Add order items
        $order['items'] = getOrderItems($order['id']);

        echo json_encode($order);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error fetching order: ' . $e->getMessage()]);
    }
}

function getOrderStats() {
    global $conn;

    try {
        $stats = [];

        // Total orders
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM orders");
        $stmt->execute();
        $stats['total'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Completed orders
        $stmt = $conn->prepare("SELECT COUNT(*) as completed FROM orders WHERE status = 'completed'");
        $stmt->execute();
        $stats['completed'] = $stmt->fetch(PDO::FETCH_ASSOC)['completed'];

        // Pending orders
        $stmt = $conn->prepare("SELECT COUNT(*) as pending FROM orders WHERE status = 'pending'");
        $stmt->execute();
        $stats['pending'] = $stmt->fetch(PDO::FETCH_ASSOC)['pending'];

        // Cancelled orders
        $stmt = $conn->prepare("SELECT COUNT(*) as cancelled FROM orders WHERE status = 'cancelled'");
        $stmt->execute();
        $stats['cancelled'] = $stmt->fetch(PDO::FETCH_ASSOC)['cancelled'];

        echo json_encode($stats);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error fetching order stats: ' . $e->getMessage()]);
    }
}

function getOrderItems($orderId) {
    global $conn;

    try {
        $query = "SELECT oi.*, p.name, p.image_url, p.sku
                  FROM order_items oi
                  LEFT JOIN products p ON oi.product_id = p.id
                  WHERE oi.order_id = ?
                  ORDER BY oi.id ASC";

        $stmt = $conn->prepare($query);
        $stmt->execute([$orderId]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Restructure to have product object
        $formattedItems = [];
        foreach ($items as $item) {
            $formattedItems[] = [
                'id' => $item['id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'line_total' => $item['line_total'],
                'product' => [
                    'id' => $item['product_id'],
                    'name' => $item['name'],
                    'sku' => $item['sku'],
                    'image_url' => $item['image_url']
                ]
            ];
        }
        
        return $formattedItems;
    } catch (Exception $e) {
        return [];
    }
}

function updateOrderStatus($id) {
    global $conn;

    try {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['status'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Status is required']);
            return;
        }

        $validStatuses = ['pending', 'processing', 'completed', 'cancelled'];
        if (!in_array($data['status'], $validStatuses)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid status']);
            return;
        }

        // Check if order exists
        $stmt = $conn->prepare("SELECT id FROM orders WHERE id = ?");
        $stmt->execute([$id]);
        if (!$stmt->fetch()) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Order not found']);
            return;
        }

        $stmt = $conn->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$data['status'], $id]);

        echo json_encode([
            'success' => true,
            'message' => 'Order status updated successfully'
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error updating order status: ' . $e->getMessage()]);
    }
}
?>