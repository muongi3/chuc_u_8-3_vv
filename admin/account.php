<?php
/**
 * ADMIN — Quản lý người dùng (Nâng cấp)
 */
$page_title  = 'Quản lý người dùng';
$active_menu = 'users';

require_once('../func/DBConnect.php');
$db_obj = new DBConnect();
$conn   = $db_obj->con;

require_once('header.php');
require_once('../func/Account.php');

$acc    = new Account($db_obj);
$id_url = isset($_GET['id']) ? (int)$_GET['id'] : null;
$my_id  = (int)($_SESSION['user_id'] ?? 0);

// ─── POST Handlers ────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['account-update']) && $id_url) {
        $username  = trim($_POST["username-$id_url"]  ?? '');
        $password  = trim($_POST["password-$id_url"]  ?? '');
        $email     = trim($_POST["email-$id_url"]     ?? '');
        $privilege = (int)($_POST["privilege-$id_url"] ?? 0);
        // Không cho hạ quyền chính mình
        if ($id_url === $my_id) $privilege = 1;
        $acc->updateAcc($id_url, $username, $password, $email, $privilege);
        $_SESSION['toast_msg']  = "✅ Đã cập nhật tài khoản #$id_url";
        $_SESSION['toast_type'] = 'success';
        header("Location: account.php"); exit;
    }

    if (isset($_POST['account-delete']) && $id_url) {
        if ($id_url === $my_id) {
            $_SESSION['toast_msg']  = '⛔ Không thể xóa tài khoản đang đăng nhập!';
            $_SESSION['toast_type'] = 'error';
        } else {
            $acc->deleteAcc($id_url);
            $_SESSION['toast_msg']  = "🗑️ Đã xóa tài khoản #$id_url";
            $_SESSION['toast_type'] = 'warning';
        }
        header("Location: account.php"); exit;
    }

    if (isset($_POST['account-insert'])) {
        $username  = trim($_POST['new_username']  ?? '');
        $password  = trim($_POST['new_password']  ?? '');
        $email     = trim($_POST['new_email']     ?? '');
        $privilege = (int)($_POST['new_privilege'] ?? 0);
        if ($username && $password && $email) {
            $acc->insertAcc($username, $password, $email, $privilege);
            $_SESSION['toast_msg']  = "✅ Thêm tài khoản \"$username\" thành công!";
            $_SESSION['toast_type'] = 'success';
        } else {
            $_SESSION['toast_msg']  = '⚠️ Vui lòng điền đầy đủ thông tin!';
            $_SESSION['toast_type'] = 'warning';
        }
        header("Location: account.php"); exit;
    }
}

// ─── Lấy dữ liệu (JOIN account + user) ──────────────────────────
$filter_role = $_GET['role'] ?? '';
$search_kw   = trim($_GET['search'] ?? '');

$where  = [];
$params = [];
$types  = '';

if ($filter_role !== '') {
    $where[] = 'a.privilege = ?';
    $params[] = (int)$filter_role;
    $types   .= 'i';
}
if ($search_kw !== '') {
    $where[] = '(a.username LIKE ? OR a.email LIKE ? OR u.fullname LIKE ?)';
    $kw = "%$search_kw%";
    $params[] = $kw; $params[] = $kw; $params[] = $kw;
    $types   .= 'sss';
}

$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$sql = "SELECT a.id, a.username, a.email, a.password, a.privilege,
               u.fullname, u.phone, u.avatar, u.address
        FROM account a
        LEFT JOIN user u ON a.id = u.id
        $where_sql
        ORDER BY a.privilege DESC, a.id ASC";

$stmt = mysqli_prepare($conn, $sql);
if ($params) mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$accData     = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
$totalUsers  = count($accData);
$totalAdmins = count(array_filter($accData, fn($a) => (int)$a['privilege'] === 1));
?>

<style>
.role-badge-admin { background:#fff3cd;color:#92400e;border:1px solid #fde68a; padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700; }
.role-badge-user  { background:#e0f2fe;color:#075985;border:1px solid #bae6fd; padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700; }
.me-badge { background:#dcfce7;color:#166534;border:1px solid #bbf7d0; padding:2px 7px;border-radius:12px;font-size:10px;font-weight:700;vertical-align:middle; }
.acc-avatar { width:34px;height:34px;border-radius:50%;object-fit:cover;background:#e2e8f0; display:flex;align-items:center;justify-content:center;font-weight:700;color:#64748b;font-size:13px; }
.filter-bar { display:flex;gap:10px;flex-wrap:wrap;align-items:center; }
.filter-bar input, .filter-bar select { font-size:13px;border-radius:8px;border:1.5px solid #e5e7eb;padding:6px 12px; outline:none; }
.filter-bar input:focus, .filter-bar select:focus { border-color:#001C30; }
</style>

<!-- ══ STATS CARDS ══════════════════════════════════════════════ -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="admin-card card-body text-center py-3">
            <div style="font-size:24px;color:#3b82f6;"><i class="fas fa-users"></i></div>
            <div style="font-size:28px;font-weight:800;color:#111;"><?= $totalUsers ?></div>
            <div style="font-size:12px;color:#888;">Tổng tài khoản</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="admin-card card-body text-center py-3">
            <div style="font-size:24px;color:#d97706;"><i class="fas fa-user-shield"></i></div>
            <div style="font-size:28px;font-weight:800;color:#111;"><?= $totalAdmins ?></div>
            <div style="font-size:12px;color:#888;">Admin</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="admin-card card-body text-center py-3">
            <div style="font-size:24px;color:#16a34a;"><i class="fas fa-user"></i></div>
            <div style="font-size:28px;font-weight:800;color:#111;"><?= $totalUsers - $totalAdmins ?></div>
            <div style="font-size:12px;color:#888;">Người dùng</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="admin-card card-body text-center py-3">
            <div style="font-size:24px;color:#8b5cf6;"><i class="fas fa-search"></i></div>
            <div style="font-size:28px;font-weight:800;color:#111;"><?= count($accData) ?></div>
            <div style="font-size:12px;color:#888;">Kết quả lọc</div>
        </div>
    </div>
</div>

<!-- ══ FORM THÊM NGƯỜI DÙNG ═══════════════════════════════════════ -->
<div class="admin-card mb-4">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="fas fa-user-plus me-2 text-success"></i>Thêm tài khoản mới</span>
        <button class="btn btn-sm btn-success px-3" onclick="
            var f=document.getElementById('add-user-form');
            f.style.display=f.style.display==='none'?'block':'none';
        "><i class="fas fa-plus me-1"></i>Thêm mới</button>
    </div>
    <div class="card-body" id="add-user-form" style="display:none;">
        <form method="POST" action="account.php">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Tên đăng nhập *</label>
                    <input type="text" name="new_username" class="form-control form-control-sm" placeholder="username" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Mật khẩu *</label>
                    <input type="text" name="new_password" class="form-control form-control-sm" placeholder="password" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Email *</label>
                    <input type="email" name="new_email" class="form-control form-control-sm" placeholder="email@example.com" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Vai trò</label>
                    <select name="new_privilege" class="form-select form-select-sm">
                        <option value="0">👤 User</option>
                        <option value="1">🛡️ Admin</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" name="account-insert" class="btn btn-success btn-sm w-100">
                        <i class="fas fa-save me-1"></i>Lưu tài khoản
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- ══ BỘ LỌC ════════════════════════════════════════════════════ -->
<div class="admin-card mb-3">
    <div class="card-body py-2">
        <form method="GET" action="account.php" class="filter-bar">
            <input type="text" name="search" placeholder="🔍 Tìm tên, email, username..."
                   value="<?= htmlspecialchars($search_kw) ?>" style="flex:1;min-width:200px;">
            <select name="role" style="min-width:130px;">
                <option value="">Tất cả vai trò</option>
                <option value="1" <?= $filter_role==='1'?'selected':'' ?>>🛡️ Admin</option>
                <option value="0" <?= $filter_role==='0'?'selected':'' ?>>👤 User</option>
            </select>
            <button type="submit" class="btn btn-sm btn-primary px-3">Lọc</button>
            <?php if ($filter_role !== '' || $search_kw !== ''): ?>
            <a href="account.php" class="btn btn-sm btn-outline-secondary">✕ Xóa lọc</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- ══ BẢNG DANH SÁCH ════════════════════════════════════════════ -->
<div class="admin-card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="fas fa-list me-2 text-primary"></i>Danh sách tài khoản</span>
        <span class="badge bg-primary rounded-pill"><?= count($accData) ?></span>
    </div>
    <div class="card-body p-0">

        <!-- PC Table -->
        <div class="table-responsive d-none d-md-block">
            <table class="table table-hover mb-0 align-middle" style="font-size:13px;">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-3" style="width:50px;">ID</th>
                        <th style="width:44px;"></th>
                        <th>Tên đăng nhập</th>
                        <th>Họ tên</th>
                        <th>Email</th>
                        <th style="width:110px;">Mật khẩu</th>
                        <th>SĐT</th>
                        <th>Địa chỉ</th>
                        <th style="width:110px;">Vai trò</th>
                        <th style="width:130px;" class="text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($accData as $item):
                        $is_me = ((int)$item['id'] === $my_id);
                        $initials = strtoupper(substr($item['username'] ?? 'U', 0, 1));
                        $avatar_src = !empty($item['avatar'])
                            ? '../assets/avatars/' . htmlspecialchars($item['avatar'])
                            : null;
                    ?>
                    <tr id="acc-row-<?= $item['id'] ?>" <?= $is_me ? 'style="background:#fffbeb;"' : '' ?>>
                        <td class="ps-3 fw-bold text-muted"><?= $item['id'] ?></td>
                        <td>
                            <?php if ($avatar_src): ?>
                                <img src="<?= $avatar_src ?>" class="acc-avatar" alt="">
                            <?php else: ?>
                                <div class="acc-avatar"><?= $initials ?></div>
                            <?php endif; ?>
                        </td>
                        <form method="POST" action="account.php?id=<?= $item['id'] ?>">
                        <td>
                            <input type="text" name="username-<?= $item['id'] ?>"
                                   value="<?= htmlspecialchars($item['username'] ?? '') ?>"
                                   class="form-control form-control-sm" <?= $is_me ? 'style="background:#fef9c3;"' : '' ?>>
                            <?php if ($is_me): ?>
                                <span class="me-badge mt-1 d-inline-block">Bạn</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="text-muted" style="font-size:12px;">
                                <?= htmlspecialchars($item['fullname'] ?? '—') ?>
                            </span>
                        </td>
                        <td>
                            <input type="email" name="email-<?= $item['id'] ?>"
                                   value="<?= htmlspecialchars($item['email'] ?? '') ?>"
                                   class="form-control form-control-sm">
                        </td>
                        <td>
                            <input type="text" name="password-<?= $item['id'] ?>"
                                   value="<?= htmlspecialchars($item['password'] ?? '') ?>"
                                   class="form-control form-control-sm">
                        </td>
                        <td>
                            <span class="text-muted" style="font-size:12px;">
                                <?= htmlspecialchars($item['phone'] ?? '—') ?>
                            </span>
                        </td>
                        <td>
                            <span class="text-muted" style="font-size:12px;">
                                <?= htmlspecialchars($item['address'] ?? '—') ?>
                            </span>
                        </td>
                        <td>
                            <select name="privilege-<?= $item['id'] ?>" class="form-select form-select-sm"
                                    <?= $is_me ? 'disabled' : '' ?>>
                                <option value="0" <?= (int)$item['privilege'] === 0 ? 'selected' : '' ?>>👤 User</option>
                                <option value="1" <?= (int)$item['privilege'] === 1 ? 'selected' : '' ?>>🛡️ Admin</option>
                            </select>
                            <?php if ($is_me): ?>
                                <input type="hidden" name="privilege-<?= $item['id'] ?>" value="1">
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <div class="d-flex gap-1 justify-content-center">
                                <button type="submit" name="account-update"
                                        class="btn btn-warning btn-sm" title="Lưu thay đổi">
                                    <i class="fas fa-save"></i>
                                </button>
                                <button type="submit" name="account-delete"
                                        class="btn btn-danger btn-sm" title="Xóa"
                                        <?= $is_me ? 'disabled title="Không thể xóa chính mình"' : '' ?>
                                        onclick="return confirm('Xóa tài khoản #<?= $item['id'] ?>?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($accData)): ?>
                    <tr>
                        <td colspan="10" class="text-center text-muted py-5">
                            <i class="fas fa-users fa-2x mb-2 d-block opacity-25"></i>
                            Không tìm thấy tài khoản nào
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards -->
        <div class="d-block d-md-none p-3">
            <div class="row g-3">
                <?php foreach ($accData as $item):
                    $is_me = ((int)$item['id'] === $my_id);
                ?>
                <div class="col-12">
                    <div style="background:<?= $is_me?'#fffbeb':'#f8f9fa' ?>;border-radius:12px;padding:14px;border:1.5px solid <?= $is_me?'#fde68a':'#e9ecef' ?>;">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <span class="fw-bold" style="font-size:15px;">
                                    <?= htmlspecialchars($item['username'] ?? '') ?>
                                    <?php if ($is_me): ?><span class="me-badge ms-1">Bạn</span><?php endif; ?>
                                </span>
                                <span class="text-muted small ms-1">#<?= $item['id'] ?></span>
                                <?php if ($item['fullname']): ?>
                                <div class="text-muted small"><?= htmlspecialchars($item['fullname']) ?></div>
                                <?php endif; ?>
                            </div>
                            <?php if ((int)$item['privilege'] === 1): ?>
                            <span class="role-badge-admin">🛡️ Admin</span>
                            <?php else: ?>
                            <span class="role-badge-user">👤 User</span>
                            <?php endif; ?>
                        </div>
                        <div class="text-muted small mb-1">📧 <?= htmlspecialchars($item['email'] ?? '—') ?></div>
                        <?php if ($item['phone']): ?>
                        <div class="text-muted small mb-1">📞 <?= htmlspecialchars($item['phone']) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($item['address'])): ?>
                        <div class="text-muted small mb-3">📍 <?= htmlspecialchars($item['address']) ?></div>
                        <?php else: ?>
                        <div class="text-muted small mb-3">📍 Chưa có địa chỉ</div>
                        <?php endif; ?>
                        <?php if (!$is_me): ?>
                        <form method="POST" action="account.php?id=<?= $item['id'] ?>" style="display:inline;">
                            <button type="submit" name="account-delete" class="btn btn-sm btn-outline-danger"
                                    onclick="return confirm('Xóa tài khoản này?')">
                                <i class="fas fa-trash"></i> Xóa
                            </button>
                        </form>
                        <?php else: ?>
                        <span class="text-muted small">Không thể xóa tài khoản đang đăng nhập</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($accData)): ?>
                <div class="col-12 text-center text-muted py-4">Chưa có tài khoản nào.</div>
                <?php endif; ?>
            </div>
        </div>

    </div><!-- /card-body -->
</div><!-- /admin-card -->

<?php require_once('footer.php'); ?>
