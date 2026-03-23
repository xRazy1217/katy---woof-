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
$path = str_replace('/api/ecommerce/products', '', $path);
$path = str_replace('.php', '', $path);
$path = trim($path, '/');

// Check for ID in query string if no path parameter
if (empty($path) && isset($_GET['id'])) {
    $path = $_GET['id'];
}

// Handle different endpoints
if ($method === 'GET') {
    if (empty($path)) {
        // GET /api/ecommerce/products - List all products
        getProducts();
    } elseif (is_numeric($path)) {
        // GET /api/ecommerce/products/{id} - Get single product
        getProduct($path);
    }
} elseif ($method === 'POST') {
    // POST supports both create and multipart update (PHP handles files reliably on POST)
    $overrideMethod = strtoupper($_POST['_method'] ?? '');
    if (!empty($path) && is_numeric($path) && $overrideMethod === 'PUT') {
        updateProduct($path);
    } elseif (empty($path) && !empty($_POST['id'])) {
        updateProduct($_POST['id']);
    } elseif (empty($path)) {
        createProduct();
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Endpoint not found']);
    }
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
            $product['image_url'] = normalizePublicImageUrl(getProductImageUrl($product['id']));
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
        $product['image_url'] = normalizePublicImageUrl(getProductImageUrl($product['id']));

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

        $slug = generateUniqueProductSlug($data['name']);

        $query = "INSERT INTO products (
            name, slug, sku, description, short_description, price, sale_price,
            stock_quantity, stock_status, category_id, image_url, status, created_at
            , attributes
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'publish', NOW(), ?)";

        $stmt = $conn->prepare($query);
        $stmt->execute([
            $data['name'],
            $slug,
            $data['sku'] ?? null,
            $data['description'] ?? null,
            $data['short_description'] ?? null,
            $data['price'],
            $data['sale_price'] ?? null,
            $data['stock_quantity'] ?? 0,
            $data['stock_status'] ?? 'instock',
            $data['category_id'] ?? null,
            $imagePath,
            $data['attributes'] ?? null
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
        // Parse PUT data (PHP doesn't auto-parse PUT post data)
        $data = $_POST;
        if (empty($data)) {
            parse_str(file_get_contents('php://input'), $data);
        }

        // Check if product exists
        $stmt = $conn->prepare("SELECT id, name FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $existingProduct = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$existingProduct) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            return;
        }

        $nameForSlug = isset($data['name']) && trim((string)$data['name']) !== ''
            ? trim((string)$data['name'])
            : $existingProduct['name'];
        $slug = generateUniqueProductSlug($nameForSlug, intval($id));

        // Handle image upload
        $imagePath = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imagePath = uploadProductImage($_FILES['image']);
        }

        $query = "UPDATE products SET
            name = ?, slug = ?, sku = ?, description = ?, short_description = ?,
            price = ?, sale_price = ?, stock_quantity = ?, stock_status = ?,
            category_id = ?, attributes = ?, updated_at = NOW()";

        $params = [
            $nameForSlug,
            $slug,
            $data['sku'] ?? null,
            $data['description'] ?? null,
            $data['short_description'] ?? null,
            $data['price'] ?? 0,
            $data['sale_price'] ?? null,
            $data['stock_quantity'] ?? 0,
            $data['stock_status'] ?? 'instock',
            $data['category_id'] ?? null,
            $data['attributes'] ?? null
        ];

        if ($imagePath) {
            $query .= ", image_url = ?";
            $params[] = $imagePath;
        }

        $query .= " WHERE id = ?";
        $params[] = $id;

        $stmt = $conn->prepare($query);
        $stmt->execute($params);

        // Return updated product data
        $stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN product_categories c ON p.category_id = c.id WHERE p.id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'message' => 'Product updated successfully',
            'product' => $product
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
    $uploadDirFs = __DIR__ . '/../../uploads/products/';
    if (!is_dir($uploadDirFs)) {
        mkdir($uploadDirFs, 0755, true);
    }

    $fileName = uniqid() . '_' . basename($file['name']);
    $targetPath = $uploadDirFs . $fileName;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return '/uploads/products/' . $fileName;
    }

    return null;
}

function normalizePublicImageUrl($path) {
    if (!$path || !is_string($path)) {
        return '/img/placeholder.svg';
    }

    $trimmedPath = ltrim($path, '/');
    if ($trimmedPath === 'img/placeholder.jpg' || $trimmedPath === 'img/placeholder.jpeg') {
        return '/img/placeholder.svg';
    }

    if (preg_match('/^https?:\/\//i', $path) || strpos($path, '/') === 0) {
        return $path;
    }

    return '/' . ltrim($path, '/');
}

function createSlug($text) {
    $text = strtolower((string)$text);
    $slug = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $slug = preg_replace('/[\s-]+/', '-', $slug);
    $slug = trim($slug, '-');

    return $slug !== '' ? $slug : 'producto';
}

function generateUniqueProductSlug($name, $excludeId = null) {
    global $conn;

    $baseSlug = createSlug($name);
    $slug = $baseSlug;
    $suffix = 2;

    while (true) {
        if ($excludeId) {
            $stmt = $conn->prepare('SELECT id FROM products WHERE slug = ? AND id <> ? LIMIT 1');
            $stmt->execute([$slug, $excludeId]);
        } else {
            $stmt = $conn->prepare('SELECT id FROM products WHERE slug = ? LIMIT 1');
            $stmt->execute([$slug]);
        }

        if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
            return $slug;
        }

        $slug = $baseSlug . '-' . $suffix;
        $suffix++;
    }
}

function getProductImageUrl($productId) {
    global $conn;

    try {
        $stmt = $conn->prepare("SELECT image_url FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        return $product['image_url'] ?? '/img/placeholder.svg';
    } catch (Exception $e) {
        return '/img/placeholder.svg';
    }
}
?>