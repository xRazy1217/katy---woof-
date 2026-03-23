<?php
class CartAPI {

    private static function sessionId(): string {
        return $_SERVER['HTTP_X_SESSION_ID'] ?? session_id() ?: 'guest';
    }

    public static function get(): array {
        try {
            $pdo = getDBConnection();
            $sid = self::sessionId();
            $stmt = $pdo->prepare("
                SELECT ci.id, ci.quantity, ci.price,
                       p.name, p.image_url, p.stock_status, p.stock_quantity
                FROM cart_items ci
                JOIN products p ON ci.product_id = p.id
                WHERE ci.session_id = ?
            ");
            $stmt->execute([$sid]);
            $items = $stmt->fetchAll();
            return ['success' => true, 'items' => $items];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage(), 'items' => []];
        }
    }

    public static function add(array $body): array {
        try {
            $pdo       = getDBConnection();
            $sid       = self::sessionId();
            $productId = intval($body['product_id'] ?? 0);
            $qty       = max(1, intval($body['quantity'] ?? 1));

            if (!$productId) return ['success' => false, 'error' => 'Producto inválido'];

            $p = $pdo->prepare("SELECT id, price, stock_status, stock_quantity FROM products WHERE id=? AND status='publish'");
            $p->execute([$productId]);
            $product = $p->fetch();
            if (!$product) return ['success' => false, 'error' => 'Producto no encontrado'];
            if ($product['stock_status'] === 'outofstock') return ['success' => false, 'error' => 'Producto agotado'];

            $price = floatval($product['price']);

            // Si ya existe en el carrito, sumar cantidad
            $existing = $pdo->prepare("SELECT id, quantity FROM cart_items WHERE session_id=? AND product_id=?");
            $existing->execute([$sid, $productId]);
            $row = $existing->fetch();

            if ($row) {
                $newQty = $row['quantity'] + $qty;
                $pdo->prepare("UPDATE cart_items SET quantity=?, price=? WHERE id=?")->execute([$newQty, $price, $row['id']]);
            } else {
                $pdo->prepare("INSERT INTO cart_items (session_id, product_id, quantity, price) VALUES (?,?,?,?)")->execute([$sid, $productId, $qty, $price]);
            }

            return self::get();
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public static function update(array $body): array {
        try {
            $pdo    = getDBConnection();
            $sid    = self::sessionId();
            $itemId = intval($body['item_id'] ?? 0);
            $qty    = intval($body['quantity'] ?? 0);

            if ($qty <= 0) {
                $pdo->prepare("DELETE FROM cart_items WHERE id=? AND session_id=?")->execute([$itemId, $sid]);
            } else {
                $pdo->prepare("UPDATE cart_items SET quantity=? WHERE id=? AND session_id=?")->execute([$qty, $itemId, $sid]);
            }
            return self::get();
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public static function remove(array $body): array {
        try {
            $pdo    = getDBConnection();
            $sid    = self::sessionId();
            $itemId = intval($body['item_id'] ?? 0);
            $pdo->prepare("DELETE FROM cart_items WHERE id=? AND session_id=?")->execute([$itemId, $sid]);
            return self::get();
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public static function clear(): array {
        try {
            $pdo = getDBConnection();
            $sid = self::sessionId();
            $pdo->prepare("DELETE FROM cart_items WHERE session_id=?")->execute([$sid]);
            return ['success' => true, 'items' => []];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public static function getTotal(string $sid): float {
        try {
            $pdo  = getDBConnection();
            $stmt = $pdo->prepare("SELECT SUM(price * quantity) FROM cart_items WHERE session_id=?");
            $stmt->execute([$sid]);
            return floatval($stmt->fetchColumn());
        } catch (Exception $e) { return 0; }
    }
}
