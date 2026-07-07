<?php
$user_id = $_SESSION['user_id'] ?? 0;
$in_cart = [];
if (isset($cart) && $user_id != 0) {
    $in_cart = $cart->getCartId($cart->getCart($user_id));
}
$in_wishlist = [];
if (isset($wishlist) && $user_id != 0) {
    $in_wishlist = $wishlist->getWishlistId($wishlist->getWishlist($user_id));
}
?>
<section id="promo-online">
    <div class="section-wrapper">
        <div class="section-title-bar">
            <h3>🔥 Điện thoại nổi bật</h3>
            <a href="products.php" class="view-all">Xem tất cả →</a>
        </div>

        <div class="product-grid">
            <?php 
            // Lọc ra các sản phẩm là điện thoại
            $phones = array_filter($productData, function($p) { return $p['category'] === 'phone'; });
            // Trộn ngẫu nhiên
            shuffle($phones);
            // Cắt lấy 8 sản phẩm
            $phones = array_slice($phones, 0, 8);
            
            $p_ids = array_column($phones, 'id');
            $bulk_stats = !empty($p_ids) ? $product->getBulkReviewStats($p_ids) : [];
            foreach ($phones as $item):
                $uid    = $_SESSION['user_id'] ?? 0;
                $in_c   = $cart->getCartId($cart->getCart($uid)) ?? [];
                $inCart = in_array($item['id'], $in_c);
                $inWishlist = in_array($item['id'], $in_wishlist ?? []);
            ?>
            <div class="product-card-v2" style="position:relative;">
                <span class="discount-badge">-15%</span>
                <!-- Wishlist button -->
                <button type="button" onclick="toggleWishlist(<?php echo $item['id']; ?>, this)" style="position:absolute; top:10px; right:10px; z-index:10; background:none; border:none; color: <?php echo $inWishlist ? '#ff4757' : '#ccc'; ?>; font-size:20px; transition: 0.2s;" name="wishlist_toggle_submit">
                    <i class="fas fa-heart"></i>
                </button>
                <a href="<?php printf('details.php?id=%s', $item['id']); ?>" class="product-img-wrap">
                    <img src="<?php echo $item['image']; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                </a>

                <div class="product-body">
                    <div class="product-name"><?php echo htmlspecialchars($item['name']); ?></div>
                    
                    <?php
                    $stat = $bulk_stats[$item['id']] ?? ['average'=>0, 'total'=>0];
                    if ($stat['total'] > 0) {
                    ?>
                        <div class="text-warning mb-1" style="font-size:12px;">
                            ⭐ <?php echo number_format($stat['average'], 1); ?> (<?php echo $stat['total']; ?> đánh giá)
                        </div>
                    <?php } else { ?>
                        <div class="text-warning mb-1" style="font-size:12px;">Chưa có đánh giá</div>
                    <?php } ?>

                    <span class="product-price-new"><?php echo number_format($item['price'], 0, ',', '.'); ?>đ</span>
                    <span class="product-price-old"><?php echo number_format($item['price'] * 1.15, 0, ',', '.'); ?>đ</span>

                    <div class="mt-1 d-flex gap-1 align-items-center">
                        <?php if ($inCart): ?>
                            <span class="btn-bought">✓ Đã thêm</span>
                        <?php else: ?>
                            <button type="button" onclick="addToCart(<?php echo $item['id']; ?>, this)" class="btn-buy-now">MUA HÀNG</button>
                        <?php endif; ?>
                        <button type="button"
                                onclick="addToCompare(<?php echo $item['id']; ?>, <?php echo htmlspecialchars(json_encode($item['name']), ENT_QUOTES, 'UTF-8'); ?>, <?php echo htmlspecialchars(json_encode($item['image'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>)"
                                class="btn-compare-mini" title="So sánh">
                            <i class="fas fa-balance-scale"></i>
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
