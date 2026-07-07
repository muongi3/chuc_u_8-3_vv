<?php
/**
 * File: order_detail.php
 * Hiển thị thông tin chi tiết của một đơn hàng cụ thể, bao gồm lịch sử trạng thái và danh sách sản phẩm.
 */
ob_start();
session_start();

// Phải đăng nhập mới xem được đơn hàng
if (!isset($_SESSION['logged']) || !$_SESSION['logged']) {
    header("Location: login.php");
    exit;
}

include('func/header.php');

$conn     = $db->con;
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id  = (int)($_SESSION['user_id'] ?? 0);
$is_admin = (int)($_SESSION['privilege'] ?? 0) === 1;

if ($order_id <= 0) {
    echo "<div class='container py-5 text-center'>
            <h3>Mã đơn hàng không hợp lệ!</h3>
            <a href='history.php' class='btn btn-primary mt-3'>Quay lại đơn hàng</a>
          </div>";
    include('func/footer.php');
    exit;
}

// ─── Lấy thông tin đơn hàng ──────────────────────────────────────
// Admin xem được tất cả, User chỉ xem đơn của mình
if ($is_admin) {
    $sql_order = "SELECT o.*, u.fullname, u.phone as user_phone
                  FROM orders o
                  LEFT JOIN user u ON o.user_id = u.id
                  WHERE o.id = $order_id";
} else {
    $sql_order = "SELECT o.*, u.fullname, u.phone as user_phone
                  FROM orders o
                  LEFT JOIN user u ON o.user_id = u.id
                  WHERE o.id = $order_id AND o.user_id = $user_id";
}

$query = mysqli_query($conn, $sql_order);
$order = mysqli_fetch_assoc($query);

if (!$order) {
    echo "<div class='container py-5 text-center'>
            <i class='fas fa-exclamation-circle fa-3x text-danger mb-3 d-block'></i>
            <h3>Không tìm thấy đơn hàng!</h3>
            <p class='text-muted'>Đơn hàng không tồn tại hoặc bạn không có quyền xem.</p>
            <a href='history.php' class='btn btn-primary mt-2'>Quay lại đơn hàng</a>
          </div>";
    include('func/footer.php');
    exit;
}

// ─── Timeline: các bước và trạng thái ─────────────────────────────
$status = $order['status'];

// Thứ tự các bước trong hành trình đơn hàng
$steps = [
    'pending'   => ['icon' => 'fa-file-alt',        'label' => 'Đã đặt hàng',  'color' => '#6366f1'],
    'confirmed' => ['icon' => 'fa-clipboard-check',  'label' => 'Xác nhận',     'color' => '#3b82f6'],
    'packing'   => ['icon' => 'fa-box',              'label' => 'Đóng gói',     'color' => '#f59e0b'],
    'shipping'  => ['icon' => 'fa-shipping-fast',    'label' => 'Đang giao',    'color' => '#8b5cf6'],
    'delivered' => ['icon' => 'fa-check-circle',     'label' => 'Đã giao',      'color' => '#10b981'],
];

// Trạng thái đặc biệt (không theo timeline)
$special = [
    'cancelled' => ['icon' => 'fa-times-circle', 'label' => 'Đã hủy',    'color' => '#ef4444'],
    'returned'  => ['icon' => 'fa-undo',          'label' => 'Hoàn trả', 'color' => '#6b7280'],
];

// Tính index của step hiện tại
$stepKeys     = array_keys($steps);
$currentIndex = array_search($status, $stepKeys);
$isSpecial    = isset($special[$status]);

// ─── Lấy danh sách sản phẩm trong đơn ────────────────────────────
$sql_detail = "SELECT p.id as product_id, p.name, p.image, od.quantity, od.price
               FROM order_detail od
               JOIN product p ON od.product_id = p.id
               WHERE od.order_id = $order_id";

$query_detail  = mysqli_query($conn, $sql_detail);
$orderItems    = mysqli_fetch_all($query_detail, MYSQLI_ASSOC);
$subtotal      = array_sum(array_map(fn($r) => $r['quantity'] * $r['price'], $orderItems));
$total_amount  = $order['total_amount'] ?? $subtotal;
?>

<!-- ══ CHI TIẾT ĐƠN HÀNG ════════════════════════════════════════ -->
<style>
/* ── Order detail CSS ── */
.order-detail-wrap  { max-width: 860px; margin: 0 auto; padding: 32px 16px 60px; }
.order-card         { background:#fff; border-radius:16px; box-shadow:0 4px 20px rgba(0,0,0,0.07); margin-bottom:20px; overflow:hidden; }
.order-card-header  { padding:18px 24px; border-bottom:1px solid #f0f0f0; font-weight:700; font-size:15px; display:flex; align-items:center; gap:8px; }

/* Info grid */
.info-grid          { display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:16px; padding:20px 24px; }
.info-item label    { font-size:12px; color:#888; text-transform:uppercase; letter-spacing:.5px; display:block; margin-bottom:4px; }
.info-item span     { font-size:15px; font-weight:600; color:#111; }

/* ── Timeline ── */
.timeline-wrap      { padding:24px; }
.timeline-track     { display:flex; align-items:flex-start; gap:0; position:relative; }
.timeline-track::before { /* đường kẻ nền */ content:''; position:absolute; top:22px; left:22px; right:22px; height:3px; background:#e5e7eb; z-index:0; }

.tl-step            { flex:1; display:flex; flex-direction:column; align-items:center; position:relative; z-index:1; }
.tl-icon            { width:44px; height:44px; border-radius:50%; display:flex; align-items:center; justify-content:center;
                      font-size:16px; border:3px solid #e5e7eb; background:#fff;
                      transition:all .3s; position:relative; z-index:2; }
.tl-icon.done       { background:var(--step-color,#10b981); border-color:var(--step-color,#10b981); color:#fff; box-shadow:0 0 0 4px rgba(16,185,129,.15); }
.tl-icon.current    { background:#fff; border-color:var(--step-color,#3b82f6); color:var(--step-color,#3b82f6); box-shadow:0 0 0 5px rgba(59,130,246,.15); }
.tl-label           { font-size:11px; font-weight:600; text-align:center; margin-top:8px; color:#888; }
.tl-label.done,
.tl-label.current   { color:#111; }

/* Trạng thái đặc biệt badge */
.status-badge       { display:inline-flex; align-items:center; gap:8px; padding:10px 20px; border-radius:50px; font-weight:700; font-size:14px; }

/* Product table */
.product-list       { padding:0 24px 24px; }
.product-row        { display:flex; align-items:center; gap:14px; padding:12px 0; border-bottom:1px solid #f0f0f0; }
.product-row:last-child { border:none; }
.product-img        { width:56px; height:56px; object-fit:contain; border-radius:8px; background:#f8f8f8; padding:4px; flex-shrink:0; }
.product-name       { flex:1; font-weight:600; font-size:14px; }
.product-qty        { color:#888; font-size:13px; }
.product-price      { font-weight:700; color:#ef4444; font-size:14px; white-space:nowrap; }

/* Total row */
.total-row          { padding:16px 24px; border-top:2px solid #f0f0f0; display:flex; justify-content:space-between; align-items:center; }
.total-row span     { font-size:16px; font-weight:800; color:#ef4444; }

/* Action buttons */
.order-actions      { display:flex; gap:10px; flex-wrap:wrap; padding:16px 24px; border-top:1px solid #f0f0f0; }

@media (max-width:600px) {
    .info-grid           { grid-template-columns:1fr 1fr; }
    .tl-label            { font-size:9.5px; }
    .tl-icon             { width:36px; height:36px; font-size:13px; }
    .timeline-track::before { top:17px; }
}
</style>

<div class="order-detail-wrap">

    <!-- Breadcrumb -->
    <nav style="font-size:13px;color:#888;margin-bottom:20px;">
        <a href="history.php" style="color:inherit;text-decoration:none;">📦 Đơn hàng của tôi</a>
        <span class="mx-2">›</span>
        <span style="color:#111;font-weight:600;">#ORD-<?= str_pad($order['id'], 5, '0', STR_PAD_LEFT) ?></span>
    </nav>

    <!-- ── Card 1: Thông tin tổng quát ── -->
    <div class="order-card">
        <div class="order-card-header">
            <i class="fas fa-receipt text-primary"></i>
            Chi tiết đơn hàng #ORD-<?= str_pad($order['id'], 5, '0', STR_PAD_LEFT) ?>
        </div>
        <div class="info-grid">
            <div class="info-item">
                <label>Ngày đặt</label>
                <span><?= date('d/m/Y H:i', strtotime($order['order_date'])) ?></span>
            </div>
            <div class="info-item">
                <label>Người nhận</label>
                <span><?= htmlspecialchars($order['fullname'] ?? 'Không có') ?></span>
            </div>
            <div class="info-item">
                <label>SĐT</label>
                <span><?= htmlspecialchars($order['phone'] ?? $order['user_phone'] ?? 'Không có') ?></span>
            </div>
            <div class="info-item">
                <label>Địa chỉ</label>
                <span style="font-size:13px;"><?= htmlspecialchars($order['shipping_address'] ?? 'Không có') ?></span>
            </div>
            <div class="info-item">
                <label>Tổng thanh toán</label>
                <span style="color:#ef4444;"><?= number_format($total_amount, 0, ',', '.') ?>đ</span>
            </div>
        </div>
    </div>

    <!-- ── Card 2: Timeline trạng thái ── -->
    <div class="order-card">
        <div class="order-card-header">
            <i class="fas fa-route text-success"></i>
            Hành trình đơn hàng
        </div>
        <div class="timeline-wrap">
            <?php if ($isSpecial): ?>
            <!-- Trạng thái đặc biệt: hủy hoặc hoàn trả -->
            <div class="text-center py-2">
                <div class="status-badge" style="background:<?= $special[$status]['color'] ?>20; color:<?= $special[$status]['color'] ?>; border:2px solid <?= $special[$status]['color'] ?>40; margin:0 auto; display:inline-flex;">
                    <i class="fas <?= $special[$status]['icon'] ?>"></i>
                    <?= $special[$status]['label'] ?>
                </div>
                <p class="text-muted small mt-3">
                    <?= $status === 'cancelled' ? 'Đơn hàng đã bị hủy.' : 'Yêu cầu hoàn trả đang được xử lý.' ?>
                </p>
            </div>
            <?php else: ?>
            <!-- Timeline bình thường -->
            <div class="timeline-track">
                <?php foreach ($steps as $key => $step):
                    $idx = array_search($key, $stepKeys);
                    $isDone    = $currentIndex !== false && $idx < $currentIndex;
                    $isCurrent = ($key === $status);
                    $cls       = $isDone ? 'done' : ($isCurrent ? 'current' : '');
                ?>
                <div class="tl-step">
                    <div class="tl-icon <?= $cls ?>"
                         style="<?= ($isDone || $isCurrent) ? '--step-color:'.$step['color'].';' : '' ?>">
                        <i class="fas <?= $step['icon'] ?>"></i>
                    </div>
                    <div class="tl-label <?= $cls ?>"><?= $step['label'] ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-4">
                <span class="badge rounded-pill px-4 py-2"
                      style="background:<?= $steps[$status]['color'] ?? '#6b7280' ?>20; color:<?= $steps[$status]['color'] ?? '#6b7280' ?>; border:1.5px solid <?= $steps[$status]['color'] ?? '#6b7280' ?>40; font-size:13px; font-weight:700;">
                    <i class="fas <?= $steps[$status]['icon'] ?? 'fa-circle' ?> me-2"></i>
                    <?= $steps[$status]['label'] ?? $status ?>
                </span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ── Card 3: Danh sách sản phẩm ── -->
    <div class="order-card">
        <div class="order-card-header">
            <i class="fas fa-shopping-bag text-warning"></i>
            Sản phẩm đã đặt
            <span class="badge bg-secondary ms-auto"><?= count($orderItems) ?> sp</span>
        </div>
        <div class="product-list">
            <?php if (empty($orderItems)): ?>
            <p class="text-muted text-center py-3">Không có sản phẩm nào trong đơn.</p>
            <?php endif; ?>
            <?php foreach ($orderItems as $row):
                $subtotal_row = $row['quantity'] * $row['price'];
                $imgSrc = !empty($row['image']) ? ltrim($row['image'], './') : 'assets/products/no-image.png';
            ?>
            <div class="product-row">
                <a href="details.php?id=<?= $row['product_id'] ?>">
                    <img src="<?= htmlspecialchars($imgSrc) ?>" alt="" class="product-img">
                </a>
                <div class="product-name">
                    <a href="details.php?id=<?= $row['product_id'] ?>" style="text-decoration:none; color:inherit;">
                        <?= htmlspecialchars($row['name']) ?>
                    </a>
                    <div class="product-qty">x<?= $row['quantity'] ?> &nbsp;·&nbsp; <?= number_format($row['price'], 0, ',', '.') ?>đ/cái</div>
                </div>
                <div class="product-price"><?= number_format($subtotal_row, 0, ',', '.') ?>đ</div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="total-row">
            <div class="text-muted fw-600">Tổng cộng:</div>
            <span><?= number_format($total_amount, 0, ',', '.') ?>đ</span>
        </div>
    </div>

    <!-- ── Nút hành động ── -->
    <div class="order-card">
        <div class="order-actions">
            <a href="history.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Quay lại
            </a>
            <?php if ($status === 'pending'): ?>
            <!-- Chỉ hủy được khi còn pending -->
            <form method="POST" action="cancel_order.php" style="margin:0;">
                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                <button type="submit" class="btn btn-outline-danger"
                        onclick="return confirm('Bạn có chắc muốn hủy đơn hàng này?')">
                    <i class="fas fa-times me-2"></i>Hủy đơn
                </button>
            </form>
            <?php elseif ($status === 'delivered'): ?>
            <!-- Đã giao → cho phép đổi trả -->
            <a href="return_request.php?order_id=<?= $order['id'] ?>" class="btn btn-warning">
                <i class="fas fa-undo me-2"></i>Yêu cầu đổi trả
            </a>
            <?php endif; ?>
        </div>
    </div>

</div><!-- /order-detail-wrap -->

<?php include('func/footer.php'); ?>
