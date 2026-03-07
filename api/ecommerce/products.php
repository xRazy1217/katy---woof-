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
$path = str_replace('/api/ecommerce/products', '', $path);
$path = trim($path, '/');

// Handle different endpoints
if ($method === 'GET') {
    if (empty($path)) {
        // GET /api/ecommerce/products - List all products
        getProducts();
    } elseif (is_numeric($path)) {
        // GET /api/ecommerce/products/{id} - Get single product
        getProduct($path);
    }
} elseif ($method === 'POST' && empty($path)) {
    // POST /api/ecommerce/products - Create product
    createProduct();
} elseif ($method === 'PUT' && is_numeric($path)) {
    // PUT /api/ecommerce/products/{id} - Update product
    updateProduct($path);
} elseif ($method === 'DELETE' && is_numeric($path)) {
    // DELETE /api/ecommerce/products/{id} - Delete product
    deleteProduct($path);
} else {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Endpoint not found']);
}

function getProducts() {
    global $conn;

    try {
        $query = "SELECT p.*, c.name as category_name
                  FROM products p
                  LEFT JOIN product_categories c ON p.category_id = c.id
                  WHERE p.status = 'publish'
                  ORDER BY p.created_at DESC";

        $stmt = $conn->prepare($query);
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Add image URLs
        foreach ($products as &$product) {
            $product['image_url'] = getProductImageUrl($product['id']);
        }

        echo json_encode($products);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error fetching products: ' . $e->getMessage()]);
    }
}

function getProduct($id) {
    global $conn;

    try {
        $query = "SELECT p.*, c.name as category_name
                  FROM products p
                  LEFT JOIN product_categories c ON p.category_id = c.id
                  WHERE p.id = ? AND p.status = 'publish'";

        $stmt = $conn->prepare($query);
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            return;
        }

        // Add image URL
        $product['image_url'] = getProductImageUrl($product['id']);

        echo json_encode($product);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error fetching product: ' . $e->getMessage()]);
    }
}

function createProduct() {
    global $conn;

    try {
        $data = $_POST;

        // Validate required fields
        if (empty($data['name']) || !isset($data['price'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Name and price are required']);
            return;
        }

        // Handle image upload
        $imagePath = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imagePath = uploadProductImage($_FILES['image']);
        }

        $query = "INSERT INTO products (
            name, sku, description, short_description, price, sale_price,
            stock_quantity, stock_status, category_id, image_url, status, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'publish', NOW())";

        $stmt = $conn->prepare($query);
        $stmt->execute([
            $data['name'],
            $data['sku'] ?? null,
            $data['description'] ?? null,
            $data['short_description'] ?? null,
            $data['price'],
            $data['sale_price'] ?? null,
            $data['stock_quantity'] ?? 0,
            $data['stock_status'] ?? 'instock',
            $data['category_id'] ?? null,
            $imagePath
        ]);

        $productId = $conn->lastInsertId();

        echo json_encode([
            'success' => true,
            'message' => 'Product created successfully',
            'product_id' => $productId
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error creating product: ' . $e->getMessage()]);
    }
}

function updateProduct($id) {
    global $conn;

    try {
        $data = $_POST;

        // Check if product exists
        $stmt = $conn->prepare("SELECT id FROM products WHERE id = ?");
        $stmt->execute([$id]);
        if (!$stmt->fetch()) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            return;
        }

        // Handle image upload
        $imagePath = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imagePath = uploadProductImage($_FILES['image']);
        }

        $query = "UPDATE products SET
            name = ?, sku = ?, description = ?, short_description = ?,
            price = ?, sale_price = ?, stock_quantity = ?, stock_status = ?,
            category_id = ?, updated_at = NOW()";

        $params = [
            $data['name'],
            $data['sku'] ?? null,
            $data['description'] ?? null,
            $data['short_description'] ?? null,
            $data['price'],
            $data['sale_price'] ?? null,
            $data['stock_quantity'] ?? 0,
            $data['stock_status'] ?? 'instock',
            $data['category_id'] ?? null
        ];

        if ($imagePath) {
            $query .= ", image_url = ?";
            $params[] = $imagePath;
        }

        $query .= " WHERE id = ?";
        $params[] = $id;

        $stmt = $conn->prepare($query);
        $stmt->execute($params);

        echo json_encode([
            'success' => true,
            'message' => 'Product updated successfully'
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error updating product: ' . $e->getMessage()]);
    }
}

function deleteProduct($id) {
    global $conn;

    try {
        // Check if product exists
        $stmt = $conn->prepare("SELECT id FROM products WHERE id = ?");
        $stmt->execute([$id]);
        if (!$stmt->fetch()) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            return;
        }

        // Soft delete - change status to 'trash'
        $stmt = $conn->prepare("UPDATE products SET status = 'trash', updated_at = NOW() WHERE id = ?");
        $stmt->execute([$id]);

        echo json_encode([
            'success' => true,
            'message' => 'Product deleted successfully'
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error deleting product: ' . $e->getMessage()]);
    }
}

function uploadProductImage($file) {
    $uploadDir = '../uploads/products/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $fileName = uniqid() . '_' . basename($file['name']);
    $targetPath = $uploadDir . $fileName;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return 'uploads/products/' . $fileName;
    }

    return null;
}

function getProductImageUrl($productId) {
    global $conn;

    try {
        $stmt = $conn->prepare("SELECT image_url FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        return $product['image_url'] ?? 'img/placeholder.jpg';
    } catch (Exception $e) {
        return 'img/placeholder.jpg';
    }
}
?>