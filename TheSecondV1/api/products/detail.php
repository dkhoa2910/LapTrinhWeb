<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {

    http_response_code(405);

    echo json_encode([
        'success' => false,
        'message' => 'Method không được phép.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

$productId = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if (!$productId || $productId < 1) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'ID sản phẩm không hợp lệ.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

try {

    $stmt = $pdo->prepare("
        SELECT
            p.*,

            c.name AS category_name,
            c.slug AS category_slug,

            u.full_name AS seller_name

        FROM products p

        INNER JOIN categories c
            ON c.id = p.category_id

        LEFT JOIN users u
            ON u.id = p.seller_id

        WHERE p.id = :id
          AND p.status = 'approved'

        LIMIT 1
    ");

    $stmt->execute([
        'id' => $productId
    ]);

    $product = $stmt->fetch();

    if (!$product) {

        http_response_code(404);

        echo json_encode([
            'success' => false,
            'message' => 'Không tìm thấy sản phẩm.'
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    $imageStmt = $pdo->prepare("
        SELECT
            id,
            image_url,
            is_primary,
            sort_order

        FROM product_images

        WHERE product_id = :product_id

        ORDER BY
            is_primary DESC,
            sort_order ASC,
            id ASC
    ");

    $imageStmt->execute([
        'product_id' => $productId
    ]);

    $product['images'] =
        $imageStmt->fetchAll();

    echo json_encode([
        'success' => true,
        'product' => $product
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Không thể tải thông tin sản phẩm.'
    ], JSON_UNESCAPED_UNICODE);
}