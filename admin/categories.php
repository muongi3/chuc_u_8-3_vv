<?php
/**
 * ADMIN — Quản lý danh mục
 * Dùng shared admin layout (sidebar + topbar)
 */

$page_title  = 'Quản lý danh mục';
$active_menu = 'categories';

require_once('header.php');
require_once('../func/DBConnect.php');
require_once('../func/Manage.php');

$db     = new DBConnect();
$manage = new Manage($db);

$id_url = isset($_GET['id']) ? (int)$_GET['id'] : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['manage-insert'])) {
        $name = trim($_POST['new_name'] ?? '');
        $slug = trim($_POST['new_slug'] ?? '');
        $icon = trim($_POST['new_icon'] ?? 'fas fa-box');

        if ($name && $slug) {
            $manage->insertCategory($slug, $name, $icon);
            $_SESSION['toast_msg']  = "✅ Thêm danh mục \"$name\" thành công!";
            $_SESSION['toast_type'] = 'success';
            header("Location: categories.php");
            exit;
        } else {
            $_SESSION['toast_msg']  = '⚠️ Vui lòng điền đủ thông tin!';
            $_SESSION['toast_type'] = 'warning';
            header("Location: categories.php");
            exit;
        }
    }

    if (isset($_POST['manage-update']) && $id_url) {
        $name = trim($_POST["name-$id_url"] ?? '');
        $slug = trim($_POST["slug-$id_url"] ?? '');
        $icon = trim($_POST["icon-$id_url"] ?? 'fas fa-box');

        $manage->updateCategory($id_url, $slug, $name, $icon);
        $_SESSION['toast_msg']  = "✅ Đã cập nhật danh mục #$id_url";
        $_SESSION['toast_type'] = 'success';
        header("Location: categories.php");
        exit;
    }

    if (isset($_POST['manage-delete']) && $id_url) {
        $manage->deleteCategory($id_url);
        $_SESSION['toast_msg']  = "🗑️ Đã xóa danh mục #$id_url";
        $_SESSION['toast_type'] = 'warning';
        header("Location: categories.php");
        exit;
    }
}

$categoriesData = $manage->getCategories();
$total_categories = count($categoriesData);
?>

<div class="row g-3 mb-4">
    <div class="col-12 col-md-4">
        <div class="admin-card card-body text-center py-3">
            <div style="font-size:26px;color:#f59e0b;"><i class="fas fa-th-list"></i></div>
            <div style="font-size:26px;font-weight:800;color:#111;"><?= $total_categories ?></div>
            <div style="font-size:12px;color:#888;">Tổng danh mục</div>
        </div>
    </div>
</div>

<div class="admin-card mb-4">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="fas fa-plus-circle me-2 text-success"></i>Thêm danh mục mới</span>
        <button class="btn btn-sm btn-outline-success" onclick="
            var f = document.getElementById('add-category-form');
            f.style.display = f.style.display === 'none' ? 'block' : 'none';
        ">
            <i class="fas fa-plus"></i> Thêm
        </button>
    </div>
    <div class="card-body" id="add-category-form" style="display:none;">
        <form method="POST" action="categories.php">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Tên danh mục *</label>
                    <input type="text" name="new_name" class="form-control form-control-sm" placeholder="VD: Điện thoại" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Slug * (Viết liền không dấu)</label>
                    <input type="text" name="new_slug" class="form-control form-control-sm" placeholder="VD: phone" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Icon (FontAwesome)</label>
                    <input type="text" name="new_icon" class="form-control form-control-sm" value="fas fa-box" placeholder="VD: fas fa-mobile-alt">
                </div>
                <div class="col-12 col-md-auto d-flex align-items-end">
                    <button type="submit" name="manage-insert" class="btn btn-success btn-sm px-4">
                        <i class="fas fa-save me-1"></i> Lưu
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="admin-card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="fas fa-list me-2 text-primary"></i>Danh sách danh mục</span>
        <span class="badge bg-primary rounded-pill"><?= $total_categories ?></span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle" style="font-size:13px;">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width:50px;">ID</th>
                        <th>Tên danh mục</th>
                        <th>Slug</th>
                        <th>Icon</th>
                        <th style="width:130px;" class="text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categoriesData as $item): ?>
                    <tr>
                        <td class="ps-3 text-muted fw-bold"><?= $item['id'] ?></td>
                        <form method="POST" action="categories.php?id=<?= $item['id'] ?>">
                        <td>
                            <input type="text" name="name-<?= $item['id'] ?>" value="<?= htmlspecialchars($item['name']) ?>" class="form-control form-control-sm" required>
                        </td>
                        <td>
                            <input type="text" name="slug-<?= $item['id'] ?>" value="<?= htmlspecialchars($item['slug']) ?>" class="form-control form-control-sm" required>
                        </td>
                        <td>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="<?= htmlspecialchars($item['icon']) ?>"></i></span>
                                <input type="text" name="icon-<?= $item['id'] ?>" value="<?= htmlspecialchars($item['icon']) ?>" class="form-control form-control-sm">
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="d-flex gap-1 justify-content-center">
                                <button type="submit" name="manage-update" class="btn btn-warning btn-sm" title="Lưu thay đổi">
                                    <i class="fas fa-save"></i>
                                </button>
                                <button type="submit" name="manage-delete" class="btn btn-danger btn-sm" title="Xóa" onclick="return confirm('Xóa danh mục #<?= $item['id'] ?>?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($categoriesData)): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted py-5">
                            <i class="fas fa-box-open fa-2x mb-2 d-block opacity-25"></i>
                            Chưa có danh mục nào
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once('footer.php'); ?>
