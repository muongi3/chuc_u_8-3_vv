<?php
chdir(__DIR__ . '/..');
require('func/functions.php');

$filter = $_GET['brand'] ?? '*';
$keyword = $_GET['keyword'] ?? '';

$user_id = $_COOKIE['user_id'] ?? ($_SESSION['user_id'] ?? 0);
$in_cart = [];
if (isset($cart) && $user_id != 0) {
    $in_cart = $cart->getCartId($cart->getCart($user_id));
}
$in_wishlist = [];
if (isset($wishlist) && $user_id != 0) {
    $in_wishlist = $wishlist->getWishlistId($wishlist->getWishlist($user_id));
}

// Lấy dữ liệu
if (!empty($keyword)) {
    $items = $product->searchProduct($keyword);
} else {
    $items = $productData;
}

$html = '';
$has_product = false;

foreach ($items as $item) {
    $item_brand_id = $item['brand_id'] ?? $item['brand'];
    if ($filter !== '*' && $filter !== '' && (string)$item_brand_id !== (string)$filter) continue;
    
    $has_product = true;
    $inCart = in_array($item['id'], $in_cart);
    $inWishlist = in_array($item['id'], $in_wishlist);
    
    $price = number_format($item['price'], 0, ',', '.');
    $old_price = number_format($item['price'] + 1500000, 0, ',', '.');
    $name = htmlspecialchars($item['name']);
    $image = $item['image'];
    $wishlist_color = $inWishlist ? '#ff4757' : '#ccc';
    
    $cart_btn = $inCart ? 
        '<span class="btn-bought">✓ Đã thêm</span>' : 
        '<button type="button" onclick="addToCart('.$item['id'].', this)" class="btn-buy-now">MUA HÀNG</button>';

    $html .= '
    <div class="product-card-v2" style="position:relative;">
        <span class="discount-badge">-20%</span>
        <button type="button" onclick="toggleWishlist('.$item['id'].', this)" style="position:absolute; top:10px; right:10px; z-index:10; background:none; border:none; color: '.$wishlist_color.'; font-size:20px; transition: 0.2s;" name="wishlist_toggle_submit">
            <i class="fas fa-heart"></i>
        </button>
        <a href="details.php?id='.$item['id'].'" class="product-img-wrap">
            <img src="'.$image.'" alt="'.$name.'" onerror="this.src=\'assets/phone.png\'">
        </a>
        <div class="product-body">
            <div class="product-name">'.$name.'</div>
            <div class="text-warning mb-1" style="font-size:12px;">⭐ 4.9 · Đã bán 1k+</div>
            <span class="product-price-new">'.$price.'đ</span>
            <span class="product-price-old">'.$old_price.'đ</span>
            <div class="mt-1">
                '.$cart_btn.'
            </div>
        </div>
    </div>';
}

if (!$has_product) {
    $html = '
    <div style="grid-column:1/-1; text-align:center; padding:30px; color:#aaa;">
        <i class="fas fa-box-open" style="font-size:48px;margin-bottom:12px;display:block;"></i>
        Không có sản phẩm nào được tìm thấy.
    </div>';
}

echo $html;
?>

