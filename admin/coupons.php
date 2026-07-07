<?php
// CLK APPLE STORE - ADMIN COUPON MANAGEMENT
$page_title  = 'Quản lý mã giảm giá';
$active_menu = 'coupons';
require_once('header.php');
require_once('../func/DBConnect.php');

$db = new DBConnect();
$conn = $db->con;

// Xử lý Xóa mã
if (isset($_POST['delete_coupon'])) {
    $id = (int)$_POST['id'];
    $conn->query("DELETE FROM coupons WHERE id = $id");
    $_SESSION['toast_msg']  = '🗑️ Đã xóa mã giảm giá';
    $_SESSION['toast_type'] = 'success';
    header("Location: coupons.php");
    exit;
}

// Xử lý Thêm / Sửa mã
if (isset($_POST['save_coupon'])) {
    $id = (int)($_POST['id'] ?? 0);
    $code = $conn->real_escape_string(trim($_POST['code']));
    $discount_value = (int)$_POST['discount_value'];
    $discount_type = $_POST['discount_type'] === 'fixed' ? 'fixed' : 'percent';
    $min_order_value = (int)$_POST['min_order_value'];
    $max_discount = (int)$_POST['max_discount'];
    $usage_limit = (int)$_POST['usage_limit'];
    $status = isset($_POST['status']) ? 1 : 0;
    
    $valid_from = !empty($_POST['valid_from']) ? "'" . $conn->real_escape_string($_POST['valid_from']) . "'" : "NULL";
    $valid_until = !empty($_POST['valid_until']) ? "'" . $conn->real_escape_string($_POST['valid_until']) . "'" : "NULL";

    if ($id > 0) {
        // Cập nhật
        $sql = "UPDATE coupons SET 
                code='$code', discount_value=$discount_value, discount_type='$discount_type',
                min_order_value=$min_order_value, max_discount=$max_discount, usage_limit=$usage_limit,
                valid_from=$valid_from, valid_until=$valid_until, status=$status
                WHERE id=$id";
        $conn->query($sql);
        $_SESSION['toast_msg']  = '✅ Cập nhật thành công!';
        $_SESSION['toast_type'] = 'success';
    } else {
        // Thêm mới
        $sql = "INSERT INTO coupons (code, discount_value, discount_type, min_order_value, max_discount, usage_limit, valid_from, valid_until, status)
                VALUES ('$code', $discount_value, '$discount_type', $min_order_value, $max_discount, $usage_limit, $valid_from, $valid_until, $status)";
        if ($conn->query($sql)) {
            $_SESSION['toast_msg']  = '✅ Thêm mã thành công!';
            $_SESSION['toast_type'] = 'success';
        } else {
            $_SESSION['toast_msg']  = '❌ Mã đã tồn tại hoặc có lỗi!';
            $_SESSION['toast_type'] = 'error';
        }
    }
    header("Location: coupons.php");
    exit;
}

// Lấy danh sách mã giảm giá
$res = $conn->query("SELECT * FROM coupons ORDER BY id DESC");
$coupons = [];
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $coupons[] = $row;
    }
}
?>

<div class="admin-card mb-4">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="fas fa-plus-circle me-2 text-success"></i>Thêm Mã Giảm Giá</span>
        <button class="btn btn-sm btn-outline-success" onclick="document.getElementById('coupon-form').style.display='block'; document.getElementById('c_id').value=0; document.getElementById('frmCoupon').reset();">
            <i class="fas fa-plus"></i> Thêm mới
        </button>
    </div>
    <div class="card-body" id="coupon-form" style="display:none;">
        <form method="POST" action="coupons.php" id="frmCoupon">
            <input type="hidden" name="id" id="c_id" value="0">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Mã Code *</label>
                    <input type="text" name="code" id="c_code" class="form-control form-control-sm" required placeholder="VD: SALE10">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Loại giảm</label>
                    <select name="discount_type" id="c_type" class="form-select form-select-sm">
                        <option value="percent">Phần trăm (%)</option>
                        <option value="fixed">Số tiền cố định (đ)</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Giá trị giảm *</label>
                    <input type="number" name="discount_value" id="c_value" class="form-control form-control-sm" required min="1" placeholder="VD: 10 hoặc 50000">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Giảm tối đa (đ)</label>
                    <input type="number" name="max_discount" id="c_max" class="form-control form-control-sm" value="0" placeholder="0 = Không giới hạn">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Đơn tối thiểu (đ)</label>
                    <input type="number" name="min_order_value" id="c_min" class="form-control form-control-sm" value="0" placeholder="0 = Áp dụng mọi đơn">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Giới hạn sử dụng</label>
                    <input type="number" name="usage_limit" id="c_limit" class="form-control form-control-sm" value="0" placeholder="0 = Không giới hạn">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Từ ngày</label>
                    <input type="datetime-local" name="valid_from" id="c_from" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Đến ngày</label>
                    <input type="datetime-local" name="valid_until" id="c_until" class="form-control form-control-sm">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" id="c_status" name="status" checked>
                        <label class="form-check-label small fw-bold" for="c_status">Kích hoạt</label>
                    </div>
                </div>
            </div>
            <div class="mt-3 text-end">
                <button type="button" class="btn btn-sm btn-secondary" onclick="document.getElementById('coupon-form').style.display='none'">Hủy</button>
                <button type="submit" name="save_coupon" class="btn btn-sm btn-primary"><i class="fas fa-save"></i> Lưu mã</button>
            </div>
        </form>
    </div>
</div>

<div class="admin-card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="fas fa-list me-2 text-primary"></i>Danh sách Mã giảm giá</span>
        <span class="badge bg-primary rounded-pill"><?= count($coupons) ?></span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle" style="font-size:13px;">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Mã</th>
                        <th>Mức giảm</th>
                        <th>ĐK Tối thiểu</th>
                        <th>Đã dùng</th>
                        <th>Hạn dùng</th>
                        <th>Trạng thái</th>
                        <th class="text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($coupons as $c): ?>
                    <tr>
                        <td class="ps-3 fw-bold text-success"><?= htmlspecialchars($c['code']) ?></td>
                        <td>
                            <?= $c['discount_type'] === 'percent' ? $c['discount_value'].'%' : number_format($c['discount_value']).'đ' ?>
                            <?php if ($c['max_discount'] > 0) echo "<br><small class='text-muted'>Tối đa: ".number_format($c['max_discount'])."đ</small>"; ?>
                        </td>
                        <td><?= number_format($c['min_order_value']) ?>đ</td>
                        <td><?= $c['used_count'] ?> / <?= $c['usage_limit'] > 0 ? $c['usage_limit'] : '∞' ?></td>
                        <td>
                            <?php 
                            if ($c['valid_until']) {
                                $isExpired = strtotime($c['valid_until']) < time();
                                echo "<span class='".($isExpired?"text-danger":"")."'>".date('d/m/Y H:i', strtotime($c['valid_until']))."</span>";
                            } else {
                                echo "Không thời hạn";
                            }
                            ?>
                        </td>
                        <td>
                            <?php if ($c['status']): ?>
                                <span class="badge bg-success">Đang bật</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Đã tắt</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-warning" onclick="editCoupon(<?= htmlspecialchars(json_encode($c)) ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="POST" style="display:inline-block;" onsubmit="return confirm('Xóa mã này?');">
                                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                <button type="submit" name="delete_coupon" class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($coupons)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="fas fa-ticket-alt fa-2x mb-2 d-block opacity-25"></i>
                            Chưa có mã giảm giá nào
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function editCoupon(data) {
    document.getElementById('coupon-form').style.display = 'block';
    document.getElementById('c_id').value = data.id;
    document.getElementById('c_code').value = data.code;
    document.getElementById('c_type').value = data.discount_type;
    document.getElementById('c_value').value = data.discount_value;
    document.getElementById('c_max').value = data.max_discount;
    document.getElementById('c_min').value = data.min_order_value;
    document.getElementById('c_limit').value = data.usage_limit;
    document.getElementById('c_from').value = data.valid_from ? data.valid_from.replace(' ', 'T').slice(0,16) : '';
    document.getElementById('c_until').value = data.valid_until ? data.valid_until.replace(' ', 'T').slice(0,16) : '';
    document.getElementById('c_status').checked = data.status == 1;
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
</script>

<?php require_once('footer.php'); ?>
