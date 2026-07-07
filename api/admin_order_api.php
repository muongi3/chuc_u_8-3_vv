<?php
ob_start(); // Bắt mọi output không mong muốn (warning, notice...) vào buffer
session_start();
require_once('../func/DBConnect.php');
require_once('../func/AdminOrderController.php');

// Đảm bảo response luôn là JSON thuần
header('Content-Type: application/json; charset=utf-8');

// Kiểm tra quyền Admin
if (!isset($_SESSION['logged']) || $_SESSION['logged'] !== true || (int)($_SESSION['privilege'] ?? 0) !== 1) {
    ob_end_clean();
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$db = new DBConnect();
$adminController = new AdminOrderController($db->con);

$action = $_POST['action'] ?? '';

// ── Cập nhật trạng thái đơn hàng ─────────────────────────────
if ($action === 'update_status') {
    $order_id = (int)($_POST['order_id'] ?? 0);
    $status   = $_POST['status'] ?? '';

    $ok = $adminController->updateOrderStatus($order_id, $status);
    ob_end_clean(); // Xóa buffer, chỉ trả JSON sạch
    echo json_encode($ok
        ? ['status' => 'success', 'message' => 'Cập nhật thành công']
        : ['status' => 'error',   'message' => 'Cập nhật thất bại']
    );
    exit;
}

// ── Xử lý yêu cầu đổi / trả ──────────────────────────────────
if ($action === 'handle_return') {
    $request_id = (int)($_POST['request_id'] ?? 0);
    $status     = $_POST['status'] ?? '';
    $order_id   = (int)($_POST['order_id'] ?? 0);

    $ok = $adminController->handleReturnRequest($request_id, $status, $order_id);
    ob_end_clean();
    echo json_encode($ok
        ? ['status' => 'success', 'message' => 'Đã xử lý yêu cầu']
        : ['status' => 'error',   'message' => 'Xử lý thất bại']
    );
    exit;
}

// ── Action không hợp lệ ───────────────────────────────────────
ob_end_clean();
echo json_encode(['status' => 'error', 'message' => 'Action không hợp lệ']);
