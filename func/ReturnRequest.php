<?php
class ReturnRequest {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Tạo yêu cầu đổi trả
    public function create($order_id, $user_id, $reason) {
        $order_id = mysqli_real_escape_string($this->db, $order_id);
        $user_id = mysqli_real_escape_string($this->db, $user_id);
        $reason = mysqli_real_escape_string($this->db, $reason);
        return mysqli_query($this->db, "INSERT INTO `return_request` (order_id, user_id, reason, status) 
                                         VALUES ('$order_id', '$user_id', '$reason', 'pending')");
    }

    // Lấy tất cả yêu cầu (Admin)
    public function getAllRequests() {
        $sql  = "SELECT r.*, u.fullname, o.total_amount
                 FROM `return_request` r
                 JOIN `user` u ON r.user_id = u.id
                 JOIN `orders` o ON r.order_id = o.id
                 ORDER BY r.id DESC";
        $stmt = mysqli_query($this->db, $sql);
        if (!$stmt) return [];
        return mysqli_fetch_all($stmt, MYSQLI_ASSOC);
    }

    // Cập nhật trạng thái yêu cầu
    public function updateStatus($request_id, $status) {
        $request_id = mysqli_real_escape_string($this->db, $request_id);
        $status = mysqli_real_escape_string($this->db, $status);
        return mysqli_query($this->db, "UPDATE `return_request` SET status = '$status' WHERE id = '$request_id'");
    }
}
