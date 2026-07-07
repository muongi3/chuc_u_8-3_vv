<?php
/**
 * File: logout.php
 * Xử lý đăng xuất tài khoản, hủy session và xóa cookie.
 */
session_start();

session_destroy();

setcookie("user_id","",time()-3600,"/");
setcookie("user_type","",time()-3600,"/");

header("Location: index.php");
exit();
?>