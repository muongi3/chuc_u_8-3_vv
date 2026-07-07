<section class="login-section">
    <div class="login-container">
        <form method="POST" action="login.php" id="sign-in" class="shadow-lg">

            <?php if(isset($_SESSION['logged']) && $_SESSION['logged'] == true) { ?>
                <div class="profile-card text-center">
                    <h3 class="heading-sm">Chào mừng trở lại</h3>
                    
                    <div class="avatar-wrapper">
                        <img src="assets/avatar.png" alt="User Avatar">
                    </div>

                    <div class="user-info">
                        <h4 class="user-name">
                            <?php 
                                if(isset($acc) && isset($_COOKIE['user_id'])) {
                                    echo htmlspecialchars($acc->getAccount($_COOKIE['user_id'], 'user')['fullname']); 
                                } else {
                                    echo "Quản trị viên";
                                }
                            ?>
                        </h4>
                        <p class="user-role">
                            <span class="role-text"><?php echo (isset($_SESSION['privilege']) && $_SESSION['privilege'] == 1) ? 'Nhà thiết kế web' : 'Khách hàng'; ?></span>
                            <span class="badge-status">
                                <?php echo (isset($_SESSION['privilege']) && $_SESSION['privilege'] == 1) ? 'Admin' : 'Member'; ?>
                            </span>
                        </p>
                    </div>

                    <button class="btn-logout" type="submit" name="logout-submit">
                        <i class="fas fa-sign-out-alt"></i> Đăng xuất
                    </button>
                </div>

            <?php } else { ?>
                <h3 class="heading">Đăng nhập</h3>

                <div class="form-content">
                    <div class="form-group">
                        <label>Tên đăng nhập (*)</label>
                        <input name="username" type="text" placeholder="Nhập tên đăng nhập" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Mật khẩu (*)</label>
                        <input name="password" type="password" placeholder="Nhập mật khẩu" class="form-control" required>
                    </div>

                    <button type="submit" class="form-submit" name="login-submit">
                        ĐĂNG NHẬP
                    </button>

                    <div class="form-footer">
                        <p>Chưa có tài khoản? <a href="./register.php">Đăng ký ngay</a></p>
                    </div>
                </div>
            <?php } ?>

        </form>
    </div>
</section>