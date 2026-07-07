<?php
class OrderItem {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Lấy chi tiết sản phẩm trong đơn hàng
    public function getItemsByOrder($order_id) {
        $order_id = mysqli_real_escape_string($this->db, $order_id);
        $stmt = mysqli_query($this->db, "SELECT od.*, p.name, p.image FROM `order_detail` od 
                                         JOIN `product` p ON od.product_id = p.id 
                                         WHERE od.order_id = '$order_id'");
        return mysqli_fetch_all($stmt, MYSQLI_ASSOC);
    }
}
