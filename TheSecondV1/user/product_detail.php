<?php
session_start();
require_once __DIR__ . '/../api/config/database.php'; 

// 1. Lấy ID sản phẩm từ URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    echo "<script>alert('Sản phẩm không hợp lệ!'); window.location.href='see_all_pd.php';</script>";
    exit;
}

$userName    = $_SESSION['user_name'] ?? '';
$userAvatar  = $_SESSION['user_avatar'] ?? '';
$isLoggedIn = isset($_SESSION['user_id']);

$cartCount = 0;

if ($isLoggedIn) {
    $stmtCart = $pdo->prepare("
        SELECT COALESCE(SUM(ci.quantity), 0)
        FROM carts c
        LEFT JOIN cart_items ci ON ci.cart_id = c.id
        WHERE c.user_id = ?
    ");

    $stmtCart->execute([$_SESSION['user_id']]);
    $cartCount = (int)$stmtCart->fetchColumn();
}

// 2. Truy vấn thông tin sản phẩm + danh mục + người bán
$stmt = $pdo->prepare("
    SELECT p.*, c.name AS category_name, u.full_name AS seller_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN users u ON p.seller_id = u.id
    WHERE p.id = ? 
    LIMIT 1
");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    echo "<script>alert('Sản phẩm không tồn tại hoặc đã bị xóa!'); window.location.href='see_all_pd.php';</script>";
    exit;
}

// 3. Lấy hình ảnh của sản phẩm
$stmtImg = $pdo->prepare("
    SELECT image_url 
    FROM product_images 
    WHERE product_id = ? 
    ORDER BY is_primary DESC, sort_order ASC
");
$stmtImg->execute([$product_id]);
$images = $stmtImg->fetchAll(PDO::FETCH_ASSOC);

$mainImage = !empty($images) ? '../api/admin/product/' . $images[0]['image_url'] : null;

// 4. Map hiển thị tình trạng
$nhanTinhTrang = [
    'like_new'  => 'Mới 99%',
    'excellent' => 'Mới 95%',
    'good'      => 'Khá 85%',
    'fair'      => 'Trung bình 70%',
    'poor'      => 'Cũ'
];
$conditionLabel = $nhanTinhTrang[$product['condition_type']] ?? 'Khác';

// 5. Tính phần trăm giảm giá
$discountPct = 0;
if (!empty($product['original_price']) && $product['original_price'] > $product['price']) {
    $discountPct = round((($product['original_price'] - $product['price']) / $product['original_price']) * 100);
}

// 6. Chuẩn bị mảng thông số kỹ thuật
$specifications = [];

// Thông số kỹ thuật lưu dạng JSON trong cột products.specifications
if (!empty($product['specifications'])) {
    $decodedSpecs = json_decode($product['specifications'], true);
    if (is_array($decodedSpecs)) {
        $specifications = $decodedSpecs;
    }
}

// Bổ sung thêm SKU nếu chưa có trong JSON
if (!empty($product['sku']) && !isset($specifications['SKU'])) {
    $specifications['SKU'] = $product['sku'];
}

// 7. Lấy đánh giá của sản phẩm
$stmtRev = $pdo->prepare("
    SELECT r.*, u.full_name 
    FROM product_reviews r 
    LEFT JOIN users u ON r.user_id = u.id 
    WHERE r.product_id = ? AND r.status = 'visible'
    ORDER BY r.created_at DESC
");
$stmtRev->execute([$product_id]);
$reviews = $stmtRev->fetchAll(PDO::FETCH_ASSOC);
$reviewCount = count($reviews);

$avgRating = 5.0; 
if ($reviewCount > 0) {
    $totalStars = 0;
    foreach ($reviews as $r) {
        $totalStars += (int)$r['rating'];
    }
    $avgRating = round($totalStars / $reviewCount, 1);
}

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?> – TheSecond</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="../assets/css/styles.css">
    
    <style>
        .pd-breadcrumb { display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; color: #94a3b8; margin-bottom: 24px; flex-wrap: wrap; }
        .pd-breadcrumb a:hover { color: #4f46e5; }
        .pd-breadcrumb .current { color: #0f172a; font-weight: 700; }
        .detail-gallery { background: #fff; border: 1px solid #e2e8f0; border-radius: 24px; display: flex; align-items: center; justify-content: center; min-height: 480px; box-shadow: 0 8px 32px rgba(15,23,42,0.06); overflow: hidden;}
        .detail-gallery img { width: 100%; height: 100%; object-fit: contain; padding: 20px; transition: transform .4s; }
        .detail-gallery:hover img { transform: scale(1.05); }
        .detail-gallery .emoji { font-size: 150px; transition: transform .4s; }
        .detail-gallery:hover .emoji { transform: scale(1.1); }

        /* ===== MODAL ===== */
        .modal { position: fixed; inset: 0; background: rgba(15,23,42,.55); display: none; align-items: center; justify-content: center; padding: 20px; z-index: 1000; }
        .modal.show { display: flex; }
        .modal-box { background: #fff; border-radius: 20px; width: 100%; max-width: 440px; padding: 28px; text-align: center; animation: pdModalIn .2s ease; }
        @keyframes pdModalIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: none; } }
        .modal-box h3 { margin: 0 0 10px; font-size: 19px; font-weight: 800; color: #0f172a; }
        .modal-box p { color: #64748b; margin: 0 0 20px; font-size: 14px; line-height: 1.6; }
        .modal-icon { width: 56px; height: 56px; border-radius: 50%; background: #dcfce7; color: #16a34a; font-size: 28px; font-weight: 900; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; }
        .modal-product { display: flex; align-items: center; gap: 14px; background: #f8fafc; border-radius: 14px; padding: 12px; margin-bottom: 20px; text-align: left; }
        .modal-product-thumb { width: 56px; height: 56px; border-radius: 10px; background: #fff; border: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: center; font-size: 26px; overflow: hidden; flex-shrink: 0; }
        .modal-product-thumb img { width: 100%; height: 100%; object-fit: contain; }
        .modal-product-name { margin: 0 0 4px; font-size: 14px; font-weight: 700; color: #0f172a; }
        .modal-product-price { margin: 0; font-size: 14px; font-weight: 800; color: #ef4444; }
        .qty-control { display: flex; align-items: center; justify-content: center; gap: 0; border: 1.5px solid #e2e8f0; border-radius: 10px; overflow: hidden; width: fit-content; margin: 0 auto; }
        .qty-control button { width: 40px; height: 40px; border: none; background: #f8fafc; font-size: 18px; font-weight: 700; color: #0f172a; cursor: pointer; }
        .qty-control button:hover { background: #e2e8f0; }
        .qty-control input { width: 56px; height: 40px; border: none; border-left: 1.5px solid #e2e8f0; border-right: 1.5px solid #e2e8f0; text-align: center; font-size: 15px; font-weight: 700; color: #0f172a; }
        .modal-field-label { display: block; font-size: 13px; font-weight: 700; color: #475569; margin-bottom: 8px; }
        .modal-actions { display: flex; gap: 10px; margin-top: 22px; }
        .modal-actions .btn-line, .modal-actions .btn-fill { flex: 1; padding: 12px 18px; border-radius: 12px; font-size: 14px; font-weight: 700; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; }
        .modal-actions .btn-line { background: #fff; border: 1.5px solid #e2e8f0; color: #0f172a; }
        .modal-actions .btn-line:hover { border-color: #4f46e5; color: #4f46e5; }
        .modal-actions .btn-fill { background: #4f46e5; border: none; color: #fff; }
        .modal-actions .btn-fill:hover { background: #4338ca; }
    </style>
</head>
<body>

    <header class="main-header">
        <div class="header-inner">
            <!-- LOGO -->
            <a href="index.php" class="logo-brand">
                <div class="logo_image">
                    <img src="../user/logo/logot2.png" alt="TheSecond">
                </div>
                <div class="logo-title" style="color: #0B63E5;">TheSecond</div>
            </a>

            <!-- SEARCH -->
     <form class="header-search" action="see_all_pd.php" method="GET">
    <i data-lucide="search"></i>
    <input type="text" name="q" placeholder="Tìm kiếm iPhone, Macbook..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" required>
    <button type="submit" style="display: none;"></button>
    </form>

            <!-- USER -->
            <div class="user-controls">
                <?php if ($isLoggedIn): ?>
                    <a href="profile.php" class="user-chip">
                        <div class="user-avatar">
                            <?php if (!empty($userAvatar)): ?>
                                <img src="<?= htmlspecialchars($userAvatar) ?>" alt="avatar"
                                    style="width:100%; height:100%; border-radius:50%; object-fit:cover;">
                            <?php else: ?>
                                <i data-lucide="user"></i>
                            <?php endif; ?>
                        </div>
                        <span><?= htmlspecialchars($userName) ?></span>
                    </a>
                <?php else: ?>
                    <a href="auth.html" class="nav-link login-link">
                        <i data-lucide="log-in"></i>
                        Đăng nhập
                    </a>
                    <a href="#" class="user-chip" onclick="alert('Bạn cần đăng nhập để xem hồ sơ'); return false;">
                        <div class="user-avatar">
                            <i data-lucide="user"></i>
                        </div>
                        <span>Hồ sơ của tôi</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- MENU -->
        <div class="header-nav-bar">
            <div class="header-nav-bar-inner">
                <nav class="main-nav">
                    <a href="index.php" class="nav-link">
                        <i data-lucide="home"></i>
                        Trang chủ
                    </a>
                    <a href="see_all_pd.php" class="nav-link active">
                        <i data-lucide="smartphone"></i>
                        Sản phẩm
                    </a>
                </nav>

                <!-- CART -->
                <div class="user-controls">
                    <?php if ($isLoggedIn): ?>
                        <a href="cart.php" class="nav-link">
                            <i data-lucide="shopping-cart"></i>
                            Giỏ hàng
                            <span id="cart-count" class="cart-count"><?= $cartCount ?? 0 ?></span>                        </a>
                    <?php else: ?>
                        <a href="#" class="nav-link" onclick="alert('Bạn cần đăng nhập để xem giỏ hàng'); return false;">
                            <i data-lucide="shopping-cart"></i>
                            Giỏ hàng
                            <span class="cart-count">0</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <main class="main-content">
        <nav class="pd-breadcrumb">
            <a href="index.php">Trang chủ</a> <span>/</span> 
            <a href="see_all_pd.php?category=<?= $product['category_id'] ?>"><?= htmlspecialchars($product['category_name'] ?? 'Khác') ?></a> <span>/</span> 
            <span class="current"><?= htmlspecialchars($product['name']) ?></span>
        </nav>

        <div class="detail-grid" style="margin-bottom: 40px; display: grid; grid-template-columns: 1fr 1fr; gap: 40px;">
            
            <!-- HÌNH ẢNH SẢN PHẨM -->
            <div id="product-images" class="detail-gallery">
                <?php if ($mainImage): ?>
                    <img src="<?= htmlspecialchars($mainImage) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                <?php else: ?>
                    <span class="emoji">📱</span>
                <?php endif; ?>
            </div>
            
            <!-- THÔNG TIN SẢN PHẨM -->
            <div class="detail-info">
                <h1 id="product-name" style="font-size: 28px; font-weight: 900; color: #0f172a; line-height: 1.3; margin-bottom: 12px;">
                    <?= htmlspecialchars($product['name']) ?> - Ngoại hình <?= $conditionLabel ?>
                </h1>
                <div style="margin-bottom: 24px; margin-top: 16px;">
                    <?php if ($discountPct > 0): ?>
                        <div style="background: #ef4444; color: #fff; display: inline-block; padding: 4px 12px; border-radius: 6px; font-weight: 700; font-size: 14px; margin-bottom: 8px;">
                            -<?= $discountPct ?>%
                        </div>
                    <?php endif; ?>
                    <div style="display: flex; align-items: baseline; gap: 12px;">
                        <span style="font-size: 32px; font-weight: 900; color: #ef4444;"><?= number_format($product['price'], 0, ',', '.') ?>đ</span>
                        <?php if (!empty($product['original_price']) && $product['original_price'] > $product['price']): ?>
                            <span style="font-size: 18px; color: #94a3b8; text-decoration: line-through;">
                                <?= number_format($product['original_price'], 0, ',', '.') ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- CHÍNH SÁCH BÁN HÀNG -->
                <div style="font-size: 15px; color: #334155; line-height: 1.8; margin-bottom: 32px;">
                    <div style="margin-bottom: 12px;">100% sản phẩm chính hãng</div>
                    <div style="margin-bottom: 12px;">Ship COD toàn quốc</div>
                    <div style="margin-bottom: 12px;">Bao test 1 tuần</div>
                    <div style="margin-bottom: 12px;">Freeship nội thành TP.HCM (dưới 15km) cho hoá đơn trên 1.000.000 đồng</div>
                    <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 12px;">
                        Bảo hành: <strong><?= !empty($product['warranty']) ? htmlspecialchars($product['warranty']) : '3 Tháng' ?></strong>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 2L15 4L18 3.5L19 6.5L22 8L20.5 11L22 14L19 15.5L18 18.5L15 18L12 20L9 18L6 18.5L5 15.5L2 14L3.5 11L2 8L5 6.5L6 3.5L9 4L12 2Z" fill="#0f172a"/>
                            <path d="M10 15L7 12L8.5 10.5L10 12L15.5 6.5L17 8L10 15Z" fill="white"/>
                        </svg>
                    </div>
                </div>
                
                <!-- TỒN KHO -->
                <div id="product-stock" style="margin-bottom: 16px; font-size: 13px; font-weight: 700; color: <?= $product['stock'] > 0 ? '#10b981' : '#ef4444' ?>;">
                    <?= $product['stock'] > 0 ? 'Còn hàng (' . $product['stock'] . ' sản phẩm)' : 'Hết hàng' ?>
                </div>
                
                <!-- NÚT MUA HÀNG -->
                <div class="action-buttons" style="display: flex; gap: 12px;">
                    <button
                        id="add-to-cart-btn"
                        type="button"
                        class="btn btn-outline btn-lg"
                        style="flex: 1; padding: 16px; font-weight: 800; border-radius: 12px; border: 1.5px solid #cbd5e1; background: white; cursor: <?= $product['stock'] <= 0 ? 'not-allowed' : 'pointer' ?>;"
                        <?= $product['stock'] <= 0 ? 'disabled' : '' ?>
                    >
                        <i data-lucide="shopping-cart"></i>
                        Thêm giỏ hàng
                    </button>
                    <button
                        id="buy-now-btn"
                        type="button"
                        class="btn btn-primary btn-lg"
                        style="flex: 1; padding: 16px; font-weight: 800; border-radius: 12px; background: #4f46e5; color: white; border: none; cursor: <?= $product['stock'] <= 0 ? 'not-allowed' : 'pointer' ?>;"
                        <?= $product['stock'] <= 0 ? 'disabled' : '' ?>
                    >
                        Mua ngay
                    </button>
                </div>
            </div>
        </div>

        <!-- TABS MÔ TẢ & THÔNG SỐ & REVIEW -->
        <div class="pd-tabs-wrapper">
            <div class="pd-tabs-nav" style="display: flex; gap: 24px; border-bottom: 2px solid #e2e8f0; margin-bottom: 24px;">
                <button class="pd-tab-btn active" onclick="switchTab('tab-desc', this)" style="padding: 12px 0; font-size: 15px; font-weight: 800; color: #4f46e5; border-bottom: 2px solid #4f46e5; background: transparent; border-top: none; border-left: none; border-right: none; cursor: pointer; transition: 0.3s;">Mô tả sản phẩm</button>
                
                <?php if (!empty($specifications)): ?>
                <button class="pd-tab-btn" onclick="switchTab('tab-specs', this)" style="padding: 12px 0; font-size: 15px; font-weight: 800; color: #94a3b8; border-bottom: 2px solid transparent; background: transparent; border-top: none; border-left: none; border-right: none; cursor: pointer; transition: 0.3s;">Thông số kỹ thuật</button>
                <?php endif; ?>
                
                <button class="pd-tab-btn" onclick="switchTab('tab-reviews', this)" style="padding: 12px 0; font-size: 15px; font-weight: 800; color: #94a3b8; border-bottom: 2px solid transparent; background: transparent; border-top: none; border-left: none; border-right: none; cursor: pointer; transition: 0.3s;">Đánh giá (<?= $reviewCount ?>)</button>
            </div>
            
            <!-- TAB MÔ TẢ -->
            <div class="pd-tab-content active" id="tab-desc" style="display: block;">
                <div id="product-description" style="line-height: 1.8; color: #475569;">
                    <?= nl2br(htmlspecialchars($product['description'] ?? 'Chưa có mô tả cho sản phẩm này.')) ?>
                </div>
            </div>

            <!-- TAB THÔNG SỐ KỸ THUẬT -->
            <?php if (!empty($specifications)): ?>
            <div class="pd-tab-content" id="tab-specs" style="display: none;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 16px; background: #fff; padding: 24px; border: 1px solid #e2e8f0; border-radius: 16px;">
                    <?php foreach ($specifications as $key => $val): ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 16px 20px; background: #f8fafc; border-radius: 12px;">
                        <span style="color: #64748b; font-size: 14px; font-weight: 500;"><?= htmlspecialchars($key) ?></span>
                        <span style="color: #0f172a; font-size: 14px; font-weight: 700; text-align: right;"><?= htmlspecialchars($val) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- TAB ĐÁNH GIÁ (REVIEW) -->
            <div class="pd-tab-content" id="tab-reviews" style="display: none;">
                <div style="background: #fff; padding: 24px; border: 1px solid #e2e8f0; border-radius: 16px;">
                    <!-- Tổng quan sao -->
                    <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 24px;">
                        <div style="font-size: 48px; font-weight: 900; color: #0f172a;"><?= number_format($avgRating, 1) ?></div>
                        <div>
                            <div style="color: #f59e0b; font-size: 20px; letter-spacing: 2px;">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <?= $i <= round($avgRating) ? '★' : '☆' ?>
                                <?php endfor; ?>
                            </div>
                            <div style="color: #64748b; font-size: 14px; margin-top: 4px;"><?= $reviewCount ?> đánh giá</div>
                        </div>
                    </div>
                    
                    <!-- FORM VIẾT ĐÁNH GIÁ -->
                    <?php if ($isLoggedIn): ?>
                    <form action="review.php" method="POST" style="margin-bottom: 28px; padding: 20px; background: #f8fafc; border-radius: 12px;">
                        <input type="hidden" name="product_id" value="<?= $product_id ?>">
                        <div style="margin-bottom: 12px;">
                            <label style="font-weight: 700; font-size: 14px; color: #0f172a; display: block; margin-bottom: 6px;">Đánh giá của bạn</label>
                            <select name="rating" required style="padding: 8px 12px; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 14px;">
                                <option value="5">5 ★ - Tuyệt vời</option>
                                <option value="4">4 ★ - Tốt</option>
                                <option value="3">3 ★ - Bình thường</option>
                                <option value="2">2 ★ - Không hài lòng</option>
                                <option value="1">1 ★ - Rất tệ</option>
                            </select>
                        </div>
                        <div style="margin-bottom: 12px;">
                            <textarea name="comment" rows="3" required placeholder="Chia sẻ cảm nhận của bạn về sản phẩm..." style="width: 100%; padding: 10px 12px; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 14px; font-family: inherit; resize: vertical;"></textarea>
                        </div>
                        <button type="submit" style="padding: 10px 20px; border-radius: 8px; background: #4f46e5; color: #fff; border: none; font-weight: 700; font-size: 14px; cursor: pointer;">Gửi đánh giá</button>
                    </form>
                    <?php else: ?>
                    <div style="margin-bottom: 28px; padding: 16px; background: #f8fafc; border-radius: 12px; text-align: center; color: #64748b; font-size: 14px;">
                        <a href="auth.html" style="color: #4f46e5; font-weight: 700;">Đăng nhập</a> để viết đánh giá cho sản phẩm này.
                    </div>
                    <?php endif; ?>

                    <!-- Danh sách đánh giá -->
                    <?php if (empty($reviews)): ?>
                        <div style="text-align: center; padding: 40px 0; color: #94a3b8;">
                            <span style="font-size: 40px; display: block; margin-bottom: 12px;">💬</span>
                            <p>Chưa có đánh giá nào cho sản phẩm này.</p>
                        </div>
                    <?php else: ?>
                        <div style="display: flex; flex-direction: column; gap: 20px;">
                            <?php foreach ($reviews as $rev): ?>
                                <div style="border-bottom: 1px solid #f1f5f9; padding-bottom: 20px;">
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                        <strong style="color: #0f172a; font-size: 15px;"><?= htmlspecialchars($rev['full_name'] ?? 'Người dùng ẩn danh') ?></strong>
                                        <span style="color: #94a3b8; font-size: 13px;"><?= date('d/m/Y', strtotime($rev['created_at'])) ?></span>
                                    </div>
                                    <div style="color: #f59e0b; font-size: 14px; margin-bottom: 12px;">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <?= $i <= $rev['rating'] ? '★' : '☆' ?>
                                        <?php endfor; ?>
                                    </div>
                                    <p style="color: #475569; font-size: 14px; line-height: 1.6; margin: 0;"><?= htmlspecialchars($rev['comment']) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </main>

    <footer class="main-footer" style="background: #0f172a; color: #94a3b8; padding: 40px 20px; text-align: center; margin-top: 60px;">
        <div style="max-width: 1280px; margin: 0 auto;">
            <h2 style="color: #fff;">THESECOND</h2>
            <p>Nền tảng mua bán đồ điện tử cũ an toàn nhất.</p>
        </div>
    </footer>

    <!-- MODAL: Yêu cầu đăng nhập (dùng chung cho cả 2 nút) -->
    <div class="modal" id="loginRequiredModal">
        <div class="modal-box">
            <h3 id="loginRequiredTitle">Yêu cầu đăng nhập</h3>
            <p id="loginRequiredMsg">Vui lòng đăng nhập để tiếp tục.</p>
            <div class="modal-actions">
                <button type="button" class="btn-line" onclick="closeModal('loginRequiredModal')">Để sau</button>
                <a href="auth.html" class="btn-fill">Đăng nhập ngay</a>
            </div>
        </div>
    </div>

    <!-- MODAL: Thêm vào giỏ hàng thành công -->
    <div class="modal" id="addToCartModal">
        <div class="modal-box">
            <div class="modal-icon">✓</div>
            <h3>Đã thêm vào giỏ hàng!</h3>
            <div class="modal-product">
                <div class="modal-product-thumb">
                    <?php if ($mainImage): ?>
                        <img src="<?= htmlspecialchars($mainImage) ?>" alt="">
                    <?php else: ?>📱<?php endif; ?>
                </div>
                <div>
                    <p class="modal-product-name"><?= htmlspecialchars($product['name']) ?></p>
                    <p class="modal-product-price"><?= number_format($product['price'], 0, ',', '.') ?>đ</p>
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-line" onclick="closeModal('addToCartModal')">Tiếp tục xem</button>
                <a href="cart.php" class="btn-fill">Xem giỏ hàng</a>
            </div>
        </div>
    </div>

    <!-- MODAL: Mua ngay -->
    <div class="modal" id="buyNowModal">
        <div class="modal-box">
            <h3>Xác nhận mua hàng</h3>
            <div class="modal-product">
                <div class="modal-product-thumb">
                    <?php if ($mainImage): ?>
                        <img src="<?= htmlspecialchars($mainImage) ?>" alt="">
                    <?php else: ?>📱<?php endif; ?>
                </div>
                <div>
                    <p class="modal-product-name"><?= htmlspecialchars($product['name']) ?></p>
                    <p class="modal-product-price"><?= number_format($product['price'], 0, ',', '.') ?>đ</p>
                </div>
            </div>
            <label class="modal-field-label">Số lượng</label>
            <div class="qty-control">
                <button type="button" onclick="changeBuyQty(-1)">−</button>
                <input type="text" id="buyNowQty" value="1" readonly>
                <button type="button" onclick="changeBuyQty(1)">+</button>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-line" onclick="closeModal('buyNowModal')">Hủy</button>
                <button type="button" class="btn-fill" onclick="confirmBuyNow()">Tiến hành thanh toán</button>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();

        function switchTab(tabId, btn) {
            // Ẩn tất cả nội dung tab
            document.getElementById('tab-desc').style.display = 'none';
            if (document.getElementById('tab-specs')) {
                document.getElementById('tab-specs').style.display = 'none';
            }
            if (document.getElementById('tab-reviews')) {
                document.getElementById('tab-reviews').style.display = 'none';
            }
            
            // Hiển thị tab được chọn
            document.getElementById(tabId).style.display = 'block';
            
            // Đặt lại style màu xám/mờ cho tất cả nút
            document.querySelectorAll('.pd-tab-btn').forEach(b => {
                b.style.color = '#94a3b8';
                b.style.borderBottomColor = 'transparent';
            });
            
            // Làm nổi bật nút đang chọn bằng màu chủ đạo
            btn.style.color = '#4f46e5';
            btn.style.borderBottomColor = '#4f46e5';
        }
        // xử lý giỏ hàng
        const isLoggedIn = <?= $isLoggedIn ? 'true' : 'false' ?>;
        const productId = <?= (int)$product_id ?>;
        const maxStock = <?= (int)$product['stock'] ?>;

        function openModal(id) { document.getElementById(id).classList.add('show'); }
        function closeModal(id) { document.getElementById(id).classList.remove('show'); }

        // Đóng modal khi bấm ra ngoài vùng nội dung
        document.querySelectorAll('.modal').forEach(function (m) {
            m.addEventListener('click', function (e) {
                if (e.target === m) closeModal(m.id);
            });
        });

        function requireLogin(message) {
            document.getElementById('loginRequiredMsg').textContent = message;
            openModal('loginRequiredModal');
        }

        document.getElementById('add-to-cart-btn').addEventListener('click', async function () {

            if (!isLoggedIn) {
                requireLogin('Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng!');
                return;
            }
            try {
                const response = await fetch('/TheSecondV1/api/cart/add.php', {
                    method: 'POST',
                    credentials: 'include',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'product_id=' + productId
                });
                const data = await response.json();
                if (!data.success) {
                    alert(data.message);
                    return;
                }
                // Cập nhật số trên HTML
                document.getElementById('cart-count').textContent = data.cart_count;
                // Mở modal xác nhận thay vì alert()
                openModal('addToCartModal');
            } catch (error) {
                console.error(error);
                alert('Không thể thêm sản phẩm vào giỏ hàng.');
            }
        });

        // mua ngay
        document.getElementById('buy-now-btn').addEventListener('click', function () {
            if (!isLoggedIn) {
                requireLogin('Vui lòng đăng nhập để mua sản phẩm!');
                return;
            }
            document.getElementById('buyNowQty').value = 1;
            openModal('buyNowModal');
        });

        function changeBuyQty(delta) {
            const input = document.getElementById('buyNowQty');
            let val = parseInt(input.value, 10) + delta;
            if (val < 1) val = 1;
            if (maxStock > 0 && val > maxStock) val = maxStock;
            input.value = val;
        }

        function confirmBuyNow() {
            const qty = document.getElementById('buyNowQty').value;
            window.location.href = 'checkout.php?product_id=' + productId + '&quantity=' + qty;
        }

    </script>
</body>
</html>