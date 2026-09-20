<?php
session_start();
require_once __DIR__ . '/../api/config/database.php';

function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function money($v) { return number_format((float)$v, 0, ',', '.') . ' ₫'; }
function q($pdo, $sql, $p) { try { $s = $pdo->prepare($sql); $s->execute($p); return $s->fetchAll(PDO::FETCH_ASSOC); } catch (Throwable $ex) { return []; } }
function go($msg) { echo "<script>alert('$msg');location.href='../user/login/auth.html';</script>"; exit; }

// ===== Đăng nhập =====
if (!isset($_SESSION['user_id'])) go('Vui lòng đăng nhập để xem hồ sơ!');
$uid = (int)$_SESSION['user_id'];
$st = $pdo->prepare("SELECT * FROM users WHERE id = ? AND status = 'active'");
$st->execute([$uid]);
$user = $st->fetch(PDO::FETCH_ASSOC);
if (!$user) { session_destroy(); go('Tài khoản không hợp lệ hoặc đã bị khóa!'); }
unset($user['password'], $user['password_hash']);
$_SESSION['csrf'] ??= bin2hex(random_bytes(16));



// ===== Cập nhật hồ sơ (AJAX) =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_profile') {
    header('Content-Type: application/json; charset=utf-8');
    $out = fn($ok, $msg) => exit(json_encode(['ok' => $ok, 'msg' => $msg], JSON_UNESCAPED_UNICODE));
    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) $out(false, 'Phiên làm việc không hợp lệ.');
    $name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $dob = trim($_POST['date_of_birth'] ?? '');
    $email = trim($_POST['email'] ?? '');
    if ($name === '' || mb_strlen($name) > 100) $out(false, 'Họ tên không hợp lệ.');
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $out(false, 'Email không hợp lệ.');
    if ($phone !== '' && !preg_match('/^[0-9+\s]{8,15}$/', $phone)) $out(false, 'Số điện thoại không hợp lệ.');
    if ($dob !== '' && !strtotime($dob)) $out(false, 'Ngày sinh không hợp lệ.');
    if (strcasecmp($email, $user['email']) !== 0) {
        $stmtEmail = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmtEmail->execute([$email, $uid]);
        if ($stmtEmail->fetch()) $out(false, 'Email này đã được sử dụng bởi tài khoản khác.');
    }
    $fields = ['full_name' => $name, 'email' => $email, 'phone' => $phone, 'date_of_birth' => $dob ?: null];
    $fields = array_filter($fields, fn($k) => array_key_exists($k, $user), ARRAY_FILTER_USE_KEY); // chỉ cập nhật cột có tồn tại
    if ($fields) {
        $set = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($fields)));
        $pdo->prepare("UPDATE users SET $set WHERE id = :id")->execute($fields + ['id' => $uid]);
    }

    // Địa chỉ lưu ở bảng addresses (users không có cột address)
    $addrInput = trim($_POST['address'] ?? '');
    if ($addrInput !== '') {
        $ex = $pdo->prepare("SELECT id FROM addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC LIMIT 1");
        $ex->execute([$uid]);
        $addrId = $ex->fetchColumn();
        if ($addrId) {
            $pdo->prepare("UPDATE addresses SET address_line = ? WHERE id = ?")->execute([$addrInput, $addrId]);
        } else {
            $pdo->prepare("INSERT INTO addresses (user_id, receiver_name, receiver_phone, address_line, city, is_default) VALUES (?, ?, ?, ?, '', 1)")
                ->execute([$uid, $name, $phone ?: '', $addrInput]);
        }
    }
    $out(true, 'Cập nhật thông tin thành công!');
}

$addrRows = q($pdo, "SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC LIMIT 1", [$uid]);
$address  = $addrRows[0] ?? null;
// ===== Dữ liệu các tab =====
$imgJoin = "LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1";
$cartItems = q($pdo, "SELECT ci.quantity, ci.price, p.name, pi.image_url FROM cart_items ci
    INNER JOIN carts c ON ci.cart_id = c.id INNER JOIN products p ON ci.product_id = p.id $imgJoin
    WHERE c.user_id = ? ORDER BY ci.created_at DESC", [$uid]);
$orders   = q($pdo, "SELECT o.id, o.order_code, o.total_amount, o.order_status AS status, o.created_at,
    (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) AS item_count
    FROM orders o WHERE o.user_id = ? ORDER BY o.created_at DESC", [$uid]);
$wishlist = q($pdo, "SELECT p.name, pi.image_url FROM wishlists w INNER JOIN products p ON w.product_id = p.id $imgJoin WHERE w.user_id = ?", [$uid]);
$reviews  = q($pdo, "SELECT r.rating, r.comment, r.created_at, p.name FROM reviews r INNER JOIN products p ON r.product_id = p.id WHERE r.user_id = ? ORDER BY r.created_at DESC", [$uid]);

$cartCount = count($cartItems);
$cartTotal = array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $cartItems));
// Chỉ tính đơn đã hoàn thành (delivered)
$completedOrders = array_filter($orders, fn($o) => ($o['status'] ?? '') === 'delivered');
$orderCount = count($completedOrders);
$totalSpent = array_sum(array_map(fn($o) => (float)$o['total_amount'], $completedOrders));
$name = $user['full_name'];
$initial = mb_strtoupper(mb_substr($name, 0, 1));
$avatar = $user['avatar'] ?? '';
$dobVal = !empty($user['date_of_birth']) ? date('Y-m-d', strtotime($user['date_of_birth'])) : '';
$fmt = fn($d) => $d ? date('d/m/Y', strtotime($d)) : '—';

// Địa chỉ mặc định lấy từ bảng addresses (users không có cột address)
$addressLine = $address['address_line'] ?? '';
$addressParts = array_filter([
    $addressLine,
    $address['ward'] ?? '',
    $address['district'] ?? '',
    $address['city'] ?? '',
]);
$addressText = $addressParts ? implode(', ', $addressParts) : '—';

$info = [
    'Họ và tên' => $name,
    'Email' => $user['email'],
    'Số điện thoại' => $user['phone'] ?: '—',
    'Ngày sinh' => $fmt($user['date_of_birth'] ?? null),
    'Địa chỉ' => $addressText,
    'Thành viên từ' => $fmt($user['created_at'] ?? null),
];
$tabs = [
    'info' => ['user', 'Thông tin của bạn'], 'cart' => ['shopping-cart', 'Giỏ hàng'], 'orders' => ['package', 'Đơn mua'],
    'wishlist' => ['heart', 'Wishlist'], 'reviews' => ['star', 'Đánh giá của tôi'],
];

$orderStatusMap = [
    'pending'    => ['Chờ xác nhận', 'tag-pending'],
    'confirmed'  => ['Đã xác nhận', 'tag-info'],
    'processing' => ['Đang xử lý', 'tag-info'],
    'shipping'   => ['Đang giao', 'tag-info'],
    'delivered'  => ['Đã giao', 'tag-success'],
    'cancelled'  => ['Đã hủy', 'tag-danger'],
    'returned'   => ['Đã trả hàng', 'tag-danger'],
];
function avatarHtml($avatar, $initial) {
    return $avatar ? '<img class="avatar" src="' . e($avatar) . '" alt="">' : '<span class="avatar">' . e($initial) . '</span>';
}
function productImgSrc($img) {
    if (!$img) return null;
    // product_images.image_url lưu dạng tên file thô (vd "uploads/x.jpg"),
    // cần thêm tiền tố thư mục thật thì ảnh mới hiển thị được.
    if (str_starts_with($img, 'http') || str_starts_with($img, '../')) return $img;
    return '../api/admin/product/' . $img;
}
function itemRow($img, $title, $sub, $right = '') {
    $src = productImgSrc($img);
    return '<div class="row-item"><div class="thumb">' . ($src ? '<img src="' . e($src) . '" alt="">' : '📱') . '</div>'
        . '<div class="grow"><b>' . e($title) . '</b><small>' . $sub . '</small></div><div class="right">' . $right . '</div></div>';
}
function empty_state($msg) { return '<p class="empty">' . $msg . '</p>'; }
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Hồ Sơ Cá Nhân – TheSecond</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<script src="https://unpkg.com/lucide@latest"></script>
<link rel="stylesheet" href="../assets/css/styles.css">
<style>
    body { background: #f1f5f9; }

    /* ===== PROFILE HEADER ===== */
    .profile-header-card {
        grid-column: 1 / -1;
        display: flex;
        align-items: center;
        gap: 24px;
        padding: 32px;
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%);
        border-radius: 20px;
        color: #fff;
        position: relative;
        overflow: hidden;
    }
    .profile-avatar-large {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        font-weight: 800;
        color: #fff;
        flex-shrink: 0;
        box-shadow: 0 4px 20px rgba(99, 102, 241, 0.4);
        z-index: 1;
        object-fit: cover;
    }
    .profile-header-info { z-index: 1; flex: 1; }
    .profile-header-info > div > span { font-size: 24px; font-weight: 800; }
    .profile-header-info p { font-size: 14px; color: #94a3b8; margin: 4px 0 0; }
    .profile-header-info .cart-item {
        margin-top: 12px;
        padding: 8px 16px;
        background: rgba(255, 255, 255, 0.08);
        border-radius: 10px;
    }
    .profile-header-info .cart-item h3 { font-size: 14px; margin: 0 0 4px; }
    .profile-header-info .cart-item p { margin: 2px 0; font-size: 13px; }
    .profile-header-info .stat-boxes {
        display: flex;
        gap: 12px;
        margin-top: 14px;
        flex-wrap: wrap;
    }
    .stat-box {
        background: rgba(255, 255, 255, 0.08);
        border-radius: 12px;
        padding: 10px 20px;
        text-align: center;
        min-width: 96px;
    }
    .stat-box .stat-num {
        font-size: 20px;
        font-weight: 800;
        color: #fff;
        line-height: 1.2;
    }
    .stat-box .stat-label {
        font-size: 11px;
        font-weight: 700;
        color: #94a3b8;
        letter-spacing: .4px;
        text-transform: uppercase;
        margin-top: 2px;
    }

    /* ===== LAYOUT ===== */
    .pf-page {
        width: 100%;
        box-sizing: border-box;
        max-width: 1100px;
        margin: 0 auto;
        padding: 32px 20px 60px;
        display: grid;
        grid-template-columns: 1fr;
        gap: 24px;
        align-items: start;
    }
    .pf-main {
        background: #fff;
        border-radius: 20px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
        padding: 32px 36px;
        min-height: 260px;
        min-width: 0;
    }
    .pf-main h2 {
        font-size: 20px;
        font-weight: 800;
        color: #0f172a;
        margin: 0;
    }
    .pf-head {
        display: flex;
        align-items: center;
        margin: 0 0 24px;
    }
    .pf-foot {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 12px;
        padding-top: 20px;
        border-top: 1px solid #e2e8f0;
    }
    .info-actions {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 10px;
        margin-left: auto;
    }
    .info-grid.edit-grid dd input,
    .info-grid.edit-grid dd textarea,
    .info-grid dd .edit-control {
        display: none;
        width: 100%;
        box-sizing: border-box;
        background: #f8fafc;
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        padding: 10px 12px;
        font: 15px Inter, sans-serif;
        color: #0f172a;
        outline: none;
    }
    .info-grid dd textarea.edit-control { resize: vertical; min-height: 72px; }
    .is-editing .view-value { display: none; }
    .is-editing .info-grid dd .edit-control { display: block; }
    .is-editing .info-grid dd input.edit-control:focus,
    .is-editing .info-grid dd textarea.edit-control:focus {
        background: #fff;
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12);
    }
    .is-editing .info-grid dd input.edit-control[readonly] {
        background: #e2e8f0;
        color: #64748b;
        cursor: not-allowed;
    }
    .is-editing #editProfileBtn { display: none; }
    #cancelEditBtn, #saveProfileBtn { display: none; }
    .is-editing #cancelEditBtn, .is-editing #saveProfileBtn { display: inline-flex; align-items: center; }
    .tab-pane { display: none; animation: fadeIn .25s ease; }
    .tab-pane.active { display: block; }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: none; }
    }

    /* ===== THÔNG TIN CÁ NHÂN ===== */
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 28px 40px;
        margin: 0 0 32px;
    }
    .info-grid dt {
        font-size: 12px;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: .3px;
        margin-bottom: 6px;
    }
    .info-grid dd {
        margin: 0;
        font-size: 15px;
        color: #0f172a;
        overflow-wrap: anywhere;
    }
    .info-grid dd .view-value {
        display: block;
        width: 100%;
        box-sizing: border-box;
        background: #f8fafc;
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        padding: 10px 12px;
        font: 15px Inter, sans-serif;
        color: #0f172a;
        min-height: 20px;
        white-space: pre-wrap;
    }
    .btn-line {
        padding: 11px 22px;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        font: 500 14px Inter, sans-serif;
        color: #0f172a;
        cursor: pointer;
        transition: .2s;
    }
    .btn-line:hover { border-color: #6366f1; color: #4f46e5; }
    .btn-fill {
        padding: 11px 22px;
        background: #4f46e5;
        border: 0;
        border-radius: 12px;
        font: 600 14px Inter, sans-serif;
        color: #fff;
        cursor: pointer;
    }
    .btn-fill.danger { background: #ef4444; }

    /* ===== DANH SÁCH ===== */
    .row-item {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 16px 0;
        border-bottom: 1px solid #f1f5f9;
    }
    .row-item:last-child { border: 0; }
    .thumb {
        width: 56px;
        height: 56px;
        border-radius: 12px;
        background: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        overflow: hidden;
        flex-shrink: 0;
    }
    .thumb img { width: 100%; height: 100%; object-fit: cover; }
    .grow { flex: 1; min-width: 0; }
    .grow b { display: block; font-size: 15px; color: #0f172a; }
    .grow small { color: #64748b; font-size: 13px; }
    .right { text-align: right; font-weight: 800; color: #ef4444; }
    .tag {
        display: inline-block;
        background: #fef3c7;
        color: #b45309;
        padding: 3px 10px;
        border-radius: 8px;
        font-size: 11px;
        font-weight: 800;
        margin-top: 4px;
    }
    .tag.tag-pending { background: #fef3c7; color: #b45309; }
    .tag.tag-info { background: #dbeafe; color: #1d4ed8; }
    .tag.tag-success { background: #dcfce7; color: #16a34a; }
    .tag.tag-danger { background: #fee2e2; color: #dc2626; }
    .total-bar {
        display: flex;
        justify-content: space-between;
        padding-top: 18px;
        margin-top: 6px;
        border-top: 2px solid #e2e8f0;
        font-weight: 800;
        color: #0f172a;
    }
    .empty { text-align: center; color: #94a3b8; padding: 40px 0; }
    .stars { color: #f59e0b; letter-spacing: 2px; }

    /* ===== MODAL ===== */
    .modal {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.55);
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
        z-index: 1000;
    }
    .modal.show { display: flex; }
    .modal-box {
        background: #fff;
        border-radius: 20px;
        width: 100%;
        max-width: 520px;
        padding: 28px;
        animation: fadeIn .2s ease;
        max-height: 90vh;
        overflow: auto;
    }
    .modal-box h3 {
        margin: 0 0 18px;
        font-size: 18px;
        font-weight: 800;
        color: #0f172a;
    }
    .modal-box p { color: #64748b; margin: 0 0 20px; }
    .field { margin-bottom: 14px; }
    .field label {
        display: block;
        font-size: 13px;
        font-weight: 700;
        color: #475569;
        margin-bottom: 6px;
    }
    .field input,
    .field textarea {
        width: 100%;
        box-sizing: border-box;
        background: #f8fafc;
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        padding: 11px 14px;
        font: 14px Inter, sans-serif;
        outline: none;
    }
    .field input:focus,
    .field textarea:focus {
        background: #fff;
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12);
    }
    .field input[readonly] {
        background: #e2e8f0;
        color: #64748b;
        cursor: not-allowed;
    }
    .modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 20px;
    }
    .form-msg {
        font-size: 13px;
        margin-top: 10px;
        min-height: 0;
        color: #ef4444;
        display: none;
    }
    .is-editing .form-msg { display: block; min-height: 18px; }
</style>
</head>
<body>
<header class="main-header">
    <div class="header-inner">
        <a href="index.php" class="logo-brand">
        <div class="logo_image">
            <img src="../user/logo/logot2.png" alt="TheSecond">
        </div><div class="logo-title">TheSecond</div></a>
<form class="header-search" action="see_all_pd.php" method="GET">
    <i data-lucide="search"></i>
    <input type="text" name="q" placeholder="Tìm kiếm iPhone, Macbook..." value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q'], ENT_QUOTES, 'UTF-8') : '' ?>">
    <button type="submit" style="display: none;"></button>
</form>
        <div class="user-controls">
            <div class="user-menu" id="userMenu">
                <button type="button" class="user-chip" id="userBtn"><?= avatarHtml($avatar, $initial) ?><span><?= e($name) ?></span><i data-lucide="chevron-down"></i></button>
                <div class="user-dropdown"><div class="user-dropdown-inner">
                    <div class="dd-head"><b><?= e($name) ?></b><small><?= e($user['email']) ?></small></div>
                    <?php foreach ($tabs as $k => [$icon, $label]): ?>
                        <button type="button" class="dd-item" data-tab="<?= $k ?>"><i data-lucide="<?= $icon ?>"></i><?= e($label) ?>
                            <?php if ($k === 'cart' && $cartCount): ?><span class="badge"><?= $cartCount ?></span><?php endif; ?></button>
                    <?php endforeach; ?>
                    <button type="button" class="dd-item danger" data-modal="logoutModal"><i data-lucide="log-out"></i>Đăng xuất</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="header-nav-bar"><div class="header-nav-bar-inner"><nav class="main-nav">
        <a href="index.php" class="nav-link"><i data-lucide="home"></i> Trang chủ</a>
        <a href="see_all_pd.php" class="nav-link"><i data-lucide="smartphone"></i> Sản phẩm</a>
    </nav></div></div>
</header>

<div class="pf-page">
    <!-- PROFILE HEADER -->
    <div class="profile-header-card">
        <?php if ($avatar): ?><img class="profile-avatar-large" src="<?= e($avatar) ?>" alt=""><?php else: ?><div class="profile-avatar-large"><?= e($initial) ?></div><?php endif; ?>
        <div class="profile-header-info">
            <div><span><?= e($name) ?></span></div>
            <div><p><span><?= e($user['email']) ?></span></p></div>
            <div class="stat-boxes">
                <div class="stat-box">
                    <div class="stat-num"><?= $orderCount ?></div>
                    <div class="stat-label">Đơn hàng</div>
                </div>
                <div class="stat-box">
                    <div class="stat-num"><?= money($totalSpent) ?></div>
                    <div class="stat-label">Tổng chi tiêu</div>
                </div>
            </div>
        </div>
        <div id="roleActions" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;justify-content:flex-end">
            <button type="button" data-modal="logoutModal" class="btn btn-outline" style="background:rgba(255,255,255,.1);color:#fff;border:1px solid rgba(255,255,255,.2)">Đăng xuất</button>
        </div>
    </div>

    <!-- NỘI DUNG -->
     <main class="pf-main">
        <section class="tab-pane active" id="tab-info">
            <form id="editForm">
                <input type="hidden" name="action" value="update_profile">
                <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
                <div class="pf-head">
                    <h2>Thông tin cá nhân</h2>
                </div>
                <dl class="info-grid">
                    <div>
                        <dt>Họ và tên</dt>
                        <dd>
                            <span class="view-value"><?= e($name) ?></span>
                            <input class="edit-control" name="full_name" type="text" value="<?= e($name) ?>" required maxlength="100">
                        </dd>
                    </div>
                    <div>
                        <dt>Email</dt>
                        <dd>
                            <span class="view-value"><?= e($user['email']) ?></span>
                            <input class="edit-control" name="email" type="email" value="<?= e($user['email']) ?>" required maxlength="150">
                        </dd>
                    </div>
                    <div>
                        <dt>Số điện thoại</dt>
                        <dd>
                            <span class="view-value"><?= e($user['phone'] ?: '—') ?></span>
                            <input class="edit-control" name="phone" type="tel" value="<?= e($user['phone']) ?>" maxlength="15">
                        </dd>
                    </div>
                    <?php if (array_key_exists('date_of_birth', $user)): ?>
                    <div>
                        <dt>Ngày sinh</dt>
                        <dd>
                            <span class="view-value"><?= e($fmt($user['date_of_birth'] ?? null)) ?></span>
                            <input class="edit-control" name="date_of_birth" type="date" value="<?= e($dobVal) ?>">
                        </dd>
                    </div>
                    <?php endif; ?>
                    <div>
                        <dt>Địa chỉ</dt>
                        <dd>
                            <span class="view-value"><?= e($addressText) ?></span>
                            <textarea class="edit-control" name="address" rows="3"><?= e($addressLine) ?></textarea>
                        </dd>
                    </div>
                    <div>
                        <dt>Thành viên từ</dt>
                        <dd><span class="view-value"><?= e($fmt($user['created_at'] ?? null)) ?></span></dd>
                    </div>
                </dl>
                <div class="pf-foot">
                    <div class="form-msg" id="formMsg"></div>
                    <div class="info-actions">
                        <button type="button" class="btn-line" id="editProfileBtn">Chỉnh sửa hồ sơ</button>
                        <button type="button" class="btn-line" id="cancelEditBtn">Hủy</button>
                        <button type="submit" class="btn-fill" id="saveProfileBtn">Lưu</button>
                    </div>
                </div>
            </form>
        </section>

        <section class="tab-pane" id="tab-cart">
            <h2>Giỏ hàng (<?= $cartCount ?>)</h2>
            <?php if (!$cartItems) echo empty_state('Giỏ hàng của bạn đang trống.');
            foreach ($cartItems as $i) echo itemRow($i['image_url'], $i['name'], 'Số lượng: ' . (int)$i['quantity'] . ' × ' . money($i['price']), money($i['price'] * $i['quantity'])); ?>
            <?php if ($cartItems): ?>
                <div class="total-bar"><span>Tổng cộng</span><span><?= money($cartTotal) ?></span></div>
                <div style="margin-top:20px; text-align:right;"><a href="cart.php" class="btn-fill" style="text-decoration:none; display:inline-flex; align-items:center; gap:6px;">Xem giỏ hàng đầy đủ <i data-lucide="arrow-right" style="width:16px;height:16px;"></i></a></div>
            <?php endif; ?>
        </section>

        <section class="tab-pane" id="tab-orders">
            <h2>Đơn mua của tôi</h2>
            <?php if (!$orders) echo empty_state('Bạn chưa có đơn hàng nào.');
            foreach ($orders as $o):
                $code = $o['order_code'] ?: ('DH' . $o['id']);
                [$statusLabel, $statusClass] = $orderStatusMap[$o['status']] ?? [$o['status'], 'tag-pending'];
                $sub = (int)$o['item_count'] . ' sản phẩm · Ngày đặt: ' . $fmt($o['created_at']);
                $right = money($o['total_amount']) . '<br><span class="tag ' . $statusClass . '">' . e($statusLabel) . '</span>';
                echo itemRow('', 'Mã đơn: #' . e($code), $sub, $right);
            endforeach; ?>
        </section>

        <section class="tab-pane" id="tab-wishlist">
            <h2>Wishlist</h2>
            <?php if (!$wishlist) echo empty_state('Bạn chưa lưu sản phẩm yêu thích nào.');
            foreach ($wishlist as $w) echo itemRow($w['image_url'], $w['name'], 'Sản phẩm yêu thích'); ?>
        </section>

        <section class="tab-pane" id="tab-reviews">
            <h2>Đánh giá</h2>
            <?php if (!$reviews) echo empty_state('Bạn chưa đánh giá sản phẩm nào.');
            foreach ($reviews as $r) echo itemRow('', $r['name'], '<span class="stars">' . str_repeat('★', (int)$r['rating']) . str_repeat('☆', 5 - (int)$r['rating']) . '</span> · ' . $fmt($r['created_at']) . '<br>' . e($r['comment'])); ?>
        </section>
    </main>
</div>

<!-- MODAL: ĐĂNG XUẤT -->
<div class="modal" id="logoutModal"><div class="modal-box" style="max-width:400px">
    <h3>Đăng xuất?</h3><p>Bạn có chắc chắn muốn đăng xuất khỏi tài khoản?</p>
    <div class="modal-actions"><button type="button" class="btn-line" data-close>Ở lại</button><button type="button" class="btn-fill danger" id="confirmLogout">Đăng xuất</button></div>
</div></div>

<footer class="main-footer" style="background:#0f172a;color:#94a3b8;padding:40px 20px;text-align:center">
    <h2 style="color:#e2e8f0;font-size:24px;font-weight:900">THESECOND</h2>
    <p>Nền tảng mua bán đồ điện tử cũ an toàn nhất.</p>
</footer>

<script src="../assets/js/script.js"></script>
<script>
lucide.createIcons();
const $ = (s, r = document) => r.querySelector(s), $$ = (s, r = document) => [...r.querySelectorAll(s)];
const menu = $('#userMenu');

// Dropdown avatar: hover (CSS) + click (mobile)
$('#userBtn').onclick = e => { e.stopPropagation(); menu.classList.toggle('open'); };
document.addEventListener('click', e => { if (!menu.contains(e.target)) menu.classList.remove('open'); });

// Tabs (sidebar + dropdown dùng chung data-tab)
function setEditMode(on) {
    const pane = $('#tab-info');
    if (pane) pane.classList.toggle('is-editing', on);
    const msg = $('#formMsg');
    if (msg && !on) { msg.textContent = ''; }
}

function showTab(id) {
    const isEdit = id === 'editprofile';
    let paneId = isEdit ? 'info' : (id || 'info');
    if (!$('#tab-' + paneId)) paneId = 'info';
    $$('.tab-pane').forEach(p => p.classList.toggle('active', p.id === 'tab-' + paneId));
    $$('.dd-item[data-tab]').forEach(b => b.classList.toggle('active', b.dataset.tab === paneId));
    setEditMode(isEdit && paneId === 'info');
    history.replaceState(null, '', '#' + (isEdit && paneId === 'info' ? 'editprofile' : paneId));
    menu.classList.remove('open');
}
$$('[data-tab]').forEach(b => b.onclick = () => showTab(b.dataset.tab));
showTab(location.hash.slice(1) || 'info');
window.addEventListener('hashchange', () => showTab(location.hash.slice(1) || 'info'));

// Modal
const openModal = id => { $('#' + id).classList.add('show'); menu.classList.remove('open'); };
const closeModals = () => $$('.modal').forEach(m => m.classList.remove('show'));
$$('[data-modal]').forEach(b => b.onclick = () => openModal(b.dataset.modal));
$$('[data-close]').forEach(b => b.onclick = closeModals);
$$('.modal').forEach(m => m.addEventListener('click', e => { if (e.target === m) closeModals(); }));
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModals(); });

const editBtn = $('#editProfileBtn');
if (editBtn) editBtn.onclick = () => showTab('editprofile');
const cancelEditBtn = $('#cancelEditBtn');
if (cancelEditBtn) cancelEditBtn.onclick = () => {
    const form = $('#editForm');
    if (form) form.reset();
    showTab('info');
};

const editForm = $('#editForm');
if (editForm) {
    editForm.onsubmit = async e => {
        e.preventDefault();
        const msg = $('#formMsg');
        if (msg) { msg.style.color = '#ef4444'; msg.textContent = ''; }
        try {
            const res = await fetch('profile.php', { method: 'POST', body: new FormData(e.target) });
            const data = await res.json();
            if (msg) {
                msg.textContent = data.msg;
                if (data.ok) {
                    msg.style.color = '#16a34a';
                    setTimeout(() => { location.hash = 'info'; location.reload(); }, 700);
                }
            }
        } catch {
            if (msg) msg.textContent = 'Có lỗi xảy ra, vui lòng thử lại.';
        }
    };
}

// Đăng xuất
$('#confirmLogout').onclick = () => typeof logout === 'function' ? logout() : (location.href = '../api/auth/logout.php');
</script>
</body>
</html>