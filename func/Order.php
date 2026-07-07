<?php
/**
 * ORDER MODEL — Nâng cấp
 * Hỗ trợ filter, phân trang, thống kê
 */
class Order {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /* ══════════════════════════════════════
       ADMIN: Lấy tất cả đơn hàng (filter + phân trang)
    ══════════════════════════════════════ */
    public function getAllOrders(array $filters = [], int $page = 1, int $limit = 15): array {
        $offset = ($page - 1) * $limit;

        // Build điều kiện WHERE động
        $conditions = [];
        $params     = [];
        $types      = '';

        if (!empty($filters['status'])) {
            $conditions[] = 'o.status = ?';
            $params[]     = $filters['status'];
            $types       .= 's';
        }
        if (!empty($filters['search'])) {
            $conditions[] = '(u.fullname LIKE ? OR u.username LIKE ? OR CAST(o.id AS CHAR) = ?)';
            $params[]     = '%' . $filters['search'] . '%';
            $params[]     = '%' . $filters['search'] . '%';
            $params[]     = $filters['search'];
            $types       .= 'sss';
        }
        if (!empty($filters['date_from'])) {
            $conditions[] = 'DATE(o.order_date) >= ?';
            $params[]     = $filters['date_from'];
            $types       .= 's';
        }
        if (!empty($filters['date_to'])) {
            $conditions[] = 'DATE(o.order_date) <= ?';
            $params[]     = $filters['date_to'];
            $types       .= 's';
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
        $sql   = "SELECT o.*, u.fullname
                  FROM `orders` o
                  JOIN `user` u ON o.user_id = u.id
                  {$where}
                  ORDER BY o.order_date DESC
                  LIMIT ? OFFSET ?";

        $params[] = $limit;
        $params[] = $offset;
        $types   .= 'ii';

        $stmt = mysqli_prepare($this->db, $sql);
        if (!$stmt) {
            // Fallback: truy vấn đơn giản nếu prepare thất bại
            $res = mysqli_query($this->db,
                "SELECT o.*, u.fullname, u.username
                 FROM `orders` o JOIN `user` u ON o.user_id = u.id
                 ORDER BY o.order_date DESC LIMIT {$limit} OFFSET {$offset}"
            );
            return $res ? mysqli_fetch_all($res, MYSQLI_ASSOC) : [];
        }

        mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
    }

    /* ══════════════════════════════════════
       ADMIN: Đếm tổng đơn (phân trang)
    ══════════════════════════════════════ */
    public function countOrders(array $filters = []): int {
        $conditions = [];
        $params     = [];
        $types      = '';

        if (!empty($filters['status'])) {
            $conditions[] = 'o.status = ?';
            $params[]     = $filters['status'];
            $types       .= 's';
        }
        if (!empty($filters['search'])) {
            $conditions[] = '(u.fullname LIKE ? OR CAST(o.id AS CHAR) = ?)';
            $params[]     = '%' . $filters['search'] . '%';
            $params[]     = $filters['search'];
            $types       .= 'ss';
        }
        if (!empty($filters['date_from'])) {
            $conditions[] = 'DATE(o.order_date) >= ?';
            $params[]     = $filters['date_from'];
            $types       .= 's';
        }
        if (!empty($filters['date_to'])) {
            $conditions[] = 'DATE(o.order_date) <= ?';
            $params[]     = $filters['date_to'];
            $types       .= 's';
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
        $sql   = "SELECT COUNT(*) FROM `orders` o JOIN `user` u ON o.user_id = u.id {$where}";

        $stmt = mysqli_prepare($this->db, $sql);
        if (!$stmt) {
            $res = mysqli_query($this->db, "SELECT COUNT(*) FROM `orders`");
            return $res ? (int)mysqli_fetch_row($res)[0] : 0;
        }

        if ($types) mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $count);
        mysqli_stmt_fetch($stmt);
        return (int)$count;
    }

    /* ══════════════════════════════════════
       ADMIN: Thống kê số lượng theo trạng thái
    ══════════════════════════════════════ */
    public function getStatusCounts(): array {
        $res  = mysqli_query($this->db,
            "SELECT status, COUNT(*) AS cnt FROM `orders` GROUP BY status"
        );
        $data = [];
        if ($res) {
            while ($row = mysqli_fetch_assoc($res)) {
                $data[$row['status']] = (int)$row['cnt'];
            }
        }
        return $data;
    }

    /* ══════════════════════════════════════
       USER: Lấy đơn hàng của 1 user
    ══════════════════════════════════════ */
    public function getOrdersByUser(int $user_id): array {
        $stmt = mysqli_prepare($this->db,
            "SELECT * FROM `orders` WHERE user_id = ? ORDER BY order_date DESC"
        );
        if (!$stmt) return [];
        mysqli_stmt_bind_param($stmt, 'i', $user_id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        return $res ? mysqli_fetch_all($res, MYSQLI_ASSOC) : [];
    }

    /* ══════════════════════════════════════
       Lấy chi tiết 1 đơn hàng
    ══════════════════════════════════════ */
    public function getOrderDetail(int $order_id): ?array {
        $stmt = mysqli_prepare($this->db,
            "SELECT o.*, u.fullname
             FROM `orders` o
             JOIN `user` u ON o.user_id = u.id
             WHERE o.id = ?"
        );
        if (!$stmt) return null;
        mysqli_stmt_bind_param($stmt, 'i', $order_id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        return $res ? (mysqli_fetch_assoc($res) ?: null) : null;
    }

    /* ══════════════════════════════════════
       Cập nhật trạng thái đơn hàng
       QUY TẮC: Chỉ chặn khi đơn đã "cancelled"
       Các trạng thái khác admin cập nhật tự do
    ══════════════════════════════════════ */
    public function updateStatus(int $order_id, string $status): bool {

        // Danh sách trạng thái hợp lệ
        $allowed = ['pending','confirmed','packing','shipping','delivered','cancelled','returned'];
        if (!in_array($status, $allowed)) return false;

        // ── Lấy trạng thái HIỆN TẠI từ DB ────────────────────────
        $stmt = mysqli_prepare($this->db, "SELECT status FROM `orders` WHERE id = ?");
        if (!$stmt) return false;
        mysqli_stmt_bind_param($stmt, 'i', $order_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $currentStatus);

        // Không tìm thấy đơn → từ chối
        if (!mysqli_stmt_fetch($stmt)) {
            mysqli_stmt_close($stmt);
            return false;
        }
        mysqli_stmt_close($stmt);

        // ── Chặn sửa khi đơn đã HỦY ──────────────────────────────
        // Đơn cancelled không thể hoàn tác hay thay đổi gì nữa
        if ($currentStatus === 'cancelled') {
            return false;
        }

        // ── Cập nhật bình thường ──────────────────────────────────
        $stmt2 = mysqli_prepare($this->db, "UPDATE `orders` SET status = ? WHERE id = ?");
        if (!$stmt2) return false;
        mysqli_stmt_bind_param($stmt2, 'si', $status, $order_id);
        $result = mysqli_stmt_execute($stmt2);

        // ── Phục hồi tồn kho khi admin hủy hoặc hoàn trả ──────────
        if ($result && ($status === 'cancelled' || $status === 'returned') && $currentStatus !== 'cancelled' && $currentStatus !== 'returned') {
            $stmt3 = mysqli_query($this->db, "SELECT product_id, quantity FROM order_detail WHERE order_id = $order_id");
            if ($stmt3) {
                while ($row = mysqli_fetch_assoc($stmt3)) {
                    $pid = (int)$row['product_id'];
                    $qty = (int)$row['quantity'];
                    mysqli_query($this->db, "UPDATE product SET stock = stock + $qty WHERE id = $pid");
                }
            }
        }

        return $result;
    }

    /* ══════════════════════════════════════
       Tạo đơn hàng mới
       Trả về: order_id mới tạo (0 = thất bại)
    ══════════════════════════════════════ */
    public function createOrder(int $user_id, float $total, string $address, string $payment): int {
        $stmt = mysqli_prepare($this->db,
            "INSERT INTO `orders` (user_id, total_amount, shipping_address, payment_method, status, order_date)
             VALUES (?, ?, ?, ?, 'pending', NOW())"
        );
        if (!$stmt) return 0;
        mysqli_stmt_bind_param($stmt, 'idss', $user_id, $total, $address, $payment);
        mysqli_stmt_execute($stmt);
        return (int)mysqli_insert_id($this->db);
    }
}
