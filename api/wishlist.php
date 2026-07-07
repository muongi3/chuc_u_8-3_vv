<?php
chdir(__DIR__ . '/..'); // Đưa working directory về thư mục gốc htdocs
require('func/functions.php');

header('Content-Type: application/json');

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

if ($action == 'toggle') {
    $res = $wishlist->toggleWishlist($user_id, $item_id, true); // true = ajax mode
    if ($res) {
        $items = $wishlist->getWishlist($user_id);
        $count = count($items);
        $response = [
            'status' => 'success', 
            'action' => $res, // 'added' or 'removed'
            'message' => $res == 'added' ? 'Đã thêm vào yêu thích' : 'Đã xóa khỏi yêu thích',
            'wishlist_count' => $count
        ];
    } else {
        $response = ['status' => 'error', 'message' => 'Cập nhật thất bại'];
    }
} elseif ($action == 'remove') {
    $res = $wishlist->deleteWishlist($user_id, $item_id, true);
    if ($res) {
        $items = $wishlist->getWishlist($user_id);
        $count = count($items);
        $response = [
            'status' => 'success', 
            'message' => 'Đã xóa khỏi yêu thích', 
            'wishlist_count' => $count
        ];
    } else {
        $response = ['status' => 'error', 'message' => 'Xóa thất bại'];
    }
}

echo json_encode($response);
