<?php
/**
 * File: cart.php
 * Quản lý giỏ hàng, cập nhật số lượng sản phẩm và xử lý đặt hàng nhanh.
 */
ob_start();
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// 1. Include header (Header đã có sẵn $db, $cart, $product)
include_once('func/header.php'); 

/* --- XỬ LÝ THANH TOÁN (CHECKOUT) --- */
if (isset($_POST['checkout-submit'])) {
    $userid = $_SESSION['user_id'] ?? 0;
    $total_amount = $_POST['total_amount'] ?? 0;

    if ($userid > 0 && $total_amount > 0) {
        $conn = $db->con; 
        
        // Bắt đầu Transaction để an toàn dữ liệu
        mysqli_begin_transaction($conn);

        try {
            $status = 'pending'; 
            $date = date('Y-m-d H:i:s');
            
            // Chống SQL Injection
            $userid = (int)$userid;
            $total_amount = (float)$total_amount;

            // Chèn vào bảng orders
            $sql_order = "INSERT INTO `orders` (`user_id`, `order_date`, `status`, `total_amount`) 
                          VALUES ($userid, '$date', '$status', $total_amount)";
            mysqli_query($conn, $sql_order);
            $order_id = mysqli_insert_id($conn);

            // Lấy item từ giỏ hàng để chèn vào chi tiết đơn hàng
            $cart_items = $cart->getCart($userid);
            foreach ($cart_items as $item) {
                $item_id = (int)$item['item_id'];
                $qty = (int)($item['quantity'] ?? 1);
                
                // Lấy giá thực tế từ bảng product để tránh khách fake giá ở Front-end
                $res_price = mysqli_query($conn, "SELECT price FROM product WHERE id = $item_id");
                if ($price_row = mysqli_fetch_assoc($res_price)) {
                    $price = $price_row['price'];
                    $sql_detail = "INSERT INTO `order_detail` (`order_id`, `product_id`, `quantity`, `price`) 
                                   VALUES ($order_id, $item_id, $qty, $price)";
                    mysqli_query($conn, $sql_detail);
                }
            }

            // Xóa giỏ hàng sau khi thanh toán thành công
            mysqli_query($conn, "DELETE FROM `cart` WHERE user_id = $userid");
            
            // Nếu mọi thứ OK thì chốt đơn
            mysqli_commit($conn);

            echo "<script>alert('Đặt hàng thành công!'); window.location.href = 'history.php';</script>";
            exit();

        } catch (Exception $e) {
            // Nếu có bất kỳ lỗi nào, hủy bỏ toàn bộ thao tác phía trên
            mysqli_rollback($conn);
            die("Lỗi thanh toán: " . $e->getMessage());
        }
    } else {
        echo "<script>alert('Vui lòng đăng nhập để thanh toán!'); window.location.href='login.php';</script>";
        exit();
    }
}

/* --- XỬ LÝ AJAX (Cập nhật số lượng giỏ hàng) --- */
if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['itemid']) && isset($_POST['qty'])) {
    $userid = $_SESSION['user_id'] ?? 0;
    // Ép kiểu đầu vào
    $i_id = (int)$_POST['itemid'];
    $i_qty = (int)$_POST['qty'];

    $result = $cart->updateCartQuantity($i_id, $userid, $i_qty);
    
    if($result) {
        ob_clean(); 
        echo "success";
        exit; 
    }
}

/* --- HIỂN THỊ GIAO DIỆN --- */
$current_user = $_SESSION['user_id'] ?? 0;
$cart_items = $cart->getCart($current_user);

// Kiểm tra giỏ hàng để include template tương ứng
if (is_array($cart_items) && count($cart_items) > 0) {
    include('libs/cart-list.php');
} else {
    include('libs/cart-empty.php');
}

include('libs/new-phones.php');
include_once('func/footer.php');
?>
