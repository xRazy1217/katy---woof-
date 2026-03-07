<?php
require_once '../config.php';
require_once '../ecommerce-config.php';

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
$path = str_replace('/api/ecommerce/coupons', '', $path);
$path = trim($path, '/');

// Handle different endpoints
if ($method === 'GET') {
    if (empty($path)) {
        // GET /api/ecommerce/coupons - List all coupons
        getCoupons();
    } elseif (is_numeric($path)) {
        // GET /api/ecommerce/coupons/{id} - Get single coupon
        getCoupon($path);
    }
} elseif ($method === 'POST' && empty($path)) {
    // POST /api/ecommerce/coupons - Create coupon
    createCoupon();
} elseif ($method === 'PUT' && is_numeric($path)) {
    // PUT /api/ecommerce/coupons/{id} - Update coupon
    updateCoupon($path);
} elseif ($method === 'DELETE' && is_numeric($path)) {
    // DELETE /api/ecommerce/coupons/{id} - Delete coupon
    deleteCoupon($path);
} else {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Endpoint not found']);
}

function getCoupons() {
    global $conn;

    try {
        $query = "SELECT * FROM coupons ORDER BY created_at DESC";

        $stmt = $conn->prepare($query);
        $stmt->execute();
        $coupons = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($coupons);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error fetching coupons: ' . $e->getMessage()]);
    }
}

function getCoupon($id) {
    global $conn;

    try {
        $query = "SELECT * FROM coupons WHERE id = ?";

        $stmt = $conn->prepare($query);
        $stmt->execute([$id]);
        $coupon = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$coupon) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Coupon not found']);
            return;
        }

        echo json_encode($coupon);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error fetching coupon: ' . $e->getMessage()]);
    }
}

function createCoupon() {
    global $conn;

    try {
        $data = json_decode(file_get_contents('php://input'), true);

        // Validate required fields
        if (empty($data['code']) || !isset($data['discount_value'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Code and discount value are required']);
            return;
        }

        // Check if code already exists
        $stmt = $conn->prepare("SELECT id FROM coupons WHERE code = ?");
        $stmt->execute([$data['code']]);
        if ($stmt->fetch()) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Coupon code already exists']);
            return;
        }

        $query = "INSERT INTO coupons (
            code, description, discount_type, discount_value, usage_limit,
            usage_count, expiry_date, minimum_amount, status, created_at
        ) VALUES (?, ?, ?, ?, ?, 0, ?, ?, 'active', NOW())";

        $stmt = $conn->prepare($query);
        $stmt->execute([
            $data['code'],
            $data['description'] ?? null,
            $data['discount_type'] ?? 'fixed',
            $data['discount_value'],
            $data['usage_limit'] ?? null,
            $data['expiry_date'] ?? null,
            $data['minimum_amount'] ?? null
        ]);

        $couponId = $conn->lastInsertId();

        echo json_encode([
            'success' => true,
            'message' => 'Coupon created successfully',
            'coupon_id' => $couponId
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error creating coupon: ' . $e->getMessage()]);
    }
}

function updateCoupon($id) {
    global $conn;

    try {
        $data = json_decode(file_get_contents('php://input'), true);

        // Check if coupon exists
        $stmt = $conn->prepare("SELECT id FROM coupons WHERE id = ?");
        $stmt->execute([$id]);
        if (!$stmt->fetch()) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Coupon not found']);
            return;
        }

        // Check if code already exists (excluding current coupon)
        if (isset($data['code'])) {
            $stmt = $conn->prepare("SELECT id FROM coupons WHERE code = ? AND id != ?");
            $stmt->execute([$data['code'], $id]);
            if ($stmt->fetch()) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Coupon code already exists']);
                return;
            }
        }

        $query = "UPDATE coupons SET
            code = ?, description = ?, discount_type = ?, discount_value = ?,
            usage_limit = ?, expiry_date = ?, minimum_amount = ?, updated_at = NOW()
            WHERE id = ?";

        $stmt = $conn->prepare($query);
        $stmt->execute([
            $data['code'],
            $data['description'] ?? null,
            $data['discount_type'] ?? 'fixed',
            $data['discount_value'],
            $data['usage_limit'] ?? null,
            $data['expiry_date'] ?? null,
            $data['minimum_amount'] ?? null,
            $id
        ]);

        echo json_encode([
            'success' => true,
            'message' => 'Coupon updated successfully'
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error updating coupon: ' . $e->getMessage()]);
    }
}

function deleteCoupon($id) {
    global $conn;

    try {
        // Check if coupon exists
        $stmt = $conn->prepare("SELECT id FROM coupons WHERE id = ?");
        $stmt->execute([$id]);
        if (!$stmt->fetch()) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Coupon not found']);
            return;
        }

        $stmt = $conn->prepare("DELETE FROM coupons WHERE id = ?");
        $stmt->execute([$id]);

        echo json_encode([
            'success' => true,
            'message' => 'Coupon deleted successfully'
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error deleting coupon: ' . $e->getMessage()]);
    }
}
?>