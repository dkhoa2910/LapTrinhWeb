<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: /../../../admin/admin_login.html"); 
    exit();
}

require_once __DIR__ . '/../config/database.php'; // $pdo

/* ========== THỐNG KÊ ========== */

// Doanh thu hôm nay (từ payments đã success)
$stmt = $pdo->query("
    SELECT COALESCE(SUM(amount), 0) 
    FROM payments 
    WHERE status = 'success' 
      AND DATE(paid_at) = CURDATE()
");
$doanh_thu_hom_nay = (float) $stmt->fetchColumn();

// Doanh thu hôm qua
$stmt = $pdo->query("
    SELECT COALESCE(SUM(amount), 0) 
    FROM payments 
    WHERE status = 'success' 
      AND DATE(paid_at) = CURDATE() - INTERVAL 1 DAY
");
$doanh_thu_hom_qua = (float) $stmt->fetchColumn();

$delta_doanh_thu = $doanh_thu_hom_qua > 0
    ? round((($doanh_thu_hom_nay - $doanh_thu_hom_qua) / $doanh_thu_hom_qua) * 100, 1)
    : ($doanh_thu_hom_nay > 0 ? 100 : 0);

// Đơn hàng hôm nay
$stmt = $pdo->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE()");
$don_hang_hom_nay = (int) $stmt->fetchColumn();

// Đơn chờ xử lý (pending + confirmed)
$stmt = $pdo->query("
    SELECT COUNT(*) FROM orders 
    WHERE order_status IN ('pending', 'confirmed')
");
$don_cho_xu_ly = (int) $stmt->fetchColumn();

// Sản phẩm đang bán (available + còn hàng)
$stmt = $pdo->query("
    SELECT COUNT(*) FROM products 
    WHERE status = 'available' AND stock > 0
");
$sp_dang_ban = (int) $stmt->fetchColumn();

// Sản phẩm mới đăng 7 ngày
$stmt = $pdo->query("
    SELECT COUNT(*) FROM products 
    WHERE created_at >= NOW() - INTERVAL 7 DAY
");
$sp_moi_dang = (int) $stmt->fetchColumn();

$stmt = $pdo->query("
    SELECT COUNT(*) FROM users 
    WHERE created_at >= CURDATE() - INTERVAL 7 DAY
");
$khach_hang_moi = (int) $stmt->fetchColumn();

$stmt = $pdo->query("
    SELECT COUNT(*) FROM users 
    WHERE created_at >= CURDATE() - INTERVAL 14 DAY 
      AND created_at < CURDATE() - INTERVAL 7 DAY
");
$khach_tuan_truoc = (int) $stmt->fetchColumn();

$delta_khach = $khach_tuan_truoc > 0
    ? round((($khach_hang_moi - $khach_tuan_truoc) / $khach_tuan_truoc) * 100, 1)
    : ($khach_hang_moi > 0 ? 100 : 0);

$nhan_thu = ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'];
$doanh_thu_7_ngay = [];

for ($i = 6; $i >= 0; $i--) {
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(amount), 0) 
        FROM payments 
        WHERE status = 'success' 
          AND DATE(paid_at) = CURDATE() - INTERVAL ? DAY
    ");
    $stmt->execute([$i]);
    $tong = (float) $stmt->fetchColumn();

    $thu_trong_tuan = (int) date('w', strtotime("-$i day"));
    $doanh_thu_7_ngay[] = [
        'l' => $nhan_thu[$thu_trong_tuan],
        'v' => round($tong / 1000000, 2) 
    ];
}

$stmt = $pdo->query("
    SELECT c.name AS ten_danh_muc, COUNT(p.id) AS so_luong
    FROM categories c
    LEFT JOIN products p ON p.category_id = c.id AND p.status = 'available'
    GROUP BY c.id
    ORDER BY so_luong DESC
");
$danh_muc_data = $stmt->fetchAll();
$tong_sp = array_sum(array_column($danh_muc_data, 'so_luong'));

$mau_donut = ['#2F6FED', '#1B9C6E', '#D98A1F', '#7C5CFC', '#E5484D', '#94A0B4'];
$donut_svg_circles = '';
$offset = 25;

foreach ($danh_muc_data as $idx => $dm) {
    $phan_tram = $tong_sp > 0 ? round(($dm['so_luong'] / $tong_sp) * 100) : 0;
    $mau = $mau_donut[$idx % count($mau_donut)];
    $donut_svg_circles .= sprintf(
        '<circle cx="21" cy="21" r="15.9" fill="transparent" stroke="%s" stroke-width="6" stroke-dasharray="%d %d" stroke-dashoffset="%d" stroke-linecap="round"></circle>',
        $mau, $phan_tram, 100 - $phan_tram, $offset
    );
    $offset -= $phan_tram;
}

$stmt = $pdo->query("
    SELECT o.id, o.order_code, o.total_amount, o.order_status, o.created_at,
           u.full_name AS ten_khach,
           (SELECT oi.product_name 
            FROM order_items oi 
            WHERE oi.order_id = o.id 
            LIMIT 1) AS san_pham_dau,
           (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) AS so_loai_sp
    FROM orders o
    JOIN users u ON u.id = o.user_id
    ORDER BY o.created_at DESC
    LIMIT 5
");
$don_gan_day = $stmt->fetchAll();

function badge_trang_thai_don(?string $tt): string {
    if ($tt === null || $tt === '') {
        return 'badge-gray';
    }
    $map = [
        'pending'     => 'badge-blue',
        'confirmed'   => 'badge-blue',
        'processing'  => 'badge-amber',
        'shipping'    => 'badge-amber',
        'delivered'   => 'badge-green',
        'cancelled'   => 'badge-red',
        'returned'    => 'badge-red',
    ];
    return $map[$tt] ?? 'badge-gray';
}

function ten_trang_thai_don(?string $tt): string {
    if ($tt === null || $tt === '') {
        return 'Không rõ';
    }
    $map = [
        'pending'     => 'Chờ xác nhận',
        'confirmed'   => 'Đã xác nhận',
        'processing'  => 'Đang xử lý',
        'shipping'    => 'Đang giao',
        'delivered'   => 'Đã giao',
        'cancelled'   => 'Đã hủy',
        'returned'    => 'Trả hàng',
    ];
    return $map[$tt] ?? $tt;
}

function format_money($so): string {
    return number_format((float)$so, 0, ',', '.') . '₫';
}

/* ========== THÔNG BÁO (chuông ở topbar) ========== */
// Đơn hàng mới cần xử lý (chờ xác nhận)
$stmt = $pdo->query("
    SELECT o.id, o.order_code, o.total_amount, o.created_at, u.full_name AS ten_khach
    FROM orders o
    JOIN users u ON u.id = o.user_id
    WHERE o.order_status = 'pending'
    ORDER BY o.created_at DESC
    LIMIT 6
");
$tb_don_moi = $stmt->fetchAll();

// Sản phẩm sắp hết hàng (còn hàng nhưng thấp)
$stmt = $pdo->query("
    SELECT id, name, stock
    FROM products
    WHERE status = 'available' AND stock > 0 AND stock <= 3
    ORDER BY stock ASC
    LIMIT 6
");
$tb_sap_het_hang = $stmt->fetchAll();

$so_thong_bao = count($tb_don_moi) + count($tb_sap_het_hang);

function thoi_gian_truoc(string $thoiDiem): string {
    $giay = time() - strtotime($thoiDiem);
    if ($giay < 60) return 'vừa xong';
    if ($giay < 3600) return floor($giay / 60) . ' phút trước';
    if ($giay < 86400) return floor($giay / 3600) . ' giờ trước';
    return floor($giay / 86400) . ' ngày trước';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>TheSecond Admin — Dashboard</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../../admin/admin.css">
</head>
<body>
<div class="app">

  <aside class="sidebar" id="sidebar">
    <div class="brand">
      <div class="brand-mark">TS</div>
      <div>
        <div class="brand-name">TheSecond</div>
        <div class="brand-sub">Admin Panel</div>
      </div>
    </div>
    <nav class="nav">
      <div type="button">Điều Hướng</div>
      <a href="../../user/index.html" class="btn btn-outline">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/></svg>
          Về trang User
      </a>
      <div class="nav-label">Tổng quan</div>
      <a class="nav-item active" href="../admin/dashboard.php">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/></svg>
        Dashboard
      </a>
      <div class="nav-label">Quản lý</div>
      <a class="nav-item" href="../admin/product/products.php">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 7 12 3 4 7v10l8 4 8-4V7Z"/><path d="M4 7l8 4 8-4M12 11v10"/></svg>
        Sản phẩm
      </a>
      <a class="nav-item" href="../admin/categories.php">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="8" height="8" rx="1.5"/><rect x="13" y="3" width="8" height="8" rx="1.5"/><rect x="3" y="13" width="8" height="8" rx="1.5"/><rect x="13" y="13" width="8" height="8" rx="1.5"/></svg>
        Danh mục
      </a>
      <a class="nav-item" href="../admin/order.php">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2 3 6v14a1 1 0 0 0 1 1h16a1 1 0 0 0 1-1V6l-3-4Z"/><path d="M3 6h18M9 10a3 3 0 0 0 6 0"/></svg>
        Đơn hàng
      </a>
      <a class="nav-item" href="../admin/customers.php">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/><circle cx="10" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        Khách hàng
      </a>
      <div class="nav-label">Báo cáo</div>
      <a class="nav-item" href="../admin/stats.php">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><path d="M7 15l4-5 3 3 5-7"/></svg>
        Thống kê
      </a>
    </nav>
    <div class="sidebar-foot">TheSecond Admin · v1.0<br>© 2026 Nhóm đồ án web</div>
  </aside>

  <div class="main">
    <header class="topbar">
      <div class="topbar-left">
        <button class="hamburger" id="hamburgerBtn">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
        </button>
        <div>
          <div class="page-title" id="pageTitle">Dashboard</div>
        </div>
      </div>
      <div class="topbar-right">
        <div class="notif-wrap">
          <button class="icon-btn" id="notifBtn" onclick="toggleNotif(event)">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/></svg>
            <?php if ($so_thong_bao > 0): ?><span class="dot"></span><?php endif; ?>
          </button>
          <div class="notif-panel" id="notifPanel">
            <div class="notif-head">
              Thông báo
              <?php if ($so_thong_bao > 0): ?><span class="notif-count"><?= $so_thong_bao ?></span><?php endif; ?>
            </div>
            <div class="notif-list">
              <?php if (empty($tb_don_moi) && empty($tb_sap_het_hang)): ?>
                <div class="notif-empty">Không có thông báo mới.</div>
              <?php else: ?>
                <?php if ($tb_don_moi): ?>
                  <div class="notif-section-label">Đơn hàng mới</div>
                  <?php foreach ($tb_don_moi as $tb): ?>
                    <a class="notif-item" href="../admin/order.php?xem=<?= (int)$tb['id'] ?>">
                      <div class="notif-item-icon">🛒</div>
                      <div>
                        <div class="notif-item-title">
                          Đơn <?= htmlspecialchars($tb['order_code'] ?: ('#DH' . $tb['id'])) ?> — <?= format_money($tb['total_amount']) ?>
                        </div>
                        <div class="notif-item-sub"><?= htmlspecialchars($tb['ten_khach']) ?> · <?= thoi_gian_truoc($tb['created_at']) ?></div>
                      </div>
                    </a>
                  <?php endforeach; ?>
                <?php endif; ?>
                <?php if ($tb_sap_het_hang): ?>
                  <div class="notif-section-label">Sắp hết hàng</div>
                  <?php foreach ($tb_sap_het_hang as $sp): ?>
                    <a class="notif-item" href="../admin/product/products.php">
                      <div class="notif-item-icon">⚠️</div>
                      <div>
                        <div class="notif-item-title"><?= htmlspecialchars($sp['name']) ?></div>
                        <div class="notif-item-sub">Còn lại <?= (int)$sp['stock'] ?> sản phẩm</div>
                      </div>
                    </a>
                  <?php endforeach; ?>
                <?php endif; ?>
              <?php endif; ?>
            </div>
            <a class="notif-foot" href="../admin/order.php?status=pending">Xem tất cả đơn chờ xử lý</a>
          </div>
        </div>
        <div class="admin-chip">
          <div class="avatar"><?= strtoupper(mb_substr($_SESSION['admin_name'] ?? 'A', 0, 1)) ?></div>
          <div>
            <div class="admin-chip-role"><?= htmlspecialchars($_SESSION['admin_name'] ?? 'Quản trị viên') ?></div>
          </div>
        </div>
      </div>
    </header>

    <main class="content">
      <section class="page active" id="page-dashboard">
        <div class="section-head">
          <div>
            <h2>Xin chào, <?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?></h2>
            <div class="section-sub">Đây là tình hình hoạt động của TheSecond hôm nay</div>
          </div>
          <button class="btn btn-outline" onclick="location.reload()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 3-6.7"/><path d="M3 4v5h5"/></svg>
            Làm mới
          </button>
        </div>

        <!-- STAT CARDS (dữ liệu thật) -->
        <div class="stat-grid">
          <div class="stat-card" style="--accent:var(--blue-600); --accent-soft:var(--blue-100)">
            <div class="stat-top">
              <span class="stat-label">Doanh thu hôm nay</span>
              <span class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></span>
            </div>
            <div class="stat-value"><?= format_money($doanh_thu_hom_nay) ?></div>
            <div class="stat-delta <?= $delta_doanh_thu >= 0 ? 'up' : 'down' ?>">
              <?= $delta_doanh_thu >= 0 ? '▲' : '▼' ?>
              <?= abs($delta_doanh_thu) ?>% so với hôm qua
            </div>
          </div>

          <div class="stat-card" style="--accent:var(--amber-600); --accent-soft:var(--amber-100)">
            <div class="stat-top">
              <span class="stat-label">Đơn hàng mới</span>
              <span class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2 3 6v14a1 1 0 0 0 1 1h16a1 1 0 0 0 1-1V6l-3-4Z"/><path d="M3 6h18"/></svg></span>
            </div>
            <div class="stat-value"><?= $don_hang_hom_nay ?></div>
            <div class="stat-delta up">▲ <?= $don_cho_xu_ly ?> đơn chờ xử lý</div>
          </div>

          <div class="stat-card" style="--accent:var(--green-600); --accent-soft:var(--green-100)">
            <div class="stat-top">
              <span class="stat-label">Sản phẩm đang bán</span>
              <span class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 7 12 3 4 7v10l8 4 8-4V7Z"/></svg></span>
            </div>
            <div class="stat-value"><?= $sp_dang_ban ?></div>
            <div class="stat-delta up">▲ <?= $sp_moi_dang ?> sản phẩm mới đăng</div>
          </div>

          <div class="stat-card" style="--accent:var(--violet-600); --accent-soft:var(--violet-100)">
            <div class="stat-top">
              <span class="stat-label">Khách hàng mới</span>
              <span class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg></span>
            </div>
            <div class="stat-value"><?= $khach_hang_moi ?></div>
            <div class="stat-delta <?= $delta_khach >= 0 ? 'up' : 'down' ?>">
              <?= $delta_khach >= 0 ? '▲' : '▼' ?>
              <?= abs($delta_khach) ?>% tuần này
            </div>
          </div>
        </div>

        <div class="grid-2">
          <!-- Biểu đồ doanh thu 7 ngày -->
          <div class="panel">
            <div class="panel-head">
              <h3>Doanh thu 7 ngày gần nhất</h3>
              <span class="badge badge-blue">Tuần này</span>
            </div>
            <div class="panel-body">
              <div class="bars" id="revenueChart"></div>
            </div>
          </div>

          <!-- Donut danh mục -->
          <div class="panel">
            <div class="panel-head"><h3>Sản phẩm theo danh mục</h3></div>
            <div class="panel-body">
              <div class="donut-wrap">
                <svg viewBox="0 0 42 42" width="150" height="150">
                  <circle cx="21" cy="21" r="15.9" fill="transparent" stroke="#EEF1F6" stroke-width="6"></circle>
                  <?= $donut_svg_circles ?>
                </svg>
                <div class="legend">
                  <?php foreach ($danh_muc_data as $idx => $dm): 
                      $phan_tram = $tong_sp > 0 ? round(($dm['so_luong'] / $tong_sp) * 100) : 0;
                      $mau = $mau_donut[$idx % count($mau_donut)];
                  ?>
                    <div class="legend-item">
                      <span class="legend-dot" style="background:<?= $mau ?>"></span>
                      <?= htmlspecialchars($dm['ten_danh_muc']) ?> <b><?= $phan_tram ?>%</b>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Đơn hàng gần đây -->
        <div class="panel" style="margin-top:16px">
          <div class="panel-head">
            <h3>Đơn hàng gần đây</h3>
            <a class="btn btn-outline btn-sm" href="../../../TheSecondV1/api/admin/order.php">Xem tất cả</a>
          </div>
          <table>
            <thead>
              <tr>
                <th>Mã đơn</th>
                <th>Khách hàng</th>
                <th>Sản phẩm</th>
                <th>Tổng tiền</th>
                <th>Trạng thái</th>
              </tr>
            </thead>
              <tbody>
                <?php if (empty($don_gan_day)): ?>
                  <tr>
                    <td colspan="5" style="text-align:center;padding:24px;color:#888">
                      Chưa có đơn hàng nào
                    </td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($don_gan_day as $don): ?>
                    <tr>
                      <td data-label="Mã đơn" class="mono">
                        <?= !empty($don['order_code']) 
                              ? htmlspecialchars($don['order_code']) 
                              : '#DH' . (int)$don['id'] ?>
                      </td>
                      <td data-label="Khách hàng">
                        <?= htmlspecialchars($don['ten_khach'] ?? '—') ?>
                      </td>
                      <td data-label="Sản phẩm">
                        <?= htmlspecialchars($don['san_pham_dau'] ?? '—') ?>
                        <?php if (($don['so_loai_sp'] ?? 0) > 1): ?>
                          <small style="color:#888">(+<?= (int)$don['so_loai_sp'] - 1 ?>)</small>
                        <?php endif; ?>
                      </td>
                      <td data-label="Tổng tiền" class="price">
                        <?= format_money($don['total_amount'] ?? 0) ?>
                      </td>
                      <td data-label="Trạng thái">
                        <span class="badge <?= badge_trang_thai_don($don['order_status'] ?? null) ?>">
                          <?= ten_trang_thai_don($don['order_status'] ?? null) ?>
                        </span>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
          </table>
        </div>
      </section>
    </main>

    <footer class="footer">
      <div class="footer-left">
        <div class="brand-mark">TS</div>
        © 2026 TheSecond — Đồ án Lập trình Web
      </div>
      <div class="footer-links">
        <a href="#">Trợ giúp</a>
        <a href="#">Chính sách</a>
        <a href="#">Liên hệ</a>
      </div>
    </footer>
  </div>
</div>


<script>
  window.revenueData = <?= json_encode($doanh_thu_7_ngay, JSON_UNESCAPED_UNICODE) ?>;

</script>
<script src="../../admin/admin.js"></script>
</body>
</html>