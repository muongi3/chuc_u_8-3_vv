<?php
// start a session
if (!isset($_SESSION)) {
    session_start();
}
if (!isset($_SESSION['logged'])) {
    $_SESSION['logged'] = false;
}
if ($_SESSION['logged'] == false) {
    setcookie('user_id', '0', time() + (86400 * 30), "/"); // 86400 = 1 day
    setcookie('user_type', '0', time() + (86400 * 30), "/"); // 86400 = 1 day
}
// Backward-compat: session cũ chưa có user_id/privilege → lấy từ cookie
if ($_SESSION['logged'] === true && !isset($_SESSION['user_id'])) {
    $uid = (int)($_COOKIE['user_id'] ?? 0);
    if ($uid > 0) {
        $_SESSION['user_id']   = $uid;
        $_SESSION['privilege'] = (int)($_COOKIE['user_type'] ?? 0);
    }
}

// require MySQL Connection
require('func/DBConnect.php');

// require Product Class
require('func/Product.php');

// require Cart Class
require('func/Cart.php');

// require Wishlist Class
require('func/Wishlist.php');

// require Account Class
require('func/Account.php');

// require Account Class
require('func/Manage.php');

// Connect object
$db = new DBConnect();

// ─── Helper: Chuẩn hóa URL ảnh sản phẩm ──────────────────────
// DB có thể lưu:
//   - Tên file thuần: "iphone_123456.jpg"       → mới
//   - Đường dẫn cũ:  "./assets/products/x.jpg" → cũ
// Hàm này trả về URL dùng được ở mọi trang frontend (root-relative)
function img_url($image, $prefix = '') {
    if (empty($image)) {
        return $prefix . 'assets/products/no-image.png';
    }
    // Trường hợp đường dẫn cũ bắt đầu bằng ./ hoặc assets/
    if (strpos($image, '/') !== false || strpos($image, '.') === 0) {
        return $prefix . ltrim($image, './');
    }
    // Trường hợp mới: chỉ là tên file
    return $prefix . 'assets/products/' . $image;
}

// Product object
$product = new Product($db);
$productData = $product->getData();

// Chuẩn hóa image path cho toàn bộ productData (1 lần duy nhất)
$productData = array_map(function($p) {
    $p['image'] = img_url($p['image']);
    return $p;
}, $productData);

// Cart object
$cart = new Cart($db);

// Wishlist object
$wishlist = new Wishlist($db);

// Tự động tạo bảng wishlist (không cần chạy lệnh console)
$db->con->query("CREATE TABLE IF NOT EXISTS `wishlist` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `item_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `item_id` (`item_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `product` (`id`) ON DELETE CASCADE,
  CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `account` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

$db->con->query("CREATE TABLE IF NOT EXISTS `product_variant` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `ram` varchar(50) NOT NULL,
  `rom` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

$db->con->query("CREATE TABLE IF NOT EXISTS `coupons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `discount_value` int(11) NOT NULL,
  `discount_type` enum('percent','fixed') NOT NULL DEFAULT 'percent',
  `min_order_value` int(11) NOT NULL DEFAULT 0,
  `max_discount` int(11) NOT NULL DEFAULT 0,
  `usage_limit` int(11) NOT NULL DEFAULT 0,
  `used_count` int(11) NOT NULL DEFAULT 0,
  `valid_from` datetime DEFAULT NULL,
  `valid_until` datetime DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

// Check and add discount_amount to orders table
$check_column = $db->con->query("SHOW COLUMNS FROM `orders` LIKE 'discount_amount'");
if ($check_column->num_rows == 0) {
    $db->con->query("ALTER TABLE `orders` ADD COLUMN `discount_amount` int(11) NOT NULL DEFAULT 0");
}

// Account object
$acc = new Account($db);
$accData = $acc->getData();

// Manage object
$manage = new Manage($db);
$manageData = $manage->getData();
$brandData = $manage->getBrands();

?>

<?php
// ─────────────────────────────────────────────────────────────
// Chú ý bảo mật: Đã xóa console.log account/product data
// Không bao giờ expose dữ liệu nhạy cảm ra browser console
// ─────────────────────────────────────────────────────────────
?>

<?php
// request method post
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if (isset($_POST['top_sale_submit'])) {

        if($_COOKIE['user_id'] == 0){
            echo "<script>alert('Vui lòng đăng nhập trước');</script>";
        }else{
            $cart->addToCart($_COOKIE['user_id'], $_POST['item_id']);
        }

    }
}
// request method post
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if (isset($_POST['special_price_submit'])) {

        if(($_COOKIE['user_id'] ?? 0) == 0){
            echo "<script>alert('Vui lòng đăng nhập trước');</script>";
        }else{
            $cart->addToCart($_COOKIE['user_id'], $_POST['item_id']);
        }

    }
}

// request method post
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if (isset($_POST['new_phones_submit'])) {

        if(($_COOKIE['user_id'] ?? 0) == 0){
            echo "<script>alert('Vui lòng đăng nhập trước');</script>";
        }else{
            $cart->addToCart($_COOKIE['user_id'], $_POST['item_id']);
        }

    }
}

// request method post
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['delete-cart-submit'])) {
        // call method deleteCart
        $cart->deleteCart($_POST['item_id']);
    }
}

// request method post
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if (isset($_POST['buy_product_submit'])) {

        if(($_COOKIE['user_id'] ?? 0) == 0){
            echo "<script>alert('Vui lòng đăng nhập trước');</script>";
        }else{
            $cart->addToCart($_COOKIE['user_id'], $_POST['item_id']);
        }

    }
}

// request method post
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if (isset($_POST['wishlist_toggle_submit'])) {
        $uid = $_COOKIE['user_id'] ?? ($_SESSION['user_id'] ?? 0);
        if ($uid == 0) {
            echo "<script>alert('Vui lòng đăng nhập trước'); window.location.href='login.php';</script>";
        } else {
            $wishlist->toggleWishlist($uid, $_POST['item_id']);
        }
    }
}

// request method post
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if (isset($_POST['wishlist_delete_submit'])) {
        $uid = $_COOKIE['user_id'] ?? ($_SESSION['user_id'] ?? 0);
        if ($uid != 0) {
            $wishlist->deleteWishlist($uid, $_POST['item_id']);
        }
    }
}

// request method post
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if (isset($_POST['login-submit'])) {
        // call method login
        $acc->login($_POST['username'], $_POST['password']);
    }
}
// request method post
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if (isset($_POST['logout-submit'])) {
        // call method logout
        $acc->logout();
    }
}
// request method post
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if (isset($_POST['register-submit'])) {

        $fullname = $_POST['fullname'] ?? '';
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $avatar = $_POST['avatar'] ?? '';
        $email = $_POST['email'] ?? '';
        $city = $_POST['city'] ?? '';
        $gender = $_POST['gender'] ?? 0;
        $address = $_POST['address'] ?? '';

        // call method register
        $acc->register(
            $fullname,
            $username,
            $password,
            $phone,
            $avatar,
            $email,
            $city,
            $gender,
            $address
        );
    }
}



?>