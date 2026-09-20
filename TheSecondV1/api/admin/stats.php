<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: /../../../admin/admin_login.html");
    exit();
}

require_once __DIR__ . '/../config/database.php';

/* ========== THÔNG BÁO (chuông ở topbar) ========== */
if (!function_exists('format_money_notif')) {
    function format_money_notif($so): string {
        return number_format((float)$so, 0, ',', '.') . '₫';
    }
}
if (!function_exists('thoi_gian_truoc')) {
    function thoi_gian_truoc(string $thoiDiem): string {
        $giay = time() - strtotime($thoiDiem);
        if ($giay < 60) return 'vừa xong';
        if ($giay < 3600) return floor($giay / 60) . ' phút trước';
        if ($giay < 86400) return floor($giay / 3600) . ' giờ trước';
        return floor($giay / 86400) . ' ngày trước';
    }
}

$stmt = $pdo->query("
    SELECT o.id, o.order_code, o.total_amount, o.created_at, u.full_name AS ten_khach
    FROM orders o
    JOIN users u ON u.id = o.user_id
    WHERE o.order_status = 'pending'
    ORDER BY o.created_at DESC
    LIMIT 6
");
$tb_don_moi = $stmt->fetchAll();

$stmt = $pdo->query("
    SELECT id, name, stock
    FROM products
    WHERE status = 'available' AND stock > 0 AND stock <= 3
    ORDER BY stock ASC
    LIMIT 6
");
$tb_sap_het_hang = $stmt->fetchAll();

$so_thong_bao = count($tb_don_moi) + count($tb_sap_het_hang);

function h(?string $s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function format_money($so): string {
    return number_format((float)$so, 0, ',', '.') . '₫';
}

function tinh_delta($hienTai, $truoc): float {
    if ($truoc > 0) return round((($hienTai - $truoc) / $truoc) * 100, 1);
    return $hienTai > 0 ? 100.0 : 0.0;
}

/* ========== BỘ LỌC KỲ BÁO CÁO ========== */
$period = $_GET['period'] ?? '30d';
if (!in_array($period, ['30d', 'quarter', 'year'], true)) $period = '30d';

$now = new DateTime();
switch ($period) {
    case 'quarter':
        $from     = (clone $now)->modify('-3 months');
        $prevFrom = (clone $now)->modify('-6 months');
        $prevTo   = clone $from;
        break;
    case 'year':
        $from     = (clone $now)->modify('-1 year');
        $prevFrom = (clone $now)->modify('-2 year');
        $prevTo   = clone $from;
        break;
    default: // 30d
        $from     = (clone $now)->modify('-30 days');
        $prevFrom = (clone $now)->modify('-60 days');
        $prevTo   = clone $from;
}
$fromStr     = $from->format('Y-m-d H:i:s');
$prevFromStr = $prevFrom->format('Y-m-d H:i:s');
$prevToStr   = $prevTo->format('Y-m-d H:i:s');

/* ========== 4 THẺ THỐNG KÊ ========== */

// Tổng doanh thu (payments đã thanh toán thành công)
$stmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status = 'success' AND paid_at >= ?");
$stmt->execute([$fromStr]);
$doanh_thu = (float)$stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status = 'success' AND paid_at >= ? AND paid_at < ?");
$stmt->execute([$prevFromStr, $prevToStr]);
$doanh_thu_truoc = (float)$stmt->fetchColumn();
$delta_doanh_thu = tinh_delta($doanh_thu, $doanh_thu_truoc);

// Tổng đơn hàng
$stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE created_at >= ?");
$stmt->execute([$fromStr]);
$tong_don = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE created_at >= ? AND created_at < ?");
$stmt->execute([$prevFromStr, $prevToStr]);
$tong_don_truoc = (int)$stmt->fetchColumn();
$delta_don = tinh_delta($tong_don, $tong_don_truoc);

// Sản phẩm đã bán (loại trừ đơn đã hủy/trả hàng)
$stmt = $pdo->prepare("
    SELECT COALESCE(SUM(oi.quantity),0)
    FROM order_items oi JOIN orders o ON o.id = oi.order_id
    WHERE o.created_at >= ? AND o.order_status NOT IN ('cancelled','returned')
");
$stmt->execute([$fromStr]);
$sp_da_ban = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT COALESCE(SUM(oi.quantity),0)
    FROM order_items oi JOIN orders o ON o.id = oi.order_id
    WHERE o.created_at >= ? AND o.created_at < ? AND o.order_status NOT IN ('cancelled','returned')
");
$stmt->execute([$prevFromStr, $prevToStr]);
$sp_da_ban_truoc = (int)$stmt->fetchColumn();
$delta_sp = tinh_delta($sp_da_ban, $sp_da_ban_truoc);

// Khách hàng mới
$stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE created_at >= ?");
$stmt->execute([$fromStr]);
$khach_moi = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE created_at >= ? AND created_at < ?");
$stmt->execute([$prevFromStr, $prevToStr]);
$khach_moi_truoc = (int)$stmt->fetchColumn();
$delta_khach = tinh_delta($khach_moi, $khach_moi_truoc);

/* ========== DOANH THU THEO THÁNG (6 THÁNG GẦN NHẤT) ========== */
$doanh_thu_6_thang = [];
for ($i = 5; $i >= 0; $i--) {
    $thang     = (new DateTime())->modify("-$i months");
    $dauThang  = $thang->format('Y-m-01 00:00:00');
    $cuoiThang = (clone $thang)->modify('last day of this month')->format('Y-m-d 23:59:59');

    $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status = 'success' AND paid_at BETWEEN ? AND ?");
    $stmt->execute([$dauThang, $cuoiThang]);
    $tong = (float)$stmt->fetchColumn();

    $doanh_thu_6_thang[] = ['l' => 'Th' . $thang->format('n'), 'v' => round($tong / 1000000, 2)];
}
$max_thang = max(array_column($doanh_thu_6_thang, 'v')) ?: 1;

/* ========== SẢN PHẨM BÁN CHẠY NHẤT (trong kỳ) ========== */
$stmt = $pdo->prepare("
    SELECT oi.product_name, SUM(oi.quantity) AS da_ban, SUM(oi.subtotal) AS doanh_thu
    FROM order_items oi
    JOIN orders o ON o.id = oi.order_id
    WHERE o.created_at >= ? AND o.order_status NOT IN ('cancelled','returned')
    GROUP BY oi.product_name
    ORDER BY da_ban DESC
    LIMIT 5
");
$stmt->execute([$fromStr]);
$sp_ban_chay = $stmt->fetchAll();

/* ========== TỈ LỆ DANH MỤC BÁN RA (theo số lượng bán trong kỳ) ========== */
$stmt = $pdo->prepare("
    SELECT c.id, c.name AS ten_danh_muc, COALESCE(SUM(x.qty), 0) AS so_luong
    FROM categories c
    LEFT JOIN (
        SELECT p.category_id AS category_id, oi.quantity AS qty
        FROM order_items oi
        JOIN orders o ON o.id = oi.order_id
        JOIN products p ON p.id = oi.product_id
        WHERE o.created_at >= ? AND o.order_status NOT IN ('cancelled','returned')
    ) x ON x.category_id = c.id
    GROUP BY c.id
    ORDER BY so_luong DESC
");
$stmt->execute([$fromStr]);
$danh_muc_ban_ra = $stmt->fetchAll();
$tong_sl_ban = array_sum(array_column($danh_muc_ban_ra, 'so_luong'));

$mau_donut = ['#2F6FED', '#1B9C6E', '#D98A1F', '#7C5CFC', '#E5484D', '#94A0B4'];
$donut_svg_circles = '';
$legend_items = [];
$offset = 25;
$colorIdx = 0;
foreach ($danh_muc_ban_ra as $dm) {
    $phan_tram = $tong_sl_ban > 0 ? round(($dm['so_luong'] / $tong_sl_ban) * 100) : 0;
    if ($phan_tram <= 0) continue; // ẩn danh mục không bán được gì trong kỳ cho đỡ rối biểu đồ
    $mau = $mau_donut[$colorIdx % count($mau_donut)];
    $colorIdx++;
    $donut_svg_circles .= sprintf(
        '<circle cx="21" cy="21" r="15.9" fill="transparent" stroke="%s" stroke-width="6" stroke-dasharray="%d %d" stroke-dashoffset="%d" stroke-linecap="round"></circle>',
        $mau, $phan_tram, 100 - $phan_tram, $offset
    );
    $offset -= $phan_tram;
    $legend_items[] = ['ten' => $dm['ten_danh_muc'], 'phan_tram' => $phan_tram, 'mau' => $mau];
}

$nhan_ky = ['30d' => '30 ngày qua', 'quarter' => 'Quý này', 'year' => 'Năm nay'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>TheSecond Admin — Thống kê</title>
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
      <a class="nav-item" href="../admin/dashboard.php">
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
      <a class="nav-item active" href="../admin/stats.php">
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
          <div class="page-title" id="pageTitle">Thống kê</div>
        </div>
      </div>
      <div class="topbar-right" style="margin-left:auto">
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
                          Đơn <?= htmlspecialchars($tb['order_code'] ?: ('#DH' . $tb['id'])) ?> — <?= format_money_notif($tb['total_amount']) ?>
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
            <div class="admin-chip-role"><?= h($_SESSION['admin_name'] ?? 'Quản trị viên') ?></div>
          </div>
        </div>
      </div>
    </header>

    <main class="content">
      <section class="page active" id="page-stats">
        <div class="section-head">
          <div><h2>Thống kê</h2><div class="section-sub">Tổng quan hiệu suất kinh doanh theo thời gian.</div></div>
          <select id="periodSelect" style="padding:9px 12px;border:1px solid var(--border);border-radius:8px;font-size:12.5px" onchange="location.href='stats.php?period=' + this.value">
            <?php foreach ($nhan_ky as $val => $label): ?>
              <option value="<?= $val ?>" <?= $period === $val ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="stat-grid">
          <div class="stat-card" style="--accent:var(--blue-600); --accent-soft:var(--blue-100)">
            <div class="stat-label">Tổng doanh thu</div>
            <div class="stat-value"><?= format_money($doanh_thu) ?></div>
            <div class="stat-delta <?= $delta_doanh_thu >= 0 ? 'up' : 'down' ?>">
              <?= $delta_doanh_thu >= 0 ? '▲' : '▼' ?> <?= abs($delta_doanh_thu) ?>% so với kỳ trước
            </div>
          </div>
          <div class="stat-card" style="--accent:var(--green-600); --accent-soft:var(--green-100)">
            <div class="stat-label">Tổng đơn hàng</div>
            <div class="stat-value"><?= $tong_don ?></div>
            <div class="stat-delta <?= $delta_don >= 0 ? 'up' : 'down' ?>">
              <?= $delta_don >= 0 ? '▲' : '▼' ?> <?= abs($delta_don) ?>% so với kỳ trước
            </div>
          </div>
          <div class="stat-card" style="--accent:var(--amber-600); --accent-soft:var(--amber-100)">
            <div class="stat-label">Sản phẩm đã bán</div>
            <div class="stat-value"><?= $sp_da_ban ?></div>
            <div class="stat-delta <?= $delta_sp >= 0 ? 'up' : 'down' ?>">
              <?= $delta_sp >= 0 ? '▲' : '▼' ?> <?= abs($delta_sp) ?>% so với kỳ trước
            </div>
          </div>
          <div class="stat-card" style="--accent:var(--violet-600); --accent-soft:var(--violet-100)">
            <div class="stat-label">Khách hàng mới</div>
            <div class="stat-value"><?= $khach_moi ?></div>
            <div class="stat-delta <?= $delta_khach >= 0 ? 'up' : 'down' ?>">
              <?= $delta_khach >= 0 ? '▲' : '▼' ?> <?= abs($delta_khach) ?>% so với kỳ trước
            </div>
          </div>
        </div>

        <div class="panel">
          <div class="panel-head"><h3>Doanh thu theo tháng (6 tháng gần nhất)</h3></div>
          <div class="panel-body">
            <div class="bars" id="statsChart">
              <?php foreach ($doanh_thu_6_thang as $dt): ?>
                <div class="bar-col" title="<?= $dt['l'] ?>: <?= $dt['v'] ?> triệu ₫">
                  <div class="bar" style="height:<?= $max_thang > 0 ? round(($dt['v'] / $max_thang) * 100) : 0 ?>%"></div>
                  <div class="bar-label"><?= $dt['l'] ?></div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <div class="grid-2" style="margin-top:16px">
          <div class="panel">
            <div class="panel-head"><h3>Sản phẩm bán chạy nhất</h3></div>
            <table>
              <thead><tr><th>Sản phẩm</th><th>Đã bán</th><th>Doanh thu</th></tr></thead>
              <tbody>
                <?php if (empty($sp_ban_chay)): ?>
                  <tr><td colspan="3" style="text-align:center;padding:20px;color:var(--text-400)">Chưa có dữ liệu bán hàng trong kỳ này.</td></tr>
                <?php else: ?>
                  <?php foreach ($sp_ban_chay as $sp): ?>
                    <tr>
                      <td data-label="Sản phẩm"><?= h($sp['product_name']) ?></td>
                      <td data-label="Đã bán"><?= (int)$sp['da_ban'] ?></td>
                      <td data-label="Doanh thu" class="price"><?= format_money($sp['doanh_thu']) ?></td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
          <div class="panel">
            <div class="panel-head"><h3>Tỉ lệ danh mục bán ra</h3></div>
            <div class="panel-body">
              <?php if (empty($legend_items)): ?>
                <p style="text-align:center;color:var(--text-400);padding:24px 0">Chưa có dữ liệu bán hàng trong kỳ này.</p>
              <?php else: ?>
                <div class="donut-wrap" style="justify-content:center;gap:24px;flex-wrap:wrap">
                  <svg viewBox="0 0 42 42" width="160" height="160">
                    <circle cx="21" cy="21" r="15.9" fill="transparent" stroke="#EEF1F6" stroke-width="6"></circle>
                    <?= $donut_svg_circles ?>
                  </svg>
                  <div class="legend">
                    <?php foreach ($legend_items as $li): ?>
                      <div class="legend-item">
                        <span class="legend-dot" style="background:<?= $li['mau'] ?>"></span>
                        <?= h($li['ten']) ?> <b><?= $li['phan_tram'] ?>%</b>
                      </div>
                    <?php endforeach; ?>
                  </div>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </section>
    </main>

    <footer class="footer">
      <div class="footer-left">
        <div class="brand-mark">TS</div>
        © 2026 TheSecond — Đồ án Lập trình Web, Nhóm 012012103104
      </div>
      <div class="footer-links">
        <a href="#">Trợ giúp</a>
        <a href="#">Chính sách</a>
        <a href="#">Liên hệ</a>
      </div>
    </footer>
  </div>
</div>

<script src="../../admin/admin.js"></script>
</body>
</html>
