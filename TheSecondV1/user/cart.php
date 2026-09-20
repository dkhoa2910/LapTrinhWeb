<?php
session_start();

require_once __DIR__ . '/../api/config/database.php';

$isLoggedIn = isset($_SESSION['user_id']);

if (!$isLoggedIn) {
    header('Location: auth.html');
    exit;
}

$user_id = (int)$_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT *
    FROM users
    WHERE id = ?
");

$stmt->execute([$user_id]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header('Location: auth.html');
    exit;
}

$userName = $user['full_name'] ?? $user['name'] ?? 'Tài khoản';
$userAvatar = $user['avatar'] ?? '';

$stmt = $pdo->prepare("SELECT id FROM carts WHERE user_id = ?");
$stmt->execute([$user_id]);
$cart = $stmt->fetch(PDO::FETCH_ASSOC);

$cartCount = 0;

if ($cart) {
    $cart_id = $cart['id'];
} else {
    $stmt = $pdo->prepare("INSERT INTO carts (user_id) VALUES (?)");
    $stmt->execute([$user_id]);
    $cart_id = $pdo->lastInsertId();
}

$stmt = $pdo->prepare("
    SELECT ci.id AS cart_item_id, ci.quantity,
           p.id AS product_id, p.name, p.price, p.stock,
           (SELECT image_url FROM product_images
            WHERE product_id = p.id
            ORDER BY is_primary DESC, sort_order ASC LIMIT 1) AS image_url
    FROM cart_items ci
    JOIN products p ON p.id = ci.product_id
    WHERE ci.cart_id = ?
");


$stmt->execute([$cart_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$cart_subtotal = 0;
foreach ($cart_items as $item) {
    $cart_subtotal += $item['price'] * $item['quantity'];
}
$cart_total = $cart_subtotal;
$cart_count = count($cart_items);
$cartCount = $cart_count;
?>





<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ Hàng – TheSecond</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="../assets/css/styles.css">

    <style>
        /* Bổ sung một chút CSS cấu trúc cho Giỏ hàng */
        .cart-page { padding: 40px 20px; }
        .cart-grid { display: grid; grid-template-columns: 1fr 380px; gap: 24px; }
        .cart-items { display: flex; flex-direction: column; gap: 16px; }
        .cart-item-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 20px; display: flex; gap: 20px; align-items: center; }
        .cart-item-thumb { width: 90px; height: 90px; background: #f8fafc; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 40px; flex-shrink: 0; }
        .cart-item-info { flex: 1; }
        .cart-item-brand { font-size: 11px; font-weight: 800; color: #4f46e5; text-transform: uppercase; margin-bottom: 4px; }
        .cart-item-name { font-size: 16px; font-weight: 800; color: #0f172a; margin-bottom: 8px; }
        .cart-item-price { font-size: 16px; font-weight: 900; color: #ef4444; }
        .qty-controls { display: inline-flex; align-items: center; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; background: #fff; }
        .qty-btn { width: 32px; height: 32px; background: none; border: none; font-size: 16px; font-weight: 600; color: #475569; cursor: pointer; transition: background 0.2s; }
        .qty-btn:hover { background: #f1f5f9; }
        .qty-input { width: 40px; border: none; border-left: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; text-align: center; font-size: 14px; font-weight: 700; outline: none; }
        
        .cart-summary { background: #fff; border: 1px solid #e2e8f0; border-radius: 20px; padding: 24px; position: sticky; top: 100px; }
        .summary-title { font-size: 18px; font-weight: 900; color: #0f172a; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid #f1f5f9; }
        .summary-row { display: flex; justify-content: space-between; font-size: 14px; font-weight: 600; color: #64748b; margin-bottom: 16px; }
        .summary-row.total { font-size: 18px; font-weight: 900; color: #0f172a; margin-top: 20px; padding-top: 20px; border-top: 1px solid #f1f5f9; }

        @media (max-width: 880px) { .cart-grid { grid-template-columns: 1fr; } }
        @media (max-width: 480px) { .cart-item-card { flex-direction: column; align-items: flex-start; } .cart-item-thumb { width: 100%; height: 120px; } }
    </style>
</head>
<body>

    <!-- HEADER -->
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

    <!-- MAIN CONTENT -->
    <main class="main-content cart-page">
        <h1 class="section-title" style="margin-bottom: 32px;">Giỏ hàng của bạn</h1>
        
        <div class="cart-grid">
            <!-- Danh sách sản phẩm -->
<!-- chỉnh sửa ngày 9/9 0h42 -->
            <div id="cart-items" class="cart-items">
                <?php if (!$cart_items): ?>
                    <div class="cart-item-card" style="justify-content:center; text-align:center; flex-direction:column; gap:10px; padding:48px 20px;">
                        <div style="font-size:40px;">🛒</div>
                        <h3 style="margin:0; color:#0f172a;">Giỏ hàng đang trống</h3>
                        <p style="margin:0; color:#64748b; font-size:14px;">Hãy khám phá các sản phẩm điện tử đã qua sử dụng.</p>
                        <a href="see_all_pd.php" class="btn btn-outline" style="margin-top:8px;">Tiếp tục mua sắm</a>
                    </div>
                <?php else: foreach ($cart_items as $item): ?>
                    <?php $itemImg = !empty($item['image_url']) ? '../api/admin/product/' . $item['image_url'] : null; ?>
                    <div class="cart-item-card" data-cart-item-id="<?= (int)$item['cart_item_id'] ?>">
                        <div class="cart-item-thumb">
                            <?php if ($itemImg): ?>
                                <img src="<?= htmlspecialchars($itemImg) ?>" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:12px;">
                            <?php else: ?>📱<?php endif; ?>
                        </div>
                        <div class="cart-item-info">
                            <div class="cart-item-name"><?= htmlspecialchars($item['name']) ?></div>
                            <div class="cart-item-price"><?= number_format($item['price'], 0, ',', '.') ?> ₫</div>
                        </div>
                        <div class="qty-controls">
                            <button type="button" class="qty-btn" onclick="changeCartQuantity(<?= (int)$item['cart_item_id'] ?>, <?= (int)$item['quantity'] - 1 ?>)">−</button>
                            <input type="text" class="qty-input" value="<?= (int)$item['quantity'] ?>" readonly>
                            <button type="button" class="qty-btn" onclick="changeCartQuantity(<?= (int)$item['cart_item_id'] ?>, <?= (int)$item['quantity'] + 1 ?>)">+</button>
                        </div>
                        <div style="font-weight:900; color:#0f172a; min-width:110px; text-align:right;">
                            <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?> ₫
                        </div>
                        <button type="button" onclick="removeCartItem(<?= (int)$item['cart_item_id'] ?>)" title="Xóa" style="background:none; border:none; color:#ef4444; cursor:pointer; padding:6px;">
                            <i data-lucide="trash-2"></i>
                        </button>
                    </div>
                <?php endforeach; endif; ?>
            </div>
<!-- chỉnh sửa ngày 9/9 0h42 -->
            <!-- Tóm tắt -->
            <div>
                <div class="cart-summary">
                    <h2 class="summary-title">Tóm tắt đơn hàng</h2>
                    
<!-- /chỉnh sửa ngày 9/9 0h43 -->
                    <div class="summary-row">
                        <span>Tạm tính</span>
                        <span id="cart-subtotal" style="color: #0f172a;"><?= number_format($cart_subtotal, 0, ',', '.') ?> ₫</span>
                    </div>

                    <div class="summary-row">
                        <span>Phí vận chuyển</span>
                        <span style="color: #10b981;">Miễn phí</span>
                    </div>

                    <div class="summary-row total">
                        <span>Tổng thanh toán</span>
                        <span id="cart-total" style="color: #ef4444; font-size: 24px;"><?= number_format($cart_total, 0, ',', '.') ?> ₫</span>
                    </div>
<!-- chỉnh sửa ngày 9/9 0h43/ -->                    
                    <button class="btn btn-primary btn-block btn-lg" style="margin-top: 24px;" onclick="window.location.href='checkout.php'" <?= $cart_items ? '' : 'disabled' ?>>
                        Tiến hành thanh toán <i data-lucide="arrow-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </main>

    <!-- FOOTER -->
    <footer class="main-footer">
        <div style="max-width: 1280px; margin: 0 auto; text-align: center;">
            <h2 style="color: #e2e8f0; font-size: 24px; font-weight: 900;">THESECOND</h2>
            <p style="font-size: 13px;">Nền tảng mua bán đồ điện tử cũ an toàn nhất.</p>
        </div>
    </footer>

    <script src="../assets/js/script.js"></script>
</body>
</html>