<?php
require_once __DIR__ . '/../api/config/database.php'; 



session_start();
require_once __DIR__ . '/../api/config/database.php'; // chỉnh đường dẫn cho đúng

// ===================== KIỂM TRA ĐĂNG NHẬP =====================
$isLoggedIn  = isset($_SESSION['user_id']);
$userName    = $_SESSION['user_name'] ?? '';
$userAvatar  = $_SESSION['user_avatar'] ?? '';
$cartCount   = 0;


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


$q            = trim($_GET['q'] ?? '');
$category_id  = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$condition    = $_GET['condition'] ?? '';
$sort         = $_GET['sort'] ?? 'newest';
$page         = max(1, (int)($_GET['page'] ?? 1));
$limit        = 12;
$offset       = ($page - 1) * $limit;

$orderBy = 'p.id DESC';
switch ($sort) {
    case 'gia_tang':  $orderBy = 'p.price ASC'; break;
    case 'gia_giam': $orderBy = 'p.price DESC'; break;
    default:           $orderBy = 'p.id DESC';
}

$where  = ["p.status = 'available'"];
$params = [];

if ($q !== '') {
    $keywords = explode(' ', $q);
    $searchClauses = [];
    
    foreach ($keywords as $index => $word) {
        $word = trim($word);
        if ($word !== '') {
            // Mỗi từ sẽ được tìm trong tên, mô tả or danh mục
            $searchClauses[] = "(p.name LIKE :q_name_$index OR p.description LIKE :q_desc_$index OR c.name LIKE :q_cat_$index)";
            $params[":q_name_$index"] = '%' . $word . '%';
            $params[":q_desc_$index"] = '%' . $word . '%';
            $params[":q_cat_$index"]  = '%' . $word . '%';
        }
    }
    
    if (!empty($searchClauses)) {
        $where[] = "(" . implode(' AND ', $searchClauses) . ")";
    }
}
if ($category_id > 0) {
    $where[] = "p.category_id = :cat";
    $params[':cat'] = $category_id;
}
if ($condition !== '' && in_array($condition, ['like_new','excellent','good','fair','poor'])) {
    $where[] = "p.condition_type = :cond";
    $params[':cond'] = $condition;
}

$whereSql = implode(' AND ', $where);

$countSql = "
    SELECT COUNT(*) 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE $whereSql
";
$stmtCount = $pdo->prepare($countSql);
$stmtCount->execute($params);
$totalItems = (int)$stmtCount->fetchColumn();
$totalPages = max(1, ceil($totalItems / $limit));

$sql = "
    SELECT 
        p.id,
        p.name,
        p.price,
        p.condition_type,
        p.stock,
        c.name AS category_name,
        pi.image_url
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
    WHERE $whereSql
    ORDER BY $orderBy
    LIMIT $limit OFFSET $offset
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$catSql = "
    SELECT c.id, c.name, COUNT(p.id) AS total
    FROM categories c
    LEFT JOIN products p ON p.category_id = c.id AND p.status = 'available'
    GROUP BY c.id, c.name
    ORDER BY c.name
";
$categories = $pdo->query($catSql)->fetchAll(PDO::FETCH_ASSOC);

$totalAll = $pdo->query("SELECT COUNT(*) FROM products WHERE status = 'available'")->fetchColumn();

$nhanTinhTrang = [
    'like_new'  => 'Like New',
    'excellent' => 'Excellent',
    'good'      => 'Good',
    'fair'      => 'Fair',
    'poor'      => 'Poor'
];
$badgeClass = [
    'like_new'  => 'badge-like-new',
    'excellent' => 'badge-excellent',
    'good'      => 'badge-good',
    'fair'      => 'badge-fair',
    'poor'      => 'badge-poor'
];


?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tất cả sản phẩm – TheSecond</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="../assets/css/styles.css">

<style>
    /* ===== PAGE HEADER ===== */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 16px;
    }
    .page-title {
        font-size: 26px;
        font-weight: 900;
        color: #0f172a;
    }
    .page-sub {
        font-size: 14px;
        color: #64748b;
        margin-top: 4px;
    }
    .page-tools {
        display: flex;
        gap: 10px;
        align-items: center;
    }
    .page-tools select {
        padding: 9px 14px;
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
        background: #fff;
        outline: none;
        cursor: pointer;
    }
    .page-tools select:focus { border-color: #6366f1; }

    /* ===== LAYOUT 2 CỘT ===== */
    .shop-layout {
        display: grid;
        grid-template-columns: 240px 1fr;
        gap: 28px;
        align-items: start;
    }

    /* ===== SIDEBAR ===== */
    .sidebar-filter {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 20px;
        position: sticky;
        top: 90px;
    }
    .filter-group { margin-bottom: 28px; }
    .filter-group:last-child { margin-bottom: 0; }
    .filter-title {
        font-size: 13px;
        font-weight: 800;
        color: #0f172a;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 12px;
    }
    .filter-list { list-style: none; }
    .filter-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 12px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
        color: #475569;
        cursor: pointer;
        transition: all .15s;
        margin-bottom: 2px;
    }
    .filter-item:hover { background: #f1f5f9; color: #0f172a; }
    .filter-item.active {
        background: #eef2ff;
        color: #4f46e5;
        font-weight: 700;
    }
    .filter-count {
        font-size: 12px;
        color: #94a3b8;
        font-weight: 600;
    }
    .filter-item.active .filter-count { color: #6366f1; }

    /* ===== PRODUCT GRID (3 cột) ===== */
    .product-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 20px;
    }
    .product-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        transition: all .25s ease;
        box-shadow: 0 2px 8px rgba(15,23,42,0.04);
    }
    .product-card:hover {
        transform: translateY(-5px);
        border-color: #c7d2fe;
        box-shadow: 0 16px 32px rgba(79,70,229,0.12);
    }
    .product-image-wrap {
        position: relative;
        width: 100%;
        height: 200px;
        background: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }
    .product-image-wrap img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        padding: 16px;
        transition: transform .3s;
    }
    .product-card:hover .product-image-wrap img { transform: scale(1.05); }

    /* Badge tình trạng */
    .cond-badge {
        position: absolute;
        top: 12px;
        left: 12px;
        padding: 4px 10px;
        border-radius: 8px;
        font-size: 11px;
        font-weight: 800;
        color: #fff;
        z-index: 2;
    }
    .badge-like-new  { background: #10b981; }
    .badge-excellent { background: #3b82f6; }
    .badge-good      { background: #6366f1; }
    .badge-fair      { background: #f59e0b; }
    .badge-poor      { background: #94a3b8; }

    .product-card-body {
        padding: 16px;
        display: flex;
        flex-direction: column;
        flex: 1;
    }
    .product-category {
        display: inline-block;
        font-size: 11px;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        margin-bottom: 6px;
    }
    .product-name {
        font-size: 14px;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.4;
        margin-bottom: 10px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 40px;
    }
    .product-price-row {
        display: flex;
        align-items: baseline;
        gap: 8px;
        margin-top: auto;
        margin-bottom: 14px;
        padding-top: 12px;
        border-top: 1px solid #f1f5f9;
    }
    .product-price {
        font-size: 16px;
        font-weight: 900;
        color: #ef4444;
    }
    .add-cart-btn {
        width: 100%;
        padding: 10px 14px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 800;
        cursor: pointer;
        transition: all .2s;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        background: #4f46e5;
        color: #fff;
    }
    .add-cart-btn:hover { background: #4338ca; }

    /* ===== PHÂN TRANG ===== */
    .pagination {
        display: flex;
        justify-content: center;
        gap: 6px;
        margin-top: 36px;
        flex-wrap: wrap;
    }
    .page-btn {
        min-width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 700;
        border: 1.5px solid #e2e8f0;
        background: #fff;
        color: #475569;
        transition: all .15s;
    }
    .page-btn:hover { border-color: #c7d2fe; color: #4f46e5; }
    .page-btn.active {
        background: #4f46e5;
        border-color: #4f46e5;
        color: #fff;
    }
    .page-btn.disabled { opacity: 0.4; pointer-events: none; }

    /* Empty */
    .empty-state {
        grid-column: 1 / -1;
        text-align: center;
        padding: 60px 20px;
        color: #64748b;
    }
    .empty-state h3 {
        font-size: 18px;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 8px;
    }

    /* Cart count */
    .cart-count {
        background: #ef4444;
        color: #fff;
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 10px;
        font-weight: 900;
        margin-left: 4px;
    }

    /* Responsive */
    @media (max-width: 1000px) {
        .shop-layout { grid-template-columns: 1fr; }
        .sidebar-filter { position: static; }
        .product-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 600px) {
        .product-grid { grid-template-columns: 1fr; }
        .page-header { flex-direction: column; align-items: flex-start; }
    }
</style>
</head>
<body>

<!-- ================= HEADER ================= -->
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
    <input 
        type="text" 
        name="q" 
        placeholder="Tìm kiếm iPhone, Macbook..." 
        value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>"
    >
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

<!-- ================= MAIN ================= -->
<main class="main-content">

    <div class="page-header">
        <div>
            <h1 class="page-title">Tất cả sản phẩm</h1>
            <p class="page-sub"><?= $totalItems ?> sản phẩm được tìm thấy</p>
        </div>

        <div class="page-tools">
            <form method="GET" id="sortForm">
                <?php if ($q): ?><input type="hidden" name="q" value="<?= htmlspecialchars($q) ?>"><?php endif; ?>
                <?php if ($category_id): ?><input type="hidden" name="category" value="<?= $category_id ?>"><?php endif; ?>
                <?php if ($condition): ?><input type="hidden" name="condition" value="<?= htmlspecialchars($condition) ?>"><?php endif; ?>

                <select name="sort" onchange="this.form.submit()">
                    <option value="mac_dinh" <?= $sort==='mac_dinh'?'selected':'' ?>>Mới nhất</option>
                    <option value="gia_tang" <?= $sort==='gia_tang'?'selected':'' ?>>Giá thấp đến cao</option>
                    <option value="gia_giam" <?= $sort==='gia_giam'?'selected':'' ?>>Giá cao đến thấp</option>
                </select>
            </form>
        </div>
    </div>

    <div class="shop-layout">

        <!-- ===== SIDEBAR FILTER ===== -->
        <aside class="sidebar-filter">

            <!-- Category -->
            <div class="filter-group">
                <div class="filter-title">Danh mục</div>
                <ul class="filter-list">
                    <li>
                        <a href="?<?= http_build_query(array_filter(['q'=>$q,'condition'=>$condition,'sort'=>$sort])) ?>"
                           class="filter-item <?= $category_id===0?'active':'' ?>">
                            <span>Tất cả</span>
                            <span class="filter-count"><?= $totalAll ?></span>
                        </a>
                    </li>
                    <?php foreach ($categories as $cat): ?>
                        <li>
                            <a href="?<?= http_build_query(array_filter([
                                'q' => $q,
                                'category' => $cat['id'],
                                'condition' => $condition,
                                'sort' => $sort
                            ])) ?>"
                               class="filter-item <?= $category_id==$cat['id']?'active':'' ?>">
                                <span><?= htmlspecialchars($cat['name']) ?></span>
                                <span class="filter-count"><?= $cat['total'] ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Condition -->
            <div class="filter-group">
                <div class="filter-title">Tình trạng</div>
                <ul class="filter-list">
                    <li>
                        <a href="?<?= http_build_query(array_filter(['q'=>$q,'category'=>$category_id,'sort'=>$sort])) ?>"
                           class="filter-item <?= $condition===''?'active':'' ?>">
                            <span>Tất cả</span>
                        </a>
                    </li>
                    <?php foreach ($nhanTinhTrang as $key => $label): ?>
                        <li>
                            <a href="?<?= http_build_query(array_filter([
                                'q' => $q,
                                'category' => $category_id,
                                'condition' => $key,
                                'sort' => $sort
                            ])) ?>"
                               class="filter-item <?= $condition===$key?'active':'' ?>">
                                <span><?= $label ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </aside>

        <!-- ===== PRODUCT GRID ===== -->
        <div>
            <div class="product-grid">
                <?php if (empty($products)): ?>
                    <div class="empty-state">
                        <h3>Không tìm thấy sản phẩm</h3>
                        <p>Thử thay đổi bộ lọc hoặc từ khóa tìm kiếm.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($products as $sp): ?>
                        <div class="product-card">
                            <a href="product_detail.php?id=<?= $sp['id'] ?>" class="product-image-wrap">
                                <?php if (!empty($sp['image_url'])): ?>
                                    <img src="<?= htmlspecialchars('../api/admin/product/'.$sp['image_url']) ?>"
                                         alt="<?= htmlspecialchars($sp['name']) ?>" loading="lazy">
                                <?php else: ?>
                                    <div style="font-size:48px">📱</div>
                                <?php endif; ?>

                                <span class="cond-badge <?= $badgeClass[$sp['condition_type']] ?? 'badge-good' ?>">
                                    <?= $nhanTinhTrang[$sp['condition_type']] ?? $sp['condition_type'] ?>
                                </span>
                            </a>

                            <div class="product-card-body">
                                <div class="product-category">
                                    <?= htmlspecialchars($sp['category_name'] ?? 'Khác') ?>
                                </div>

                                <h3 class="product-name">
                                    <?= htmlspecialchars($sp['name']) ?>
                                </h3>

                                <div class="product-price-row">
                                    <span class="product-price">
                                        <?= number_format($sp['price'], 0, ',', '.') ?> ₫
                                    </span>
                                </div>

                                <a href="product_detail.php?id=<?= $sp['id'] ?>" class="add-cart-btn">
                                    <i data-lucide="shopping-cart" style="width:15px;height:15px"></i>
                                    Xem chi tiết
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- PHÂN TRANG -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <!-- Prev -->
                    <a class="page-btn <?= $page<=1?'disabled':'' ?>"
                       href="?<?= http_build_query(array_filter([
                           'q'=>$q, 'category'=>$category_id, 'condition'=>$condition,
                           'sort'=>$sort, 'page'=>$page-1
                       ])) ?>">‹</a>

                    <?php
                    $start = max(1, $page - 2);
                    $end   = min($totalPages, $page + 2);
                    for ($i = $start; $i <= $end; $i++):
                    ?>
                        <a class="page-btn <?= $i==$page?'active':'' ?>"
                           href="?<?= http_build_query(array_filter([
                               'q'=>$q, 'category'=>$category_id, 'condition'=>$condition,
                               'sort'=>$sort, 'page'=>$i
                           ])) ?>"><?= $i ?></a>
                    <?php endfor; ?>

                    <!-- Next -->
                    <a class="page-btn <?= $page>=$totalPages?'disabled':'' ?>"
                       href="?<?= http_build_query(array_filter([
                           'q'=>$q, 'category'=>$category_id, 'condition'=>$condition,
                           'sort'=>$sort, 'page'=>$page+1
                       ])) ?>">›</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<footer style="background:#0f172a;color:#94a3b8;padding:40px 20px;margin-top:40px;">
    <div style="max-width:1280px;margin:0 auto;text-align:center;">
        <div style="font-size:24px;font-weight:900;color:#e2e8f0;margin-bottom:12px;">THESECOND</div>
        <p style="font-size:13px;margin-bottom:24px;">Nền tảng mua bán đồ điện tử cũ an toàn, minh bạch và tiết kiệm nhất.</p>
        <div style="margin-top:30px;font-size:12px;border-top:1px solid #1e293b;padding-top:20px;">
            © 2026 TheSecond - Lập Trình Web [012012103104].
        </div>
    </div>
</footer>

<script>
    if (typeof lucide !== 'undefined') lucide.createIcons();
</script>
</body>
</html>