<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: admin_login.html");
    exit();
}

// Đường dẫn config (điều chỉnh nếu cấu trúc thư mục khác)
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: products.php");
    exit();
}

$id            = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$name          = trim($_POST['name'] ?? '');
$category_id   = (int)($_POST['category_id'] ?? 0);
$price         = (float)($_POST['price'] ?? 0);
$condition_type = $_POST['condition_type'] ?? '';
$stock         = (int)($_POST['stock'] ?? 0);
$status        = $_POST['status'] ?? 'pending';
$description   = trim($_POST['description'] ?? '');
$sku           = trim($_POST['sku'] ?? '');

// ===== Validate =====
if (
    $id <= 0 ||
    $name === '' ||
    $category_id <= 0 ||
    $price < 0 ||
    $stock < 0
) {
    die("Dữ liệu không hợp lệ.");
}

$allowedConditions = ['like_new', 'excellent', 'good', 'fair', 'poor'];
$allowedStatuses   = ['available', 'sold_out', 'hidden'];   // ← sửa lại cho khớp DB

if (!in_array($condition_type, $allowedConditions, true)) {
    die("Tình trạng sản phẩm không hợp lệ.");
}
if (!in_array($status, $allowedStatuses, true)) {
    die("Trạng thái sản phẩm không hợp lệ.");
}

// Lấy specifications
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

try {
    if ($sku === '') {
        $stmtSku = $pdo->prepare("SELECT sku FROM products WHERE id = :id");
        $stmtSku->execute([':id' => $id]);
        $row = $stmtSku->fetch();

        if (!$row) {
            die("Không tìm thấy sản phẩm.");
        }
        $sku = $row['sku'];
    }

        $sql = "UPDATE products SET
                    name            = :name,
                    category_id     = :category_id,
                    sku             = :sku,
                    price           = :price,
                    condition_type  = :condition_type,
                    stock           = :stock,
                    status          = :status,
                    description     = :description,
                    specifications  = :specifications          
                WHERE id = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':name'           => $name,
            ':category_id'    => $category_id,
            ':sku'            => $sku,
            ':price'          => $price,
            ':condition_type' => $condition_type,
            ':stock'          => $stock,
            ':status'         => $status,
            ':description'    => $description,
            ':specifications' => $specifications,     
            ':id'             => $id
        ]);

    // ===== Xử lý ảnh mới (nếu có) =====
    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {

        $uploadDir = __DIR__ . '/uploads/'; // Thư mục lưu ảnh
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Tạo tên file an toàn
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowedExt = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        if (!in_array($ext, $allowedExt)) {
            die("Chỉ chấp nhận file ảnh: jpg, jpeg, png, webp, gif");
        }

        $fileName   = 'product_' . $id . '_' . time() . '.' . $ext;
        $targetFile = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            // Đường dẫn lưu vào database (relative)
            $imageUrl = 'uploads/' . $fileName;

            // Xóa ảnh primary cũ (nếu có)
            $pdo->prepare("DELETE FROM product_images WHERE product_id = ? AND is_primary = 1")
                ->execute([$id]);

            // Thêm ảnh mới làm primary
            $pdo->prepare("INSERT INTO product_images (product_id, image_url, is_primary) VALUES (?, ?, 1)")
                ->execute([$id, $imageUrl]);
        } else {
            die("Không thể upload ảnh. Kiểm tra quyền thư mục uploads.");
        }
    }

    // Thành công → quay về trang danh sách
    header("Location: products.php?success=updated");
    exit();

} catch (PDOException $e) {
    die("Lỗi cập nhật sản phẩm: " . $e->getMessage());
}