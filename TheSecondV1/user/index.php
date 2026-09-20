<?php


session_start();
require_once __DIR__ . '/../api/config/database.php';

// Kiểm tra đăng nhập
$isLoggedIn = isset($_SESSION['user_id']);
$userName   = $_SESSION['user_name'] ?? 'Người dùng';
$userAvatar =$_SESSION['user_avatar'] ?? null;

$cartCount = 0;

if ($isLoggedIn) {

    $stmtCart = $pdo->prepare("
        SELECT COALESCE(SUM(ci.quantity), 0)
        FROM carts c
        LEFT JOIN cart_items ci
            ON ci.cart_id = c.id
        WHERE c.user_id = ?
    ");

    $stmtCart->execute([
        $_SESSION['user_id']
    ]);

    $cartCount = (int)$stmtCart->fetchColumn();
}

$price = isset($_GET['price']) ? $_GET['price'] : '';

// Xác định ORDER BY
$orderBy = 'p.id DESC'; 

if ($price === 'tang') {
    $orderBy = 'p.price ASC';
} elseif ($price === 'giam') {
    $orderBy = 'p.price DESC';
}

// Lấy sản phẩm đang bán
$sql = "
    SELECT 
        p.id,
        p.name,
        p.price,
        p.condition_type,
        c.name AS category_name,
        pi.image_url
    FROM products p
    LEFT JOIN categories c 
        ON p.category_id = c.id
    LEFT JOIN product_images pi 
        ON p.id = pi.product_id 
        AND pi.is_primary = 1
    WHERE p.status = 'available'
    ORDER BY $orderBy
    LIMIT 12
";

$products = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);


// ===============================
// MAP TÌNH TRẠNG
// ===============================

$nhanTinhTrang = [
    'like_new'  => '~99%',
    'excellent' => '~95%',
    'good'      => '~85%',
    'fair'      => '~70%',
    'poor'      => 'Khác'
];



?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TheSecond – Mua bán đồ công nghệ cũ uy tín</title>

    <!-- Font chữ & Icon -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>

    <link rel="stylesheet" href="../assets/css/styles.css">


<style>

    .product-image-wrap {
        width: 100%;
        height: 210px;
        background: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        cursor: pointer;
        position: relative;
    }

    .product-image-wrap img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        padding: 18px;
        transition: transform 0.3s ease;
    }

    .product-card:hover .product-image-wrap img {
        transform: scale(1.04);
    }

    .product-card-body {
        padding: 18px;
        display: flex;
        flex-direction: column;
        flex: 1;
    }

    .product-category {
        display: inline-flex;
        padding: 5px 9px;
        border-radius: 7px;
        background: #eef2ff;
        color: #4f46e5;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        margin-bottom: 8px;
    }

    .product-name {
        font-size: 15px;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.45;
        margin: 0 0 10px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 43px;
    }

    .product-condition {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 14px;
        font-size: 12px;
        color: #64748b;
        font-weight: 600;
    }

    .product-condition span {
        background: #f0fdf4;
        color: #16a34a;
        padding: 3px 7px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 800;
    }

    .product-price-row {
        display: flex;
        align-items: baseline;
        gap: 8px;
        margin-top: auto;
        margin-bottom: 16px;
        padding-top: 14px;
        border-top: 1px solid #f1f5f9;
    }

    .product-price {
        font-size: 17px;
        font-weight: 900;
        color: #ef4444;
    }

    .product-detail-btn {
        width: 100%;
        border: 1px solid #c7d2fe;
        background: #eef2ff;
        color: #4f46e5;
        padding: 10px 14px;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 800;
        transition: all 0.2s;
    }

    .product-detail-btn:hover {
        background: #4f46e5;
        color: #fff;
        border-color: #4f46e5;
    }

    /* Cart count badge */
    .cart-count {
        background: #ef4444;
        color: #fff;
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 10px;
        font-weight: 900;
        margin-left: 4px;
    }

    /* Empty / Error state */
    .product-empty,
    .product-error {
        grid-column: 1 / -1;
        padding: 50px 20px;
        text-align: center;
        color: #64748b;
    }
</style>
</head>

<body>

    <!-- ================= HEADER & NAVIGATION ================= -->
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
                <a href="index.php" class="nav-link active">
                    <i data-lucide="home"></i>
                    Trang chủ
                </a>
                <a href="see_all_pd.php" class="nav-link">
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
                        <span class="cart-count"><?= $cartCount ?? 0 ?></span>
                    </a>
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


    <!-- ================= MAIN CONTENT ================= -->
    <main class="main-content">

        <!-- CAROUSEL / HERO BANNER -->
        <section class="carousel">

            <div class="carousel-content">

                <span class="carousel-tag">
                    🔥 Flash Sale Cuối Tuần
                </span>

                <h1 class="carousel-name">
                    Lên đời Macbook M2<br>
                    Tiết kiệm đến 30%
                </h1>

                <p
                    style="
                        margin-bottom: 24px;
                        color: rgba(255,255,255,0.8);
                        font-size: 14px;
                    "
                >
                    Hàng Like New 99% - Bảo hành 12 tháng 1 đổi 1.
                    Số lượng có hạn!
                </p>

                <div class="carousel-prices">

                    <span class="carousel-deal-price">
                        21.500.000 ₫
                    </span>

                    <a
                        href="product_detail.html"
                        style="
                            background: #fff;
                            color: #1e1b4b;
                            padding: 10px 20px;
                            border-radius: 12px;
                            font-weight: 800;
                            font-size: 14px;
                            margin-left: 16px;
                        "
                    >
                        Mua Ngay
                    </a>

                </div>

            </div>

            <div class="carousel-visual">
                💻
            </div>

        </section>


        <!-- DANH MỤC NỔI BẬT -->
        <section>

            <div class="section-header">

                <div>

                    <p class="section-sub">
                        Tìm kiếm nhanh
                    </p>

                    <h2 class="section-title">
                        Danh mục nổi bật
                    </h2>

                </div>

            </div>

            <div class="mini-card-grid">

                <a href="#products" class="mini-card">

                    <div class="mini-card-emoji">
                        📱
                    </div>

                    <div>

                        <strong>
                            Điện thoại cũ
                        </strong>

                        <p>
                            124 sản phẩm
                        </p>

                    </div>

                </a>


                <a href="#products" class="mini-card">

                    <div class="mini-card-emoji">
                        💻
                    </div>

                    <div>

                        <strong>
                            Laptop / PC
                        </strong>

                        <p>
                            86 sản phẩm
                        </p>

                    </div>

                </a>


                <a href="#products" class="mini-card">

                    <div class="mini-card-emoji">
                        🎧
                    </div>

                    <div>

                        <strong>
                            Âm thanh
                        </strong>

                        <p>
                            54 sản phẩm
                        </p>

                    </div>

                </a>


                <a href="#products" class="mini-card">

                    <div class="mini-card-emoji">
                        ⌚
                    </div>

                    <div>

                        <strong>
                            Smartwatch
                        </strong>

                        <p>
                            32 sản phẩm
                        </p>

                    </div>

                </a>

            </div>

        </section>


        <!-- DANH SÁCH SẢN PHẨM -->
        <section id="products">

            <div class="section-header">

                <div>

                    <p class="section-sub">
                        THESECOND MARKET
                    </p>

                    <h2 class="section-title">
                        Sản phẩm nổi bật
                    </h2>

                    <p
                        style="
                            margin-top: 8px;
                            font-size: 13px;
                            color: #64748b;
                        "
                    >
                        Những thiết bị đã qua sử dụng được kiểm tra
                        trước khi đến tay bạn.
                    </p>

                </div>

                <!-- Bộ lọc cơ bản -->

                <div style="display: flex; gap: 8px;">

                    <form method="GET" action="">
                        <select
                            style="
                                padding: 8px 16px;
                                border: 1px solid #cbd5e1;
                                border-radius: 8px;
                                font-size: 13px;
                                outline: none;
                                background: #fff;
                            "
                            name="price"
                            onchange="this.form.submit()"
                        >
                            <option value=""
                                <?= $price === '' ? 'selected' : '' ?>>
                                Mặc định
                            </option>
                            <option value="tang"
                                <?= $price === 'tang' ? 'selected' : '' ?>>
                                Giá thấp đến cao
                            </option>
                            <option value="giam"
                                <?= $price === 'giam' ? 'selected' : '' ?>>
                                Giá cao đến thấp
                            </option>
                        </select>
                    </form>
                </div>
            </div>
            <!-- DANH SÁCH SẢN PHẨM ĐỘNG -->
        <div class="product-grid">
        <?php if (empty($products)): ?>
            <div class="product-empty" style="grid-column: 1 / -1;">
                <h3>Chưa có sản phẩm</h3>
                <p>Hiện tại chưa có sản phẩm nào.</p>
            </div>
        <?php else: ?>
            <?php foreach ($products as $sp): ?>
                <div class="product-card">
                    <a href="product_detail.php?id=<?= $sp['id'] ?>" class="product-image-wrap">
                        <?php if (!empty($sp['image_url'])): ?>
                            <img src="<?= htmlspecialchars('../api/admin/product/'.$sp['image_url']) ?>"
                                         alt="<?= htmlspecialchars($sp['name']) ?>"loading="lazy">
                        <?php else: ?>
                            <div style="font-size:48px">📱</div>
                        <?php endif; ?>
                    </a>

                    <div class="product-card-body">
                        <span class="product-category">
                            <?= htmlspecialchars($sp['category_name'] ?? 'Khác') ?>
                        </span>

                        <h3 class="product-name">
                            <?= htmlspecialchars($sp['name']) ?>
                        </h3>

                        <div class="product-condition">
                            Tình trạng: 
                            <span><?= $nhanTinhTrang[$sp['condition_type']] ?? $sp['condition_type'] ?></span>
                        </div>

                        <div class="product-price-row">
                            <span class="product-price">
                                <?= number_format($sp['price'], 0, ',', '.') ?> ₫
                            </span>
                            <?php if (!empty($sp['original_price'])): ?>
                                <span class="product-original-price">
                                    <s><?= number_format($sp['original_price'], 0, ',', '.') ?> ₫</s>
                                </span>
                            <?php endif; ?>
                        </div>

                        <a href="product_detail.php?id=<?= $sp['id'] ?>" class="product-detail-btn">
                            Xem chi tiết
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        </div>
            <!-- Xem thêm -->
            <div
                style="text-align: center;"
                onclick="prod_detail(4)"
            >
                <a href="see_all_pd.php" class > Xem thêm sản phẩm</a>
            </div>
        </section>
    </main>

    <footer
        style="
            background: #0f172a;
            color: #94a3b8;
            padding: 40px 20px;
            margin-top: 60px;
        "
    >
        <div
            style="
                max-width: 1280px;
                margin: 0 auto;
                text-align: center;
            "
        >
            <div
                style="
                    font-size: 24px;
                    font-weight: 900;
                    color: #e2e8f0;
                    margin-bottom: 12px;
                "
            >
                THESECOND
            </div>

            <p
                style="
                    font-size: 13px;
                    margin-bottom: 24px;
                "
            >
                Nền tảng mua bán đồ điện tử cũ an toàn,
                minh bạch và tiết kiệm nhất.
            </p>

            <div
                style="
                    display: flex;
                    justify-content: center;
                    gap: 20px;
                    font-size: 13px;
                    font-weight: 700;
                "
            >

                <a
                    href="#"
                    style="color: #cbd5e1;"
                >
                </a>

                <a
                    href="#"
                    style="color: #cbd5e1;"
                >
                    Hướng dẫn mua hàng
                </a>

                <a
                    href="#"
                    style="color: #cbd5e1;"
                >
                    Liên hệ hỗ trợ
                </a>

            </div>

            <div
                style="
                    margin-top: 30px;
                    font-size: 12px;
                    border-top: 1px solid #1e293b;
                    padding-top: 20px;
                "
            >
                © 2026 TheSecond - Lập Trình Web [012012103104].
            </div>

        </div>

    </footer>

    <script src="../assets/js/script.js"></script>

    <script>
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    </script>

</body>
</html>