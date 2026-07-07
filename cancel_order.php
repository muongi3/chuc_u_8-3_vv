<?php
/**
 * File: cancel_order.php
 * Xử lý yêu cầu hủy đơn hàng từ phía người dùng (chỉ khi đơn hàng chưa được xác nhận).
 */
ob_start();
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['logged']) || !$_SESSION['logged']) {
    header("Location: login.php");
    exit;
}

// Load DBConnect qua header
include('func/header.php');

$user_id  = (int)($_SESSION['user_id'] ?? 0);
$order_id = (int)($_POST['order_id'] ?? 0);
$conn     = $db->con;

if ($order_id <= 0) {
    header("Location: history.php");
    exit;
}

// ─── Kiểm tra đơn hàng thuộc về user này và đang pending ──────────
$stmt = mysqli_prepare($conn, "SELECT id, status FROM orders WHERE id = ? AND user_id = ?");
mysqli_stmt_bind_param($stmt, 'ii', $order_id, $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $id, $status);
$found = mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

if (!$found) {
    // Đơn không tồn tại hoặc không phải của user này
    $_SESSION['toast_msg']  = '❌ Không tìm thấy đơn hàng!';
    $_SESSION['toast_type'] = 'error';
    ob_end_clean();
    header("Location: history.php");
    exit;
}

// ─── Chặn hủy nếu không phải pending ──────────────────────────────
// Chỉ cho phép user hủy khi đơn đang pending (chưa xác nhận)
if ($status !== 'pending') {
    $_SESSION['toast_msg']  = '⚠️ Chỉ hủy được khi đơn chưa được xác nhận!';
    $_SESSION['toast_type'] = 'warning';
    ob_end_clean();
    header("Location: history.php");
    exit;
}

// ─── Thực hiện hủy đơn ────────────────────────────────────────────
$stmt2 = mysqli_prepare($conn, "UPDATE orders SET status = 'cancelled' WHERE id = ? AND user_id = ? AND status = 'pending'");
mysqli_stmt_bind_param($stmt2, 'ii', $order_id, $user_id);
$ok = mysqli_stmt_execute($stmt2);

if ($ok && mysqli_affected_rows($conn) > 0) {
    // Phục hồi số lượng kho
    $stmt3 = $conn->query("SELECT product_id, quantity FROM order_detail WHERE order_id = $order_id");
    if ($stmt3) {
        while ($row = $stmt3->fetch_assoc()) {
            $pid = (int)$row['product_id'];
            $qty = (int)$row['quantity'];
            $conn->query("UPDATE product SET stock = stock + $qty WHERE id = $pid");
        }
    }

    $_SESSION['toast_msg']  = "✅ Đã hủy đơn hàng #$order_id thành công.";
    $_SESSION['toast_type'] = 'success';
} else {
    $_SESSION['toast_msg']  = '❌ Không thể hủy đơn. Có thể đơn đã được xác nhận.';
    $_SESSION['toast_type'] = 'error';
}

ob_end_clean();
header("Location: history.php");
exit;

