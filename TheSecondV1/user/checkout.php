<?php
session_start();
require_once __DIR__ . '/../api/config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: auth.html');
    exit;
}
$user_id = $_SESSION['user_id'];
$_SESSION['csrf'] ??= bin2hex(random_bytes(16));

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| Đặt hàng (AJAX) — trước đây nút "Hoàn tất đặt hàng" chỉ alert() demo,
| không lưu gì vào CSDL. Giờ xử lý thật: tạo order + order_items, trừ kho,
| tạo payment, xóa giỏ hàng — toàn bộ trong 1 transaction.
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'place_order') {
    header('Content-Type: application/json; charset=utf-8');
    $respond = fn($ok, $msg, $extra = []) => exit(json_encode(['success' => $ok, 'message' => $msg] + $extra, JSON_UNESCAPED_UNICODE));

    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        $respond(false, 'Phiên làm việc đã hết hạn, vui lòng tải lại trang.');
    }

    $receiverName  = trim($_POST['receiver_name'] ?? '');
    $receiverPhone = trim($_POST['receiver_phone'] ?? '');
    $addressLine   = trim($_POST['address_line'] ?? '');
    $paymentMethod = $_POST['payment_method'] ?? 'cod';
    if (!in_array($paymentMethod, ['cod', 'bank_transfer', 'momo', 'vnpay'], true)) $paymentMethod = 'cod';

    if ($receiverName === '' || $receiverPhone === '' || $addressLine === '') {
        $respond(false, 'Vui lòng nhập đầy đủ thông tin giao hàng.');
    }
    if (!preg_match('/^[0-9+\s]{8,15}$/', $receiverPhone)) {
        $respond(false, 'Số điện thoại không hợp lệ.');
    }

    try {
        $pdo->beginTransaction();

        // Lấy lại giỏ hàng mới nhất từ CSDL (không tin dữ liệu phía client),
        // khóa các dòng sản phẩm liên quan để tránh bán vượt tồn kho khi có nhiều người đặt cùng lúc.
        $stmt = $pdo->prepare("SELECT id FROM carts WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $cartRow = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$cartRow) { $pdo->rollBack(); $respond(false, 'Giỏ hàng trống.'); }

        $stmt = $pdo->prepare("
            SELECT ci.id AS cart_item_id, ci.quantity,
                   p.id AS product_id, p.name, p.sku, p.price, p.stock, p.status
            FROM cart_items ci
            JOIN products p ON p.id = ci.product_id
            WHERE ci.cart_id = ?
            FOR UPDATE
        ");
        $stmt->execute([$cartRow['id']]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$items) { $pdo->rollBack(); $respond(false, 'Giỏ hàng trống.'); }

        foreach ($items as $it) {
            if ($it['status'] !== 'available') {
                $pdo->rollBack();
                $respond(false, 'Sản phẩm "' . $it['name'] . '" hiện không có sẵn để bán.');
            }
            if ((int)$it['stock'] < (int)$it['quantity']) {
                $pdo->rollBack();
                $respond(false, 'Sản phẩm "' . $it['name'] . '" không đủ tồn kho.');
            }
        }

        $subtotal = 0;
        foreach ($items as $it) $subtotal += $it['price'] * $it['quantity'];
        $shippingFeeNow = 0;
        $discountNow = 0;
        $totalNow = $subtotal + $shippingFeeNow - $discountNow;

        $orderCode = 'DH' . date('YmdHis') . strtoupper(bin2hex(random_bytes(3)));

        $pdo->prepare("
            INSERT INTO orders
                (user_id, order_code, receiver_name, receiver_phone, shipping_address,
                 subtotal, shipping_fee, discount, total_amount, payment_method, payment_status, order_status, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'pending', NOW(), NOW())
        ")->execute([
            $user_id, $orderCode, $receiverName, $receiverPhone, $addressLine,
            $subtotal, $shippingFeeNow, $discountNow, $totalNow, $paymentMethod
        ]);
        $orderId = (int)$pdo->lastInsertId();

        $insItem  = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, sku, quantity, unit_price, subtotal) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $updStock = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");

        foreach ($items as $it) {
            $insItem->execute([$orderId, $it['product_id'], $it['name'], $it['sku'], $it['quantity'], $it['price'], $it['price'] * $it['quantity']]);
            $updStock->execute([$it['quantity'], $it['product_id']]);
        }

        $pdo->prepare("INSERT INTO payments (order_id, payment_method, amount, status, created_at, updated_at) VALUES (?, ?, ?, 'pending', NOW(), NOW())")
            ->execute([$orderId, $paymentMethod, $totalNow]);

        // Xóa giỏ hàng sau khi đặt thành công
        $pdo->prepare("DELETE FROM cart_items WHERE cart_id = ?")->execute([$cartRow['id']]);

        $pdo->commit();

        $respond(true, 'Đặt hàng thành công!', ['order_code' => $orderCode, 'redirect' => 'profile.php#donhang']);

    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $respond(false, 'Có lỗi xảy ra khi đặt hàng, vui lòng thử lại.');
    }
}

$stmt = $pdo->prepare("SELECT * FROM addresses WHERE user_id = ? AND is_default = 1 LIMIT 1");
$stmt->execute([$user_id]);
$default_address = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT id FROM carts WHERE user_id = ?");
$stmt->execute([$user_id]);
$cart = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cart) {
    header('Location: cart.php');
    exit;
}
$cart_id = $cart['id'];

$stmt = $pdo->prepare("
    SELECT ci.quantity, p.id AS product_id, p.name, p.price, p.sku,
           (SELECT image_url FROM product_images
            WHERE product_id = p.id
            ORDER BY is_primary DESC, sort_order ASC LIMIT 1) AS image_url
    FROM cart_items ci
    JOIN products p ON p.id = ci.product_id
    WHERE ci.cart_id = ?
");
$stmt->execute([$cart_id]);
$checkout_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($checkout_items)) {
    header('Location: cart.php');
    exit;
}

$checkout_subtotal = 0;
foreach ($checkout_items as $item) {
    $checkout_subtotal += $item['price'] * $item['quantity'];
}
$shipping_fee = 0;
$checkout_total = $checkout_subtotal + $shipping_fee;
$so_luong_sp = count($checkout_items);
$cart_count = count($checkout_items); // số sản phẩm trong giỏ, hiện ở badge header

// Thông tin tài khoản ngân hàng nhận chuyển khoản của shop
// (thay bằng thông tin ngân hàng thật khi triển khai chính thức)
$bank_name = 'Ngân hàng TMCP Ngoại Thương Việt Nam (Vietcombank)';
$bank_account_no = '0123456789';
$bank_account_name = 'CONG TY TNHH THESECOND';
$bank_transfer_note = 'TS' . $user_id . strtoupper(substr(uniqid(), -6));
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh Toán – TheSecond</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="../assets/css/styles.css">

    <style>
        .checkout-steps { display: flex; justify-content: center; align-items: center; gap: 12px; margin-bottom: 40px; margin-top: 20px; }
        .step-item { font-size: 13px; font-weight: 700; color: #94a3b8; display: flex; align-items: center; gap: 8px; }
        .step-item.active { color: #4f46e5; }
        .step-item.done { color: #10b981; }
        .step-icon { width: 24px; height: 24px; border-radius: 50%; background: #f1f5f9; display: flex; align-items: center; justify-content: center; font-size: 11px; }
        .step-item.active .step-icon { background: #4f46e5; color: #fff; }
        .step-item.done .step-icon { background: #10b981; color: #fff; }
        .step-divider { width: 60px; height: 2px; background: #e2e8f0; }

        .form-section { background: #fff; border: 1px solid #e2e8f0; border-radius: 20px; padding: 32px; margin-bottom: 24px; }
        .form-section-title { font-size: 18px; font-weight: 800; color: #0f172a; margin-bottom: 24px; display: flex; align-items: center; gap: 10px; }
        .form-group { margin-bottom: 16px; }
        .form-label { display: block; font-size: 12px; font-weight: 700; color: #475569; margin-bottom: 8px; text-transform: uppercase; }
        .form-control { width: 100%; padding: 12px 16px; border: 1.5px solid #cbd5e1; border-radius: 12px; font-size: 14px; outline: none; transition: 0.2s; }
        .form-control:focus { border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79,70,229,0.1); }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

        .pay-options { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .pay-option { border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 16px; cursor: pointer; display: flex; flex-direction: column; align-items: center; gap: 8px; text-align: center; transition: all .2s; }
        .pay-option input { display: none; }
        .pay-option:has(input:checked) { border-color: #4f46e5; background: #eef2ff; }
        .pay-icon { font-size: 24px; }

        .badge { display: inline-flex; align-items: center; justify-content: center; min-width: 18px; height: 18px; padding: 0 5px; border-radius: 999px; background: #ef4444; color: #fff; font-size: 11px; font-weight: 800; margin-left: 4px; }

        .bank-info-box { display: none; margin-top: 20px; background: #f8fafc; border: 1.5px dashed #cbd5e1; border-radius: 14px; padding: 18px 20px; }
        .bank-info-box.show { display: block; }
        .bank-info-row { display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 8px 0; border-bottom: 1px solid #e2e8f0; }
        .bank-info-row:last-of-type { border-bottom: none; }
        .bank-info-label { font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; }
        .bank-info-value { font-size: 14px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px; }
        .bank-copy-btn { font-size: 11px; font-weight: 700; color: #4f46e5; background: #eef2ff; border: none; border-radius: 6px; padding: 4px 8px; cursor: pointer; }
        .bank-copy-btn:hover { background: #e0e7ff; }
        .bank-note { margin-top: 14px; font-size: 12px; color: #f59e0b; font-weight: 600; background: #fffbeb; border-radius: 10px; padding: 10px 14px; }

        .order-summary { background: #fff; border: 1px solid #e2e8f0; border-radius: 20px; padding: 24px; position: sticky; top: 100px; }
        .summary-item { display: flex; gap: 12px; margin-bottom: 16px; padding-bottom: 16px; border-bottom: 1px solid #f1f5f9; }
        .summary-item-thumb { width: 50px; height: 50px; background: #f8fafc; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 24px; flex-shrink: 0;}
        .summary-item-info h4 { font-size: 13px; font-weight: 700; color: #0f172a; margin-bottom: 4px; }
        .summary-item-info p { font-size: 12px; color: #ef4444; font-weight: 800; }
        
        .summary-calc { display: flex; justify-content: space-between; font-size: 14px; font-weight: 600; color: #64748b; margin-bottom: 12px; }
        .summary-calc.total { font-size: 18px; font-weight: 900; color: #0f172a; margin-top: 16px; padding-top: 16px; border-top: 1px solid #f1f5f9; }

        @media (max-width: 880px) { .cart-grid { grid-template-columns: 1fr; } }
        @media (max-width: 600px) { .form-row { grid-template-columns: 1fr; } .pay-options { grid-template-columns: 1fr; } .step-divider { width: 30px; } }
    </style>
</head>
<body>

    <header class="main-header">
        <div class="header-inner">
            <a href="index.php" class="logo-brand">
                <div class="logo_image">
                    <img src="../user/logo/logot2.png" alt="TheSecond">
                </div>
                <div class="logo-title" style="color: #0B63E5;">TheSecond</div>
            </a>
            <form class="header-search" action="see_all_pd.php" method="GET">
                <button type="submit" style="background:none;border:none;padding:0;display:flex;align-items:center;cursor:pointer;">
                    <i data-lucide="search"></i>
                </button>
                <input type="text" name="search" placeholder="Tìm kiếm iPhone, Macbook...">
            </form>
            <div class="user-controls">
                <a href="profile.php" class="user-chip">
                    <div class="user-avatar">
                        <?php if (!empty($user['avatar'])): ?>
                            <img src="<?= htmlspecialchars($user['avatar']) ?>" alt="avatar" style="width:100%; height:100%; border-radius:50%; object-fit:cover;">
                        <?php else: ?>
                            <i data-lucide="user"></i>
                        <?php endif; ?>
                    </div>
                    <span><?= htmlspecialchars($user['full_name'] ?? 'Hồ sơ của tôi') ?></span>
                </a>
            </div>
        </div>
        <div class="header-nav-bar">
            <div class="header-nav-bar-inner">
                <nav class="main-nav">
                    <a href="index.php" class="nav-link"><i data-lucide="home"></i> Trang chủ</a>
                    <a href="see_all_pd.php" class="nav-link"><i data-lucide="smartphone"></i> Sản phẩm</a>
                </nav>
                <div class="user-controls">
                    <a href="cart.php" class="nav-link">
                        <i data-lucide="shopping-cart"></i> Giỏ hàng <span class="badge"><?= $cart_count ?></span>
                    </a>
                </div>
            </div>
        </div>
    </header>


    <main class="main-content">
        <!-- Progress Steps -->
        <div class="checkout-steps">
            <div class="step-item done"><div class="step-icon">✓</div> Giỏ hàng</div>
            <div class="step-divider"></div>
            <div class="step-item active"><div class="step-icon">2</div> Thanh toán</div>
            <div class="step-divider"></div>
            <div class="step-item"><div class="step-icon">3</div> Hoàn tất</div>
        </div>
 
        <div class="cart-grid" style="display: grid; grid-template-columns: 1fr 380px; gap: 24px;">
            <!-- Cột trái: Form nhập liệu -->
            <div>
                <form action="profile.php" onsubmit="return false;" id="checkoutForm">
                    <input type="hidden" id="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
                    <div class="form-section">
                        <h2 class="form-section-title"><i data-lucide="map-pin"></i> Thông tin giao hàng</h2>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Họ và tên người nhận</label>
                                <input type="text" class="form-control" id="receiver_name" value="<?= htmlspecialchars($default_address['receiver_name'] ?? $user['full_name']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Số điện thoại</label>
                                <input type="tel" class="form-control" id="receiver_phone" value="<?= htmlspecialchars($default_address['receiver_phone'] ?? $user['phone']) ?>" required>
                            </div>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Địa chỉ chi tiết</label>
                            <input type="text" class="form-control" id="address_line" value="<?= htmlspecialchars($default_address['address_line'] ?? '') ?>" required>
                        </div>
                    </div>
 
                    <div class="form-section">
                        <h2 class="form-section-title"><i data-lucide="credit-card"></i> Phương thức thanh toán</h2>
                        <div class="pay-options">
                            <label class="pay-option">
                                <input type="radio" name="payment" value="cod" checked onchange="togglePaymentInfo()">
                                <div class="pay-icon">💵</div>
                                <div style="font-size: 13px; font-weight: 700; color: #1e293b;">Thanh toán khi nhận hàng (COD)</div>
                            </label>
                            <label class="pay-option">
                                <input type="radio" name="payment" value="bank_transfer" onchange="togglePaymentInfo()">
                                <div class="pay-icon">🏦</div>
                                <div style="font-size: 13px; font-weight: 700; color: #1e293b;">Chuyển khoản Ngân hàng</div>
                            </label>
                        </div>
 
                        <!-- Thông tin chuyển khoản, chỉ hiện khi chọn "Chuyển khoản Ngân hàng" -->
                        <div class="bank-info-box" id="bank-info-box">
                            <div class="bank-info-row">
                                <span class="bank-info-label">Ngân hàng</span>
                                <span class="bank-info-value"><?= htmlspecialchars($bank_name) ?></span>
                            </div>
                            <div class="bank-info-row">
                                <span class="bank-info-label">Số tài khoản</span>
                                <span class="bank-info-value">
                                    <?= htmlspecialchars($bank_account_no) ?>
                                    <button type="button" class="bank-copy-btn" onclick="copyText('<?= htmlspecialchars($bank_account_no) ?>', this)">Copy</button>
                                </span>
                            </div>
                            <div class="bank-info-row">
                                <span class="bank-info-label">Chủ tài khoản</span>
                                <span class="bank-info-value"><?= htmlspecialchars($bank_account_name) ?></span>
                            </div>
                            <div class="bank-info-row">
                                <span class="bank-info-label">Số tiền</span>
                                <span class="bank-info-value" style="color:#ef4444;"><?= number_format($checkout_total, 0, ',', '.') ?> ₫</span>
                            </div>
                            <div class="bank-info-row">
                                <span class="bank-info-label">Nội dung CK</span>
                                <span class="bank-info-value">
                                    <?= htmlspecialchars($bank_transfer_note) ?>
                                    <button type="button" class="bank-copy-btn" onclick="copyText('<?= htmlspecialchars($bank_transfer_note) ?>', this)">Copy</button>
                                </span>
                            </div>
                            <div class="bank-note">Vui lòng chuyển đúng nội dung để đơn hàng được xác nhận nhanh nhất.</div>
                        </div>
                    </div>
                </form>
            </div>
 
            <!-- Cột phải: Tóm tắt đơn hàng -->
            <div>
                <div class="order-summary">
                    <h2 class="form-section-title" style="margin-bottom: 20px; font-size: 16px;">Đơn hàng (<?= $so_luong_sp ?> sản phẩm)</h2>
 
                    <?php foreach ($checkout_items as $item): ?>
                    <div class="summary-item">
                        <div class="summary-item-thumb">
                            <?php if ($item['image_url']): ?>
                                <img src="../api/admin/product/<?= htmlspecialchars($item['image_url']) ?>" style="width:100%;height:100%;object-fit:cover;border-radius:8px;">
                            <?php else: ?>
                                📱
                            <?php endif; ?>
                        </div>
                        <div class="summary-item-info">
                            <h4><?= htmlspecialchars($item['name']) ?></h4>
                            <p><?= number_format($item['price'], 0, ',', '.') ?> ₫
                                <span style="color: #64748b; font-weight: 600; font-size: 12px; margin-left: 4px;">x <?= $item['quantity'] ?></span>
                            </p>
                        </div>
                    </div>
                    <?php endforeach; ?>
 
                    <div class="summary-calc" style="margin-top: 20px;"><span>Tạm tính</span><span style="color: #0f172a;"><?= number_format($checkout_subtotal, 0, ',', '.') ?> ₫</span></div>
                    <div class="summary-calc"><span>Phí vận chuyển</span><span style="color: #10b981;">Miễn phí</span></div>
                    <div class="summary-calc total"><span>Tổng thanh toán</span><span style="color: #ef4444; font-size: 24px;"><?= number_format($checkout_total, 0, ',', '.') ?> ₫</span></div>
 
                    <button class="btn btn-primary btn-block btn-lg" id="placeOrderBtn" style="margin-top: 24px;" onclick="placeOrder()">
                        Hoàn tất đặt hàng <i data-lucide="check-circle"></i>
                    </button>
                    <div style="text-align: center; font-size: 11px; color: #94a3b8; margin-top: 16px; line-height: 1.5;">Bằng việc đặt hàng, bạn đồng ý với <a href="#" style="color: #4f46e5; font-weight: 600;">Điều khoản</a> của TheSecond.</div>
                </div>
            </div>
        </div>
    </main>
 
    <footer class="main-footer" style="padding: 24px;">
        <p style="font-size: 12px;">© 2026 TheSecond - Nền tảng mua bán đồ điện tử cũ an toàn nhất.</p>
    </footer>
 
    <script src="../assets/js/script.js"></script>
    <script>
        if (typeof lucide !== "undefined") { lucide.createIcons(); }
 
        // Hiện/ẩn khối thông tin chuyển khoản theo lựa chọn phương thức thanh toán
        function togglePaymentInfo() {
            const bankRadio = document.querySelector('input[name="payment"][value="bank_transfer"]');
            const box = document.getElementById('bank-info-box');
            box.classList.toggle('show', bankRadio.checked);
        }
        togglePaymentInfo(); // chạy 1 lần khi tải trang để đúng trạng thái mặc định (COD)
 
        // Copy nhanh số tài khoản / nội dung chuyển khoản
        function copyText(text, btn) {
            navigator.clipboard.writeText(text).then(() => {
                const old = btn.textContent;
                btn.textContent = "Đã copy";
                setTimeout(() => { btn.textContent = old; }, 1500);
            });
        }

        // Đặt hàng thật: gửi dữ liệu lên server, tạo đơn hàng trong CSDL
        async function placeOrder() {
            const btn = document.getElementById('placeOrderBtn');
            const name = document.getElementById('receiver_name').value.trim();
            const phone = document.getElementById('receiver_phone').value.trim();
            const address = document.getElementById('address_line').value.trim();
            const paymentMethod = document.querySelector('input[name="payment"]:checked')?.value || 'cod';
            const csrf = document.getElementById('csrf').value;

            if (!name || !phone || !address) {
                alert('Vui lòng nhập đầy đủ thông tin giao hàng.');
                return;
            }

            btn.disabled = true;
            const oldText = btn.innerHTML;
            btn.innerHTML = 'Đang xử lý...';

            try {
                const res = await fetch('checkout.php', {
                    method: 'POST',
                    credentials: 'include',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        action: 'place_order',
                        csrf: csrf,
                        receiver_name: name,
                        receiver_phone: phone,
                        address_line: address,
                        payment_method: paymentMethod
                    })
                });
                const data = await res.json();

                if (!data.success) {
                    alert(data.message || 'Không thể đặt hàng, vui lòng thử lại.');
                    btn.disabled = false;
                    btn.innerHTML = oldText;
                    return;
                }

                alert('🎉 Đặt hàng thành công! Mã đơn hàng: ' + data.order_code);
                window.location.href = data.redirect || 'profile.php#donhang';

            } catch (err) {
                console.error(err);
                alert('Có lỗi xảy ra, vui lòng thử lại.');
                btn.disabled = false;
                btn.innerHTML = oldText;
            }
        }
    </script>
</body>
</html>