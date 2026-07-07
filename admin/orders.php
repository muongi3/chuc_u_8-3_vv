<?php
/**
 * ADMIN — Quản lý đơn hàng
 */
$page_title  = 'Quản lý đơn hàng';
$active_menu = 'orders';
require_once('header.php');

require_once('../func/DBConnect.php');
require_once('../func/Order.php');
require_once('../func/ReturnRequest.php');

$db          = new DBConnect();
$orderModel  = new Order($db->con);
$returnModel = new ReturnRequest($db->con);

// ── Đọc filter từ GET ──────────────────────────────────────────
$filters = [
    'status'    => $_GET['status']    ?? '',
    'search'    => $_GET['search']    ?? '',
    'date_from' => $_GET['date_from'] ?? '',
    'date_to'   => $_GET['date_to']   ?? '',
];
$current_page = max(1, (int)($_GET['page'] ?? 1));
$per_page     = 15;

// ── Lấy dữ liệu ──────────────────────────────────────────────
$statusCounts   = $orderModel->getStatusCounts();
$total_orders   = $orderModel->countOrders($filters);
$total_pages    = (int)ceil($total_orders / $per_page);
$orders         = $orderModel->getAllOrders($filters, $current_page, $per_page);
$returnRequests = $returnModel->getAllRequests();

// ── Nhãn badge cho từng trạng thái ──────────────────────────
function statusBadge(string $s): string {
    $cfg = [
        'pending'   => ['bg-warning text-dark',  'Chờ xác nhận'],
        'confirmed' => ['bg-primary',             'Đã xác nhận'],
        'packing'   => ['bg-info text-dark',      'Đóng gói'],
        'shipping'  => ['bg-info',                'Đang giao'],
        'delivered' => ['bg-success',             'Đã giao'],
        'cancelled' => ['bg-danger',              'Đã hủy'],
        'returned'  => ['bg-secondary',           'Hoàn trả'],
    ];
    [$cls, $lbl] = $cfg[$s] ?? ['bg-light text-dark', $s];
    return "<span class=\"badge {$cls}\">{$lbl}</span>";
}

// Danh sách trạng thái cho select
$statusOptions = [
    'pending'   => 'Chờ xác nhận',
    'confirmed' => 'Đã xác nhận',
    'packing'   => 'Đóng gói',
    'shipping'  => 'Đang giao',
    'delivered' => 'Đã giao',
    'cancelled' => 'Đã hủy',
    'returned'  => 'Hoàn trả',
];

// Tổng thống kê nhanh
$cnt = fn(string $s) => $statusCounts[$s] ?? 0;
?>

<!-- ══ Stats cards ══ -->
<div class="row g-3 mb-4">
    <?php
    $stats = [
        ['icon'=>'fa-inbox',        'color'=>'#f59e0b', 'label'=>'Chờ xác nhận', 'val'=>$cnt('pending'),   'filter'=>'pending'],
        ['icon'=>'fa-check-circle', 'color'=>'#3b82f6', 'label'=>'Đã xác nhận',  'val'=>$cnt('confirmed'), 'filter'=>'confirmed'],
        ['icon'=>'fa-truck',        'color'=>'#06b6d4', 'label'=>'Đang giao',    'val'=>$cnt('shipping'),  'filter'=>'shipping'],
        ['icon'=>'fa-box-check',    'color'=>'#16a34a', 'label'=>'Đã giao',      'val'=>$cnt('delivered'), 'filter'=>'delivered'],
    ];
    foreach ($stats as $s): ?>
    <div class="col-6 col-md-3">
        <a href="?status=<?= $s['filter'] ?>" style="text-decoration:none;">
            <div class="admin-card card-body text-center py-3 <?= $filters['status']===$s['filter'] ? 'border border-2' : '' ?>"
                 style="<?= $filters['status']===$s['filter'] ? 'border-color:'.$s['color'].'!important;' : '' ?>">
                <div style="font-size:24px;color:<?= $s['color'] ?>"><i class="fas <?= $s['icon'] ?>"></i></div>
                <div style="font-size:26px;font-weight:800;color:#1a1a1a;"><?= $s['val'] ?></div>
                <div style="font-size:12px;color:#888;"><?= $s['label'] ?></div>
            </div>
        </a>
    </div>
    <?php endforeach; ?>
</div>

<!-- ══ Filter Panel ══ -->
<div class="admin-card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <!-- Tìm kiếm -->
            <div class="col-12 col-md-3">
                <label class="form-label small fw-600 mb-1">🔍 Tìm kiếm</label>
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="Tên, username, mã đơn..."
                       value="<?= htmlspecialchars($filters['search']) ?>">
            </div>
            <!-- Trạng thái -->
            <div class="col-6 col-md-2">
                <label class="form-label small fw-600 mb-1">📋 Trạng thái</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">-- Tất cả --</option>
                    <?php foreach ($statusOptions as $val => $lbl): ?>
                    <option value="<?= $val ?>" <?= $filters['status']===$val ? 'selected' : '' ?>><?= $lbl ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <!-- Từ ngày -->
            <div class="col-6 col-md-2">
                <label class="form-label small fw-600 mb-1">📅 Từ ngày</label>
                <input type="date" name="date_from" class="form-control form-control-sm"
                       value="<?= htmlspecialchars($filters['date_from']) ?>">
            </div>
            <!-- Đến ngày -->
            <div class="col-6 col-md-2">
                <label class="form-label small fw-600 mb-1">📅 Đến ngày</label>
                <input type="date" name="date_to" class="form-control form-control-sm"
                       value="<?= htmlspecialchars($filters['date_to']) ?>">
            </div>
            <!-- Buttons -->
            <div class="col-6 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-filter"></i> Lọc
                </button>
                <a href="orders.php" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-times"></i> Xóa lọc
                </a>
            </div>
        </form>
    </div>
</div>

<!-- ══ Danh sách đơn hàng ══ -->
<div class="admin-card mb-4">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <span>
            <i class="fas fa-list me-2 text-primary"></i>
            Danh sách đơn hàng
            <?php if ($filters['status']): ?>
                <span class="badge bg-primary ms-1"><?= htmlspecialchars($statusOptions[$filters['status']] ?? $filters['status']) ?></span>
            <?php endif; ?>
        </span>
        <span class="text-muted small">
            Hiển thị <?= count($orders) ?> / <?= $total_orders ?> đơn
            — Trang <?= $current_page ?>/<?= max(1,$total_pages) ?>
        </span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle" style="font-size:13px;">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width:60px;">Mã ĐH</th>
                        <th>Khách hàng</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th>Ngày đặt</th>
                        <th style="min-width:230px;">Cập nhật nhanh</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $o): 
                        // Chỉ lock khi đơn đã HỦY — còn lại admin cập nhật tự do
                        $locked = ($o['status'] === 'cancelled');
                    ?>
                    <tr id="order-row-<?= $o['id'] ?>">
                        <td class="ps-3 fw-bold text-muted">#<?= $o['id'] ?></td>
                        <td>
                            <div class="fw-600" style="font-weight:600;"><?= htmlspecialchars($o['fullname']) ?></div>
                        </td>
                        <td class="text-danger fw-bold">
                            <?= number_format($o['total_amount'] ?? $o['total'] ?? $o['total_price'] ?? 0, 0, ',', '.') ?>đ
                        </td>
                        <td>
                            <div id="status-badge-<?= $o['id'] ?>">
                                <?= statusBadge($o['status']) ?>
                            </div>
                        </td>
                        <td class="text-muted small">
                            <?= date('d/m/Y', strtotime($o['order_date'])) ?>
                            <br><?= date('H:i', strtotime($o['order_date'])) ?>
                        </td>
                        <td>
                            <?php if ($locked): ?>
                            <!-- Đơn ĐÃ HỦY: không cho thao tác gì nữa -->
                            <span class="text-danger small fst-italic">
                                🚫 Đã hủy — không thể thay đổi
                            </span>
                            <?php else: ?>
                            <!-- Full dropdown: admin cập nhật tự do -->
                            <div class="d-flex align-items-center gap-1 flex-wrap">
                                <select class="form-select form-select-sm status-select"
                                        data-id="<?= $o['id'] ?>"
                                        style="width:140px;">
                                    <?php foreach ($statusOptions as $val => $lbl): ?>
                                    <option value="<?= $val ?>"
                                        <?= $o['status'] === $val ? 'selected' : '' ?>>
                                        <?= $lbl ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <button class="btn btn-sm btn-primary update-status-btn"
                                        data-id="<?= $o['id'] ?>" title="Lưu thay đổi">
                                    <i class="fas fa-save"></i>
                                </button>
                                <?php if ($o['status'] === 'pending'): ?>
                                <button class="btn btn-sm btn-success quick-confirm-btn"
                                        data-id="<?= $o['id'] ?>" title="Xác nhận ngay">
                                    <i class="fas fa-check"></i> Xác nhận
                                </button>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="fas fa-inbox fa-2x mb-2 d-block opacity-25"></i>
                            Không có đơn hàng nào
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div class="card-body border-top py-2">
        <nav>
            <ul class="pagination pagination-sm mb-0 flex-wrap gap-1">
                <?php
                // Giữ lại filter khi chuyển trang
                $queryBase = http_build_query(array_filter([
                    'status'    => $filters['status'],
                    'search'    => $filters['search'],
                    'date_from' => $filters['date_from'],
                    'date_to'   => $filters['date_to'],
                ]));
                ?>
                <li class="page-item <?= $current_page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="?<?= $queryBase ?>&page=<?= $current_page-1 ?>">‹</a>
                </li>
                <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                <li class="page-item <?= $p === $current_page ? 'active' : '' ?>">
                    <a class="page-link" href="?<?= $queryBase ?>&page=<?= $p ?>"><?= $p ?></a>
                </li>
                <?php endfor; ?>
                <li class="page-item <?= $current_page >= $total_pages ? 'disabled' : '' ?>">
                    <a class="page-link" href="?<?= $queryBase ?>&page=<?= $current_page+1 ?>">›</a>
                </li>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>

<!-- ══ Yêu cầu đổi trả ══ -->
<?php
$returnRequests = $returnModel->getAllRequests();
$pendingReturns = array_filter($returnRequests, fn($r) => $r['status'] === 'pending');
?>
<div class="admin-card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="fas fa-undo me-2 text-warning"></i>Yêu cầu đổi / trả hàng</span>
        <?php if (count($pendingReturns)): ?>
        <span class="badge bg-warning text-dark"><?= count($pendingReturns) ?> chờ duyệt</span>
        <?php endif; ?>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle" style="font-size:13px;">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Mã ĐH</th>
                        <th>Khách hàng</th>
                        <th>Lý do</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($returnRequests as $r): ?>
                    <tr id="return-row-<?= $r['id'] ?>">
                        <td class="ps-3 fw-bold text-muted">#<?= $r['order_id'] ?></td>
                        <td><?= htmlspecialchars($r['fullname'] ?? '') ?></td>
                        <td><?= htmlspecialchars($r['reason']) ?></td>
                        <td id="return-status-<?= $r['id'] ?>">
                            <?php
                            $rb = ['pending'=>'bg-warning text-dark','approved'=>'bg-success','rejected'=>'bg-danger'];
                            $rl = ['pending'=>'Chờ duyệt','approved'=>'Đã duyệt','rejected'=>'Từ chối'];
                            echo '<span class="badge '.($rb[$r['status']]??'bg-secondary').'">'.($rl[$r['status']]??$r['status']).'</span>';
                            ?>
                        </td>
                        <td>
                            <?php if ($r['status'] === 'pending'): ?>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-success handle-return"
                                        data-id="<?= $r['id'] ?>" data-order="<?= $r['order_id'] ?>" data-status="approved">
                                    <i class="fas fa-check"></i> Duyệt
                                </button>
                                <button class="btn btn-sm btn-outline-danger handle-return"
                                        data-id="<?= $r['id'] ?>" data-order="<?= $r['order_id'] ?>" data-status="rejected">
                                    <i class="fas fa-times"></i> Từ chối
                                </button>
                            </div>
                            <?php else: ?>
                            <span class="text-muted small">Đã xử lý</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($returnRequests)): ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">Không có yêu cầu đổi trả</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ══ AJAX ══ -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
const STATUS_LABELS = {
    pending:   '<span class="badge bg-warning text-dark">Chờ xác nhận</span>',
    confirmed: '<span class="badge bg-primary">Đã xác nhận</span>',
    packing:   '<span class="badge bg-info text-dark">Đóng gói</span>',
    shipping:  '<span class="badge bg-info">Đang giao</span>',
    delivered: '<span class="badge bg-success">Đã giao</span>',
    cancelled: '<span class="badge bg-danger">Đã hủy</span>',
    returned:  '<span class="badge bg-secondary">Hoàn trả</span>',
};

function doUpdateStatus(orderId, status, btn) {
    $(btn).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
    $.post('../api/admin_order_api.php', {
        action: 'update_status',
        order_id: orderId,
        status: status
    }, function(res) {
        const data = (typeof res === 'string') ? JSON.parse(res) : res;
        if (data.status === 'success') {
            const label = STATUS_LABELS[status]?.replace(/<[^>]+>/g,'') || status;
            adminToast('Đã cập nhật đơn #' + orderId + ' → ' + label, 'success');
            $(`#status-badge-${orderId}`).html(STATUS_LABELS[status] || status);

            if (status === 'cancelled') {
                // Đơn đã hủy → ẩn controls, chỉ hiện text
                $(`#order-row-${orderId} td:last-child`)
                    .html('<span class="text-danger small fst-italic">🚫 Đã hủy — không thể thay đổi</span>');
            } else {
                // Cập nhật select về trạng thái mới, giữ nguyên controls
                $(`.status-select[data-id="${orderId}"]`).val(status);
                if (status !== 'pending') {
                    $(`#order-row-${orderId} .quick-confirm-btn`).remove();
                }
                $(btn).prop('disabled', false).html('<i class="fas fa-save"></i>');
            }
        } else {
            adminToast(data.message || '❌ Đơn đã hủy — không thể thay đổi!', 'error');
            $(btn).prop('disabled', false).html('<i class="fas fa-save"></i>');
        }
    }).fail(() => {
        adminToast('Lỗi kết nối server!', 'error');
        $(btn).prop('disabled', false).html('<i class="fas fa-save"></i>');
    });
}

$(document).on('click', '.update-status-btn', function() {
    const id     = $(this).data('id');
    const status = $(`.status-select[data-id="${id}"]`).val();
    doUpdateStatus(id, status, this);
});

$(document).on('click', '.quick-confirm-btn', function() {
    const id  = $(this).data('id');
    const btn = this;
    if (!confirm(`Xác nhận đơn hàng #${id}?`)) return;
    doUpdateStatus(id, 'confirmed', btn);
});

$(document).on('click', '.handle-return', function() {
    const btn    = this;
    const id     = $(btn).data('id');
    const order  = $(btn).data('order');
    const status = $(btn).data('status');
    if (!confirm(status==='approved'?'Duyệt yêu cầu đổi trả này?':'Từ chối yêu cầu đổi trả?')) return;

    $(btn).prop('disabled', true);
    $.post('../api/admin_order_api.php', {
        action: 'handle_return', request_id: id, order_id: order, status: status
    }, function(res) {
        const data = (typeof res === 'string') ? JSON.parse(res) : res;
        if (data.status === 'success') {
            adminToast('Đã xử lý yêu cầu đổi trả!', 'success');
            const badge = status==='approved'
                ? '<span class="badge bg-success">Đã duyệt</span>'
                : '<span class="badge bg-danger">Từ chối</span>';
            $(`#return-status-${id}`).html(badge);
            $(`#return-row-${id} td:last-child`).html('<span class="text-muted small">Đã xử lý</span>');
        } else {
            adminToast(data.message || 'Lỗi!', 'error');
            $(btn).prop('disabled', false);
        }
    });
});
</script>

<?php require_once('footer.php'); ?>
