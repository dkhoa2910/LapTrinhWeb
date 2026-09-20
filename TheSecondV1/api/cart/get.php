<?php
declare(strict_types=1);

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Phương thức không được hỗ trợ.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Vui lòng đăng nhập.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$userId = (int) $_SESSION['user_id'];

try {
    // Chỉ SELECT các cột chắc chắn có (khớp với cart.php)
    $stmt = $pdo->prepare("
        SELECT
            c.id AS cart_id,
            ci.id AS cart_item_id,
            ci.product_id,
            ci.quantity,
            ci.price,
            p.name,
            p.stock,
            p.status,
            (
                SELECT pi.image_url
                FROM product_images pi
                WHERE pi.product_id = p.id
                ORDER BY pi.is_primary DESC, pi.id ASC
                LIMIT 1
            ) AS image_url
        FROM carts c
        INNER JOIN cart_items ci ON ci.cart_id = c.id
        INNER JOIN products p ON p.id = ci.product_id
        WHERE c.user_id = ?
        ORDER BY ci.id DESC
    ");
    $stmt->execute([$userId]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $total = 0;
    foreach ($items as &$item) {
        $item['quantity'] = (int) $item['quantity'];
        $item['price'] = (float) $item['price'];
        $item['subtotal'] = $item['price'] * $item['quantity'];
        $total += $item['subtotal'];
    }
    unset($item);

    echo json_encode([
        'success' => true,
        'items' => $items,
        'total' => $total,
        'item_count' => count($items)
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi máy chủ khi tải giỏ hàng.',
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
