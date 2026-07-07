<?php
/**
 * File: details.php
 * Hiển thị thông tin chi tiết sản phẩm.
 * Toàn bộ logic POST (thêm giỏ hàng, gửi/xóa đánh giá) được xử lý bên trong libs/_products.php.
 */
ob_start();

// Include header — khởi tạo $db, $product, $cart, $wishlist, session_start()
include('func/header.php');

// Trang chi tiết sản phẩm + xử lý review
include('libs/product-details.php');

// Gợi ý sản phẩm hot phía dưới
include('libs/featured.php');

include('func/footer.php');
ob_end_flush();
?>
