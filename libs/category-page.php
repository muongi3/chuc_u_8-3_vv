<?php
/**
 * FILE: libs/_category.php
 * Trang danh mục sản phẩm — hỗ trợ cả lọc theo brand và keyword (phụ kiện)
 *
 * Tính năng:
 *  - Lọc theo brand_id (điện thoại) hoặc keyword (phụ kiện)
 *  - Phân trang (pagination) với LIMIT + OFFSET
 *  - Sắp xếp: Mới nhất / Giá tăng / Giá giảm / Tên A–Z
 *  - getBulkReviewStats → 1 query thay N queries (chống N+1)
 *  - Bảo mật: (int) cast, htmlspecialchars, real_escape_string
 */

ob_start();

// ─────────────────────────────────────────────────────────────
//  1. THAM SỐ ĐẦU VÀO
// ─────────────────────────────────────────────────────────────
$brand_id = isset($_GET['brand_id']) ? (int)$_GET['brand_id'] : 0;
$cat      = trim($_GET['cat'] ?? '');          // headphone/charger/case/powerbank
$page     = max(1, (int)($_GET['page'] ?? 1));
$allowed_sorts = ['newest', 'price_asc', 'price_desc', 'name_asc'];
$sort     = in_array($_GET['sort'] ?? '', $allowed_sorts) ? $_GET['sort'] : 'newest';
$limit    = 8;

// Danh mục phụ kiện (dùng chung sidebar + nav)
$accessory_cats = [
    ['cat' => 'headphone', 'label' => 'Tai nghe',      'icon' => 'fas fa-headphones'],
    ['cat' => 'charger',   'label' => 'Sạc & Cáp',    'icon' => 'fas fa-bolt'],
    ['cat' => 'case',      'label' => 'Ốp lưng',      'icon' => 'fas fa-shield-alt'],
    ['cat' => 'powerbank', 'label' => 'Pin dự phòng', 'icon' => 'fas fa-battery-full'],
];

// ─────────────────────────────────────────────────────────────
//  2. LẤY BRAND (cache session)
// ─────────────────────────────────────────────────────────────
if (!isset($_SESSION['cache_brands']) || !is_array($_SESSION['cache_brands'])) {
    $_SESSION['cache_brands'] = $manage->getBrands();
}
$all_brands = $_SESSION['cache_brands'];

// ─────────────────────────────────────────────────────────────
//  3. QUERY SẢN PHẨM
// ─────────────────────────────────────────────────────────────
if (!empty($cat)) {
    // ── Category mode: phụ kiện (dùng cột category trong DB) ──
    $products    = $product->getProductsByCategory($cat, $page, $limit, $sort);
    $total_items = $product->countByCategory($cat);

    $cur_acc = null;
    foreach ($accessory_cats as $ac) {
        if ($ac['cat'] === $cat) { $cur_acc = $ac; break; }
    }
    $brand_name    = $cur_acc ? $cur_acc['label'] : 'Phụ kiện';
    $cur_icon      = $cur_acc ? $cur_acc['icon']  : 'fas fa-plug';
    $current_brand = null;
} else {
    // ── Brand mode: điện thoại ──
    $products    = $product->getByBrand($brand_id, $page, $limit, $sort);
    $total_items = $product->countByBrand($brand_id);

    $current_brand = null;
    foreach ($all_brands as $b) {
        if ((int)$b['id'] === $brand_id) { $current_brand = $b; break; }
    }
    $brand_name = $current_brand
        ? htmlspecialchars($current_brand['brand'])
        : 'Tất cả điện thoại';
    $cur_icon = $current_brand
        ? ($brand_icons[$current_brand['brand']] ?? 'fas fa-mobile')
        : 'fas fa-mobile-alt';
}

$total_pages = max(1, (int)ceil($total_items / $limit));
if ($page > $total_pages) $page = $total_pages;

$product_ids = array_column($products, 'id');
$bulk_stats  = !empty($product_ids) ? $product->getBulkReviewStats($product_ids) : [];

// ─────────────────────────────────────────────────────────────
//  4. HELPER FUNCTIONS
// ─────────────────────────────────────────────────────────────

function catUrl(array $params = []) {
    $current = [
        'brand_id' => $_GET['brand_id'] ?? '',
        'cat'      => $_GET['cat']      ?? '',
        'sort'     => $_GET['sort']     ?? 'newest',
        'page'     => (int)($_GET['page'] ?? 1),
    ];
    $merged = array_merge($current, $params);
    if (empty($merged['brand_id'])) unset($merged['brand_id']);
    if (empty($merged['cat']))      unset($merged['cat']);
    if ($merged['page'] <= 1)       unset($merged['page']);
    if ($merged['sort'] === 'newest') unset($merged['sort']);
    return 'products.php' . (!empty($merged) ? '?' . http_build_query($merged) : '');
}


/**
 * Render sao đánh giá (dùng cached stats)
 */
function renderStars($avg, $size = 'sm') {
    $out = '<span class="text-warning">';
    for ($i = 1; $i <= 5; $i++) {
        $cls = $i <= $avg ? 'fas' : 'far';
        $out .= "<i class=\"{$cls} fa-star\"></i>";
    }
    $out .= '</span>';
    return $out;
}

// Nhãn sắp xếp
$sort_labels = [
    'newest'     => 'Mới nhất',
    'price_asc'  => 'Giá tăng dần',
    'price_desc' => 'Giá giảm dần',
    'name_asc'   => 'Tên A–Z',
];

// Icon cho brand
$brand_icons = [
    'Apple'   => 'fab fa-apple',
    'Samsung' => 'fab fa-android',
    'Redmi'   => 'fas fa-mobile-alt',
    'Oppo'    => 'fas fa-phone',
];
?>

<div class="homepage-layout">

    <!-- ══ SIDEBAR DANH MỤC ══ -->
    <aside class="brand-sidebar d-none d-lg-flex flex-column">
        <div class="sidebar-header">
            <i class="fas fa-th-list"></i> DANH MỤC SẢN PHẨM
        </div>
        <ul class="sidebar-menu flex-grow-1">

            <!-- ── ĐIỆN THOẠI ───────────────────────── -->
            <li class="sidebar-section-label">📱 ĐIỆN THOẠI</li>

            <?php
            $phone_brands = [
                0 => ['Tất cả điện thoại', 'fas fa-mobile-alt', false],
                3 => ['Apple iPhone',       'fab fa-apple',      true],
                1 => ['Samsung Galaxy',     'fab fa-android',    true],
                2 => ['Redmi / Xiaomi',     'fas fa-mobile',     true],
                4 => ['Oppo',               'fas fa-phone',      true],
            ];
            foreach ($phone_brands as $bid => $binfo):
                $is_active = (empty($cat) && $brand_id === $bid);
                $is_sub    = $binfo[2]; // indent sub-brands
            ?>
            <li class="<?php echo $is_sub ? 'sidebar-sub-item ' : ''; ?><?php echo $is_active ? 'active' : ''; ?>">
                <a href="javascript:void(0)"
                   onclick="loadCategoryPage({brand_id: <?php echo $bid; ?>, cat: '', page: 1})">
                    <i class="<?php echo $binfo[1]; ?>"></i>
                    <?php echo $binfo[0]; ?>
                </a>
            </li>
            <?php endforeach; ?>

            <!-- ── PHỤ KIỆN ─────────────────────────── -->
            <li class="sidebar-divider-item"></li>
            <li class="sidebar-section-label">🔌 PHỤ KIỆN</li>

            <?php foreach ($accessory_cats as $ac):
                $is_active = ($cat === $ac['cat']);
            ?>
            <li class="<?php echo $is_active ? 'active' : ''; ?>">
                <a href="javascript:void(0)"
                   onclick="loadCategoryPage({cat: '<?php echo $ac['cat']; ?>', brand_id: '', page: 1})">
                    <i class="<?php echo $ac['icon']; ?>"></i>
                    <?php echo $ac['label']; ?>
                </a>
            </li>
            <?php endforeach; ?>

        </ul>
        <div class="sidebar-contact">
            <div class="mb-1"><i class="fas fa-phone-alt me-2" style="color:var(--accent-light)"></i><strong>Hotline:</strong> 0358 *** ***</div>
            <div><i class="fas fa-clock me-2" style="color:var(--accent-light)"></i>8:00 – 21:30 hằng ngày</div>
        </div>
    </aside>

    <!-- ══ NỘI DUNG CHÍNH ══ -->
    <div class="main-content-area">

    <!-- BREADCRUMB -->
    <div class="breadcrumb-bar">
        <div class="container-fluid px-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 py-2">
                    <li class="breadcrumb-item"><a href="index.php"><i class="fas fa-home"></i> Trang chủ</a></li>
                    <li class="breadcrumb-item"><a href="products.php">Danh mục</a></li>
                    <?php if ($brand_id > 0): ?>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo $brand_name; ?></li>
                    <?php endif; ?>
                </ol>
            </nav>
        </div>
    </div>

    <section class="category-section py-4 mb-5">
        <div class="container-fluid px-3 px-md-4">
            <div id="category-content-area">

                <!-- Topbar: tiêu đề + sort + count -->
                <div class="cat-topbar">
                    <div class="cat-topbar-left">
                        <!-- Mobile pills: Điện thoại + Phụ kiện -->
                        <div class="mobile-brand-pills d-lg-none">
                            <!-- Điện thoại -->
                            <a href="javascript:void(0)" onclick="loadCategoryPage({brand_id: 0, cat: '', page: 1})"
                               class="brand-pill <?php echo (empty($cat) && $brand_id === 0) ? 'active' : ''; ?>">
                                <i class="fas fa-mobile-alt me-1"></i>Điện thoại
                            </a>
                            <!-- Phụ kiện -->
                            <?php foreach ($accessory_cats as $ac): ?>
                            <a href="javascript:void(0)"
                               onclick="loadCategoryPage({cat: '<?php echo $ac['cat']; ?>', brand_id: '', page: 1})"
                               class="brand-pill <?php echo ($cat === $ac['cat']) ? 'active' : ''; ?>">
                                <i class="<?php echo $ac['icon']; ?> me-1"></i><?php echo $ac['label']; ?>
                            </a>
                            <?php endforeach; ?>
                        </div>

                        <h5 class="cat-page-title">
                            <i class="<?php echo $cur_icon; ?> me-2 text-primary"></i>
                            <?php echo $brand_name; ?>
                            <span class="cat-count-badge"><?php echo $total_items; ?> sản phẩm</span>
                        </h5>
                    </div>

                    <!-- Sort dropdown -->
                    <div class="cat-sort-bar">
                        <label class="sort-label d-none d-md-inline">Sắp xếp:</label>
                        <div class="sort-pills">
                            <?php foreach ($sort_labels as $key => $label): ?>
                            <button type="button" onclick="loadCategoryPage({sort: '<?php echo $key; ?>', page: 1})"
                               class="sort-pill <?php echo $sort === $key ? 'active' : ''; ?>">
                                <?php echo $label; ?>
                            </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- ═══ PRODUCT GRID ═══ -->
                <?php if (!empty($products)): ?>
                <div class="row g-3 mt-1" id="category-product-grid">
                    <?php foreach ($products as $p):
                        // Lấy stats từ cache bulk — không query thêm
                        $pid   = (int)$p['id'];
                        $stats = $bulk_stats[$pid] ?? ['average' => 0, 'total' => 0];
                        $avg   = (float)$stats['average'];
                        $cnt   = (int)$stats['total'];

                        $b_icon_card = $brand_icons[$p['brand_name'] ?? ''] ?? 'fas fa-mobile';
                        $in_stock    = (int)($p['stock'] ?? 100) > 0;
                    ?>
                    <div class="col-6 col-md-4 col-xl-3">
                        <div class="cat-product-card">
                            <!-- Badge -->
                            <?php if (!$in_stock): ?>
                            <div class="cat-card-badge out">Hết hàng</div>
                            <?php else: ?>
                            <div class="cat-card-badge new">Mới</div>
                            <?php endif; ?>

                            <!-- Ảnh -->
                            <a href="details.php?id=<?php echo $pid; ?>" class="cat-card-img-wrap">
                                <img src="<?php echo htmlspecialchars(img_url($p['image'])); ?>"
                                     alt="<?php echo htmlspecialchars($p['name']); ?>"
                                     class="cat-card-img"
                                     loading="lazy">
                            </a>

                            <div class="cat-card-body">
                                <!-- Brand tag -->
                                <a href="<?php echo catUrl(['brand_id' => (int)$p['brand'], 'page' => 1]); ?>"
                                   class="cat-brand-tag">
                                    <i class="<?php echo $b_icon_card; ?> me-1"></i>
                                    <?php echo htmlspecialchars($p['brand_name'] ?? ''); ?>
                                </a>

                                <!-- Tên sản phẩm -->
                                <a href="details.php?id=<?php echo $pid; ?>" class="cat-card-name">
                                    <?php echo htmlspecialchars($p['name']); ?>
                                </a>

                                <!-- Rating -->
                                <div class="cat-card-rating">
                                    <?php echo renderStars($avg); ?>
                                    <span class="rating-count">(<?php echo $cnt; ?>)</span>
                                </div>

                                <!-- Giá -->
                                <div class="cat-card-price-row">
                                    <span class="cat-price">
                                        <?php echo number_format($p['price'], 0, ',', '.'); ?>đ
                                    </span>
                                    <span class="cat-price-old text-decoration-line-through text-muted">
                                        <?php echo number_format($p['price'] * 1.2, 0, ',', '.'); ?>đ
                                    </span>
                                </div>

                                <!-- Nút -->
                                <div class="cat-card-actions">
                                    <a href="details.php?id=<?php echo $pid; ?>"
                                       class="btn-cat-detail">
                                        <i class="fas fa-eye me-1"></i>Xem
                                    </a>
                                    <?php if ($in_stock): ?>
                                    <form method="POST" action="details.php?id=<?php echo $pid; ?>" class="m-0">
                                        <input type="hidden" name="item_id" value="<?php echo $pid; ?>">
                                        <input type="hidden" name="user_id" value="<?php echo (int)($_SESSION['user_id'] ?? 0); ?>">
                                        <input type="hidden" name="item_quantity" value="1">
                                        <button type="submit" name="product_submit"
                                                class="btn-cat-cart"
                                                <?php echo !isset($_SESSION['logged']) || !$_SESSION['logged'] ? 'onclick="window.location=\'login.php\';return false;"' : ''; ?>>
                                            <i class="fas fa-cart-plus"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div><!-- /row -->

                <!-- ═══ PAGINATION ═══ -->
                <?php if ($total_pages > 1): ?>
                <nav class="cat-pagination mt-4" aria-label="Phân trang">
                    <ul class="pagination-list">

                        <!-- Prev -->
                        <?php if ($page > 1): ?>
                        <li>
                            <button type="button" onclick="loadCategoryPage({page: <?php echo $page - 1; ?>})"
                               class="page-btn prev" aria-label="Trang trước">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                        </li>
                        <?php endif; ?>

                        <!-- Page numbers -->
                        <?php
                        $window = 2;
                        $start  = max(1, $page - $window);
                        $end    = min($total_pages, $page + $window);
                        for ($p_num = $start; $p_num <= $end; $p_num++): ?>
                        <li>
                            <button type="button" onclick="loadCategoryPage({page: <?php echo $p_num; ?>})"
                               class="page-btn <?php echo $p_num === $page ? 'active' : ''; ?>">
                                <?php echo $p_num; ?>
                            </button>
                        </li>
                        <?php endfor; ?>

                        <!-- Next -->
                        <?php if ($page < $total_pages): ?>
                        <li>
                            <button type="button" onclick="loadCategoryPage({page: <?php echo $page + 1; ?>})"
                               class="page-btn next" aria-label="Trang sau">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </li>
                        <?php endif; ?>
                    </ul>

                    <!-- Info -->
                    <p class="pagination-info">
                        Trang <strong><?php echo $page; ?></strong> /
                        <strong><?php echo $total_pages; ?></strong>
                        &nbsp;·&nbsp;
                        <?php
                        $from = ($page - 1) * $limit + 1;
                        $to   = min($page * $limit, $total_items);
                        echo "Hiển thị $from–$to trong $total_items sản phẩm";
                        ?>
                    </p>
                </nav>
                <?php endif; ?>

                <?php else: ?>
                <!-- ═══ EMPTY STATE ═══ -->
                <div class="cat-empty">
                    <i class="fas fa-box-open"></i>
                    <h5>Không có sản phẩm nào</h5>
                    <p class="text-muted">Thương hiệu này chưa có sản phẩm hoặc đang cập nhật.</p>
                    <a href="products.php" class="btn btn-warning rounded-pill px-4">
                        Xem tất cả sản phẩm
                    </a>
                </div>
                <?php endif; ?>

            </div><!-- /category-content-area -->
        </div><!-- /container -->
    </section>
    </div><!-- /main-content-area -->
</div><!-- /homepage-layout -->

<!-- CSS cho trang Category -->
<style>
/* ═══════════════════════════════════════════════════════
   CATEGORY PAGE STYLES
   Tất cả class prefix "cat-" để tránh conflict với class khác
═══════════════════════════════════════════════════════ */

.category-section { background: #f5f7fa; min-height: 60vh; }

/* Sidebar dùng chung .brand-sidebar + .sidebar-menu từ style.css */

/* ── Topbar ── */
.cat-topbar {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 6px;
}
.cat-topbar-left { display: flex; flex-direction: column; gap: 10px; }
.cat-page-title {
    font-size: clamp(1rem, 3vw, 1.3rem);
    font-weight: 800;
    color: var(--primary);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}
.cat-count-badge {
    font-size: 12px;
    font-weight: 500;
    color: #888;
    background: #f0f0f0;
    padding: 2px 10px;
    border-radius: 20px;
    margin-left: 4px;
}

/* Mobile brand pills */
.mobile-brand-pills {
    display: flex;
    gap: 6px;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    padding-bottom: 4px;
    scrollbar-width: none;
}
.mobile-brand-pills::-webkit-scrollbar { display: none; }
.brand-pill {
    white-space: nowrap;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    background: #f0f0f0;
    color: #555;
    text-decoration: none;
    transition: 0.15s;
    border: 1px solid transparent;
}
.brand-pill.active  { background: var(--primary); color: #fff; }
.brand-pill:hover:not(.active) { background: #e2e8ff; color: var(--primary); }

/* Sort pills */
.cat-sort-bar {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}
.sort-label { font-size: 12px; color: #888; font-weight: 500; white-space: nowrap; }
.sort-pills {
    display: flex;
    gap: 4px;
    flex-wrap: wrap;
}
.sort-pill {
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
    background: #f0f0f0;
    color: #666;
    text-decoration: none;
    transition: 0.15s;
    white-space: nowrap;
}
.sort-pill.active { background: var(--primary); color: #fff; }
.sort-pill:hover:not(.active) { background: var(--primary); color: #fff; }

/* ── Product Card ── */
.cat-product-card {
    background: #fff;
    border-radius: 14px;
    overflow: hidden;
    border: 1px solid #f0f0f0;
    transition: transform 0.22s, box-shadow 0.22s;
    display: flex;
    flex-direction: column;
    height: 100%;
    position: relative;
}
.cat-product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 30px rgba(0,0,0,0.11);
    border-color: var(--accent);
}
/* Badge góc trái */
.cat-card-badge {
    position: absolute;
    top: 10px;
    left: 10px;
    z-index: 2;
    font-size: 10px;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 6px;
    letter-spacing: 0.3px;
}
.cat-card-badge.new { background: #d4edda; color: #155724; }
.cat-card-badge.out { background: #f8d7da; color: #721c24; }

/* Ảnh */
.cat-card-img-wrap {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 160px;
    background: #fafafa;
    border-bottom: 1px solid #f5f5f5;
    overflow: hidden;
    padding: 12px;
}
.cat-card-img {
    max-height: 130px;
    max-width: 100%;
    object-fit: contain;
    transition: transform 0.3s;
}
.cat-product-card:hover .cat-card-img { transform: scale(1.07); }

/* Body */
.cat-card-body {
    padding: 12px;
    display: flex;
    flex-direction: column;
    gap: 6px;
    flex: 1;
}
.cat-brand-tag {
    display: inline-flex;
    align-items: center;
    font-size: 10px;
    font-weight: 700;
    color: var(--primary);
    background: #eef1ff;
    padding: 2px 8px;
    border-radius: 6px;
    text-decoration: none;
    width: fit-content;
    transition: 0.15s;
}
.cat-brand-tag:hover { background: var(--primary); color: #fff; }
.cat-card-name {
    font-size: 13px;
    font-weight: 700;
    color: #222;
    text-decoration: none;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    line-height: 1.4;
}
.cat-card-name:hover { color: var(--primary); }
.cat-card-rating { font-size: 11px; display: flex; align-items: center; gap: 4px; }
.rating-count { color: #999; font-size: 10px; }
.cat-card-price-row {
    display: flex;
    align-items: baseline;
    gap: 6px;
    flex-wrap: wrap;
}
.cat-price {
    font-size: 15px;
    font-weight: 800;
    color: #dc3545;
    font-family: 'Inter', sans-serif;
}
.cat-price-old { font-size: 11px; }

/* Action buttons */
.cat-card-actions {
    display: flex;
    gap: 6px;
    margin-top: auto;
    padding-top: 6px;
}
.btn-cat-detail {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 8px;
    border-radius: 8px;
    background: #f0f4ff;
    color: var(--primary);
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
    transition: 0.15s;
}
.btn-cat-detail:hover { background: var(--primary); color: #fff; }
.btn-cat-cart {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--accent);
    color: var(--primary);
    border: none;
    border-radius: 8px;
    font-size: 13px;
    cursor: pointer;
    transition: 0.15s;
    flex-shrink: 0;
}
.btn-cat-cart:hover { background: var(--primary); color: #fff; }

/* ── Pagination ── */
.cat-pagination { display: flex; flex-direction: column; align-items: center; gap: 10px; }
.pagination-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
    justify-content: center;
}
.page-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 36px;
    height: 36px;
    padding: 0 10px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    color: #555;
    background: #fff;
    border: 1px solid #e0e0e0;
    transition: 0.15s;
    cursor: pointer;
}
.page-btn:hover:not(.active):not(.disabled) {
    background: var(--primary);
    color: #fff;
    border-color: var(--primary);
}
.page-btn.active { background: var(--primary); color: #fff; border-color: var(--primary); }
.page-btn.disabled { color: #ccc; cursor: default; pointer-events: none; }
.page-btn.prev, .page-btn.next { background: #f8f9fa; }
.pagination-info { font-size: 12px; color: #888; margin: 0; }

/* ── Empty state ── */
.cat-empty {
    text-align: center;
    padding: 60px 20px;
    color: #aaa;
}
.cat-empty i { font-size: 70px; opacity: 0.3; margin-bottom: 20px; display: block; }
.cat-empty h5 { color: #555; font-weight: 700; }

/* ── RESPONSIVE ── */
@media (max-width: 767px) {
    .cat-topbar { flex-direction: column; }
    .cat-sort-bar { width: 100%; }
    .sort-pills { overflow-x: auto; flex-wrap: nowrap; padding-bottom: 4px; }
    .sort-pill { white-space: nowrap; }
    .cat-card-img-wrap { height: 120px; }
    .cat-card-img { max-height: 100px; }
    .cat-card-body { padding: 8px; gap: 4px; }
    .cat-price { font-size: 13px; }
    .btn-cat-detail { font-size: 11px; }
}

@media (max-width: 375px) {
    .cat-card-img-wrap { height: 100px; }
    .cat-card-name { font-size: 12px; }
}
</style>

