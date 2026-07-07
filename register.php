<?php
/**
 * File: register.php
 * Trang xử lý đăng ký tài khoản mới cho khách hàng.
 */
ob_start();
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// 1. Phải include header trước để lấy kết nối $db từ hệ thống
include_once('func/header.php'); 
$conn = $db->con; 

if(isset($_POST['register-submit'])){

    // Lấy dữ liệu và làm sạch
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $address  = trim($_POST['address'] ?? '');

    if($username != '' && $password != ''){
        
        // Bật Transaction để đảm bảo an toàn dữ liệu 2 bảng
        mysqli_begin_transaction($conn);

        try {
            // 1. Kiểm tra username tồn tại bằng Prepared Statement
            $stmt_check = $conn->prepare("SELECT id FROM account WHERE username=?");
            $stmt_check->bind_param("s", $username);
            $stmt_check->execute();
            $res_check = $stmt_check->get_result();

            if($res_check->num_rows > 0){
                echo "<script>alert('Tên đăng nhập này đã có người sử dụng!');</script>";
            } else {
                // 2. Chèn vào bảng account
                // Giả sử bảng account của ông có các cột: username, password, email
                $stmt_acc = $conn->prepare("INSERT INTO account(username, password, email) VALUES(?, ?, ?)");
                $stmt_acc->bind_param("sss", $username, $password, $email);
                $stmt_acc->execute();
                
                $new_user_id = mysqli_insert_id($conn);

                // 3. Chèn vào bảng user (fullname lấy tạm là username)
                $stmt_user = $conn->prepare("INSERT INTO user(id, fullname, phone, address) VALUES(?, ?, ?, ?)");
                $stmt_user->bind_param("isss", $new_user_id, $username, $phone, $address);
                $stmt_user->execute();

                // Nếu mọi thứ ok thì xác nhận lưu vào DB
                mysqli_commit($conn);

                echo "<script>
                    alert('Đăng ký tài khoản thành công!');
                    window.location.href='login.php';
                </script>";
                exit();
            }
        } catch (Exception $e) {
            // Nếu có lỗi ở bất kỳ bước nào, hủy bỏ toàn bộ
            mysqli_rollback($conn);
            echo "<script>alert('Lỗi hệ thống: " . $e->getMessage() . "');</script>";
        }
    } else {
        echo "<script>alert('Vui lòng điền đầy đủ thông tin bắt buộc!');</script>";
    }
}

// Hiển thị Form đăng ký
include('libs/register-form.php');

// Include footer
include('func/footer.php');
?>
<script src="script.js"></script>