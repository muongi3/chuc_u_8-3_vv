<?php
/**
 * File: search.php
 * Trang tìm kiếm sản phẩm, cho phép người dùng tìm điện thoại theo tên hoặc thương hiệu.
 */
ob_start();
session_start();
include('func/header.php');

$conn    = $db->con;
$keyword = trim($_GET['keyword'] ?? '');

// ─── Tìm kiếm an toàn với Prepared Statement ───────────────────
$products = [];
$total    = 0;

if ($keyword !== '') {
    $like = '%' . $keyword . '%';

    // Đếm tổng kết quả
    $stmtCount = mysqli_prepare($conn,
        "SELECT COUNT(*) FROM product p
         JOIN manufacturer m ON p.brand = m.id
         WHERE p.name LIKE ? OR m.brand LIKE ?"
    );
    mysqli_stmt_bind_param($stmtCount, 'ss', $like, $like);
    mysqli_stmt_execute($stmtCount);
    mysqli_stmt_bind_result($stmtCount, $total);
    mysqli_stmt_fetch($stmtCount);
    mysqli_stmt_close($stmtCount);

    // Lấy sản phẩm
    $stmt = mysqli_prepare($conn,
        "SELECT p.*, m.brand AS brand_name
         FROM product p
         JOIN manufacturer m ON p.brand = m.id
         WHERE p.name LIKE ? OR m.brand LIKE ?
         ORDER BY p.price ASC"
    );
    mysqli_stmt_bind_param($stmt, 'ss', $like, $like);
    mysqli_stmt_execute($stmt);
    $result   = mysqli_stmt_get_result($stmt);
    $products = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
}
?>

<style>
.search-page   { max-width: 1100px; margin: 0 auto; padding: 28px 16px 60px; }
.search-header { margin-bottom: 24px; }
.search-header h2 { font-size: 20px; font-weight: 800; color: #111; margin: 0 0 4px; }
.search-header p  { color: #888; font-size: 13px; margin: 0; }

/* Grid sản phẩm */
.search-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(190px, 1fr)); gap: 16px; }

.product-card {
    background: #fff;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 2px 12px rgba(0,0,0,0.07);
    transition: transform .2s, box-shadow .2s;
    text-decoration: none;
    color: inherit;
    display: block;
}
.product-card:hover { transform: translateY(-4px); box-shadow: 0 8px 28px rgba(0,0,0,0.13); }
.product-card img   { width: 100%; height: 160px; object-fit: contain; background: #f5f5f7; padding: 10px; }
.product-card-body  { padding: 12px 14px 14px; }
.product-card-name  { font-size: 13px; font-weight: 700; color: #111; margin-bottom: 4px; line-height: 1.4; }
.product-card-brand { font-size: 11px; color: #888; margin-bottom: 6px; }
.product-card-price { font-size: 15px; font-weight: 800; color: #ef4444; }
.product-card-btn   {
    display: block; margin-top: 10px;
    background: #001C30; color: #DAA520;
    text-align: center; border-radius: 8px;
    padding: 7px 0; font-size: 12px; font-weight: 700;
    text-decoration: none; transition: background .2s;
}
.product-card-btn:hover { background: #003153; color: #ffd700; }

/* Empty state */
.empty-box { text-align: center; padding: 60px 20px; background: #fff; border-radius: 14px; }
.empty-box h3 { font-size: 18px; color: #111; margin: 12px 0 6px; }
.empty-box p  { color: #888; font-size: 13px; }

/* Search bar nổi bật */
.search-bar-wrap { margin-bottom: 24px; }
.search-bar-wrap form { display: flex; gap: 8px; }
.search-bar-wrap input {
    flex: 1; border: 2px solid #e5e7eb; border-radius: 10px;
    padding: 10px 16px; font-size: 14px; outline: none;
    transition: border-color .2s;
}
.search-bar-wrap input:focus { border-color: #001C30; }
.search-bar-wrap button {
    background: #001C30; color: #DAA520; border: none;
    border-radius: 10px; padding: 10px 20px; font-weight: 700; cursor: pointer;
}
</style>

<div class="search-page">

    <!-- Search bar tìm lại -->
    <div class="search-bar-wrap">
        <form action="search.php" method="GET">
            <input type="search" name="keyword"
                   value="<?= htmlspecialchars($keyword) ?>"
                   placeholder="Tìm điện thoại, thương hiệu...">
            <button type="submit"><i class="fas fa-search"></i> Tìm</button>
        </form>
    </div>

    <!-- Tiêu đề kết quả -->
    <div class="search-header">
        <?php if ($keyword !== ''): ?>
        <h2>🔍 Kết quả cho "<?= htmlspecialchars($keyword) ?>"</h2>
        <p><?= $total ?> sản phẩm tìm thấy</p>
        <?php else: ?>
        <h2>🔍 Tìm kiếm sản phẩm</h2>
        <p>Nhập từ khóa để tìm điện thoại bạn muốn</p>
        <?php endif; ?>
    </div>

    <?php if ($keyword !== '' && $total === 0): ?>
    <!-- Không tìm thấy -->
    <div class="empty-box">
        <div style="font-size:48px;">😕</div>
        <h3>Không tìm thấy sản phẩm</h3>
        <p>Thử tìm với từ khóa khác như "iPhone", "Samsung", "Redmi"...</p>
        <a href="index.php" class="btn btn-primary mt-2">Về trang chủ</a>
    </div>

    <?php elseif (!empty($products)): ?>
    <!-- Grid sản phẩm -->
    <div class="search-grid">
        <?php foreach ($products as $p):
            $imgSrc = img_url($p['image']);
        ?>
        <a href="details.php?id=<?= $p['id'] ?>" class="product-card">
            <img src="<?= htmlspecialchars($imgSrc) ?>"
                 alt="<?= htmlspecialchars($p['name']) ?>"
                 onerror="this.src='assets/products/no-image.png'">
            <div class="product-card-body">
                <div class="product-card-name"><?= htmlspecialchars($p['name']) ?></div>
                <div class="product-card-brand"><?= htmlspecialchars($p['brand_name']) ?></div>
                <div class="product-card-price"><?= number_format($p['price'], 0, ',', '.') ?>đ</div>
                <span class="product-card-btn">Xem chi tiết</span>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</div>

<?php include('func/footer.php'); ?>
