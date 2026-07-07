<?php
/**
 * File: wishlist.php
 * Hiển thị danh sách các sản phẩm yêu thích của người dùng.
 */
ob_start();
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// 1. Include header
include_once('func/header.php'); 

/* --- HIỂN THỊ GIAO DIỆN --- */
$current_user = $_SESSION['user_id'] ?? 0;
$wishlist_items = [];
if ($current_user > 0 && isset($wishlist)) {
    $wishlist_items = $wishlist->getWishlist($current_user);
}

// Kiểm tra danh sách yêu thích để include template tương ứng
if (is_array($wishlist_items) && count($wishlist_items) > 0) {
    include('libs/wishlist-list.php');
} else {
    include('libs/wishlist-empty.php');
}

include('libs/new-phones.php');
include_once('func/footer.php');
?>
