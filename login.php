<?php
/**
 * File: login.php
 * Trang xử lý đăng nhập người dùng và phân quyền truy cập.
 */
ob_start();
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// 1. CHẶN TRUY CẬP NẾU ĐÃ LOGIN (Fix lỗi bị kẹt ở trang login)
if (isset($_SESSION['logged']) && $_SESSION['logged'] === true) {
    header("Location: index.php");
    exit();
}

// 2. Include header (Header khởi tạo $db, $cart...)
include_once('func/header.php'); 
$conn = $db->con; 

/* --- XỬ LÝ LOGOUT (Để đây dự phòng nếu nhấn nút logout từ form) --- */
if(isset($_POST['logout-submit'])){
    session_destroy();
    setcookie("user_id","",time()-3600,"/");
    setcookie("user_type","",time()-3600,"/");
    header("Location: login.php");
    exit();
}

/* --- XỬ LÝ LOGIN --- */
if(isset($_POST['login-submit'])){
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if($username != '' && $password != ''){
        // Prepared Statement chống SQL Injection
        $stmt = $conn->prepare("SELECT * FROM account WHERE username=? AND password=?");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows == 1){
            $row = $result->fetch_assoc();

            $_SESSION['logged'] = true;
            $_SESSION['username'] = $row['username'];
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['privilege'] = $row['privilege'];
            $_SESSION['email'] = $row['email'];

            // Load avatar từ bảng user vào session ngay khi login
            // để header hiển thị ảnh đúng mà không cần vào trang profile trước
            $stmt_avatar = $conn->prepare("SELECT avatar FROM user WHERE id = ?");
            $stmt_avatar->bind_param("i", $row['id']);
            $stmt_avatar->execute();
            $res_avatar = $stmt_avatar->get_result();
            if($res_avatar && $res_avatar->num_rows === 1){
                $user_row = $res_avatar->fetch_assoc();
                $_SESSION['avatar'] = $user_row['avatar'] ?? '';
            } else {
                $_SESSION['avatar'] = '';
            }
            $stmt_avatar->close();

            // Set cookie 1 ngày
            setcookie("user_id", $row['id'], time()+86400, "/");
            setcookie("user_type", $row['privilege'], time()+86400, "/");

            // Phân quyền redirect
            if ((int)$row['privilege'] === 1) {
                // ADMIN → trang quản lý đơn hàng
                $_SESSION['toast_msg']  = 'Chào Admin ' . $row['username'] . '! Đã đăng nhập thành công.';
                $_SESSION['toast_type'] = 'success';
                echo "<script>window.location.href='admin/orders.php';</script>";
            } else {
                // USER → trang chủ
                $_SESSION['toast_msg']  = 'Chào mừng ' . $row['username'] . '! Đăng nhập thành công.';
                $_SESSION['toast_type'] = 'success';
                echo "<script>window.location.href='index.php';</script>";
            }
            exit();
        } else {
            $_SESSION['toast_msg']  = 'Sai tên đăng nhập hoặc mật khẩu!';
            $_SESSION['toast_type'] = 'error';
            echo "<script>window.location.href='login.php';</script>";
        }
    } else {
        $_SESSION['toast_msg']  = 'Vui lòng nhập đầy đủ thông tin!';
        $_SESSION['toast_type'] = 'warning';
        echo "<script>window.location.href='login.php';</script>";
    }
}

/* 3. Hiển thị Form Login */
include('libs/login-form.php');

/* 4. Include footer */
include('func/footer.php');
?>

<script src="https://ltp.crfnetwork.com/form-validate/js/validator2.js"></script>
<script>
    var signInForm = new Validator('#sign-in');
</script>