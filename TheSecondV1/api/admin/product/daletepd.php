<?php
session_start();


if (!isset($_SESSION["admin_id"])) {
    header("Location: /../../../../admin/login.php");
    exit();
}

require_once __DIR__ . '/../../config/database.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    die("ID sản phẩm không hợp lệ.");
}

try {
    // Xóa ảnh của sản phẩm trước
    $stmt = $pdo->prepare("
        DELETE FROM product_images
        WHERE product_id = :id
    ");

    $stmt->execute([
        ':id' => $id
    ]);

    // Xóa sản phẩm
    $stmt = $pdo->prepare("
        DELETE FROM products
        WHERE id = :id
    ");

    $stmt->execute([
        ':id' => $id
    ]);

    header("Location: products.php?success=deleted");
    exit();

} catch (PDOException $e) {
    die("Lỗi xóa sản phẩm: " . $e->getMessage());
}