<?php
chdir(__DIR__ . '/..');
require('func/functions.php');

// ── Đọc tham số từ GET ──────────────────────────────────────────
$brand_id = isset($_GET['brand_id']) ? (int)$_GET['brand_id'] : 0;
$page     = max(1, (int)($_GET['page'] ?? 1));
$sort     = in_array($_GET['sort'] ?? '', ['newest','price_asc','price_desc','name_asc'])
            ? $_GET['sort'] : 'newest';
$limit    = 8;
$cat      = trim($_GET['cat'] ?? '');

// Danh mục phụ kiện (đồng bộ với _category.php và header.php)
$accessory_cats = [
    ['cat' => 'headphone', 'label' => 'Tai nghe',      'icon' => 'fas fa-headphones'],
    ['cat' => 'charger',   'label' => 'Sạc & Cáp',    'icon' => 'fas fa-bolt'],
    ['cat' => 'case',      'label' => 'Ốp lưng',      'icon' => 'fas fa-shield-alt'],
    ['cat' => 'powerbank', 'label' => 'Pin dự phòng', 'icon' => 'fas fa-battery-full'],
];

$brand_icons = [
    'Apple'   => 'fab fa-apple',
    'Samsung' => 'fab fa-android',
    'Redmi'   => 'fas fa-mobile-alt',
    'Oppo'    => 'fas fa-phone',
];

function renderStarsAjax($avg) {
    $out = '<span class="text-warning">';
    for ($i = 1; $i <= 5; $i++) {
        $cls = $i <= $avg ? 'fas' : 'far';
        $out .= "<i class=\"{$cls} fa-star\"></i>";
    }
    return $out . '</span>';
}

// ── Luôn load danh sách brand (cho mobile pills) ─────────────────
$all_brands    = $manage->getBrands();
$current_brand = null;

// ── Query sản phẩm ───────────────────────────────────────────────
if (!empty($cat)) {
    // Category mode (phụ kiện)
    $products    = $product->getProductsByCategory($cat, $page, $limit, $sort);
    $total_items = $product->countByCategory($cat);

    $cur_acc  = null;
    foreach ($accessory_cats as $ac) {
        if ($ac['cat'] === $cat) { $cur_acc = $ac; break; }
    }
    $brand_name = $cur_acc ? $cur_acc['label'] : 'Phụ kiện';
    $cur_icon   = $cur_acc ? $cur_acc['icon']  : 'fas fa-plug';
} else {
    // Brand mode (điện thoại)
    $products    = $product->getByBrand($brand_id, $page, $limit, $sort);
    $total_items = $product->countByBrand($brand_id);
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

// ── Tính phân trang + bulk stats ─────────────────────────────────
$total_pages = max(1, (int)ceil($total_items / $limit));
$product_ids = array_column($products, 'id');
$bulk_stats  = !empty($product_ids) ? $product->getBulkReviewStats($product_ids) : [];
?>


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
    <div class="cat-sort-bar">
        <label class="sort-label d-none d-md-inline">Sắp xếp:</label>
        <div class="sort-pills">
            <?php 
            $sort_labels = ['newest'=>'Mới nhất','price_asc'=>'Giá tăng dần','price_desc'=>'Giá giảm dần','name_asc'=>'Tên A–Z'];
            foreach ($sort_labels as $key => $label): ?>
            <button type="button" onclick="loadCategoryPage({sort: '<?php echo $key; ?>', page: 1})"
               class="sort-pill <?php echo $sort === $key ? 'active' : ''; ?>">
                <?php echo $label; ?>
            </button>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php if (!empty($products)): ?>
<div class="row g-3 mt-1">
    <?php foreach ($products as $p):
        $pid = (int)$p['id'];
        $stats = $bulk_stats[$pid] ?? ['average' => 0, 'total' => 0];
        $b_icon_card = $brand_icons[$p['brand_name'] ?? ''] ?? 'fas fa-mobile';
        $in_stock = (int)($p['stock'] ?? 100) > 0;
    ?>
    <div class="col-6 col-md-4 col-xl-3">
        <div class="cat-product-card">
            <div class="cat-card-badge <?php echo $in_stock ? 'new' : 'out'; ?>"><?php echo $in_stock ? 'Mới' : 'Hết hàng'; ?></div>
            <a href="details.php?id=<?php echo $pid; ?>" class="cat-card-img-wrap">
                <img src="<?php echo htmlspecialchars(img_url($p['image'])); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>" class="cat-card-img" loading="lazy" onerror="this.src='assets/products/no-image.png'">
            </a>
            <div class="cat-card-body">
                <a href="javascript:void(0)" onclick="loadCategoryPage({brand_id: <?php echo (int)$p['brand']; ?>, page: 1})" class="cat-brand-tag">
                    <i class="<?php echo $b_icon_card; ?> me-1"></i><?php echo htmlspecialchars($p['brand_name'] ?? ''); ?>
                </a>
                <a href="details.php?id=<?php echo $pid; ?>" class="cat-card-name"><?php echo htmlspecialchars($p['name']); ?></a>
                <div class="cat-card-rating"><?php echo renderStarsAjax($stats['average']); ?> <span class="rating-count">(<?php echo $stats['total']; ?>)</span></div>
                <div class="cat-card-price-row">
                    <span class="cat-price"><?php echo number_format($p['price'], 0, ',', '.'); ?>đ</span>
                    <span class="cat-price-old text-decoration-line-through text-muted"><?php echo number_format($p['price'] * 1.2, 0, ',', '.'); ?>đ</span>
                </div>
                <div class="cat-card-actions">
                    <a href="details.php?id=<?php echo $pid; ?>" class="btn-cat-detail"><i class="fas fa-eye me-1"></i>Xem</a>
                    <?php if ($in_stock): ?>
                        <button type="button" onclick="addToCart(<?php echo $pid; ?>, this)" class="btn-cat-cart"><i class="fas fa-cart-plus"></i></button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php if ($total_pages > 1): ?>
<nav class="cat-pagination mt-4">
    <ul class="pagination-list">
        <?php if ($page > 1): ?>
            <li><button type="button" onclick="loadCategoryPage({page: <?php echo $page-1; ?>})" class="page-btn prev"><i class="fas fa-chevron-left"></i></button></li>
        <?php endif; ?>
        <?php
        $start = max(1, $page - 2); $end = min($total_pages, $page + 2);
        for ($i = $start; $i <= $end; $i++): ?>
            <li><button type="button" onclick="loadCategoryPage({page: <?php echo $i; ?>})" class="page-btn <?php echo $i==$page?'active':''; ?>"><?php echo $i; ?></button></li>
        <?php endfor; ?>
        <?php if ($page < $total_pages): ?>
            <li><button type="button" onclick="loadCategoryPage({page: <?php echo $page+1; ?>})" class="page-btn next"><i class="fas fa-chevron-right"></i></button></li>
        <?php endif; ?>
    </ul>
</nav>
<?php endif; ?>

<?php else: ?>
<div class="cat-empty">
    <i class="fas fa-box-open"></i>
    <h5>Không tìm thấy sản phẩm</h5>
</div>
<?php endif; ?>

