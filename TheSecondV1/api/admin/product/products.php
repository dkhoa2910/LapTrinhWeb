<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: /../../../../admin/admin_login.html");
    exit();
}
include("../../config/database.php");

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

/* ===== PHÂN TRANG ===== */
$limit  = 10; // số sản phẩm mỗi trang
$page   = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

/* ===== LỌC ===== */
$search      = trim($_GET['search'] ?? '');
$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
$status      = $_GET['status'] ?? '';

$where  = [];
$params = [];

/* Tìm theo tên hoặc SKU */
if ($search !== '') {
    $where[] = "(p.name LIKE :search_name OR p.sku LIKE :search_sku)";
    $params[':search_name'] = "%{$search}%";
    $params[':search_sku']  = "%{$search}%";   // ← thêm dòng này
}

/* Lọc danh mục */
if ($category_id > 0) {
    $where[] = "p.category_id = :category_id";
    $params[':category_id'] = $category_id;
}

/* Lọc trạng thái */
if (in_array($status, ['available', 'sold_out', 'hidden'], true)) {
    $where[] = "p.status = :status";
    $params[':status'] = $status;
}

$whereSql = $where
    ? 'WHERE ' . implode(' AND ', $where)
    : '';

/* ===== ĐẾM TỔNG ===== */
$sqlCount = "SELECT COUNT(*)
             FROM products p
             $whereSql";

$stmtCount = $pdo->prepare($sqlCount);

foreach ($params as $key => $value) {
    $stmtCount->bindValue($key, $value);
}

$stmtCount->execute();
$total_records = (int)$stmtCount->fetchColumn();
$total_pages   = max(1, (int)ceil($total_records / $limit));

/* ===== LẤY DỮ LIỆU ===== */
$sql = "SELECT 
            p.id,
            p.name,
            p.sku,
            p.price,
            p.condition_type,
            p.stock,
            p.status,
            p.description,
            p.category_id,
            p.specifications,
            c.name AS category_name,
            pi.image_url
        FROM products p
        LEFT JOIN categories c 
            ON p.category_id = c.id
        LEFT JOIN product_images pi
            ON p.id = pi.product_id AND pi.is_primary = 1
        $whereSql
        ORDER BY p.id DESC
        LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($sql);

// bind các param lọc
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val);
}
$stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

$danh_sach_sp = $stmt->fetchAll();

/* ===== NHÃN HIỂN THỊ ===== */
$nhanTinhTrang = [
    'like_new'  => '~99%',
    'excellent' => '~95%',
    'good'      => '~85%',
    'fair'      => '~70%',
    'poor'      => 'Khác'
];

$nhanTrangThai = [
    'available' => 'Còn hàng',
    'sold_out'  => 'Hết hàng',
    'hidden'    => 'Ngừng bán'
];

/* ===== LẤY DANH MỤC ĐỂ ĐỔ SELECT ===== */
$categories = $pdo->query("SELECT id, name FROM categories WHERE status = 'active' ORDER BY name")->fetchAll();

// Tính hiển thị phân trang
$from = $total_records === 0 ? 0 : $offset + 1;
$to   = min($offset + $limit, $total_records);

// Giữ lại các param lọc khi chuyển trang
$queryParams = [];
if ($search !== '')      $queryParams['search'] = $search;
if ($category_id > 0)    $queryParams['category_id'] = $category_id;
if ($status !== '')      $queryParams['status'] = $status;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>TheSecond Admin — Sản phẩm</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../../../admin/admin.css">
<style>
  /* Style bổ sung cho icon action + pagination */
  .row-actions {
    display: flex;
    gap: 8px;
    align-items: center;
  }
  .icon-action {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    border: 1px solid #e5e7eb;
    background: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: #374151;
    transition: all 0.15s ease;
    text-decoration: none;
  }
  .icon-action svg {
    width: 18px;
    height: 18px;
  }
  .icon-action:hover {
    background: #f3f4f6;
    border-color: #d1d5db;
  }
  .icon-action.danger {
    color: #ef4444;
  }
  .icon-action.danger:hover {
    background: #fef2f2;
    border-color: #fecaca;
  }
  .pager {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px;
    border-top: 1px solid #e5e7eb;
    font-size: 14px;
    color: #6b7280;
  }
  .pager-btns {
    display: flex;
    gap: 6px;
  }
  .pager-btns a {
    text-decoration: none;
  }
  .pager-btns button {
    min-width: 36px;
    height: 36px;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    background: #fff;
    cursor: pointer;
    font-size: 14px;
    color: #374151;
  }
  .pager-btns button.active {
    background: #2563eb;
    color: #fff;
    border-color: #2563eb;
  }
  .pager-btns button:hover:not(.active) {
    background: #f3f4f6;
  }

 
  #modalProduct .modal,
  #modalEditProduct .modal {
      width: 720px;          
      max-width: 95vw;        
      max-height: 90vh;       
  }

  #modalProduct .modal-body,
  #modalEditProduct .modal-body {
      max-height: 70vh;       
      overflow-y: auto;
      padding: 24px 28px;
  }

  #modalProduct .field-row,
  #modalEditProduct .field-row {
      gap: 16px;
  }

  #add-specs-container .field-row,
  #edit-specs-container .field-row {
      gap: 12px;
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
      <a href="../../../user/index.html" class="btn btn-outline">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/></svg>
          Về trang User
      </a>
      <div class="nav-label">Tổng quan</div>
      <a class="nav-item" href="../dashboard.php">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/></svg>
        Dashboard
      </a>
      <div class="nav-label">Quản lý</div>
      <a class="nav-item active" href="products.php">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 7 12 3 4 7v10l8 4 8-4V7Z"/><path d="M4 7l8 4 8-4M12 11v10"/></svg>
        Sản phẩm
      </a>
      <a class="nav-item" href="../categories.php">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="8" height="8" rx="1.5"/><rect x="13" y="3" width="8" height="8" rx="1.5"/><rect x="3" y="13" width="8" height="8" rx="1.5"/><rect x="13" y="13" width="8" height="8" rx="1.5"/></svg>
        Danh mục
      </a>
      <a class="nav-item" href="../order.php">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2 3 6v14a1 1 0 0 0 1 1h16a1 1 0 0 0 1-1V6l-3-4Z"/><path d="M3 6h18M9 10a3 3 0 0 0 6 0"/></svg>
        Đơn hàng
      </a>
      <a class="nav-item" href="../customers.php">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/><circle cx="10" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        Khách hàng
      </a>
      <div class="nav-label">Báo cáo</div>
      <a class="nav-item" href="../stats.php">
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
          <div class="page-title" id="pageTitle">Sản phẩm</div>
        </div>
      </div>
      <form class="searchbox" method="get" action="products.php">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="11" cy="11" r="7"/>
        <path d="m21 21-4.3-4.3"/>
    </svg>

    <input type="text"
           name="search"
           value="<?= htmlspecialchars($search) ?>"
           placeholder="Tìm sản phẩm...">

    <?php if ($category_id > 0): ?>
        <input type="hidden" name="category_id" value="<?= $category_id ?>">
    <?php endif; ?>

    <?php if ($status !== ''): ?>
        <input type="hidden" name="status" value="<?= htmlspecialchars($status) ?>">
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
                    <a class="notif-item" href="../order.php?xem=<?= (int)$tb['id'] ?>">
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
                    <a class="notif-item" href="products.php">
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
            <a class="notif-foot" href="../order.php?status=pending">Xem tất cả đơn chờ xử lý</a>
          </div>
        </div>

        <div class="admin-chip">
          <div class="avatar">A</div>
          <div>
            <div class="admin-chip-role">Quản trị viên</div>
          </div>
        </div>
      </div>
    </header>

    <main class="content">
      <section class="page active" id="page-products">
        <div class="section-head">
          <div>
            <h2>Quản lý sản phẩm</h2>
            <div class="section-sub">Thêm, sửa, xoá và cập nhật tình trạng còn hàng của sản phẩm điện tử cũ trên TheSecond.</div>
          </div>
          <button class="btn btn-primary" onclick="openModal('modalProduct')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
            Thêm sản phẩm
          </button>
        </div>
        <div class="panel">
          <!-- TOOLBAR LỌC -->
          <div class="table-toolbar">
            <form method="get" style="display:flex; gap:12px; align-items:center; flex-wrap:wrap; width:100%;">
              
              <!-- Tìm kiếm -->
              <div class="searchbox" style="width:240px">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/>
                </svg>
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Tìm sản phẩm...">
              </div>

              <!-- Danh mục -->
              <select name="category_id" onchange="this.form.submit()">
                <option value="0">Tất cả danh mục</option>
                <?php foreach ($categories as $cat): ?>
                  <option value="<?= $cat['id'] ?>" <?= $category_id == $cat['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>

              <!-- Trạng thái -->
              <select name="status" onchange="this.form.submit()">
                <option value="">Tất cả tồn kho</option>
                <option value="available" <?= $status === 'available' ? 'selected' : '' ?>>Còn hàng</option>
                <option value="sold_out"  <?= $status === 'sold_out'  ? 'selected' : '' ?>>Hết hàng</option>
                <option value="hidden"    <?= $status === 'hidden'    ? 'selected' : '' ?>>Ngừng bán</option>
              </select>
              
              <?php if ($search || $category_id || $status): ?>
                <a href="products.php" class="btn btn-outline" style="padding:8px 14px;">Xóa lọc</a>
              <?php endif; ?>
            </form>
          </div>

          <table>
            <thead>
              <tr>
                <th>Sản phẩm</th>
                <th>Danh mục</th>
                <th>Giá</th>
                <th>Tình trạng máy</th>
                <th>Tồn kho</th>
                <th>Hành động</th>
              </tr>
            </thead>
            <tbody>
            <?php if (empty($danh_sach_sp)): ?>
              <tr>
                <td colspan="6" style="text-align:center;padding:40px;color:#888">
                  Chưa có sản phẩm nào
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($danh_sach_sp as $sp): ?>
                <tr>
                  <td>
                    <div class="cell-main">
                      <div class="thumb">
                        <?php if (!empty($sp['image_url'])): ?>
                          <img src="<?= htmlspecialchars($sp['image_url']) ?>" 
                               style="width:40px;height:40px;object-fit:cover;border-radius:6px">
                        <?php else: ?>
                          📱
                        <?php endif; ?>
                      </div>
                      <div>
                        <div class="cell-title"><?= htmlspecialchars($sp['name']) ?></div>
                      </div>
                    </div>
                  </td>
                  <td><?= htmlspecialchars($sp['category_name'] ?? '—') ?></td>
                  <td class="price"><?= number_format($sp['price'], 0, ',', '.') ?>₫</td>
                  <td><?= htmlspecialchars($nhanTinhTrang[$sp['condition_type']] ?? $sp['condition_type']) ?></td>
                  <td>
                    <?php if ($sp['stock'] > 0): ?>
                      <span class="badge badge-green">Còn hàng (<?= $sp['stock'] ?>)</span>
                    <?php else: ?>
                      <span class="badge badge-red">Hết hàng</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <div class="row-actions">
                      <button
                        type="button"
                        class="icon-action"
                        title="Sửa"
                        onclick='openEditModal(<?= json_encode([
                            "id" => (int)$sp["id"],
                            "name" => $sp["name"],
                            "category_id" => (int)$sp["category_id"],
                            "price" => (float)$sp["price"],
                            "condition_type" => $sp["condition_type"],
                            "stock" => (int)$sp["stock"],
                            "status" => $sp["status"],
                            "description" => $sp["description"],
                            "specifications" => $sp["specifications"]
                        ], JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'
                      >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <path d="M12 20h9"/>
                          <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
                        </svg>
                      </button>

                      <a href="daletepd.php?id=<?= $sp['id'] ?>"
                         class="icon-action danger"
                         title="Xoá"
                         onclick="return confirm('Bạn có chắc muốn xoá sản phẩm này?')">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <path d="M3 6h18"/>
                          <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/>
                          <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                          <line x1="10" y1="11" x2="10" y2="17"/>
                          <line x1="14" y1="11" x2="14" y2="17"/>
                        </svg>
                      </a>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
          </table>

          <!-- PHÂN TRANG -->
          <div class="pager">
            <span>
              Hiển thị <?= $from ?>–<?= $to ?> trong <?= number_format($total_records) ?> sản phẩm
            </span>

            <div class="pager-btns">
              <?php if ($page > 1): ?>
                <a href="?<?= http_build_query(array_merge($queryParams, ['page' => $page - 1])) ?>">
                  <button type="button">‹</button>
                </a>
              <?php endif; ?>

              <?php
              // Hiển thị tối đa 5 nút trang xung quanh trang hiện tại
              $start = max(1, $page - 2);
              $end   = min($total_pages, $page + 2);

              for ($i = $start; $i <= $end; $i++):
                $active = $i === $page ? 'active' : '';
              ?>
                <a href="?<?= http_build_query(array_merge($queryParams, ['page' => $i])) ?>">
                  <button type="button" class="<?= $active ?>"><?= $i ?></button>
                </a>
              <?php endfor; ?>

              <?php if ($page < $total_pages): ?>
                <a href="?<?= http_build_query(array_merge($queryParams, ['page' => $page + 1])) ?>">
                  <button type="button">›</button>
                </a>
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

<!-- Modal: Thêm sản phẩm -->
<div class="overlay" id="modalProduct">
  <div class="modal">
    <div class="modal-head">
      <h3>Thêm sản phẩm</h3>
      <button class="modal-close" onclick="closeModal('modalProduct')">✕</button>
    </div>
    <form action="themsp.php" method="POST" enctype="multipart/form-data">
      <div class="modal-body">
        <div class="field">
          <label>Tên sản phẩm</label>
          <input type="text" name="ten_san_pham" placeholder="VD: iPhone 12 Pro Max 128GB" required>
        </div>
        <div class="field-row">
          <div class="field">
            <label>Danh mục</label>
            <select name="danh_muc_id" required>
              <option value="">-- Chọn danh mục --</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field">
            <label>Giá bán (đ)</label>
            <input type="number" name="gia" placeholder="0" min="0" step="1000" required>
          </div>
        </div>
        <div class="field-row">
          <div class="field">
            <label>Tình trạng máy</label>
            <select name="tinh_trang" required>
              <option value="">-- Chọn tình trạng --</option>
              <option value="~99%">~99%</option>
              <option value="~95%">~95%</option>
              <option value="~85%">~85%</option>
              <option value="~70%">~70%</option>
              <option value="Khác">Khác</option>
            </select>
          </div>
          <div class="field">
            <label>Tồn kho</label>
            <select name="trang_thai" required>
              <option value="Còn hàng">Còn hàng</option>
              <option value="Hết hàng">Hết hàng</option>
              <option value="Ngừng bán">Ngừng bán</option>
            </select>
          </div>
          <div class="field">
            <label>Số lượng tồn</label>
            <input type="number" name="so_luong_ton" placeholder="0" min="0" required>
          </div>
        </div>
        <div class="field">
          <label>Hình ảnh sản phẩm</label>
          <input type="file" name="hinh_anh" accept="image/*">
        </div>
        <div class="field">
          <label>Mô tả ngắn</label>
          <textarea name="mo_ta" rows="3" placeholder="Mô tả thêm về sản phẩm..."></textarea>
        </div>
        <div class="field">
          <label>Thông số kỹ thuật</label>
          <div id="add-specs-container"></div>
          <button type="button" class="btn btn-outline" style="margin-top:8px;padding:6px 12px;font-size:13px" onclick="addSpecRow('add-specs-container')">
            + Thêm thông số
          </button>
          <input type="hidden" name="specifications" id="add_specifications_json">
        </div>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn btn-outline" onclick="closeModal('modalProduct')">Huỷ</button>
        <button type="submit" class="btn btn-primary">Lưu sản phẩm</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal: Chỉnh sửa sản phẩm -->
<div class="overlay" id="modalEditProduct">
  <div class="modal">
    <div class="modal-head">
      <h3>Chỉnh sửa sản phẩm</h3>
      <button class="modal-close" onclick="closeModal('modalEditProduct')">✕</button>
    </div>

    <form action="updatepd.php" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="id" id="edit_id">

      <div class="modal-body">
        <div class="field">
          <label>Tên sản phẩm</label>
          <input type="text" name="name" id="edit_name" required>
        </div>

        <div class="field-row">
          <div class="field">
            <label>Danh mục</label>
            <select name="category_id" id="edit_category_id" required>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field">
            <label>Giá bán (đ)</label>
            <input type="number" name="price" id="edit_price" min="0" step="1000" required>
          </div>
        </div>

        <div class="field-row">
          <div class="field">
            <label>Tình trạng máy</label>
            <select name="condition_type" id="edit_condition_type" required>
              <option value="like_new">~99%</option>
              <option value="excellent">~95%</option>
              <option value="good">~85%</option>
              <option value="fair">~70%</option>
              <option value="poor">Khác</option>
            </select>
          </div>
          <div class="field">
            <label>Trạng thái</label>
            <select name="status" id="edit_status" required>
              <option value="available">Còn hàng</option>
              <option value="sold_out">Hết hàng</option>
              <option value="hidden">Ngừng bán</option>
            </select>
          </div>
          <div class="field">
            <label>Số lượng tồn</label>
            <input type="number" name="stock" id="edit_stock" min="0" required>
          </div>
        </div>
        <div class="field">
          <label>Ảnh sản phẩm (để trống nếu không muốn thay đổi)</label>
          <input type="file" name="image" id="edit_image" accept="image/*">
          <small style="color:#888;font-size:12px;margin-top:4px;display:block;">
            Chọn ảnh mới nếu muốn cập nhật. Ảnh cũ sẽ được giữ nguyên nếu không chọn.
          </small>
        </div>

        <div class="field">
          <label>Mô tả</label>
          <textarea name="description" id="edit_description" rows="3"></textarea>
        </div>
      </div>

      <div class="field">
        <label>Thông số kỹ thuật</label>
        <div id="edit-specs-container"></div>
        <button type="button" class="btn btn-outline" style="margin-top:8px;padding:6px 12px;font-size:13px" onclick="addSpecRow('edit-specs-container')">
          + Thêm thông số
        </button>
        <input type="hidden" name="specifications" id="edit_specifications_json">
      </div>

      <div class="modal-foot">
        <button type="button" class="btn btn-outline" onclick="closeModal('modalEditProduct')">Huỷ</button>
        <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
      </div>
    </form>
  </div>
</div>

<script src="../../../admin/admin.js"></script>
<script>
// thêm specifications

// Hàm thêm 1 dòng thông số
function addSpecRow(containerId, key = '', value = '') {
  const container = document.getElementById(containerId);
  const row = document.createElement('div');
  row.className = 'field-row';
  row.style.marginBottom = '8px';
  row.style.alignItems = 'center';
  row.innerHTML = `
    <div class="field" style="flex:1; margin:0">
      <input type="text" class="spec-key" placeholder="Tên thông số (VD: Storage)" value="${key}" style="width:100%">
    </div>
    <div class="field" style="flex:1; margin:0">
      <input type="text" class="spec-value" placeholder="Giá trị (VD: 256GB)" value="${value}" style="width:100%">
    </div>
    <button type="button" class="icon-action danger" style="margin-left:6px" onclick="this.parentElement.remove()">
      ✕
    </button>
  `;
  container.appendChild(row);
}

// Gom tất cả thông số thành JSON trước khi submit
function collectSpecs(containerId, hiddenInputId) {
  const keys = document.querySelectorAll(`#${containerId} .spec-key`);
  const values = document.querySelectorAll(`#${containerId} .spec-value`);
  const data = {};
  
  keys.forEach((k, i) => {
    const key = k.value.trim();
    const val = values[i].value.trim();
    if (key) data[key] = val;
  });
  
  document.getElementById(hiddenInputId).value = JSON.stringify(data);
}

// Load template theo category_id
async function loadSpecTemplate(categoryId, containerId) {
  const container = document.getElementById(containerId);
  container.innerHTML = '';

  if (!categoryId) return;

  try {
    const res = await fetch(`get_spec_template.php?category_id=${categoryId}`);
    const templates = await res.json();

    if (templates.length > 0) {
      templates.forEach(t => {
        addSpecRow(containerId, t.spec_key, '');
      });
    } else {
      // Không có template → thêm 1 dòng trống
      addSpecRow(containerId);
    }
  } catch (e) {
    console.error(e);
    addSpecRow(containerId);
  }
}

// Gắn sự kiện khi chọn danh mục (Modal Thêm)
document.querySelector('#modalProduct select[name="danh_muc_id"]')
  ?.addEventListener('change', function () {
    loadSpecTemplate(this.value, 'add-specs-container');
  });

// Gắn sự kiện khi chọn danh mục (Modal Sửa)
document.getElementById('edit_category_id')
  ?.addEventListener('change', function () {
    loadSpecTemplate(this.value, 'edit-specs-container');
  });

// Trước khi submit form Thêm
document.querySelector('#modalProduct form')
  ?.addEventListener('submit', function () {
    collectSpecs('add-specs-container', 'add_specifications_json');
  });

// Trước khi submit form Sửa
document.querySelector('#modalEditProduct form')
  ?.addEventListener('submit', function () {
    collectSpecs('edit-specs-container', 'edit_specifications_json');
  });

function openEditModal(sp) {
  document.getElementById('edit_id').value = sp.id;
  document.getElementById('edit_name').value = sp.name;
  document.getElementById('edit_category_id').value = sp.category_id;
  document.getElementById('edit_price').value = sp.price;
  document.getElementById('edit_stock').value = sp.stock;
  document.getElementById('edit_condition_type').value = sp.condition_type;
  document.getElementById('edit_status').value = sp.status;
  document.getElementById('edit_description').value = sp.description || '';

  const container = document.getElementById('edit-specs-container');
  container.innerHTML = '';

  if (sp.specifications) {
    try {
      const specs = typeof sp.specifications === 'string'
        ? JSON.parse(sp.specifications)
        : sp.specifications;

      Object.entries(specs).forEach(([key, value]) => {
        addSpecRow('edit-specs-container', key, value);
      });
    } catch (e) {
      addSpecRow('edit-specs-container');
    }
  } else {
    // Nếu chưa có thông số → load template theo danh mục
    loadSpecTemplate(sp.category_id, 'edit-specs-container');
  }

  openModal('modalEditProduct');
}
</script>
</body>
</html>