</main><!-- /main-site -->

<!-- MOBILE BOTTOM NAVIGATION (Shopee-style)
     Chỉ hiện trên mobile ≤768px -->
<?php
$_current = basename($_SERVER['PHP_SELF']);
$_uid     = $_SESSION['user_id'] ?? 0;
$_cartCnt = 0;
if(isset($cart) && $_uid > 0){
    $_cartData = $cart->getCart($_uid);
    $_cartCnt  = is_array($_cartData) ? count($_cartData) : 0;
}
?>
<nav class="mobile-bottom-nav">
    <a href="index.php" class="<?php echo $_current=='index.php'?'active':''; ?>">
        <i class="fas fa-home"></i>
        Trang chủ
    </a>
    <a href="#" data-bs-toggle="offcanvas" data-bs-target="#categoryDrawer">
        <i class="fas fa-th-list"></i>
        Danh mục
    </a>
    <a href="#" onclick="document.getElementById('mobileSearchOverlay').classList.add('show');return false;">
        <i class="fas fa-search"></i>
        Tìm kiếm
    </a>
    <a href="cart.php" class="<?php echo $_current=='cart.php'?'active':''; ?>" style="position:relative;">
        <i class="fas fa-shopping-cart"></i>
        <?php if($_cartCnt > 0): ?>
            <span class="nav-cart-badge"><?php echo $_cartCnt; ?></span>
        <?php endif; ?>
        Giỏ hàng
    </a>
    <a href="<?php echo isset($_SESSION['logged'])&&$_SESSION['logged'] ? 'profile.php':'login.php'; ?>"
       class="<?php echo in_array($_current,['profile.php','account.php'])?'active':''; ?>">
        <i class="fas fa-user"></i>
        Tài khoản
    </a>
</nav>



<!-- FOOTER -->
<footer class="site-footer">
    <div class="container">
        <div class="row g-4">

            <!-- Cột 1: Giới thiệu -->
            <div class="col-lg-3 col-md-6">
                <h5 class="footer-title">CLK Apple Store</h5>
                <p>Chuyên kinh doanh điện thoại chính hãng:<br>iPhone, Samsung, Redmi, Oppo.<br>Cam kết giá tốt – bảo hành uy tín.</p>
                <div class="footer-social">
                    <a href="#" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" title="YouTube"><i class="fab fa-youtube"></i></a>
                    <a href="#" title="TikTok"><i class="fab fa-tiktok"></i></a>
                    <a href="#" title="Zalo"><i class="fas fa-comment-dots"></i></a>
                </div>
            </div>

            <!-- Cột 2: Hỗ trợ -->
            <div class="col-lg-2 col-md-6">
                <h5 class="footer-title">Hỗ trợ</h5>
                <ul class="footer-links">
                    <li><a href="#"><i class="fas fa-chevron-right me-1" style="font-size:10px;"></i>Chính sách đổi trả</a></li>
                    <li><a href="#"><i class="fas fa-chevron-right me-1" style="font-size:10px;"></i>Chính sách bảo hành</a></li>
                    <li><a href="#"><i class="fas fa-chevron-right me-1" style="font-size:10px;"></i>Hướng dẫn thanh toán</a></li>
                    <li><a href="#"><i class="fas fa-chevron-right me-1" style="font-size:10px;"></i>Mua hàng trả góp</a></li>
                    <li><a href="#"><i class="fas fa-chevron-right me-1" style="font-size:10px;"></i>Chính sách bảo mật</a></li>
                </ul>
            </div>

            <!-- Cột 3: Liên hệ -->
            <div class="col-lg-3 col-md-6">
                <h5 class="footer-title">Thông tin liên hệ</h5>
                <ul class="footer-contact">
                    <li><i class="fas fa-phone-alt"></i>Hotline: <strong style="color:var(--accent);">0389 *** ***</strong></li>
                    <li><i class="fas fa-envelope"></i>clkstore@gmail.com</li>
                    <li><i class="fas fa-map-marker-alt"></i>TP. Hồ Chí Minh, Việt Nam</li>
                    <li><i class="fas fa-clock"></i>8h00 – 21h30 (Tất cả các ngày)</li>
                </ul>
            </div>

            <!-- Cột 4: Thanh toán + Newsletter -->
            <div class="col-lg-4 col-md-6">
                <h5 class="footer-title">Phương thức thanh toán</h5>
                <div class="payment-badges">
                    <span class="payment-badge">💵 COD</span>
                    <span class="payment-badge">💳 VISA</span>
                    <span class="payment-badge">🟣 MoMo</span>
                    <span class="payment-badge">🔵 ZaloPay</span>
                    <span class="payment-badge">🏧 ATM</span>
                </div>

                <h5 class="footer-title mt-4">Đăng ký nhận ưu đãi</h5>
                <form class="newsletter-form" onsubmit="return false;">
                    <input type="email" placeholder="Nhập email của bạn...">
                    <button type="submit"><i class="fas fa-paper-plane"></i></button>
                </form>
                <p class="mt-2" style="font-size:11px;color:rgba(255,255,255,0.4);">Nhận thông tin khuyến mãi mỗi tuần, không spam.</p>
            </div>

        </div>
    </div>
</footer>

<div class="footer-copyright">
    <p>© 2026 CLK Apple Store · Thiết kế bởi <strong style="color:var(--accent);">Nguyễn Duy Khánh</strong></p>
</div>

<?php include_once('libs/chatbot.php'); ?>

<!-- JS LIBS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"
        integrity="sha256-pTxD+DSzIwmwhOqTFN+DB+nHjO4iAsbgfyFq5K5bcE0=" crossorigin="anonymous"></script>
<script src="script.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/app.js?v=<?php echo time(); ?>"></script>

</body>
</html>