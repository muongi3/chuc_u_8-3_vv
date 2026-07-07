<?php
require_once(__DIR__ . '/Order.php');
require_once(__DIR__ . '/OrderItem.php');
require_once(__DIR__ . '/ReturnRequest.php');

class OrderController {
    private $orderModel;
    private $itemModel;
    private $returnModel;

    public function __construct($db) {
        $this->orderModel = new Order($db);
        $this->itemModel = new OrderItem($db);
        $this->returnModel = new ReturnRequest($db);
    }

    // Xử lý hủy đơn hàng (chỉ khi pending)
    public function cancelOrder($order_id, $user_id) {
        $order = $this->orderModel->getOrderDetail($order_id);
        if ($order && $order['user_id'] == $user_id && $order['status'] == 'pending') {
            return $this->orderModel->updateStatus($order_id, 'cancelled');
        }
        return false;
    }

    // Xử lý yêu cầu đổi trả
    public function requestReturn($order_id, $user_id, $reason) {
        $order = $this->orderModel->getOrderDetail($order_id);
        if ($order && $order['user_id'] == $user_id && in_array($order['status'], ['delivered', 'Đã giao'])) {
            return $this->returnModel->create($order_id, $user_id, $reason);
        }
        return false;
    }
}
