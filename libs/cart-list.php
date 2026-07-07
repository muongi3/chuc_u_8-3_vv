<section id="cart" class="py-3 mb-5">
    <div class="container">
        <h5 class="font-size-20 font-rubik">
            Giỏ hàng của <span class="text-primary">
                <?php
                if (isset($_SESSION['logged']) && $_SESSION['logged'] == true) {
                    $user_info = $acc->getAccount($_SESSION['user_id'], 'user');
                    echo ($user_info && isset($user_info['fullname'])) ? $user_info['fullname'] : "Thành viên";
                } else {
                    echo "Khách";
                }
                ?>
            </span>
        </h5>

        <div class="row">
            <div class="col-sm-9">
                <?php
                $current_user_id = $_SESSION['user_id'] ?? 0;
                $products = $cart->getCart($current_user_id);
                $subTotal = [];

                if (!empty($products)):
                    foreach ($products as $productItems):
                        $item = $product->getProduct($productItems['item_id']);
                        if(!$item) continue;

                        // Lấy dữ liệu đánh giá thực tế từ bảng reviews
                        $reviewStats = $product->getReviewStats($item['id']);
                // Tính giá trước để dùng cả mobile lẫn desktop
                $price     = (float)($item['price'] ?? 0);
                $qty       = (int)($productItems['quantity'] ?? 1);
                $itemTotal = $price * $qty;
                $subTotal[] = $itemTotal;
                ?>
                    <div class="row border-top py-3 mt-2 align-items-center cart-item-row">
                        <!-- Ảnh sản phẩm -->
                        <div class="col-3 col-sm-2 text-center px-1">
                            <a href="<?php printf('%s?id=%s', 'details.php', $item['id']); ?>">
                                <img src="<?php echo htmlspecialchars(img_url($item['image'])); ?>"
                                     class="img-fluid cart-product-img" alt="product"
                                     style="max-height:90px;object-fit:contain;">
                            </a>
                        </div>
                        
                        <!-- Thông tin sản phẩm -->
                        <div class="col-9 col-sm-8">
                            <h6 class="fw-bold mb-1" style="font-size:clamp(13px,3.5vw,17px);">
                                <a href="<?php printf('%s?id=%s', 'details.php', $item['id']); ?>" class="text-dark text-decoration-none">
                                    <?php echo $item['name'] ?? "Sản phẩm"; ?>
                                </a>
                            </h6>
                            <small class="text-muted d-block mb-1">Thương hiệu:
                                <?php
                                    $brand_id = $item['brand'] ?? 0;
                                    $brand_data = $manage->getBrand($brand_id);
                                    echo htmlspecialchars($brand_data['brand'] ?? 'CLK');
                                ?>
                            </small>
                            <!-- Sao đánh giá -->
                            <div class="d-flex align-items-center mb-2">
                                <div class="rating text-warning" style="font-size:11px;">
                                    <?php
                                    $avg_rating = floor($reviewStats['average'] ?: 5);
                                    for ($i = 1; $i <= 5; $i++) {
                                        echo ($i <= $avg_rating) ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                                    }
                                    ?>
                                </div>
                                <span class="ms-1 text-secondary" style="font-size:11px;">
                                    <?php echo $reviewStats['total']; ?> đánh giá
                                </span>
                            </div>
                            <!-- Giá trên mobile (hiện ngay dưới tên) -->
                            <div class="d-sm-none text-danger fw-bold mb-2" style="font-size:14px;">
                                <?php echo number_format($price, 0, ',', '.'); ?> ₫
                            </div>
                            <!-- Số lượng + Xóa -->
                            <div class="d-flex align-items-center gap-2">
                                <div class="d-flex align-items-center border rounded" style="height:32px;">
                                    <button type="button" class="border-0 bg-light px-2 h-100" onclick="updateCartQty(<?php echo $item['id']; ?>, 1)">
                                        <i class="fas fa-angle-up" style="font-size:10px;"></i>
                                    </button>
                                    <input type="text" data-id="<?php echo $item['id']; ?>"
                                           class="text-center border-0 bg-light"
                                           style="width:36px;font-size:13px;"
                                           readonly value="<?php echo $productItems['quantity'] ?? 1; ?>">
                                    <button type="button" class="border-0 bg-light px-2 h-100" onclick="updateCartQty(<?php echo $item['id']; ?>, -1)">
                                        <i class="fas fa-angle-down" style="font-size:10px;"></i>
                                    </button>
                                </div>
                                <button type="button" onclick="removeCartItem(<?php echo $item['id']; ?>, this.closest('.cart-item-row'))"
                                        class="btn btn-sm btn-outline-danger py-1" style="font-size:12px;">
                                    <i class="fas fa-trash-alt"></i> Xóa
                                </button>
                            </div>
                        </div>
                        <!-- Giá trên desktop -->
                        <div class="col-sm-2 d-none d-sm-block text-end">
                            <div class="text-danger fw-bold" style="font-size:16px;">
                                <span class="product_price" data-id="<?php echo $item['id'] ?? '0'; ?>">
                                    <?php echo number_format($price, 0, ',', '.') . " ₫"; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php
                    endforeach;
                else:
                    echo "<div class='text-center py-5 shadow-sm bg-white mt-3' style='border-radius:15px;'>
                            <img src='./assets/empty_cart.png' style='height:180px;' class='mb-4' alt='empty cart'>
                            <h5 class='font-rubik'>Giỏ hàng của bạn đang trống!</h5>
                            <p class='text-muted'>Hãy chọn những chiếc iPhone tuyệt vời nhất cho mình nhé.</p>
                            <a href='index.php' class='btn btn-warning mt-2 px-5 py-2 font-weight-bold'>MUA SẮM NGAY</a>
                          </div>";
                endif;
                ?>
            </div>

            <!-- Tổng tiền: Full width trên mobile, col-sm-3 trên desktop -->
            <div class="col-12 col-sm-3 mt-3">
                <div class="sub-total border text-center shadow-sm bg-white p-3" style="border-radius:14px;">
                    <div class="border-top pt-3">
                        <h6 class="mb-1" style="font-size:15px;">Tổng tiền (<?php echo count($subTotal); ?> sản phẩm):</h6>
                        <h4 class="text-danger fw-bold mt-1">
                            <span id="deal-price">
                                <?php
                                    $total = array_sum($subTotal);
                                    echo number_format($total, 0, ',', '.') . " ₫";
                                ?>
                            </span>
                        </h4>
                        <form method="POST" action="checkout.php">
                            <input type="hidden" name="total_amount" value="<?php echo $total; ?>">
                            <button type="submit"
                                    class="btn btn-warning w-100 fw-bold py-2 mt-2 shadow-sm text-uppercase"
                                    style="border-radius:8px;font-size:14px;">
                                Thanh toán ngay
                            </button>
                        </form>
                    </div>
                </div>
                <a href="index.php" class="btn btn-outline-dark btn-sm w-100 mt-2 py-2">
                    <i class="fas fa-arrow-left me-1"></i> Tiếp tục mua sắm
                </a>
            </div>
        </div>
    </div>
</section>
