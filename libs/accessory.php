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
// Lấy danh sách phụ kiện
$accessories = array_filter($productData, function($p) { return $p['category'] !== 'phone'; });
shuffle($accessories);
$accessories = array_slice($accessories, 0, 8);

// Lấy bulk review stats
$p_ids = array_column($accessories, 'id');
$bulk_stats = !empty($p_ids) ? $product->getBulkReviewStats($p_ids) : [];
?>

<section id="special-price" style="scroll-margin-top: 140px;">
    <div class="section-wrapper">
        <div class="section-title-bar">
            <h3>🎧 Phụ kiện chính hãng</h3>
            <a href="products.php?cat=headphone" class="view-all">Xem tất cả →</a>
        </div>

        <!-- Lưới sản phẩm -->
        <div class="product-grid">
            <?php
            $has_product = false;
            foreach ($accessories as $item):
                $has_product = true;
                $inCart = in_array($item['id'], $in_cart ?? []);
                $inWishlist = in_array($item['id'], $in_wishlist ?? []);
            ?>
            <div class="product-card-v2" style="position:relative;">
                <span class="discount-badge" style="background:#f39c12;">Giá Tốt</span>

                <!-- Wishlist button -->
                <button type="button" onclick="toggleWishlist(<?php echo $item['id']; ?>, this)" style="position:absolute; top:10px; right:10px; z-index:10; background:none; border:none; color: <?php echo $inWishlist ? '#ff4757' : '#ccc'; ?>; font-size:20px; transition: 0.2s;" name="wishlist_toggle_submit">
                    <i class="fas fa-heart"></i>
                </button>

                <a href="details.php?id=<?php echo $item['id']; ?>" class="product-img-wrap">
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
                    <span class="product-price-old"><?php echo number_format($item['price'] * 1.05, 0, ',', '.'); ?>đ</span>

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
            
            <?php if (!$has_product): ?>
                <p class="text-center w-100 py-4">Chưa có phụ kiện nào.</p>
            <?php endif; ?>
        </div>
    </div>
</section>
