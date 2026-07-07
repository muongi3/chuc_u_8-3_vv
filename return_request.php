<?php
/**
 * File: return_request.php
 * Trang xử lý yêu cầu trả hàng hoặc hoàn tiền từ phía khách hàng.
 */
ob_start();
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['logged']) || !$_SESSION['logged']) {
    header("Location: login.php");
    exit;
}

/**
 * QUAN TRỌNG: Include header TRƯỚC khi dùng $db
 * Vì func/header.php → functions.php → require('func/DBConnect.php')
 * Nếu require_once DBConnect riêng TRƯỚC header sẽ bị lỗi class redeclare
 */
include('func/header.php');

// Bây giờ mới load thêm controller (sau khi header đã tạo $db)
require_once('func/OrderController.php');

$orderController = new OrderController($db->con);
$order_id = (int)($_GET['order_id'] ?? 0);
$user_id  = (int)($_SESSION['user_id'] ?? 0);  // 'user_id' theo login.php

// ─── Xử lý form submit gửi yêu cầu đổi trả ──────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reason = trim($_POST['reason'] ?? '');

    if ($reason && $orderController->requestReturn($order_id, $user_id, $reason)) {
        $_SESSION['toast_msg']  = '✅ Gửi yêu cầu đổi trả thành công!';
        $_SESSION['toast_type'] = 'success';
        ob_end_clean(); // Xóa buffer (có header HTML đang trong đó)
        header("Location: history.php");
        exit;
    } else {
        $_SESSION['toast_msg']  = '❌ Gửi thất bại! Kiểm tra lại trạng thái đơn hàng.';
        $_SESSION['toast_type'] = 'error';
        ob_end_clean();
        header("Location: return_request.php?order_id={$order_id}");
        exit;
    }
}
?>
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm border-0" style="border-radius:15px;">
                <div class="card-header bg-danger text-white p-3">
                    <h5 class="mb-0"><i class="fas fa-undo"></i> Yêu cầu đổi trả cho đơn hàng #<?= $order_id ?></h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Lý do đổi trả / hoàn tiền:</label>
                            <textarea name="reason" class="form-control" rows="5"
                                placeholder="Mô tả chi tiết lý do (sản phẩm lỗi, không đúng mô tả...)" required></textarea>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-danger py-2">Gửi yêu cầu</button>
                            <a href="history.php" class="btn btn-outline-secondary py-2">Hủy bỏ</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include('func/footer.php'); ?>

