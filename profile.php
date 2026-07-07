<?php
/**
 * File: profile.php
 * Quản lý thông tin cá nhân của người dùng, bao gồm cập nhật avatar và địa chỉ.
 */
ob_start();
// header.php tự gọi session_start() bên trong
include('func/header.php');

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['logged']) || $_SESSION['logged'] !== true) {
    ob_end_clean();
    header("Location: login.php");
    exit();
}

$conn = $db->con;
$u_id = (int)($_SESSION['user_id'] ?? 0);

// 2. Xử lý upload avatar
if (isset($_POST['upload_avatar_btn']) && isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {
    $ext      = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
    $allowed  = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    if (in_array($ext, $allowed)) {
        $filename = "avatar_{$u_id}_" . time() . ".$ext";
        $dest     = "assets/avatars/$filename";
        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $dest)) {
            $stmtAv = $conn->prepare("UPDATE user SET avatar=? WHERE id=?");
            $stmtAv->bind_param("si", $filename, $u_id);
            $stmtAv->execute();
            $_SESSION['avatar'] = $filename;
        }
    }
    ob_end_clean();
    header("Location: profile.php");
    exit();
}

// 3. Xử lý cập nhật thông tin chữ
if (isset($_POST['update_profile'])) {
    $new_fullname = trim($_POST['fullname'] ?? '');
    $new_phone    = trim($_POST['phone']    ?? '');
    $new_address  = trim($_POST['address']  ?? '');

    $stmt = $conn->prepare("UPDATE user SET fullname=?, phone=?, address=? WHERE id=?");
    $stmt->bind_param("sssi", $new_fullname, $new_phone, $new_address, $u_id);
    if ($stmt->execute()) {
        $_SESSION['toast_msg']  = '✅ Cập nhật thông tin thành công!';
        $_SESSION['toast_type'] = 'success';
    }
    ob_end_clean();
    header("Location: profile.php");
    exit();
}

// 4. Lấy dữ liệu user mới nhất từ DB
$row = [];
if ($u_id > 0) {
    $stmt = $conn->prepare("SELECT u.*, a.email FROM user u JOIN account a ON u.id = a.id WHERE u.id = ?");
    $stmt->bind_param("i", $u_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc() ?? [];
    if (!empty($row['avatar'])) $_SESSION['avatar'] = $row['avatar'];
}

$fullname = htmlspecialchars($row['fullname'] ?? ($_SESSION['username'] ?? 'Khách'));
$email    = htmlspecialchars($row['email']    ?? ($_SESSION['email']    ?? ''));
$phone    = htmlspecialchars($row['phone']    ?? '');
$address  = htmlspecialchars($row['address']  ?? '');
$avatar   = $row['avatar'] ?? '';
$initials = strtoupper(mb_substr($fullname, 0, 2));
?>

<style>
.profile-wrap { max-width: 900px; margin: 32px auto; padding: 0 16px 60px; }
.profile-card { background:#fff; border-radius:18px; box-shadow:0 2px 20px rgba(0,0,0,0.08); overflow:hidden; }
.profile-sidebar {
    background: linear-gradient(160deg, #001C30, #003153);
    padding: 36px 24px; text-align: center; color: #fff;
}
.avatar-wrap { position:relative; width:90px; height:90px; margin:0 auto 14px; }
.avatar-circle {
    width:90px; height:90px; border-radius:50%; overflow:hidden;
    border:3px solid rgba(255,255,255,.3);
    background:#DAA520; color:#001C30;
    display:flex; align-items:center; justify-content:center;
    font-size:28px; font-weight:800;
}
.avatar-circle img { width:100%; height:100%; object-fit:cover; }
.avatar-cam-btn {
    position:absolute; bottom:2px; right:2px;
    width:28px; height:28px; border-radius:50%;
    background:#fff; border:none; cursor:pointer;
    display:flex; align-items:center; justify-content:center;
    box-shadow:0 2px 6px rgba(0,0,0,0.2);
}
.avatar-cam-btn i { font-size:12px; color:#001C30; }
.profile-name  { font-size:16px; font-weight:700; margin:0 0 2px; }
.profile-email { font-size:12px; opacity:.7; }
.profile-menu  { margin-top:24px; }
.profile-menu a {
    display:block; padding:10px 14px; border-radius:10px;
    text-decoration:none; color:rgba(255,255,255,.8);
    font-size:13px; font-weight:600; margin-bottom:4px;
    transition:.15s;
}
.profile-menu a.active, .profile-menu a:hover { background:rgba(255,255,255,.15); color:#fff; }
.profile-menu a.danger { color:#ff6b6b; }
.profile-menu a.danger:hover { background:rgba(255,107,107,.15); }

.profile-main { padding: 32px 28px; }
.profile-main h4 { font-size:17px; font-weight:800; color:#111; margin:0 0 24px; }
.field-row { margin-bottom:18px; }
.field-row label { font-size:11px; font-weight:700; text-transform:uppercase; color:#888; margin-bottom:4px; display:block; }
.field-row input {
    width:100%; border:1.5px solid #e5e7eb; border-radius:10px;
    padding:10px 14px; font-size:14px; color:#111; outline:none;
    transition:border-color .2s; background:#fafafa;
}
.field-row input:focus { border-color:#001C30; background:#fff; }
.field-row input[readonly] { background:#f5f5f5; color:#666; cursor:default; }
.save-btn {
    background:#001C30; color:#DAA520; border:none;
    border-radius:10px; padding:11px 32px; font-size:14px;
    font-weight:700; cursor:pointer; transition:.2s;
}
.save-btn:hover { background:#003153; }

@media(min-width:700px){
    .profile-layout { display:flex; }
    .profile-sidebar { width:220px; flex-shrink:0; }
    .profile-main { flex:1; }
}
</style>

<div class="profile-wrap">
  <div class="profile-card">
    <div class="profile-layout">

      <!-- SIDEBAR -->
      <div class="profile-sidebar">
        <!-- Avatar upload -->
        <form action="profile.php" method="POST" enctype="multipart/form-data" id="avatarUploadForm">
            <div class="avatar-wrap">
                <div class="avatar-circle">
                    <?php
                    $avPath = "assets/avatars/$avatar";
                    if (!empty($avatar) && file_exists($avPath)):
                    ?>
                    <img src="<?= $avPath ?>?v=<?= time() ?>" alt="avatar">
                    <?php else: echo $initials; endif; ?>
                </div>
                <label class="avatar-cam-btn" for="avatarInput" title="Đổi ảnh">
                    <i class="fas fa-camera"></i>
                </label>
                <input type="file" id="avatarInput" name="avatar" class="d-none"
                       accept="image/*" onchange="document.getElementById('avatarUploadForm').submit()">
                <input type="hidden" name="upload_avatar_btn" value="1">
            </div>
        </form>

        <div class="profile-name"><?= $fullname ?></div>
        <div class="profile-email"><?= $email ?></div>

        <nav class="profile-menu">
            <a href="profile.php" class="active"><i class="fas fa-user me-2"></i>Thông tin cá nhân</a>
            <a href="history.php"><i class="fas fa-box me-2"></i>Đơn hàng của tôi</a>
            <a href="wishlist.php"><i class="fas fa-heart me-2"></i>Yêu thích</a>
            <a href="logout.php" class="danger"><i class="fas fa-sign-out-alt me-2"></i>Đăng xuất</a>
        </nav>
      </div>

      <!-- NỘI DUNG CHÍNH -->
      <div class="profile-main">
        <h4>⚙️ Thiết lập tài khoản</h4>

        <form action="profile.php" method="POST">
            <div class="field-row">
                <label>Họ và tên</label>
                <input type="text" name="fullname" value="<?= $fullname ?>" required>
            </div>
            <div class="field-row">
                <label>Email</label>
                <input type="email" value="<?= $email ?>" readonly title="Không thể thay đổi email">
            </div>
            <div class="field-row">
                <label>Số điện thoại</label>
                <input type="tel" name="phone" value="<?= $phone ?>" placeholder="0xxx xxx xxx">
            </div>
            <div class="field-row">
                <label>Địa chỉ</label>
                <input type="text" name="address" value="<?= $address ?>" placeholder="Số nhà, đường, phường, quận, thành phố">
            </div>
            <button type="submit" name="update_profile" class="save-btn">
                <i class="fas fa-save me-2"></i>Lưu thay đổi
            </button>
        </form>
      </div>

    </div><!-- /profile-layout -->
  </div><!-- /profile-card -->
</div>

<?php
include('func/footer.php');
ob_end_flush();
?>
