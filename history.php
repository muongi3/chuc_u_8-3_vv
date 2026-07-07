<?php
/**
 * File: history.php
 * Hiển thị danh sách các đơn hàng của người dùng và trạng thái của chúng.
 */
ob_start();
session_start();

// Chỉ user đã đăng nhập mới xem được
if (!isset($_SESSION['logged']) || !$_SESSION['logged']) {
    header("Location: login.php");
    exit;
}

// Load header trước (tạo ra $db)
include('func/header.php');

$user_id = (int)($_SESSION['user_id'] ?? 0);
$conn    = $db->con;

// ─── Lấy tất cả đơn hàng của user ──────────────────────────────
$sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$orders = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Mapping màu + nhãn trạng thái
function userStatusBadge($status) {
    $map = [
        'pending'   => ['Chờ xác nhận', '#f59e0b', '#fff7ed'],
        'confirmed' => ['Đã xác nhận',  '#3b82f6', '#eff6ff'],
        'packing'   => ['Đang đóng gói','#0ea5e9', '#f0f9ff'],
        'shipping'  => ['Đang giao',    '#8b5cf6', '#f5f3ff'],
        'delivered' => ['Đã giao',      '#10b981', '#f0fdf4'],
        'cancelled' => ['Đã hủy',       '#ef4444', '#fef2f2'],
        'returned'  => ['Đã hoàn trả',  '#6b7280', '#f9fafb'],
    ];
    [$label, $color, $bg] = $map[$status] ?? [$status, '#6b7280', '#f9fafb'];
    return "<span style='background:{$bg};color:{$color};border:1.5px solid {$color}40;
                border-radius:20px;padding:4px 12px;font-size:12px;font-weight:700;'>
                {$label}</span>";
}
?>

<!-- ══ CSS riêng cho trang đơn hàng user ══ -->
<style>
.orders-page    { max-width: 860px; margin: 0 auto; padding: 32px 16px 60px; }
.order-item     { background:#fff; border-radius:14px; box-shadow:0 2px 12px rgba(0,0,0,0.07); margin-bottom:16px; overflow:hidden; border:1px solid #f0f0f0; transition:.2s; }
.order-item:hover { box-shadow: 0 6px 24px rgba(0,0,0,0.1); }
.order-head     { display:flex; align-items:center; justify-content:space-between; padding:14px 20px; border-bottom:1px solid #f5f5f5; flex-wrap:wrap; gap:8px; }
.order-body     { padding:14px 20px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; }
.order-meta     { display:flex; gap:24px; flex-wrap:wrap; }
.order-meta-item label { font-size:11px; color:#888; display:block; text-transform:uppercase; letter-spacing:.5px; }
.order-meta-item span  { font-size:14px; font-weight:600; color:#111; }
.order-actions  { display:flex; gap:8px; align-items:center; flex-shrink:0; }
</style>

<div class="orders-page">

    <!-- Tiêu đề -->
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:10px;">
        <div>
            <h2 style="font-size:22px;font-weight:800;color:#111;margin:0;">📦 Đơn hàng của tôi</h2>
            <p style="color:#888;font-size:13px;margin:4px 0 0;">Theo dõi và quản lý đơn hàng</p>
        </div>
        <span style="background:#eff6ff;color:#3b82f6;padding:6px 14px;border-radius:20px;font-size:13px;font-weight:600;">
            <?= count($orders) ?> đơn hàng
        </span>
    </div>

    <!-- Toast từ session -->
    <?php if (!empty($_SESSION['toast_msg'])): ?>
    <div id="page-toast" style="background:#f0fdf4;border:1.5px solid #bbf7d0;border-radius:10px;padding:12px 16px;margin-bottom:20px;color:#166534;font-weight:600;">
        ✅ <?= htmlspecialchars($_SESSION['toast_msg']) ?>
    </div>
    <?php unset($_SESSION['toast_msg'], $_SESSION['toast_type']); endif; ?>

    <?php if (empty($orders)): ?>
    <!-- Chưa có đơn hàng -->
    <div style="text-align:center;padding:60px 20px;background:#fff;border-radius:14px;">
        <div style="font-size:48px;margin-bottom:16px;">🛍️</div>
        <h3 style="font-size:18px;color:#111;">Bạn chưa có đơn hàng nào</h3>
        <p style="color:#888;">Khám phá sản phẩm và mua sắm ngay!</p>
        <a href="index.php" class="btn btn-primary mt-2">Mua sắm ngay</a>
    </div>
    <?php endif; ?>

    <!-- Danh sách đơn hàng -->
    <?php foreach ($orders as $o):
        // Chỉ cho hủy khi còn pending (chưa xác nhận)
        $canCancel = $o['status'] === 'pending';
        // Chỉ cho đổi trả khi đã giao thành công
        $canReturn = $o['status'] === 'delivered';
    ?>
    <div class="order-item">
        <div class="order-head">
            <div>
                <span style="font-weight:800;color:#111;">
                    #ORD-<?= str_pad($o['id'], 5, '0', STR_PAD_LEFT) ?>
                </span>
                <span style="color:#888;font-size:12px;margin-left:10px;">
                    <?= date('d/m/Y H:i', strtotime($o['order_date'])) ?>
                </span>
            </div>
            <?= userStatusBadge($o['status']) ?>
        </div>

        <div class="order-body">
            <div class="order-meta">
                <div class="order-meta-item">
                    <label>Tổng tiền</label>
                    <span style="color:#ef4444;"><?= number_format($o['total_amount'] ?? 0, 0, ',', '.') ?>đ</span>
                </div>
                <?php if (!empty($o['shipping_address'])): ?>
                <div class="order-meta-item">
                    <label>Địa chỉ giao</label>
                    <span style="font-size:13px;"><?= htmlspecialchars($o['shipping_address']) ?></span>
                </div>
                <?php endif; ?>
            </div>

            <!-- Nút hành động -->
            <div class="order-actions">
                <!-- Xem chi tiết -->
                <a href="order_detail.php?id=<?= $o['id'] ?>" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-eye"></i> Chi tiết
                </a>

                <?php if ($canCancel): ?>
                <!-- Hủy đơn: chỉ khi pending -->
                <form method="POST" action="cancel_order.php" style="margin:0;">
                    <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-outline-danger"
                            onclick="return confirm('Bạn có chắc muốn hủy đơn hàng này?')">
                        <i class="fas fa-times"></i> Hủy
                    </button>
                </form>
                <?php endif; ?>

                <?php if ($canReturn): ?>
                <!-- Yêu cầu đổi trả: chỉ khi delivered -->
                <a href="return_request.php?order_id=<?= $o['id'] ?>" class="btn btn-sm btn-warning">
                    <i class="fas fa-undo"></i> Đổi trả
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

</div>

<?php include('func/footer.php'); ?>
