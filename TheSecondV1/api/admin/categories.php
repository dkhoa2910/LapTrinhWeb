<?php
declare(strict_types=1);

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

function slugify(string $text): string {
    $text = mb_strtolower($text, 'UTF-8');
    $text = preg_replace('/[áàảãạăắằẳẵặâấầẩẫậ]/u', 'a', $text);
    $text = preg_replace('/[éèẻẽẹêếềểễệ]/u', 'e', $text);
    $text = preg_replace('/[íìỉĩị]/u', 'i', $text);
    $text = preg_replace('/[óòỏõọôốồổỗộơớờởỡợ]/u', 'o', $text);
    $text = preg_replace('/[úùủũụưứừửữự]/u', 'u', $text);
    $text = preg_replace('/[ýỳỷỹỵ]/u', 'y', $text);
    $text = preg_replace('/đ/u', 'd', $text);
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', trim($text));
    return $text ?: 'category';
}

/* ========== XỬ LÝ THÊM / SỬA / XÓA ========== */
$thongBao = '';
$loaiThongBao = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // --- THÊM ---
    if ($action === 'add') {
        $name   = trim($_POST['name'] ?? '');
        $status = ($_POST['status'] ?? '') === 'inactive' ? 'inactive' : 'active';
        $desc   = trim($_POST['description'] ?? '');

        if ($name === '') {
            $thongBao = 'Tên danh mục không được để trống.';
            $loaiThongBao = 'error';
        } else {
            $slug = slugify($name);
            // Đảm bảo slug unique
            $check = $pdo->prepare("SELECT id FROM categories WHERE slug = ?");
            $check->execute([$slug]);
            if ($check->fetch()) {
                $slug .= '-' . time();
            }

            $stmt = $pdo->prepare("
                INSERT INTO categories (name, slug, description, status)
                VALUES (?, ?, ?, ?)
            ");
            if ($stmt->execute([$name, $slug, $desc ?: null, $status])) {
                $thongBao = 'Thêm danh mục thành công.';
                $loaiThongBao = 'success';
            } else {
                $thongBao = 'Thêm danh mục thất bại.';
                $loaiThongBao = 'error';
            }
        }
    }

    // --- SỬA ---
    if ($action === 'edit') {
        $id     = (int)($_POST['id'] ?? 0);
        $name   = trim($_POST['name'] ?? '');
        $status = ($_POST['status'] ?? '') === 'inactive' ? 'inactive' : 'active';
        $desc   = trim($_POST['description'] ?? '');

        if ($id <= 0 || $name === '') {
            $thongBao = 'Dữ liệu không hợp lệ.';
            $loaiThongBao = 'error';
        } else {
            $slug = slugify($name);
            $check = $pdo->prepare("SELECT id FROM categories WHERE slug = ? AND id != ?");
            $check->execute([$slug, $id]);
            if ($check->fetch()) {
                $slug .= '-' . $id;
            }

            $stmt = $pdo->prepare("
                UPDATE categories 
                SET name = ?, slug = ?, description = ?, status = ?
                WHERE id = ?
            ");
            if ($stmt->execute([$name, $slug, $desc ?: null, $status, $id])) {
                $thongBao = 'Cập nhật danh mục thành công.';
                $loaiThongBao = 'success';
            } else {
                $thongBao = 'Cập nhật thất bại.';
                $loaiThongBao = 'error';
            }
        }
    }

    // --- XÓA ---
    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            // Kiểm tra còn sản phẩm không
            $cnt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
            $cnt->execute([$id]);
            if ((int)$cnt->fetchColumn() > 0) {
                $thongBao = 'Không thể xóa vì danh mục vẫn còn sản phẩm.';
                $loaiThongBao = 'error';
            } else {
                $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
                if ($stmt->execute([$id])) {
                    $thongBao = 'Đã xóa danh mục.';
                    $loaiThongBao = 'success';
                } else {
                    $thongBao = 'Xóa thất bại.';
                    $loaiThongBao = 'error';
                }
            }
        }
    }

    // Redirect để tránh resubmit
    $redirect = 'categories.php';
    if ($thongBao) {
        $redirect .= '?msg=' . urlencode($thongBao) . '&type=' . $loaiThongBao;
    }
    header('Location: ' . $redirect);
    exit;
}

/* ========== LẤY DANH SÁCH ========== */
$stmt = $pdo->query("
    SELECT
        c.id, c.name, c.slug, c.description, c.image, c.status,
        c.created_at, c.updated_at,
        (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) AS product_count
    FROM categories c
    ORDER BY c.id ASC
");
$categories = $stmt->fetchAll();

/* ========== LẤY DỮ LIỆU SỬA (nếu có) ========== */
$editCategory = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    if ($editId > 0) {
        $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([$editId]);
        $editCategory = $stmt->fetch();
    }
}

function categoryIcon(string $name): string {
    $name = mb_strtolower($name, 'UTF-8');
    if (str_contains($name, 'điện thoại')) return '📱';
    if (str_contains($name, 'laptop')) return '💻';
    if (str_contains($name, 'tablet') || str_contains($name, 'máy tính bảng')) return '📱';
    if (str_contains($name, 'đồng hồ')) return '⌚';
    if (str_contains($name, 'tai nghe')) return '🎧';
    if (str_contains($name, 'phụ kiện')) return '🔌';
    return '📦';
}

// Thông báo từ redirect
if (isset($_GET['msg'])) {
    $thongBao = $_GET['msg'];
    $loaiThongBao = $_GET['type'] ?? 'success';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>TheSecond Admin — Danh mục</title>
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
      <a class="nav-item active" href="categories.php">
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
          <div class="page-title" id="pageTitle">Danh mục</div>
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
      <section class="page active" id="page-categories">
        <div class="section-head">
          <div>
            <h2>Quản lý danh mục</h2>
            <div class="section-sub">Sắp xếp các nhóm sản phẩm hiển thị trên trang chủ.</div>
          </div>
          <button class="btn btn-primary" onclick="openModal('modalCategory')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
            Thêm danh mục
          </button>
        </div>

        <?php if ($thongBao): ?>
          <div style="margin-bottom:16px;padding:12px 16px;border-radius:10px;font-weight:600;
                      background:<?= $loaiThongBao === 'success' ? 'var(--green-100)' : 'var(--red-100)' ?>;
                      color:<?= $loaiThongBao === 'success' ? 'var(--green-600)' : 'var(--red-600)' ?>">
            <?= h($thongBao) ?>
          </div>
        <?php endif; ?>

        <div class="panel">
          <table>
            <thead>
              <tr>
                <th>Danh mục</th>
                <th>Số sản phẩm</th>
                <th>Trạng thái</th>
                <th>Hành động</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($categories)): ?>
                <tr>
                  <td colspan="4" style="text-align:center;padding:24px;color:var(--text-400)">
                    Chưa có danh mục nào trong database.
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($categories as $category): ?>
                  <tr>
                    <td data-label="Danh mục">
                      <div class="cell-main">
                        <div class="thumb">
                          <?php if (!empty($category['image'])): ?>
                            <img src="<?= h($category['image']) ?>" alt="<?= h($category['name']) ?>"
                                 style="width:40px;height:40px;object-fit:cover;border-radius:8px;">
                          <?php else: ?>
                            <?= categoryIcon((string)$category['name']) ?>
                          <?php endif; ?>
                        </div>
                        <div>
                          <div class="cell-title"><?= h($category['name']) ?></div>
                        </div>
                      </div>
                    </td>
                    <td data-label="Số sản phẩm"><?= (int)$category['product_count'] ?></td>
                    <td data-label="Trạng thái">
                      <?php if ($category['status'] === 'active'): ?>
                        <span class="badge badge-green">Hiển thị</span>
                      <?php else: ?>
                        <span class="badge badge-gray">Đã ẩn</span>
                      <?php endif; ?>
                    </td>
                    <td data-label="Hành động">
                      <div class="row-actions">
                        <!-- Sửa -->
                        <a class="icon-action" title="Sửa"
                           href="?edit=<?= (int)$category['id'] ?>">
                          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1-1-4Z"/>
                          </svg>
                        </a>
                        <!-- Xóa -->
                        <form method="post" style="display:inline"
                              onsubmit="return confirm('Bạn chắc chắn muốn xóa danh mục «<?= h(addslashes($category['name'])) ?>»?')">
                          <input type="hidden" name="action" value="delete">
                          <input type="hidden" name="id" value="<?= (int)$category['id'] ?>">
                          <button type="submit" class="icon-action danger" title="Xóa">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                              <path d="M3 6h18M8 6V4h8v2M6 6l1 14h10l1-14"/>
                            </svg>
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

<!-- ========== MODAL THÊM DANH MỤC ========== -->
<div class="overlay" id="modalCategory">
  <div class="modal">
    <div class="modal-head">
      <h3>Thêm danh mục</h3>
      <button class="modal-close" onclick="closeModal('modalCategory')">✕</button>
    </div>
    <form method="post">
      <input type="hidden" name="action" value="add">
      <div class="modal-body">
        <div class="field">
          <label>Tên danh mục <span style="color:var(--red-600)">*</span></label>
          <input type="text" name="name" placeholder="VD: Máy tính bảng" required>
        </div>
        <div class="field">
          <label>Mô tả (tuỳ chọn)</label>
          <input type="text" name="description" placeholder="Mô tả ngắn...">
        </div>
        <div class="field">
          <label>Trạng thái hiển thị</label>
          <select name="status">
            <option value="active">Hiển thị</option>
            <option value="inactive">Ẩn</option>
          </select>
        </div>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn btn-outline" onclick="closeModal('modalCategory')">Huỷ</button>
        <button type="submit" class="btn btn-primary">Lưu danh mục</button>
      </div>
    </form>
  </div>
</div>

<!-- ========== MODAL SỬA DANH MỤC ========== -->
<?php if ($editCategory): ?>
<div class="overlay show" id="modalEditCategory">
  <div class="modal">
    <div class="modal-head">
      <h3>Sửa danh mục</h3>
      <a href="categories.php" class="modal-close" title="Đóng">✕</a>
    </div>
    <form method="post">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="id" value="<?= (int)$editCategory['id'] ?>">
      <div class="modal-body">
        <div class="field">
          <label>Tên danh mục <span style="color:var(--red-600)">*</span></label>
          <input type="text" name="name" value="<?= h($editCategory['name']) ?>" required>
        </div>
        <div class="field">
          <label>Mô tả (tuỳ chọn)</label>
          <input type="text" name="description" value="<?= h($editCategory['description'] ?? '') ?>">
        </div>
        <div class="field">
          <label>Trạng thái hiển thị</label>
          <select name="status">
            <option value="active"   <?= $editCategory['status'] === 'active'   ? 'selected' : '' ?>>Hiển thị</option>
            <option value="inactive" <?= $editCategory['status'] === 'inactive' ? 'selected' : '' ?>>Ẩn</option>
          </select>
        </div>
      </div>
      <div class="modal-foot">
        <a href="categories.php" class="btn btn-outline">Huỷ</a>
        <button type="submit" class="btn btn-primary">Cập nhật</button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<script src="../../admin/admin.js"></script>
<script>
  // Đóng modal sửa khi click nền
  document.querySelectorAll('.overlay').forEach(ov => {
    ov.addEventListener('click', e => {
      if (e.target === ov && ov.id === 'modalEditCategory') {
        window.location.href = 'categories.php';
      }
    });
  });
</script>
</body>
</html>