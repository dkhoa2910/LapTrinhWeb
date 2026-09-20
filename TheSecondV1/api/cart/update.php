<?php
declare(strict_types=1);

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
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

$input = json_decode(
    file_get_contents('php://input'),
    true
);

$cartItemId = isset($input['cart_item_id'])
    ? (int) $input['cart_item_id']
    : 0;

$quantity = isset($input['quantity'])
    ? (int) $input['quantity']
    : 0;

if ($cartItemId <= 0 || $quantity <= 0) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Dữ liệu không hợp lệ.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

$stmt = $pdo->prepare("
    SELECT
        ci.id,
        p.stock
    FROM cart_items ci

    INNER JOIN carts c
        ON c.id = ci.cart_id

    INNER JOIN products p
        ON p.id = ci.product_id

    WHERE ci.id = ?
      AND c.user_id = ?

    LIMIT 1
");

$stmt->execute([
    $cartItemId,
    $userId
]);

$item = $stmt->fetch();

if (!$item) {

    http_response_code(404);

    echo json_encode([
        'success' => false,
        'message' => 'Không tìm thấy sản phẩm trong giỏ.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

if ($quantity > (int) $item['stock']) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Số lượng vượt quá tồn kho.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

$stmt = $pdo->prepare("
    UPDATE cart_items
    SET quantity = ?
    WHERE id = ?
");

$stmt->execute([
    $quantity,
    $cartItemId
]);

echo json_encode([
    'success' => true,
    'message' => 'Đã cập nhật giỏ hàng.'
], JSON_UNESCAPED_UNICODE);