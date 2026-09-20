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

function badgeClass(?string $tt): string {
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

function tenTrangThai(?string $tt): string {
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

function formatDate($d): string {
    return $d ? date('d/m/Y H:i', strtotime($d)) : '—';
}

/* ===== CẬP NHẬT TRẠNG THÁI ===== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['order_id'])) {
    $id     = (int)$_POST['order_id'];
    $action = $_POST['action'];

    $map = [
        'confirm' => 'shipping',
        'deliver' => 'delivered',
        'cancel'  => 'cancelled',
    ];

    // Cho phép cập nhật trực tiếp từ modal
    if (isset($_POST['trang_thai']) && $_POST['trang_thai'] !== '') {
        $valid = ['pending','confirmed','processing','shipping','delivered','cancelled','returned'];
        if (in_array($_POST['trang_thai'], $valid, true)) {
            $stmt = $pdo->prepare("UPDATE orders SET order_status = ? WHERE id = ?");
            $stmt->execute([$_POST['trang_thai'], $id]);
        }
    } elseif ($id > 0 && isset($map[$action])) {
        $stmt = $pdo->prepare("UPDATE orders SET order_status = ? WHERE id = ?");
        $stmt->execute([$map[$action], $id]);
    }

    $redirect = 'order.php';
    if (!empty($_SERVER['QUERY_STRING'])) {
        // Giữ lại bộ lọc, bỏ tham số xem
        parse_str($_SERVER['QUERY_STRING'], $qs);
        unset($qs['xem']);
        if ($qs) $redirect .= '?' . http_build_query($qs);
    }
    header('Location: ' . $redirect);
    exit;
}

/* ===== BỘ LỌC ===== */
$keyword = trim($_GET['q'] ?? '');
$status  = trim($_GET['status'] ?? '');


/* ===== LẤY DANH SÁCH ===== */
$sql = "
    SELECT 
        o.id,
        o.order_code,
        o.created_at,
        o.total_amount,
        o.order_status,
        u.full_name AS ten_khach,
        GROUP_CONCAT(oi.product_name SEPARATOR ', ') AS san_pham
    FROM orders o
    JOIN users u ON u.id = o.user_id
    LEFT JOIN order_items oi ON oi.order_id = o.id
";

$where = [];
$params = [];
/* ===== TÌM KIẾM ===== */
if ($keyword !== '') {
    $where[] = "
        (
            o.order_code LIKE :keyword1
            OR u.full_name LIKE :keyword2
            OR oi.product_name LIKE :keyword3
        )
    ";

    $kw = '%' . $keyword . '%';

    $params[':keyword1'] = $kw;
    $params[':keyword2'] = $kw;
    $params[':keyword3'] = $kw;
}

/* ===== LỌC TRẠNG THÁI ===== */
$validStatuses = [
    'pending',
    'confirmed',
    'processing',
    'shipping',
    'delivered',
    'cancelled',
    'returned'
];

if (
    $status !== '' &&
    in_array($status, $validStatuses, true)
) {
    $where[] = "o.order_status = :status";
    $params[':status'] = $status;
}

/* ===== GHÉP WHERE ===== */
if (!empty($where)) {
    $sql .= " WHERE " . implode(' AND ', $where);
}

$sql .= "
    GROUP BY o.id
    ORDER BY o.created_at DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$orders = $stmt->fetchAll();

/* ===== CHI TIẾT ĐƠN (MODAL) ===== */
$detail = null;
$items  = [];

if (isset($_GET['xem'])) {
    $id = (int)$_GET['xem'];
    if ($id > 0) {
        $stmt = $pdo->prepare("
            SELECT 
                o.*,
                u.full_name AS ten_khach,
                u.email,
                p.payment_method,
                p.status AS payment_status,
                p.amount AS payment_amount
            FROM orders o
            JOIN users u ON u.id = o.user_id
            LEFT JOIN payments p ON p.order_id = o.id
            WHERE o.id = ?
            LIMIT 1
        ");
        $stmt->execute([$id]);
        $detail = $stmt->fetch();

        if ($detail) {
            $stmt = $pdo->prepare("
                SELECT product_name, quantity, unit_price, subtotal 
                FROM order_items 
                WHERE order_id = ?
            ");
            $stmt->execute([$id]);
            $items = $stmt->fetchAll();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>TheSecond Admin — Đơn hàng</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../../admin/admin.css">
<style>
  /* Bổ sung cho modal chi tiết đơn */
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
  .detail-products {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 16px;
  }
  .detail-products th {
    text-align: left;
    font-size: 11px;
    text-transform: uppercase;
    color: var(--text-400);
    padding: 8px 0;
    border-bottom: 1px solid var(--border);
  }
  .detail-products td {
    padding: 10px 0;
    border-bottom: 1px solid var(--border);
    font-size: 13.5px;
  }
  .detail-total {
    text-align: right;
    font-size: 16px;
    font-weight: 800;
    margin-bottom: 20px;
  }
  .detail-total span {
    color: var(--red-600);
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
      <a class="nav-item active" href="../admin/order.php">
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
          <div class="page-title" id="pageTitle">Đơn hàng</div>
        </div>
      </div>
      <form class="searchbox" method="get" action="order.php">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="11" cy="11" r="7"/>
              <path d="m21 21-4.3-4.3"/>
          </svg>
          <input
              type="text"
              name="q"
              placeholder="Tìm mã đơn, khách hàng, sản phẩm..."
              value="<?= h($keyword) ?>">
          <?php if ($status !== ''): ?>
              <input
                  type="hidden"
                  name="status"
                  value="<?= h($status) ?>">
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
                    <a class="notif-item" href="order.php?xem=<?= (int)$tb['id'] ?>">
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
            <a class="notif-foot" href="order.php?status=pending">Xem tất cả đơn chờ xử lý</a>
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
      <section class="page active" id="page-orders">
        <div class="section-head">
          <div>
            <h2>Quản lý đơn hàng</h2>
            <div class="section-sub">Theo dõi và cập nhật trạng thái các đơn mua bán đồ cũ.</div>
          </div>
        </div>

        <div class="panel">
          <form class="table-toolbar" method="get" action="order.php">
            <div class="searchbox" style="width:220px">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
              <input type="text" name="q" placeholder="Tìm mã đơn, khách hàng..." value="<?= h($keyword) ?>">
            </div>
            <select name="status" onchange="this.form.submit()">
              <option value="">Tất cả trạng thái</option>
              <option value="pending"     <?= $status === 'pending'     ? 'selected' : '' ?>>Chờ xác nhận</option>
              <option value="confirmed"   <?= $status === 'confirmed'   ? 'selected' : '' ?>>Đã xác nhận</option>
              <option value="processing"  <?= $status === 'processing'  ? 'selected' : '' ?>>Đang xử lý</option>
              <option value="shipping"    <?= $status === 'shipping'    ? 'selected' : '' ?>>Đang giao</option>
              <option value="delivered"   <?= $status === 'delivered'   ? 'selected' : '' ?>>Đã giao</option>
              <option value="cancelled"   <?= $status === 'cancelled'   ? 'selected' : '' ?>>Đã hủy</option>
              <option value="returned"    <?= $status === 'returned'    ? 'selected' : '' ?>>Trả hàng</option>
            </select>
            <button type="submit" class="btn btn-outline btn-sm">Lọc</button>
          </form>

          <table>
            <thead>
              <tr>
                <th>Mã đơn</th>
                <th>Khách hàng</th>
                <th>Sản phẩm</th>
                <th>Tổng tiền</th>
                <th>Ngày đặt</th>
                <th>Trạng thái</th>
                <th>Hành động</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($orders)): ?>
                <tr>
                  <td colspan="7" style="text-align:center;padding:32px;color:var(--text-400)">
                    Chưa có đơn hàng nào
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($orders as $o): ?>
                  <tr>
                    <td data-label="Mã đơn" class="mono">
                      <?= $o['order_code'] 
                            ? h($o['order_code']) 
                            : '#DH' . str_pad($o['id'], 4, '0', STR_PAD_LEFT) ?>
                    </td>
                    <td data-label="Khách hàng"><?= h($o['ten_khach']) ?></td>
                    <td data-label="Sản phẩm"><?= h($o['san_pham'] ?? '—') ?></td>
                    <td data-label="Tổng tiền" class="price"><?= formatMoney($o['total_amount']) ?></td>
                    <td data-label="Ngày đặt"><?= formatDate($o['created_at']) ?></td>
                    <td data-label="Trạng thái">
                      <span class="badge <?= badgeClass($o['order_status']) ?>">
                        <?= tenTrangThai($o['order_status']) ?>
                      </span>
                    </td>
                    <td data-label="Hành động">
                      <div class="row-actions">
                        <?php if ($o['order_status'] === 'pending'): ?>
                          <form method="post" style="display:inline" onsubmit="return confirm('Xác nhận đơn hàng này?')">
                            <input type="hidden" name="order_id" value="<?= (int)$o['id'] ?>">
                            <input type="hidden" name="action" value="confirm">
                            <button type="submit" class="icon-action" title="Xác nhận đơn">
                              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg>
                            </button>
                          </form>
                        <?php endif; ?>

                        <a class="icon-action" title="Xem chi tiết"
                           href="?xem=<?= (int)$o['id'] ?>&q=<?= urlencode($keyword) ?>&status=<?= urlencode($status) ?>">
                          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                        </a>
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

<!-- ==================== xem chi tiết ==================== -->
<?php if ($detail): ?>
<div class="overlay show" id="orderDetailModal">
  <div class="modal" style="max-width:680px">
    <div class="modal-head">
      <h3>
        Chi tiết đơn 
        <?= $detail['order_code'] 
              ? h($detail['order_code']) 
              : '#DH' . str_pad($detail['id'], 4, '0', STR_PAD_LEFT) ?>
      </h3>
      <a href="order.php?q=<?= urlencode($keyword) ?>&status=<?= urlencode($status) ?>" 
         class="modal-close" title="Đóng">&times;</a>
    </div>

    <div class="modal-body">
      <div class="detail-grid">
        <div class="detail-item">
          <span class="label">Khách hàng</span>
          <span class="value"><?= h($detail['ten_khach']) ?></span>
        </div>
        <div class="detail-item">
          <span class="label">Email</span>
          <span class="value"><?= h($detail['email']) ?></span>
        </div>
        <div class="detail-item">
          <span class="label">Người nhận</span>
          <span class="value"><?= h($detail['receiver_name']) ?></span>
        </div>
        <div class="detail-item">
          <span class="label">Số điện thoại</span>
          <span class="value"><?= h($detail['receiver_phone']) ?></span>
        </div>
        <div class="detail-item" style="grid-column:1/-1">
          <span class="label">Địa chỉ giao hàng</span>
          <span class="value"><?= h($detail['shipping_address']) ?></span>
        </div>
        <div class="detail-item">
          <span class="label">Ngày đặt</span>
          <span class="value"><?= formatDate($detail['created_at']) ?></span>
        </div>
        <div class="detail-item">
          <span class="label">Trạng thái</span>
          <span class="value">
            <span class="badge <?= badgeClass($detail['order_status']) ?>">
              <?= tenTrangThai($detail['order_status']) ?>
            </span>
          </span>
        </div>
        <div class="detail-item">
          <span class="label">Phương thức TT</span>
          <span class="value"><?= h($detail['payment_method'] ?? $detail['payment_method'] ?? '—') ?></span>
        </div>
        <div class="detail-item">
          <span class="label">Thanh toán</span>
          <span class="value"><?= h($detail['payment_status'] ?? '—') ?></span>
        </div>
        <?php if (!empty($detail['note'])): ?>
        <div class="detail-item" style="grid-column:1/-1">
          <span class="label">Ghi chú</span>
          <span class="value"><?= h($detail['note']) ?></span>
        </div>
        <?php endif; ?>
      </div>

      <table class="detail-products">
        <thead>
          <tr>
            <th>Sản phẩm</th>
            <th>SL</th>
            <th>Đơn giá</th>
            <th>Thành tiền</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($items)): ?>
            <tr><td colspan="4" style="text-align:center;color:var(--text-400)">Không có sản phẩm</td></tr>
          <?php else: ?>
            <?php foreach ($items as $it): ?>
              <tr>
                <td><?= h($it['product_name']) ?></td>
                <td><?= (int)$it['quantity'] ?></td>
                <td class="price"><?= formatMoney($it['unit_price']) ?></td>
                <td class="price"><?= formatMoney($it['subtotal']) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>

      <div class="detail-total">
        Tổng tiền: <span><?= formatMoney($detail['total_amount']) ?></span>
      </div>

      <!-- Form cập nhật trạng thái -->
      <form method="post">
        <input type="hidden" name="order_id" value="<?= (int)$detail['id'] ?>">
        <input type="hidden" name="action" value="update_status">
        <div class="field">
          <label>Cập nhật trạng thái đơn hàng</label>
          <div style="display:flex;gap:10px">
            <select name="trang_thai" style="flex:1">
              <?php foreach (['pending','confirmed','processing','shipping','delivered','cancelled','returned'] as $s): ?>
                <option value="<?= $s ?>" <?= $detail['order_status'] === $s ? 'selected' : '' ?>>
                  <?= tenTrangThai($s) ?>
                </option>
              <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary">Cập nhật</button>
          </div>
        </div>
      </form>
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
        window.location.href = 'order.php?q=<?= urlencode($keyword) ?>&status=<?= urlencode($status) ?>';
      }
    });
  });
</script>
</body>
</html>