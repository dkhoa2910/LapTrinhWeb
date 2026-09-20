<?php
// Xử lý xóa sản phẩm
if (isset($_POST['xoa_san_pham'])) {
    $san_pham_id = $_POST['san_pham_id'];

    // Chuyển hướng về trang danh sách sản phẩm
    header("Location: products.php");
    exit();
}
?>