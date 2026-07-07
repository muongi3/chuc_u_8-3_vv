<?php
/**
 * ADMIN ORDER CONTROLLER
 * Xử lý logic admin: cập nhật trạng thái đơn hàng, duyệt đổi trả
 */

// Dùng __DIR__ để đường dẫn luôn đúng dù gọi từ đâu
require_once(__DIR__ . '/Order.php');
require_once(__DIR__ . '/ReturnRequest.php');

class AdminOrderController {
    private $orderModel;
    private $returnModel;

    // Nhận $db là mysqli connection (từ $db->con)
    public function __construct($db) {
        $this->orderModel  = new Order($db);
        $this->returnModel = new ReturnRequest($db);
    }

    // Cập nhật trạng thái đơn hàng qua AJAX
    public function updateOrderStatus($order_id, $status) {
        return $this->orderModel->updateStatus((int)$order_id, $status);
    }

    // Duyệt hoặc từ chối yêu cầu đổi trả
    public function handleReturnRequest($request_id, $status, $order_id) {
        $updateReq = $this->returnModel->updateStatus((int)$request_id, $status);
        // Nếu duyệt đổi trả → chuyển đơn hàng sang "returned"
        if ($updateReq && $status === 'approved') {
            return $this->orderModel->updateStatus((int)$order_id, 'returned');
        }
        return $updateReq;
    }
}
