<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (file_exists('func/functions.php')) {
    require_once('func/functions.php');
}

$user_name     = $_SESSION['username'] ?? 'Tài khoản';
$user_initials = mb_strtoupper(mb_substr($user_name, 0, 2, "UTF-8"));
$avatar_file   = $_SESSION['avatar'] ?? '';

// Đếm số item trong giỏ hàng
$current_user_id = $_SESSION['user_id'] ?? 0;
$cart_count = 0;
$wishlist_count = 0;
if (isset($cart) && $current_user_id > 0) {
    $cart_data  = $cart->getCart($current_user_id);
    $cart_count = is_array($cart_data) ? count($cart_data) : 0;
    if (isset($wishlist)) {
        $wishlist_data = $wishlist->getWishlist($current_user_id);
        $wishlist_count = is_array($wishlist_data) ? count($wishlist_data) : 0;
    }
}
$is_logged = isset($_SESSION['logged']) && $_SESSION['logged'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="google" content="notranslate">
    <meta name="description" content="CLK Apple Store - Chuyên iPhone, Samsung, Redmi chính hãng giá tốt nhất">
    <link rel="icon" href="./assets/phone.png" type="image/x-icon">
    <title>CLK Apple Store - iPhone & Điện Thoại Chính Hãng</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<span id="top"></span>
<!-- Scroll-to-top: được tạo bởi app.js (bên trái màn hình) -->

<!-- ══════════════════════════════════════
     HEADER TOPBAR
══════════════════════════════════════ -->
<header id="header">
<div class="header-topbar">
    <div class="container-fluid px-3">
        <div class="d-flex align-items-center gap-3">

            <!-- LOGO -->
            <a href="./index.php" class="site-logo me-3">
                <i class="fab fa-apple"></i>
                <span>CLK</span> Apple Store
            </a>

            <!-- SEARCH -->
            <?php
            $currentPage = basename($_SERVER['PHP_SELF']);
            if ($currentPage != 'login.php' && $currentPage != 'register.php'):
            ?>
            <div class="search-wrapper flex-grow-1" style="position:relative;max-width:600px;">
                <form class="search-form header-search-full" action="search.php" method="GET" id="main-search-form">
                    <input type="search" name="keyword" id="main-search-input"
                           placeholder="Tìm kiếm iPhone, Samsung, Redmi..."
                           autocomplete="off">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
                <!-- Live Search Dropdown -->
                <div id="live-search-dropdown" style="display:none;"></div>
            </div>
            <!-- Icon search mobile (chỉ hiện trên màn hình nhỏ) -->
            <button class="mobile-search-icon ms-auto" onclick="document.getElementById('mobileSearchOverlay').classList.add('show')" style="background:none;border:none;">
                <i class="fas fa-search" style="color:white;font-size:20px;"></i>
            </button>
            <?php endif; ?>

            <!-- ACTIONS -->
            <div class="header-actions ms-auto">

                <?php if ($is_logged): ?>
                <!-- USER DROPDOWN -->
                <div class="dropdown">
                    <a href="#" class="header-user-btn position-relative text-decoration-none" role="button"
                       data-bs-toggle="dropdown" aria-expanded="false">
                        <div style="width:36px;height:36px;border-radius:50%;background:var(--accent);color:var(--primary);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:13px;overflow:hidden;border:2px solid rgba(255,255,255,0.3);">
                            <?php if (!empty($avatar_file) && file_exists(__DIR__ . "/../assets/avatars/" . $avatar_file)): ?>
                                <img src="assets/avatars/<?php echo $avatar_file; ?>" style="width:100%;height:100%;object-fit:cover;">
                            <?php else: ?>
                                <?php echo $user_initials; ?>
                            <?php endif; ?>
                        </div>
                        <span style="color:rgba(255,255,255,0.9);font-size:12px;"><?php echo htmlspecialchars($user_name); ?></span>
                    </a>

                    <div class="dropdown-menu dropdown-menu-end p-0 shadow border-0 google-style-dropdown">
                        <!-- Card đầu -->
                        <div class="text-center p-4" style="background:#f8f9fa;">
                            <div style="width:72px;height:72px;border-radius:50%;background:var(--accent);color:var(--primary);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:26px;overflow:hidden;margin:0 auto 8px;border:3px solid #fff;box-shadow:0 4px 12px rgba(0,0,0,0.1);">
                                <?php if (!empty($avatar_file) && file_exists(__DIR__ . "/../assets/avatars/" . $avatar_file)): ?>
                                    <img src="assets/avatars/<?php echo $avatar_file; ?>" style="width:100%;height:100%;object-fit:cover;">
                                <?php else: ?>
                                    <?php echo $user_initials; ?>
                                <?php endif; ?>
                            </div>
                            <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($user_name); ?></h6>
                            <small class="text-muted"><?php echo isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : ''; ?></small>
                        </div>

                        <div class="p-2 border-top">
                            <a class="dropdown-item rounded-3 py-2" href="./profile.php"><i class="fas fa-id-card me-3 text-primary"></i>Hồ sơ</a>
                            <a class="dropdown-item rounded-3 py-2" href="./history.php"><i class="fas fa-box me-3 text-warning"></i>Đơn hàng</a>

                            <?php if (isset($_SESSION['privilege']) && $_SESSION['privilege'] == 1): ?>
                                <hr class="my-1">
                                <p class="dropdown-header text-primary small fw-bold mb-1">⚙️ Khu vực Admin</p>
                                <div class="px-2 mb-2">
                                    <a class="btn btn-sm w-100 text-white fw-bold py-2"
                                       href="./admin/orders.php"
                                       style="background:var(--primary);border-radius:8px;">
                                        <i class="fas fa-tachometer-alt me-2"></i>Vào trang quản trị
                                    </a>
                                </div>
                                <a class="dropdown-item rounded-3 py-2" href="./admin/orders.php"><i class="fas fa-shopping-basket me-3 text-danger"></i>Quản lý đơn hàng</a>
                                <a class="dropdown-item rounded-3 py-2" href="./admin/products.php"><i class="fas fa-box-open me-3 text-primary"></i>Quản lý sản phẩm</a>
                                <a class="dropdown-item rounded-3 py-2" href="./admin/account.php"><i class="fas fa-users-cog me-3 text-info"></i>Quản lý thành viên</a>
                                <a class="dropdown-item rounded-3 py-2" href="./admin/dashboard.php"><i class="fas fa-chart-line me-3 text-success"></i>Thống kê doanh thu</a>
                            <?php endif; ?>
                        </div>

                        <div class="p-2 border-top">
                            <a class="btn btn-outline-danger btn-sm w-100 rounded-pill py-2" href="./logout.php">Đăng xuất</a>
                        </div>
                    </div>
                </div>

                <?php else: ?>
                <!-- CHƯA ĐĂNG NHẬP -->
                <a href="./login.php" class="header-user-btn text-decoration-none">
                    <i class="fas fa-user-circle"></i>
                    <span>Đăng nhập</span>
                </a>
                <?php endif; ?>

                <?php if (isset($_SESSION['logged']) && $_SESSION['logged']): ?>
                <!-- YÊU THÍCH (chỉ hiện khi đã đăng nhập) -->
                <a href="./wishlist.php" class="header-cart-btn text-decoration-none position-relative me-3">
                    <div style="position:relative;display:inline-block;">
                        <i class="fas fa-heart" style="font-size:22px;color:#ff4757;"></i>
                        <span id="wishlist-count-badge" class="cart-count-badge" style="background:#ff4757;"><?php echo $wishlist_count; ?></span>
                    </div>
                    <span>Yêu thích</span>
                </a>

                <!-- GIỎ HÀNG (chỉ hiện khi đã đăng nhập) -->
                <a href="./cart.php" class="header-cart-btn text-decoration-none position-relative">
                    <div style="position:relative;display:inline-block;">
                        <i class="fas fa-shopping-cart" style="font-size:22px;"></i>
                        <span id="cart-count-badge" class="cart-count-badge"><?php echo $cart_count; ?></span>
                    </div>
                    <span>Giỏ hàng</span>
                </a>
                <?php else: ?>
                <!-- CHƯA ĐĂNG NHẬP: hiện nút Đăng ký nhanh -->
                <a href="./register.php" class="btn btn-sm btn-outline-light ms-2" style="border-radius:20px;font-size:12px;padding:5px 14px;">
                    <i class="fas fa-user-plus me-1"></i>Đăng ký
                </a>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════
     SUB NAVIGATION BAR
══════════════════════════════════════ -->
<div class="header-subnav">
    <div class="container-fluid px-3">
        <nav class="subnav-links">
            <?php
            $onCategory  = ($currentPage == 'products.php');
            $activeKw    = $_GET['keyword'] ?? '';
            $activeBrand = (int)($_GET['brand_id'] ?? -1); // -1 = not on category page
            if (!$onCategory) { $activeBrand = -1; $activeKw = ''; }

            // Phụ kiện — dùng cat= param (headphone/charger/case/powerbank)
            $nav_accessories = [
                ['cat'=>'headphone', 'label'=>'Tai nghe',      'icon'=>'fas fa-headphones'],
                ['cat'=>'charger',   'label'=>'Sạc & Cáp',    'icon'=>'fas fa-bolt'],
                ['cat'=>'case',      'label'=>'Ốp lưng',      'icon'=>'fas fa-shield-alt'],
                ['cat'=>'powerbank', 'label'=>'Pin dự phòng', 'icon'=>'fas fa-battery-full'],
            ];
            ?>

            <!-- Trang chủ -->
            <a href="./index.php"
               <?php echo ($currentPage==='index.php') ? 'class="active"' : ''; ?>>
                <i class="fas fa-home me-1"></i>Trang chủ
            </a>

            <!-- Điện thoại (tất cả) -->
            <a href="<?php echo $onCategory ? 'javascript:void(0)' : './products.php'; ?>"
               <?php if($onCategory) echo 'onclick="loadCategoryPage({brand_id: 0, cat: \'\', page: 1})"'; ?>
               <?php echo ($onCategory && empty($activeCat) && isset($_GET['brand_id']) && (int)$_GET['brand_id']===0) || ($onCategory && empty($activeCat) && !isset($_GET['brand_id'])) ? 'class="active"' : ''; ?>>
                <i class="fas fa-mobile-alt me-1"></i>Điện thoại
            </a>

            <!-- Divider -->
            <span class="subnav-divider">|</span>

            <!-- Phụ kiện -->
            <?php
            $activeCat = $_GET['cat'] ?? '';
            foreach ($nav_accessories as $na): ?>
            <a href="<?php echo $onCategory
                    ? 'javascript:void(0)'
                    : './products.php?cat=' . urlencode($na['cat']); ?>"
               <?php if($onCategory) echo 'onclick="loadCategoryPage({cat: \'' . $na['cat'] . '\', brand_id: \'\', page: 1})"'; ?>
               <?php echo ($onCategory && $activeCat === $na['cat']) ? 'class="active"' : ''; ?>>
                <i class="<?php echo $na['icon']; ?> me-1"></i><?php echo $na['label']; ?>
            </a>
            <?php endforeach; ?>

            <!-- Divider -->
            <span class="subnav-divider">|</span>

            <!-- Đơn hàng / Đăng ký -->
            <?php if (isset($_SESSION['logged']) && $_SESSION['logged']): ?>
                <a href="./history.php" <?php echo ($currentPage==='history.php') ? 'class="active"' : ''; ?>>
                    <i class="fas fa-box me-1"></i>Đơn hàng
                </a>
            <?php else: ?>
                <a href="./register.php" <?php echo ($currentPage==='register.php') ? 'class="active"' : ''; ?>>
                    <i class="fas fa-user-plus me-1"></i>Đăng ký
                </a>
            <?php endif; ?>

            <!-- So sánh -->
            <a href="./compare.php" <?php echo ($currentPage==='compare.php') ? 'class="active"' : ''; ?>>
                <i class="fas fa-balance-scale me-1"></i>So sánh
            </a>

            <!-- Admin shortcut -->
            <?php if (isset($_SESSION['privilege']) && (int)$_SESSION['privilege'] === 1): ?>
                <a href="./admin/orders.php" style="color:#DAA520 !important; font-weight:700;">
                    <i class="fas fa-cog me-1"></i>Admin
                </a>
            <?php endif; ?>

        </nav>
    </div>
</div>
</header>

<!-- ══════════════════════════════════════
     MOBILE SEARCH OVERLAY
══════════════════════════════════════ -->
<div class="mobile-search-overlay" id="mobileSearchOverlay">
    <form class="search-form" action="search.php" method="GET" style="flex:1;max-width:100%;" onsubmit="searchProducts(event)">
        <input type="search" name="keyword" placeholder="Tìm kiếm sản phẩm..." autofocus>
        <button type="submit"><i class="fas fa-search"></i></button>
    </form>
    <button class="btn-close-search" onclick="document.getElementById('mobileSearchOverlay').classList.remove('show')">
        <i class="fas fa-times"></i>
    </button>
</div>

<!-- ══════════════════════════════════════
     OFF-CANVAS DANH MỤC (Mobile Drawer)
══════════════════════════════════════ -->
<div class="offcanvas offcanvas-start offcanvas-category" tabindex="-1" id="categoryDrawer">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title"><i class="fas fa-th-list me-2"></i>DANH MỤC SẢN PHẨM</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-0 d-flex flex-column">
        <ul class="sidebar-menu flex-grow-1">
            <?php
            $brand_sidebar_oc = [
                0 => ['Tất cả', 'fas fa-th-large'],
                3 => ['Apple iPhone', 'fab fa-apple'],
                1 => ['Samsung Galaxy', 'fab fa-android'],
                2 => ['Redmi / Xiaomi', 'fas fa-mobile-alt'],
                4 => ['Oppo', 'fas fa-phone'],
            ];
            $active_brand_oc = (int)($_GET['brand_id'] ?? 0);
            foreach($brand_sidebar_oc as $bid => $binfo):
                $is_active_oc = $active_brand_oc === $bid;
                $href_oc = $bid === 0 ? 'products.php' : "products.php?brand_id={$bid}";
            ?>
            <li class="<?php echo $is_active_oc ? 'active' : ''; ?>">
                <a href="<?php echo $href_oc; ?>" data-bs-dismiss="offcanvas">
                    <i class="<?php echo $binfo[1]; ?>"></i>
                    <?php echo $binfo[0]; ?>
                </a>
            </li>
            <?php endforeach; ?>
            <li><a href="cart.php" data-bs-dismiss="offcanvas"><i class="fas fa-shopping-cart"></i>Giỏ hàng</a></li>
            <li><a href="history.php" data-bs-dismiss="offcanvas"><i class="fas fa-box"></i>Đơn hàng của tôi</a></li>
        </ul>
        <div class="sidebar-contact">
            <div class="mb-1"><i class="fas fa-phone-alt me-2" style="color:var(--accent);"></i><strong>Hotline:</strong> 0358 *** ***</div>
            <div><i class="fas fa-clock me-2" style="color:var(--accent);"></i>8:00 – 21:30 hằng ngày</div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════
     FLOATING COMPARE BAR
══════════════════════════════════════ -->
<div id="compare-float-bar" style="display:none;">
    <div class="compare-bar-inner">
        <div class="compare-bar-items" id="compare-bar-items"></div>
        <div class="compare-bar-actions">
            <a href="#" id="compare-bar-go-btn" class="btn-compare-go">
                <i class="fas fa-balance-scale me-2"></i>So sánh ngay
            </a>
            <button onclick="clearCompare()" class="btn-compare-clear">
                <i class="fas fa-trash"></i> Xóa tất cả
            </button>
        </div>
    </div>
</div>

<main id="main-site">

<!-- TOAST NOTIFICATION (PHP session) -->
<?php if (isset($_SESSION['toast_msg'])): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var msg  = <?php echo json_encode(htmlspecialchars($_SESSION['toast_msg'])); ?>;
    var type = <?php echo json_encode($_SESSION['toast_type'] ?? 'success'); ?>;
    if (typeof showToastJS === 'function') {
        showToastJS(msg, type);
    }
});
</script>
<?php
    unset($_SESSION['toast_msg']);
    unset($_SESSION['toast_type']);
endif;
?>
<style>
/* Hiệu ứng active khi bấm nút Mua hàng / Yêu thích */
.btn-buy-now:active,
.btn-add-cart:active,
button[name="wishlist_toggle_submit"]:active {
    transform: scale(0.93);
    transition: transform 0.1s;
}
button[name="wishlist_toggle_submit"] i {
    transition: transform 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
button[name="wishlist_toggle_submit"]:active i {
    transform: scale(1.4);
}
</style>
