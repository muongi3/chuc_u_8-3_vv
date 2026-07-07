<?php
/**
 * ADMIN SHARED HEADER + SIDEBAR
 * Include file này ở ĐẦU mỗi trang admin.
 * Nó sẽ tự kiểm tra quyền Admin và render sidebar.
 *
 * Cách dùng:
 *   require_once 'header.php';          (nếu cùng thư mục admin/)
 *   require_once '../admin/header.php';  (nếu từ thư mục khác)
 *
 * Biến tuỳ chỉnh (khai báo TRƯỚC khi include):
 *   $page_title  — Tiêu đề trang (mặc định: "Admin Panel")
 *   $active_menu — Menu đang active (orders|products|users|stats)
 */

ob_start(); // Buffer output — cho phép header() redirect sau khi HTML bắt đầu
session_start();

// ── Kiểm tra quyền Admin ──────────────────────────────────────
if (!isset($_SESSION['logged']) || $_SESSION['logged'] !== true || (int)($_SESSION['privilege'] ?? 0) !== 1) {
    header("Location: ../login.php");
    exit;
}

$page_title  = $page_title  ?? 'Admin Panel';
$active_menu = $active_menu ?? '';
$admin_name  = $_SESSION['username'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> — CLK Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* ── Variables ── */
        :root {
            --sidebar-w: 240px;
            --sidebar-bg: #001C30;
            --sidebar-hover: #003153;
            --accent: #DAA520;
            --accent-light: #ffd700;
            --topbar-h: 56px;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f0f2f5;
            min-height: 100vh;
        }

        /* ── Sidebar ── */
        #admin-sidebar {
            position: fixed;
            top: 0; left: 0;
            width: var(--sidebar-w);
            height: 100vh;
            background: var(--sidebar-bg);
            display: flex;
            flex-direction: column;
            z-index: 1000;
            transition: transform 0.3s ease;
        }

        .sidebar-brand {
            padding: 20px 20px 16px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        .sidebar-brand h5 {
            color: white;
            font-weight: 800;
            font-size: 1rem;
            margin: 0;
        }
        .sidebar-brand h5 span { color: var(--accent); }
        .sidebar-brand small {
            color: rgba(255,255,255,0.45);
            font-size: 11px;
        }

        .sidebar-nav {
            flex: 1;
            padding: 12px 0;
            overflow-y: auto;
        }

        .nav-section-label {
            color: rgba(255,255,255,0.3);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            padding: 12px 20px 6px;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 20px;
            color: rgba(255,255,255,0.72);
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 500;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }
        .sidebar-link i {
            width: 18px;
            text-align: center;
            font-size: 14px;
            color: rgba(255,255,255,0.4);
            transition: 0.2s;
        }
        .sidebar-link:hover {
            background: rgba(255,255,255,0.06);
            color: white;
            border-left-color: rgba(218,165,32,0.5);
        }
        .sidebar-link:hover i { color: var(--accent); }
        .sidebar-link.active {
            background: rgba(218,165,32,0.12);
            color: var(--accent);
            border-left-color: var(--accent);
            font-weight: 700;
        }
        .sidebar-link.active i { color: var(--accent); }

        .sidebar-divider {
            border-top: 1px solid rgba(255,255,255,0.07);
            margin: 8px 0;
        }

        /* Admin info ở bottom */
        .sidebar-user {
            padding: 14px 20px;
            border-top: 1px solid rgba(255,255,255,0.08);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .sidebar-user .avatar {
            width: 36px; height: 36px;
            border-radius: 50%;
            background: var(--accent);
            display: flex; align-items: center; justify-content: center;
            color: var(--sidebar-bg);
            font-size: 14px;
            font-weight: 800;
            flex-shrink: 0;
        }
        .sidebar-user .info { flex: 1; min-width: 0; }
        .sidebar-user .info strong {
            display: block;
            color: white;
            font-size: 13px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .sidebar-user .info small { color: rgba(255,255,255,0.4); font-size: 11px; }
        .sidebar-user a.logout-btn {
            color: rgba(255,255,255,0.4);
            font-size: 15px;
            transition: 0.2s;
        }
        .sidebar-user a.logout-btn:hover { color: #ff6b6b; }

        /* ── Topbar ── */
        #admin-topbar {
            position: fixed;
            top: 0;
            left: var(--sidebar-w);
            right: 0;
            height: var(--topbar-h);
            background: white;
            border-bottom: 1px solid #e8eaed;
            display: flex;
            align-items: center;
            padding: 0 24px;
            z-index: 900;
            gap: 12px;
        }

        #sidebar-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 18px;
            color: #444;
            cursor: pointer;
            padding: 4px 8px;
        }

        .topbar-title {
            font-weight: 700;
            font-size: 15px;
            color: #1a1a1a;
            flex: 1;
        }

        .topbar-badge {
            background: #f0fdf4;
            color: #16a34a;
            border: 1px solid #bbf7d0;
            border-radius: 20px;
            padding: 3px 10px;
            font-size: 11.5px;
            font-weight: 600;
        }

        .btn-visit-shop {
            background: var(--sidebar-bg);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 6px 14px;
            font-size: 12.5px;
            font-weight: 600;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: 0.2s;
        }
        .btn-visit-shop:hover {
            background: var(--accent);
            color: var(--sidebar-bg);
        }

        /* ── Main Content Area ── */
        #admin-main {
            margin-left: var(--sidebar-w);
            margin-top: var(--topbar-h);
            padding: 28px 28px 40px;
            min-height: calc(100vh - var(--topbar-h));
        }

        /* ── Cards ── */
        .admin-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
            border: none;
        }
        .admin-card .card-header {
            background: none;
            border-bottom: 1px solid #f0f0f0;
            padding: 16px 20px;
            font-weight: 700;
            font-size: 14px;
            color: #1a1a1a;
        }
        .admin-card .card-body { padding: 20px; }

        /* ── Toast Container (bottom-right, stackable) ── */
        #admin-toast-container {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 99999;
            display: flex;
            flex-direction: column-reverse;
            gap: 10px;
            pointer-events: none;
        }
        .admin-toast {
            pointer-events: all;
            min-width: 300px;
            max-width: 380px;
            background: white;
            border-radius: 12px;
            border-left: 4px solid #16a34a;
            padding: 14px 16px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 13.5px;
            transform: translateY(20px);
            opacity: 0;
            transition: transform 0.35s cubic-bezier(0.34,1.56,0.64,1), opacity 0.3s ease;
        }
        .admin-toast.show {
            transform: translateY(0);
            opacity: 1;
        }
        .admin-toast.hide {
            transform: translateY(10px);
            opacity: 0;
        }
        .admin-toast.success { border-left-color: #16a34a; }
        .admin-toast.error   { border-left-color: #dc2626; }
        .admin-toast.warning { border-left-color: #d97706; }
        .admin-toast .t-icon { font-size: 18px; flex-shrink: 0; }
        .admin-toast.success .t-icon { color: #16a34a; }
        .admin-toast.error   .t-icon { color: #dc2626; }
        .admin-toast.warning .t-icon { color: #d97706; }
        .admin-toast .t-msg { flex: 1; color: #1a1a1a; line-height: 1.4; }
        .admin-toast .t-close {
            background: none; border: none;
            color: #aaa; font-size: 14px;
            cursor: pointer; flex-shrink: 0;
            transition: 0.15s;
        }
        .admin-toast .t-close:hover { color: #333; }

        /* ── Mobile Responsive ── */
        @media (max-width: 768px) {
            #admin-sidebar {
                transform: translateX(-100%);
            }
            #admin-sidebar.open {
                transform: translateX(0);
            }
            #admin-topbar {
                left: 0;
            }
            #admin-main {
                margin-left: 0;
            }
            #sidebar-toggle {
                display: flex;
            }
            #sidebar-overlay {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(0,0,0,0.4);
                z-index: 999;
            }
            #sidebar-overlay.show { display: block; }
        }
    </style>
</head>
<body>

<!-- ══ Sidebar ══ -->
<nav id="admin-sidebar">
    <!-- Brand -->
    <div class="sidebar-brand">
        <h5>🛍️ CLK <span>Admin</span></h5>
        <small>Bảng điều khiển quản trị</small>
    </div>

    <!-- Nav Links -->
    <div class="sidebar-nav">
        <div class="nav-section-label">Quản lý</div>

        <a href="orders.php" class="sidebar-link <?php echo $active_menu === 'orders' ? 'active' : ''; ?>">
            <i class="fas fa-shopping-cart"></i> Quản lý đơn hàng
        </a>
        <a href="categories.php" class="sidebar-link <?php echo $active_menu === 'categories' ? 'active' : ''; ?>">
            <i class="fas fa-th-list"></i> Quản lý danh mục
        </a>
        <a href="products.php" class="sidebar-link <?php echo $active_menu === 'products' ? 'active' : ''; ?>">
            <i class="fas fa-mobile-alt"></i> Quản lý sản phẩm
        </a>
        <a href="account.php" class="sidebar-link <?php echo $active_menu === 'users' ? 'active' : ''; ?>">
            <i class="fas fa-users"></i> Quản lý người dùng
        </a>
        <a href="coupons.php" class="sidebar-link <?php echo $active_menu === 'coupons' ? 'active' : ''; ?>">
            <i class="fas fa-ticket-alt"></i> Quản lý mã giảm giá
        </a>

        <div class="nav-section-label" style="margin-top:4px;">Báo cáo</div>
        <a href="dashboard.php" class="sidebar-link <?php echo $active_menu === 'stats' ? 'active' : ''; ?>">
            <i class="fas fa-chart-bar"></i> Thống kê doanh thu
        </a>

        <div class="sidebar-divider"></div>
        <a href="../index.php" class="sidebar-link">
            <i class="fas fa-store"></i> Về trang cửa hàng
        </a>
    </div>

    <!-- User info -->
    <div class="sidebar-user">
        <div class="avatar"><?php echo strtoupper(substr($admin_name, 0, 1)); ?></div>
        <div class="info">
            <strong><?php echo htmlspecialchars($admin_name); ?></strong>
            <small>Administrator</small>
        </div>
        <a href="../logout.php" class="logout-btn" title="Đăng xuất">
            <i class="fas fa-sign-out-alt"></i>
        </a>
    </div>
</nav>

<!-- Mobile overlay -->
<div id="sidebar-overlay" onclick="closeSidebar()"></div>

<!-- ══ Topbar ══ -->
<header id="admin-topbar">
    <button id="sidebar-toggle" onclick="toggleSidebar()" title="Menu">
        <i class="fas fa-bars"></i>
    </button>
    <span class="topbar-title"><?php echo htmlspecialchars($page_title); ?></span>
    <span class="topbar-badge"><i class="fas fa-shield-alt me-1"></i>Admin</span>
    <a href="../index.php" class="btn-visit-shop">
        <i class="fas fa-external-link-alt"></i> Xem shop
    </a>
</header>

<!-- ══ Toast Container ══ -->
<div id="admin-toast-container"></div>

<!-- ══ Main Content begins ══ -->
<main id="admin-main">
