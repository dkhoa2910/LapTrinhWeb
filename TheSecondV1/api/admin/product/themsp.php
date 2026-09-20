<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /../../../../../TheSecondV1/admin/products.html');
    exit;
}


$name = trim($_POST['ten_san_pham'] ?? '');

$categoryId = filter_input(
    INPUT_POST,
    'danh_muc_id',
    FILTER_VALIDATE_INT
);

$price = filter_input(
    INPUT_POST,
    'gia',
    FILTER_VALIDATE_INT
);

$stock = filter_input(
    INPUT_POST,
    'so_luong_ton',
    FILTER_VALIDATE_INT
);

$description = trim($_POST['mo_ta'] ?? '');

$conditionType = trim($_POST['condition_type'] ?? '');

$status = trim($_POST['status'] ?? '');

$sku = trim($_POST['sku'] ?? '');
$model = trim($_POST['model'] ?? '');




/*
 * =========================
 * KIỂM TRA
 * =========================
 */

if ($name === '') {
    die('Vui lòng nhập tên sản phẩm.');
}

if (!$categoryId) {
    die('Danh mục không hợp lệ.');
}

if ($price === false || $price < 0) {
    die('Giá sản phẩm không hợp lệ.');
}

if ($stock === false || $stock < 0) {
    die('Số lượng không hợp lệ.');
}


// Lấy đúng tên field từ form
$conditionType = trim($_POST['tinh_trang'] ?? '');
$statusRaw     = trim($_POST['trang_thai'] ?? '');

// Map tình trạng máy
$mapTinhTrang = [
    '~99%' => 'like_new',
    '~95%' => 'excellent',
    '~85%' => 'good',
    '~70%' => 'fair',
    'Khác' => 'poor'
];

if (!isset($mapTinhTrang[$conditionType])) {
    die('Tình trạng sản phẩm không hợp lệ.');
}
$conditionType = $mapTinhTrang[$conditionType];

// Map trạng thái
$mapTrangThai = [
    'Còn hàng'  => 'available',
    'Hết hàng'  => 'sold_out',
    'Ngừng bán' => 'hidden'
];

if (!isset($mapTrangThai[$statusRaw])) {
    die('Trạng thái sản phẩm không hợp lệ.');
}
$status = $mapTrangThai[$statusRaw];

// spe
$specifications = trim($_POST['specifications'] ?? '');

if ($specifications === '') {
    $specifications = null;
} else {
    $decoded = json_decode($specifications, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        die('Thông số kỹ thuật không hợp lệ.');
    }
    $specifications = json_encode($decoded, JSON_UNESCAPED_UNICODE);
}

// sku

if ($sku === '') {

    $sku = 'SP-' .
        date('YmdHis') .
        '-' .
        strtoupper(bin2hex(random_bytes(3)));

}


/*
 * =========================
 * INSERT
 * =========================
 */

try {

    $pdo->beginTransaction();

$stmt = $pdo->prepare("
    INSERT INTO products
    (
        seller_id,
        category_id,
        sku,
        name,
        description,
        specifications,          
        price,
        condition_type,
        stock,
        status
    )
    VALUES
    (
        :seller_id,
        :category_id,
        :sku,
        :name,
        :description,
        :specifications,         
        :price,
        :condition_type,
        :stock,
        :status
    )
");

$stmt->execute([
    'seller_id'       => null,
    'category_id'     => $categoryId,
    'sku'             => $sku,
    'name'            => $name,
    'description'     => $description !== '' ? $description : null,
    'specifications'  => $specifications,   
    'price'           => $price,
    'condition_type'  => $conditionType,
    'stock'           => $stock,
    'status'          => $status
]);

    $pdo->commit();

    header('Location: /../../../../../TheSecondV1/api/admin/product/products.php');
    exit;

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    die(
        'Không thể thêm sản phẩm: ' .
        $e->getMessage()
    );
}