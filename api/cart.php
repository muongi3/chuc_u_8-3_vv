<?php
chdir(__DIR__ . '/..'); // Đưa working directory về thư mục gốc htdocs
require('func/functions.php');

header('Content-Type: application/json');

// Xử lý dữ liệu gửi lên (có thể là FormData hoặc JSON)
$input = json_decode(file_get_contents('php://input'), true);
if (empty($input)) {
    $input = $_POST;
}

$action = $input['action'] ?? '';
$item_id = $input['item_id'] ?? 0;
$user_id = $_COOKIE['user_id'] ?? ($_SESSION['user_id'] ?? 0);

if ($user_id == 0) {
    echo json_encode(['status' => 'error', 'message' => 'Vui lòng đăng nhập trước!']);
    exit;
}

$response = ['status' => 'error', 'message' => 'Hành động không hợp lệ'];

if ($action == 'add') {
    $qty = $input['qty'] ?? 1;
    $res = $cart->addToCart($user_id, $item_id, $qty, true);
    if ($res) {
        $cartItems = $cart->getCart($user_id);
        $count = count($cartItems);
        $response = [
            'status' => 'success', 
            'message' => 'Đã thêm vào giỏ hàng', 
            'cart_count' => $count
        ];
    } else {
        $response = ['status' => 'error', 'message' => 'Thêm thất bại'];
    }
} elseif ($action == 'remove') {
    $res = $cart->deleteCart($item_id, 'cart', true);
    if ($res) {
        // Trả về count mới và subtotal mới
        $cartItems = $cart->getCart($user_id);
        $count = count($cartItems);
        
        $subTotal = 0;
        foreach($cartItems as $c) {
            $p = $product->getProduct($c['item_id']);
            $subTotal += ($p['price'] * $c['quantity']);
        }
        
        $response = [
            'status' => 'success', 
            'message' => 'Đã xóa khỏi giỏ', 
            'cart_count' => $count,
            'subtotal' => number_format($subTotal, 0, ',', '.') . " ₫"
        ];
    } else {
        $response = ['status' => 'error', 'message' => 'Xóa thất bại'];
    }
} elseif ($action == 'update_qty') {
    // API Cập nhật số lượng
    $qty = $input['qty'] ?? 1;
    if ($qty < 1) $qty = 1;
    
    $stmt_update = $db->con->prepare("UPDATE cart SET quantity = ? WHERE user_id=? AND item_id=?");
    $stmt_update->bind_param("iii", $qty, $user_id, $item_id);
    $res = $stmt_update->execute();
    
    if ($res) {
        // Trả về giá mới
        $p = $product->getProduct($item_id);
        $itemTotal = $p['price'] * $qty;
        
        $cartItems = $cart->getCart($user_id);
        $subTotal = 0;
        foreach($cartItems as $c) {
            $p2 = $product->getProduct($c['item_id']);
            $subTotal += ($p2['price'] * $c['quantity']);
        }
        
        $response = [
            'status' => 'success',
            'item_total' => number_format($itemTotal, 0, ',', '.') . " ₫",
            'subtotal' => number_format($subTotal, 0, ',', '.') . " ₫"
        ];
    } else {
        $response = ['status' => 'error', 'message' => 'Cập nhật thất bại'];
    }
}

echo json_encode($response);
