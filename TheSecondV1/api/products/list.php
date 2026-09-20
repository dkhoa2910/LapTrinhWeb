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

try {

    $sql = "
        SELECT
            p.id,
            p.sku,
            p.name,
            p.brand,
            p.model,
            p.price,
            p.original_price,
            p.condition_type,
            p.condition_score,
            p.battery_health,
            p.stock,
            p.status,
            p.created_at,

            c.id AS category_id,
            c.name AS category_name,

            u.id AS seller_id,
            u.full_name AS seller_name,

            (
                SELECT pi.image_url
                FROM product_images pi
                WHERE pi.product_id = p.id
                ORDER BY
                    pi.is_primary DESC,
                    pi.sort_order ASC,
                    pi.id ASC
                LIMIT 1
            ) AS image

        FROM products p

        INNER JOIN categories c
            ON c.id = p.category_id

        LEFT JOIN users u
            ON u.id = p.seller_id

        WHERE p.status = 'approved'
          AND p.stock > 0

        ORDER BY p.created_at DESC
    ";

    $stmt = $pdo->query($sql);

    $products = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'count' => count($products),
        'products' => $products
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Không thể tải danh sách sản phẩm.'
    ], JSON_UNESCAPED_UNICODE);
}