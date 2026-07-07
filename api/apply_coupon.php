<?php
session_start();
require_once('../func/DBConnect.php');

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit;
}

$db = new DBConnect();
$conn = $db->con;

$code = $conn->real_escape_string(trim($_POST['code'] ?? ''));
$cart_total = (float)($_POST['cart_total'] ?? 0);

if (empty($code)) {
    echo json_encode(['status' => 'error', 'message' => 'Vui lòng nhập mã giảm giá']);
    exit;
}

// Fetch coupon
$res = $conn->query("SELECT * FROM coupons WHERE BINARY code = '$code'");
if (!$res || $res->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Mã giảm giá không hợp lệ']);
    exit;
}

$coupon = $res->fetch_assoc();

// Check status
if ($coupon['status'] == 0) {
    echo json_encode(['status' => 'error', 'message' => 'Mã giảm giá đã bị khóa']);
    exit;
}

// Check expiry
if ($coupon['valid_until'] && strtotime($coupon['valid_until']) < time()) {
    echo json_encode(['status' => 'error', 'message' => 'Mã giảm giá đã hết hạn']);
    exit;
}
if ($coupon['valid_from'] && strtotime($coupon['valid_from']) > time()) {
    echo json_encode(['status' => 'error', 'message' => 'Mã giảm giá chưa đến thời gian sử dụng']);
    exit;
}

// Check usage limit
if ($coupon['usage_limit'] > 0 && $coupon['used_count'] >= $coupon['usage_limit']) {
    echo json_encode(['status' => 'error', 'message' => 'Mã giảm giá đã hết lượt sử dụng']);
    exit;
}

// Check min order value
if ($cart_total < $coupon['min_order_value']) {
    echo json_encode(['status' => 'error', 'message' => 'Đơn hàng chưa đạt giá trị tối thiểu ' . number_format($coupon['min_order_value']) . 'đ']);
    exit;
}

// Calculate discount
$discount_amount = 0;
if ($coupon['discount_type'] === 'percent') {
    $discount_amount = $cart_total * ($coupon['discount_value'] / 100);
} else {
    $discount_amount = $coupon['discount_value'];
}

// Cap max discount
if ($coupon['max_discount'] > 0 && $discount_amount > $coupon['max_discount']) {
    $discount_amount = $coupon['max_discount'];
}

// Discount shouldn't exceed cart total
if ($discount_amount > $cart_total) {
    $discount_amount = $cart_total;
}

// Return success
echo json_encode([
    'status' => 'success',
    'message' => 'Áp dụng mã giảm giá thành công!',
    'discount_amount' => $discount_amount,
    'code' => $coupon['code']
]);
