<?php
/**
 * ADMIN — Thống kê & Báo cáo
 */
$page_title  = 'Thống kê & Báo cáo';
$active_menu = 'stats';

// Khởi tạo $db TRƯỚC header để dashboard-stats.php dùng được
require_once('../func/DBConnect.php');
$db = new DBConnect();

require_once('header.php');
?>

<?php include('../libs/dashboard-stats.php'); ?>

<?php require_once('footer.php'); ?>
