<?php

// php cart class
class Cart
{
    public $db = null;

    public function __construct(DBConnect $db)
    {
        if (!isset($db->con))
            exit;
        $this->db = $db;
    }

    // 1. Lấy giỏ hàng: Giữ nguyên logic cũ nhưng thêm trả về mảng trống nếu ko có data
    public function getCart($userid = null, $table = 'cart')
    {
        if ($userid != null) {
            $stmt = $this->db->con->prepare("SELECT * FROM {$table} WHERE user_id=?");
            $stmt->bind_param("i", $userid);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $resultArray = array();
            while ($item = $result->fetch_array(MYSQLI_ASSOC)) {
                $resultArray[] = $item;
            }
            return $resultArray;
        }
        return array(); // Đảm bảo luôn trả về mảng để không lỗi foreach
    }

    // 2. Insert vào table: Giữ nguyên để các hàm khác dùng chung
    public function insertIntoCart($params = null, $table = "cart")
    {
        if ($this->db->con != null && $params != null) {
            $columns = implode(',', array_keys($params));
            $values = array_map(function($value) {
                return is_string($value) ? "'" . $this->db->con->real_escape_string($value) . "'" : $value;
            }, array_values($params));
            
            $values_str = implode(',', $values);
            $sql = sprintf("INSERT INTO %s(%s) VALUES(%s)", $table, $columns, $values_str);

            return $this->db->con->query($sql);
        }
        return false;
    }

    // 3. Thêm vào giỏ: Fix logic cộng dồn số lượng chuẩn
    public function addToCart($userid, $itemid, $qty = 1, $ajax = false)
    {
        if (isset($userid) && isset($itemid)) {
            $stmt = $this->db->con->prepare("SELECT * FROM cart WHERE user_id=? AND item_id=?");
            $stmt->bind_param("ii", $userid, $itemid);
            $stmt->execute();
            $check_res = $stmt->get_result();

            if ($check_res->num_rows > 0) {
                // Đã tồn tại -> Cập nhật số lượng dùng Prepared Statement cho đồng bộ
                $stmt_update = $this->db->con->prepare("UPDATE cart SET quantity = quantity + ? WHERE user_id=? AND item_id=?");
                $stmt_update->bind_param("iii", $qty, $userid, $itemid);
                $result = $stmt_update->execute();
            } else {
                // Chưa tồn tại -> Thêm mới
                $params = array(
                    "user_id" => $userid,
                    "item_id" => $itemid,
                    "quantity" => $qty
                );
                $result = $this->insertIntoCart($params);
            }

            if ($result) {
                if ($ajax) return true;
                $_SESSION['toast_msg'] = "Đã thêm vào giỏ hàng!";
                $_SESSION['toast_type'] = "success";
                echo "<script>window.location.href='" . $_SERVER['REQUEST_URI'] . "';</script>";
                exit;
            }
        }
        return false;
    }

    // 4. Xóa item: Giữ nguyên cấu trúc redirect
    public function deleteCart($item_id = null, $table = 'cart', $ajax = false)
    {
        if ($item_id != null) {
            $stmt = $this->db->con->prepare("DELETE FROM {$table} WHERE item_id=?");
            $stmt->bind_param("i", $item_id);
            $result = $stmt->execute();
            
            if ($result) {
                if ($ajax) return true;
                $_SESSION['toast_msg'] = "Đã xóa khỏi giỏ hàng!";
                $_SESSION['toast_type'] = "danger";
                echo "<script>window.location.href='" . $_SERVER['REQUEST_URI'] . "';</script>";
                exit;
            }
            return $result;
        }
    }

    // 5. Tính tổng tiền: Quan trọng nhất để hiển thị không sai
    public function getSum($arr)
    {
        $sum = 0;
        if(is_array($arr)){
            foreach ($arr as $item) {
                // Phải đảm bảo $item có giá trị price. 
                // Nếu truyền mảng giá thì giữ nguyên, nếu truyền mảng Cart thì lấy price
                $sum += floatval($item);
            }
        }
        return $sum; // Để số thô, format tiền ở file giao diện bằng number_format
    }

    // 6. Lấy danh sách ID
    public function getCartId($cartArray = null)
    {
        $cart_id = array();
        if ($cartArray != null) {
            foreach ($cartArray as $item) {
                $cart_id[] = $item['item_id'];
            }
        }
        return $cart_id;
    }

    // 7. Update số lượng dùng cho AJAX
    public function updateCartQuantity($itemid = null, $userid = null, $qty = 1)
    {
        if ($itemid != null && $userid != null) {
            $stmt = $this->db->con->prepare("UPDATE cart SET quantity=? WHERE item_id=? AND user_id=?");
            $stmt->bind_param("iii", $qty, $itemid, $userid);
            return $stmt->execute();
        }
        return false;
    }
}