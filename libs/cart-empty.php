<section id="cart" class="py-3 mb-5">
    <div class="container">
        <h5 class="font-size-20 font-rubik">
            Giỏ hàng của <span>
                <?php
                // Kiểm tra đăng nhập và lấy tên hiển thị từ bảng 'user'
                if (isset($_SESSION['logged']) && $_SESSION['logged'] == true) {
                    // Sử dụng ID từ session để lấy fullname
                    $user_id = $_SESSION['user_id'] ?? 0;
                    $user_data = $acc->getAccount($user_id, 'user');
                    echo ($user_data && isset($user_data['fullname'])) ? $user_data['fullname'] : "Thành viên";
                } else {
                    echo "Khách";
                }
                ?>
            </span>
        </h5>
        
        <div class="row">
            <div class="col-sm-9">
                <div class="row border-top py-3 mt-3">
                    <div class="col-sm-12 text-center py-5 bg-white shadow-sm" style="border-radius: 15px;">
                        <img src="./assets/empty_cart.png" alt="Empty Cart" class="img-fluid" style="height: 200px;">
                        <p class="font-size-20 text-black-50 mt-4">Giỏ hàng của bạn đang trống!</p>
                        
                        <a href="index.php" class="btn btn-primary btn-lg rounded-pill mt-3 px-5 shadow-sm">
                            <i class="fas fa-shopping-bag mr-2"></i> Tiếp tục mua hàng
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-sm-3">
                <div class="sub-total border text-center mt-2 shadow-sm bg-light" style="border-radius: 10px; overflow: hidden;">
                    <h6 class="font-size-12 text-success py-3 mb-0">
                        <i class="fas fa-check"></i>
                        Đơn hàng của bạn đủ điều kiện FREE SHIP.
                    </h6>
                    <div class="border-top py-4 px-2 bg-white">
                        <h5 class="font-size-20">
                            <p class="text-muted font-size-14 mb-1">Tổng cộng (0 sản phẩm):</p>
                            <p class="text-danger font-weight-bold h3">
                                <span>$</span>
                                <span id="deal-price">0.00</span>
                            </p>
                        </h5>
                        
                        <button type="button" class="btn btn-warning btn-block mt-3 font-weight-bold py-2 shadow-sm" 
                                onclick="showEmptyMessage()">
                            Tiến hành thanh toán
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function showEmptyMessage() {
    // Sử dụng Confirm để tăng trải nghiệm người dùng
    if(confirm('Giỏ hàng đang trống! Bạn có muốn quay về Trang chủ để chọn sản phẩm không?')) {
        window.location.href = 'index.php';
    }
}
</script>