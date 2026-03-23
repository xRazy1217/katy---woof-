<?php
/**
 * Public products endpoint for e-commerce frontend.
 * Uses the same ProductAPI used by admin to keep storefront data in sync.
 */

require_once __DIR__ . '/../api-products.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-Token');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Metodo no permitido',
        'allowed' => ['GET']
    ]);
    exit;
}

try {
    $productAPI = getProductAPI();
    $action = $_GET['action'] ?? null;

    if ($action === 'categories') {
        $parent = $_GET['parent'] ?? null;
        $result = $productAPI->getCategories($parent);
        echo json_encode($result);
        exit;
    }

    $id = $_GET['id'] ?? null;
    if ($id !== null && $id !== '') {
        $result = $productAPI->getProduct((int)$id);
        echo json_encode($result);
        exit;
    }

    $filters = [
        'page' => isset($_GET['page']) ? (int)$_GET['page'] : 1,
        'per_page' => isset($_GET['per_page']) ? (int)$_GET['per_page'] : 12,
        'category' => $_GET['category'] ?? null,
        'search' => $_GET['search'] ?? null,
        'min_price' => isset($_GET['min_price']) ? (float)$_GET['min_price'] : null,
        'max_price' => isset($_GET['max_price']) ? (float)$_GET['max_price'] : null,
        'orderby' => $_GET['orderby'] ?? null,
        'featured' => isset($_GET['featured']) ? filter_var($_GET['featured'], FILTER_VALIDATE_BOOLEAN) : null,
        'in_stock' => isset($_GET['in_stock']) ? filter_var($_GET['in_stock'], FILTER_VALIDATE_BOOLEAN) : null,
        'status' => 'publish'
    ];

    $result = $productAPI->getProducts($filters);
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno al consultar productos',
        'error' => $e->getMessage()
    ]);
}
