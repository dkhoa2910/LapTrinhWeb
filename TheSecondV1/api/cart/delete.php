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

if ($cartItemId <= 0) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Dữ liệu không hợp lệ.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

$stmt = $pdo->prepare("
    DELETE ci
    FROM cart_items ci

    INNER JOIN carts c
        ON c.id = ci.cart_id

    WHERE ci.id = ?
      AND c.user_id = ?
");

$stmt->execute([
    $cartItemId,
    $userId
]);

echo json_encode([
    'success' => true,
    'message' => 'Đã xóa sản phẩm khỏi giỏ hàng.'
], JSON_UNESCAPED_UNICODE);