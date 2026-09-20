
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

function h(?string $str): string {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

function badgeClassOrder(?string $tt): string {
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

function tenTrangThaiOrder(?string $tt): string {
    $map = [
        'pending'     => 'Chờ xác nhận',
        'confirmed'   => 'Đã xác nhận',
        'processing'  => 'Đang xử lý',
        'shipping'    => 'Đang giao',
        'delivered'   => 'Đã giao',
        'cancelled'   => 'Đã hủy',
        'returned'    => 'Trả hàng',
    ];
    return $map[$tt] ?? ($tt ?: 'Không rõ');
}

function formatMoney($n): string {
    return number_format((float)$n, 0, ',', '.') . '₫';
}

/* ========== XỬ LÝ KHÓA / MỞ KHÓA ========== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['user_id'])) {
    $userId = (int) $_POST['user_id'];
    $action = $_POST['action'];

    if ($action === 'lock' || $action === 'unlock') {
        $newStatus = ($action === 'lock') ? 'blocked' : 'active';
        $stmt = $pdo->prepare('UPDATE users SET status = :status WHERE id = :id');
        $stmt->execute([':status' => $newStatus, ':id' => $userId]);
    }

    $redirect = 'customers.php';
    if (!empty($_SERVER['QUERY_STRING'])) {
        parse_str($_SERVER['QUERY_STRING'], $qs);
        unset($qs['xem']);
        if ($qs) $redirect .= '?' . http_build_query($qs);
    }
    header('Location: ' . $redirect);
    exit;
}

/* ========== BỘ LỌC ========== */
$keyword = trim($_GET['q'] ?? '');
$status  = trim($_GET['status'] ?? '');

/* ========== DANH SÁCH ========== */
$sql = "
    SELECT
        u.id,
        u.full_name,
        u.email,
        u.status,
        u.created_at,
        u.phone,
        COUNT(o.id) AS so_don_hang
    FROM users u
    LEFT JOIN orders o ON o.user_id = u.id
    WHERE 1 = 1
";

$params = [];

if ($keyword !== '') {
    $sql .= "
        AND (
            u.full_name LIKE :keyword1
            OR u.email LIKE :keyword2)";
    $params[':keyword1'] = '%' . $keyword . '%';
    $params[':keyword2'] = '%' . $keyword . '%';
}
if ($status === 'active' || $status === 'blocked') {
    $sql .= " AND u.status = :status ";
    $params[':status'] = $status;
}
$sql .= "
    GROUP BY u.id
    ORDER BY u.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$customers = $stmt->fetchAll();

function get_initials(string $hoTen): string {
    $parts = preg_split('/\s+/', trim($hoTen));
    $parts = array_values(array_filter($parts));
    $count = count($parts);
    if ($count === 0) return '?';
    if ($count === 1) return mb_strtoupper(mb_substr($parts[0], 0, 2, 'UTF-8'), 'UTF-8');
    $first  = mb_substr($parts[$count - 2], 0, 1, 'UTF-8');
    $second = mb_substr($parts[$count - 1], 0, 1, 'UTF-8');
    return mb_strtoupper($first . $second, 'UTF-8');
}

/* ========== CHI TIẾT KHÁCH HÀNG (MODAL) ========== */
$detail = null;
$ordersOfUser = [];

if (isset($_GET['xem'])) {
    $id = (int)$_GET['xem'];
    if ($id > 0) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $detail = $stmt->fetch();

        if ($detail) {
            $stmt = $pdo->prepare("
                SELECT o.id, o.order_code, o.total_amount, o.order_status, o.created_at,
                       (SELECT oi.product_name FROM order_items oi WHERE oi.order_id = o.id LIMIT 1) AS san_pham
                FROM orders o
                WHERE o.user_id = ?
                ORDER BY o.created_at DESC
                LIMIT 20
            ");
            $stmt->execute([$id]);
            $ordersOfUser = $stmt->fetchAll();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>TheSecond Admin — Khách hàng</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../../admin/admin.css">
<style>
  .detail-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px 18px;
    margin-bottom: 20px;
  }
  .detail-item {
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    padding: 12px 14px;
  }
  .detail-item .label {
    display: block;
    font-size: 11px;
    font-weight: 700;
    color: var(--text-400);
    text-transform: uppercase;
    margin-bottom: 4px;
  }
  .detail-item .value {
    font-size: 13.5px;
    font-weight: 600;
    color: var(--text-900);
  }
  .detail-orders {
    width: 100%;
    border-collapse: collapse;
  }
  .detail-orders th {
    text-align: left;
    font-size: 11px;
    text-transform: uppercase;
    color: var(--text-400);
    padding: 8px 0;
    border-bottom: 1px solid var(--border);
  }
  .detail-orders td {
    padding: 10px 0;
    border-bottom: 1px solid var(--border);
    font-size: 13.5px;
  }
  @media (max-width: 600px) {
    .detail-grid { grid-template-columns: 1fr; }
  }
</style>
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
      <a class="nav-item active" href="customers.php">
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
          <div class="page-title" id="pageTitle">Khách hàng</div>
        </div>
      </div>
      <form class="searchbox" method="get" action="customers.php">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="11" cy="11" r="7"/>
              <path d="m21 21-4.3-4.3"/>
          </svg>

          <input
              type="text"
              name="q"
              placeholder="Tìm khách hàng..."
              value="<?= h($keyword) ?>"
          >

          <?php if ($status !== ''): ?>
              <input type="hidden" name="status" value="<?= h($status) ?>">
          <?php endif; ?>
      </form>
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
      <section class="page active" id="page-customers">
        <div class="section-head">
          <div>
            <h2>Quản lý khách hàng</h2>
            <div class="section-sub">Xem danh sách khách hàng, thông tin tài khoản, lịch sử mua hàng và khoá/mở khoá tài khoản khi cần.</div>
          </div>
        </div>

        <div class="panel">
          <form class="table-toolbar" method="get" action="customers.php">
            <div class="searchbox" style="width:220px">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
              <input type="text" name="q" placeholder="Tìm khách hàng..." value="<?= h($keyword) ?>">
            </div>
            <select name="status" onchange="this.form.submit()">
              <option value="" <?= $status === '' ? 'selected' : '' ?>>Tất cả trạng thái</option>
              <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Hoạt động</option>
              <option value="blocked" <?= $status === 'blocked' ? 'selected' : '' ?>>Đã khoá</option>
            </select>
            <button type="submit" class="btn btn-outline">Lọc</button>
          </form>

          <table>
            <thead>
              <tr>
                <th>Khách hàng</th>
                <th>Email</th>
                <th>Lịch sử mua hàng</th>
                <th>Ngày tham gia</th>
                <th>Trạng thái</th>
                <th>Hành động</th>
              </tr>
            </thead>
            <tbody>
              <?php if (count($customers) === 0): ?>
                <tr>
                  <td colspan="6" style="text-align:center; padding:24px; color:#888;">
                    Không tìm thấy khách hàng nào.
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($customers as $c): ?>
                  <?php
                    $isLocked   = ($c['status'] === 'blocked' || $c['status'] === 'inactive');
                    $badgeClass = $isLocked ? 'badge-red'  : 'badge-green';
                    $badgeText  = $isLocked ? 'Đã khoá'    : 'Hoạt động';
                    $joinDate   = date('d/m/Y', strtotime($c['created_at']));
                    $initials   = get_initials($c['full_name'] ?? '');
                  ?>
                  <tr>
                    <td data-label="Khách hàng">
                      <div class="cell-main">
                        <div class="avatar" style="width:32px;height:32px;font-size:11px"><?= h($initials) ?></div>
                        <div class="cell-title"><?= h($c['full_name']) ?></div>
                      </div>
                    </td>
                    <td data-label="Email"><?= h($c['email']) ?></td>
                    <td data-label="Lịch sử mua hàng"><?= (int) $c['so_don_hang'] ?> đơn hàng</td>
                    <td data-label="Ngày tham gia"><?= h($joinDate) ?></td>
                    <td data-label="Trạng thái">
                      <span class="badge <?= $badgeClass ?>"><?= $badgeText ?></span>
                    </td>
                    <td data-label="Hành động">
                      <div class="row-actions">
                        <!-- Nút xem chi tiết (mở modal) -->
                        <a class="icon-action" title="Xem chi tiết"
                           href="?xem=<?= (int)$c['id'] ?>&q=<?= urlencode($keyword) ?>&status=<?= urlencode($status) ?>">
                          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/><circle cx="12" cy="12" r="3"/>
                          </svg>
                        </a>

                        <form method="post" style="display:inline"
                              onsubmit="return confirm('<?= $isLocked ? 'Mở khoá' : 'Khoá' ?> tài khoản của <?= h(addslashes($c['full_name'])) ?>?');">
                          <input type="hidden" name="user_id" value="<?= (int)$c['id'] ?>">
                          <input type="hidden" name="action" value="<?= $isLocked ? 'unlock' : 'lock' ?>">
                          <button type="submit" class="icon-action <?= $isLocked ? '' : 'danger' ?>"
                                  title="<?= $isLocked ? 'Mở khoá tài khoản' : 'Khoá tài khoản' ?>">
                            <?php if ($isLocked): ?>
                              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="4" y="11" width="16" height="9" rx="2"/>
                                <path d="M8 11V7a4 4 0 0 1 7-3.5"/>
                              </svg>
                            <?php else: ?>
                              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="4" y="11" width="16" height="9" rx="2"/>
                                <path d="M8 11V7a4 4 0 0 1 8 0v4"/>
                              </svg>
                            <?php endif; ?>
                          </button>
                        </form>
                      </div>
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

<!-- ==================== MODAL CHI TIẾT KHÁCH HÀNG ==================== -->
<?php if ($detail): ?>
<div class="overlay show" id="customerDetailModal">
  <div class="modal" style="max-width:720px">
    <div class="modal-head">
      <h3>Chi tiết khách hàng</h3>
      <a href="customers.php?q=<?= urlencode($keyword) ?>&status=<?= urlencode($status) ?>" 
         class="modal-close" title="Đóng">&times;</a>
    </div>

    <div class="modal-body">
      <div class="detail-grid">
        <div class="detail-item">
          <span class="label">Họ tên</span>
          <span class="value"><?= h($detail['full_name']) ?></span>
        </div>
        <div class="detail-item">
          <span class="label">Email</span>
          <span class="value"><?= h($detail['email']) ?></span>
        </div>
        <div class="detail-item">
          <span class="label">Số điện thoại</span>
          <span class="value"><?= h($detail['phone'] ?? '—') ?></span>
        </div>
        <div class="detail-item">
          <span class="label">Ngày tham gia</span>
          <span class="value"><?= date('d/m/Y H:i', strtotime($detail['created_at'])) ?></span>
        </div>
        <div class="detail-item">
          <span class="label">Vai trò</span>
          <span class="value"><?= h($detail['role'] ?? 'user') ?></span>
        </div>
        <div class="detail-item">
          <span class="label">Trạng thái</span>
          <span class="value">
            <?php
              $isLocked = ($detail['status'] === 'blocked' || $detail['status'] === 'inactive');
            ?>
            <span class="badge <?= $isLocked ? 'badge-red' : 'badge-green' ?>">
              <?= $isLocked ? 'Đã khoá' : 'Hoạt động' ?>
            </span>
          </span>
        </div>
      </div>

      <h4 style="font-size:14px;font-weight:800;margin-bottom:12px">
        Lịch sử đơn hàng (<?= count($ordersOfUser) ?>)
      </h4>

      <?php if (empty($ordersOfUser)): ?>
        <p style="color:var(--text-400);font-size:13.5px">Khách hàng chưa có đơn hàng nào.</p>
      <?php else: ?>
        <table class="detail-orders">
          <thead>
            <tr>
              <th>Mã đơn</th>
              <th>Sản phẩm</th>
              <th>Tổng tiền</th>
              <th>Ngày đặt</th>
              <th>Trạng thái</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($ordersOfUser as $o): ?>
              <tr>
                <td class="mono">
                  <?= $o['order_code'] 
                        ? h($o['order_code']) 
                        : '#DH' . str_pad($o['id'], 4, '0', STR_PAD_LEFT) ?>
                </td>
                <td><?= h($o['san_pham'] ?? '—') ?></td>
                <td class="price"><?= formatMoney($o['total_amount']) ?></td>
                <td><?= date('d/m/Y', strtotime($o['created_at'])) ?></td>
                <td>
                  <span class="badge <?= badgeClassOrder($o['order_status']) ?>">
                    <?= tenTrangThaiOrder($o['order_status']) ?>
                  </span>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<script src="../../admin/admin.js"></script>
<script>
  // Đóng modal khi click nền tối
  document.querySelectorAll('.overlay').forEach(ov => {
    ov.addEventListener('click', e => {
      if (e.target === ov) {
        window.location.href = 'customers.php?q=<?= urlencode($keyword) ?>&status=<?= urlencode($status) ?>';
      }
    });
  });
</script>
</body>
</html>