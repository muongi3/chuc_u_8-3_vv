<?php
/**
 * File: index.php
 * Trang chủ — CLK Apple Store
 */
ob_start();
include('func/header.php');
?>

<div class="homepage-layout">

    <!-- ══ SIDEBAR DANH MỤC ══ -->
    <aside class="brand-sidebar d-none d-lg-flex flex-column">
        <div class="sidebar-header">
            <i class="fas fa-th-list"></i> DANH MỤC SẢN PHẨM
        </div>
        <ul class="sidebar-menu flex-grow-1">
            <?php
            $brand_sidebar = [
                0 => ['Tất cả điện thoại', 'fas fa-mobile-alt'],
                3 => ['Apple iPhone',       'fab fa-apple'],
                1 => ['Samsung Galaxy',     'fab fa-android'],
                2 => ['Redmi / Xiaomi',     'fas fa-mobile'],
                4 => ['Oppo',               'fas fa-phone'],
            ];
            foreach ($brand_sidebar as $bid => $binfo):
                $href = $bid === 0 ? 'products.php' : "products.php?brand_id={$bid}";
            ?>
            <li>
                <a href="<?php echo $href; ?>">
                    <i class="<?php echo $binfo[1]; ?>"></i>
                    <?php echo $binfo[0]; ?>
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


        <!-- BANNER -->

        <?php include('libs/banner.php'); ?>

        <?php if (!isset($_SESSION['logged']) || !$_SESSION['logged']): ?>
        <!-- SECTION ĐĂNG KÝ (chỉ hiện cho khách chưa đăng nhập) -->
        <div style="background:linear-gradient(135deg,#001C30 0%,#003153 100%);
                    border-radius:16px;padding:32px 28px;margin:20px 0;
                    display:flex;align-items:center;justify-content:space-between;
                    flex-wrap:wrap;gap:16px;">
            <div>
                <div style="font-size:22px;font-weight:800;color:#fff;margin-bottom:6px;">
                    🎁 Tham gia CLK Apple Store!
                </div>
                <div style="color:rgba(255,255,255,0.7);font-size:14px;line-height:1.6;">
                    Đăng ký ngay để nhận <strong style="color:#DAA520;">ưu đãi độc quyền</strong>,
                    theo dõi đơn hàng và tham gia chương trình tích điểm!
                </div>
            </div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <a href="register.php"
                   style="background:#DAA520;color:#001C30;border-radius:25px;
                          padding:10px 24px;font-weight:700;text-decoration:none;
                          font-size:14px;white-space:nowrap;transition:.2s;"
                   onmouseover="this.style.background='#ffd700'"
                   onmouseout="this.style.background='#DAA520'">
                    <i class="fas fa-user-plus me-2"></i>Đăng ký miễn phí
                </a>
                <a href="login.php"
                   style="background:transparent;color:#fff;border:1.5px solid rgba(255,255,255,0.4);
                          border-radius:25px;padding:10px 24px;font-weight:600;
                          text-decoration:none;font-size:14px;white-space:nowrap;">
                    Đã có tài khoản?
                </a>
            </div>
        </div>
        <?php endif; ?>

        <!-- ĐIỆN THOẠI NỔI BẬT -->
        <?php include('libs/featured.php'); ?>

        <!-- BANNER ADS -->
        <?php include('libs/ads.php'); ?>

        <!-- ĐIỆN THOẠI MỚI NHẤT -->
        <?php include('libs/new-phones.php'); ?>

        <!-- PHỤ KIỆN CHÍNH HÃNG -->
        <?php include('libs/accessory.php'); ?>

        <!-- TIN TỨC -->
        <?php include('libs/news.php'); ?>

    </div><!-- /main-content-area -->
</div><!-- /homepage-layout -->

<?php include('func/footer.php'); ?>
