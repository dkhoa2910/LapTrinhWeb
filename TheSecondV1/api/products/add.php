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

/*
|--------------------------------------------------------------------------
| Kiểm tra đăng nhập
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);

    echo json_encode([
        'success' => false,
        'message' => 'Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

$userId = (int) $_SESSION['user_id'];

/*
|--------------------------------------------------------------------------
| Đọc dữ liệu (form-urlencoded hoặc JSON)
|--------------------------------------------------------------------------
*/

// Frontend hiện gửi dạng application/x-www-form-urlencoded (body: product_id=...)
// nên ưu tiên đọc từ $_POST; nếu rỗng thì thử đọc JSON để tương thích ngược.
$input = $_POST;

if (empty($input)) {
    $jsonInput = json_decode(
        file_get_contents('php://input'),
        true
    );

    if (is_array($jsonInput)) {
        $input = $jsonInput;
    }
}

$productId = isset($input['product_id'])
    ? (int) $input['product_id']
    : 0;

$quantity = isset($input['quantity'])
    ? (int) $input['quantity']
    : 1;

if ($productId <= 0 || $quantity <= 0) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Dữ liệu sản phẩm không hợp lệ.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

/*
|--------------------------------------------------------------------------
| Kiểm tra sản phẩm
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        id,
        name,
        price,
        stock,
        status
    FROM products
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$productId]);

$product = $stmt->fetch();

if (!$product) {

    http_response_code(404);

    echo json_encode([
        'success' => false,
        'message' => 'Không tìm thấy sản phẩm.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

if ($product['status'] !== 'approved') {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Sản phẩm chưa được phép bán.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

if ((int) $product['stock'] <= 0) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Sản phẩm đã hết hàng.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

/*
|--------------------------------------------------------------------------
| Lấy / tạo cart
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT id
    FROM carts
    WHERE user_id = ?
    LIMIT 1
");

$stmt->execute([$userId]);

$cart = $stmt->fetch();

if ($cart) {

    $cartId = (int) $cart['id'];

} else {

    $stmt = $pdo->prepare("
        INSERT INTO carts (user_id)
        VALUES (?)
    ");

    $stmt->execute([$userId]);

    $cartId = (int) $pdo->lastInsertId();
}

/*
|--------------------------------------------------------------------------
| Kiểm tra sản phẩm đã có trong giỏ
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT id, quantity
    FROM cart_items
    WHERE cart_id = ?
      AND product_id = ?
    LIMIT 1
");

$stmt->execute([
    $cartId,
    $productId
]);

$item = $stmt->fetch();

if ($item) {

    $newQuantity =
        (int) $item['quantity'] + $quantity;

    if ($newQuantity > (int) $product['stock']) {

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
        $newQuantity,
        $item['id']
    ]);

} else {

    if ($quantity > (int) $product['stock']) {

        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' => 'Số lượng vượt quá tồn kho.'
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO cart_items
            (cart_id, product_id, quantity, price)
        VALUES
            (?, ?, ?, ?)
    ");

    $stmt->execute([
        $cartId,
        $productId,
        $quantity,
        $product['price']
    ]);
}

/*
|--------------------------------------------------------------------------
| Tính lại tổng số lượng trong giỏ để trả về cho frontend
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT COALESCE(SUM(quantity), 0)
    FROM cart_items
    WHERE cart_id = ?
");
$stmt->execute([$cartId]);
$cartCount = (int) $stmt->fetchColumn();

echo json_encode([
    'success'    => true,
    'message'    => 'Đã thêm sản phẩm vào giỏ hàng.',
    'cart_count' => $cartCount
], JSON_UNESCAPED_UNICODE);