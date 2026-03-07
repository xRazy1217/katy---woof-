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
$path = str_replace('/api/ecommerce/categories', '', $path);
$path = trim($path, '/');

// Handle different endpoints
if ($method === 'GET') {
    if (empty($path)) {
        // GET /api/ecommerce/categories - List all categories
        getCategories();
    } elseif (is_numeric($path)) {
        // GET /api/ecommerce/categories/{id} - Get single category
        getCategory($path);
    }
} elseif ($method === 'POST' && empty($path)) {
    // POST /api/ecommerce/categories - Create category
    createCategory();
} elseif ($method === 'PUT' && is_numeric($path)) {
    // PUT /api/ecommerce/categories/{id} - Update category
    updateCategory($path);
} elseif ($method === 'DELETE' && is_numeric($path)) {
    // DELETE /api/ecommerce/categories/{id} - Delete category
    deleteCategory($path);
} else {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Endpoint not found']);
}

function getCategories() {
    global $conn;

    try {
        $query = "SELECT * FROM product_categories ORDER BY name ASC";

        $stmt = $conn->prepare($query);
        $stmt->execute();
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($categories);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error fetching categories: ' . $e->getMessage()]);
    }
}

function getCategory($id) {
    global $conn;

    try {
        $query = "SELECT * FROM product_categories WHERE id = ?";

        $stmt = $conn->prepare($query);
        $stmt->execute([$id]);
        $category = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$category) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Category not found']);
            return;
        }

        echo json_encode($category);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error fetching category: ' . $e->getMessage()]);
    }
}

function createCategory() {
    global $conn;

    try {
        $data = json_decode(file_get_contents('php://input'), true);

        // Validate required fields
        if (empty($data['name'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Category name is required']);
            return;
        }

        $query = "INSERT INTO product_categories (name, description, slug, created_at)
                  VALUES (?, ?, ?, NOW())";

        $slug = createSlug($data['name']);

        $stmt = $conn->prepare($query);
        $stmt->execute([
            $data['name'],
            $data['description'] ?? null,
            $slug
        ]);

        $categoryId = $conn->lastInsertId();

        echo json_encode([
            'success' => true,
            'message' => 'Category created successfully',
            'category_id' => $categoryId
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error creating category: ' . $e->getMessage()]);
    }
}

function updateCategory($id) {
    global $conn;

    try {
        $data = json_decode(file_get_contents('php://input'), true);

        // Check if category exists
        $stmt = $conn->prepare("SELECT id FROM product_categories WHERE id = ?");
        $stmt->execute([$id]);
        if (!$stmt->fetch()) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Category not found']);
            return;
        }

        $query = "UPDATE product_categories SET
                  name = ?, description = ?, slug = ?, updated_at = NOW()
                  WHERE id = ?";

        $slug = createSlug($data['name']);

        $stmt = $conn->prepare($query);
        $stmt->execute([
            $data['name'],
            $data['description'] ?? null,
            $slug,
            $id
        ]);

        echo json_encode([
            'success' => true,
            'message' => 'Category updated successfully'
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error updating category: ' . $e->getMessage()]);
    }
}

function deleteCategory($id) {
    global $conn;

    try {
        // Check if category exists
        $stmt = $conn->prepare("SELECT id FROM product_categories WHERE id = ?");
        $stmt->execute([$id]);
        if (!$stmt->fetch()) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Category not found']);
            return;
        }

        // Check if category has products
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM products WHERE category_id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result['count'] > 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Cannot delete category with existing products']);
            return;
        }

        $stmt = $conn->prepare("DELETE FROM product_categories WHERE id = ?");
        $stmt->execute([$id]);

        echo json_encode([
            'success' => true,
            'message' => 'Category deleted successfully'
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error deleting category: ' . $e->getMessage()]);
    }
}

function createSlug($text) {
    // Convert to lowercase and replace spaces with hyphens
    $slug = strtolower($text);
    $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
    $slug = preg_replace('/[\s-]+/', '-', $slug);
    $slug = trim($slug, '-');

    return $slug;
}
?>