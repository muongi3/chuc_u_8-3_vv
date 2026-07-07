<?php
/**
 * ADMIN — Quản lý sản phẩm
 * Dùng shared admin layout (sidebar + topbar)
 */

// Khai báo trước khi include header
$page_title  = 'Quản lý sản phẩm';
$active_menu = 'products';

// Include header: kiểm tra session + render sidebar
require_once('header.php');

// Load class (Manage cần DBConnect object, không phải $db->con)
require_once('../func/DBConnect.php');
require_once('../func/Manage.php');

$db     = new DBConnect();  // DBConnect object
$manage = new Manage($db);  // Truyền DBConnect, không phải $db->con

// ─── Lấy ID từ URL (dùng cho update/delete) ──────────────────────
$id_url = isset($_GET['id']) ? (int)$_GET['id'] : null;

// ─── Xử lý POST (Thêm / Sửa / Xóa) ──────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // THÊM sản phẩm mới (cần ảnh bắt buộc)
    if (isset($_POST['manage-insert'])) {
        $name  = trim($_POST['new_name']  ?? '');
        $brand = (int)($_POST['new_brand'] ?? 0);
        $price = (int)($_POST['new_price'] ?? 0);
        $stock = (int)($_POST['new_stock'] ?? 100);
        $cat   = trim($_POST['new_category'] ?? 'phone');
        $image = $_FILES['new_image'] ?? null;

        if ($name && $brand) {
            $manage->insertProduct($name, $brand, $price, $image, $cat, $stock);
            // insertProduct tự redirect nếu thành công
            // Nếu tới đây: set toast rồi redirect thủ công
            $_SESSION['toast_msg']  = "✅ Thêm sản phẩm \"$name\" thành công!";
            $_SESSION['toast_type'] = 'success';
            header("Location: products.php");
            exit;
        } else {
            $_SESSION['toast_msg']  = '⚠️ Vui lòng điền tên và chọn hãng!';
            $_SESSION['toast_type'] = 'warning';
            header("Location: products.php");
            exit;
        }
    }

    // CẬP NHẬT sản phẩm
    if (isset($_POST['manage-update']) && $id_url) {
        $name  = $_POST["name-$id_url"]  ?? null;
        $brand = $_POST["brand-$id_url"] ?? null;
        $price = $_POST["price-$id_url"] ?? 0;
        $stock = $_POST["stock-$id_url"] ?? 0;
        $cat   = $_POST["category-$id_url"] ?? null;
        $image = $_FILES["image-$id_url"] ?? null;

        $manage->updateProduct($id_url, $name, $brand, $price, $image, $cat, $stock);
        // updateProduct tự redirect; nếu qua đây set toast
        $_SESSION['toast_msg']  = "✅ Đã cập nhật sản phẩm #$id_url";
        $_SESSION['toast_type'] = 'success';
        header("Location: products.php");
        exit;
    }

    // XÓA sản phẩm
    if (isset($_POST['manage-delete']) && $id_url) {
        $manage->deleteProduct($id_url);
        $_SESSION['toast_msg']  = "🗑️ Đã xóa sản phẩm #$id_url";
        $_SESSION['toast_type'] = 'warning';
        header("Location: products.php");
        exit;
    }
}

// ─── Lấy dữ liệu từ database ──────────────────────────────────────
$manageData     = $manage->getData();    // Danh sách sản phẩm (có JOIN brand)
$brandData      = $manage->getBrands();  // Danh sách hãng
$categoriesData = $manage->getCategories(); // Danh sách danh mục
$total_products = count($manageData);
?>

<!-- ══ STATS CARDS ══════════════════════════════════════════════ -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4">
        <div class="admin-card card-body text-center py-3">
            <div style="font-size:26px;color:#f59e0b;"><i class="fas fa-mobile-alt"></i></div>
            <div style="font-size:26px;font-weight:800;color:#111;"><?= $total_products ?></div>
            <div style="font-size:12px;color:#888;">Tổng sản phẩm</div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="admin-card card-body text-center py-3">
            <div style="font-size:26px;color:#3b82f6;"><i class="fas fa-tags"></i></div>
            <div style="font-size:26px;font-weight:800;color:#111;"><?= count($brandData) ?></div>
            <div style="font-size:12px;color:#888;">Thương hiệu</div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="admin-card card-body text-center py-3">
            <div style="font-size:26px;color:#16a34a;"><i class="fas fa-check-circle"></i></div>
            <?php
            // Đếm sản phẩm có ảnh
            $hasImage = count(array_filter($manageData, fn($p) => !empty($p['image'])));
            ?>
            <div style="font-size:26px;font-weight:800;color:#111;"><?= $hasImage ?></div>
            <div style="font-size:12px;color:#888;">Có hình ảnh</div>
        </div>
    </div>
</div>

<!-- ══ FORM THÊM SẢN PHẨM MỚI ══════════════════════════════════ -->
<div class="admin-card mb-4">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="fas fa-plus-circle me-2 text-success"></i>Thêm sản phẩm mới</span>
        <button class="btn btn-sm btn-outline-success" onclick="
            var f = document.getElementById('add-product-form');
            f.style.display = f.style.display === 'none' ? 'block' : 'none';
        ">
            <i class="fas fa-plus"></i> Thêm
        </button>
    </div>
    <div class="card-body" id="add-product-form" style="display:none;">
        <form method="POST" enctype="multipart/form-data" action="products.php">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Tên sản phẩm *</label>
                    <input type="text" name="new_name" class="form-control form-control-sm"
                           placeholder="VD: iPhone 15 Pro Max" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Thương hiệu *</label>
                    <select name="new_brand" class="form-select form-select-sm" required>
                        <option value="">-- Chọn hãng --</option>
                        <?php foreach ($brandData as $b): ?>
                        <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['brand']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Loại hàng *</label>
                    <select name="new_category" class="form-select form-select-sm" required>
                        <?php foreach ($categoriesData as $c): ?>
                        <option value="<?= htmlspecialchars($c['slug']) ?>"><?= htmlspecialchars($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Giá (đ)</label>
                    <input type="number" name="new_price" step="1000" min="0"
                           class="form-control form-control-sm" placeholder="0">
                </div>
                <div class="col-md-1">
                    <label class="form-label small fw-bold">Kho</label>
                    <input type="number" name="new_stock" min="0" value="100"
                           class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Hình ảnh</label>
                    <input type="file" name="new_image" accept="image/*"
                           class="form-control form-control-sm">
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

<!-- ══ DANH SÁCH SẢN PHẨM ════════════════════════════════════ -->
<div class="admin-card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="fas fa-list me-2 text-primary"></i>Danh sách sản phẩm</span>
        <span class="badge bg-primary rounded-pill"><?= $total_products ?></span>
    </div>
    <div class="card-body p-0">

        <!-- ── PC: Hiển thị bảng ────────────────────────────────── -->
        <div class="table-responsive d-none d-md-block">
            <table class="table table-hover mb-0 align-middle" style="font-size:13px;">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width:50px;">ID</th>
                        <th style="width:60px;">Ảnh</th>
                        <th>Tên sản phẩm</th>
                        <th style="width:140px;">Thương hiệu</th>
                        <th style="width:140px;">Giá</th>
                        <th style="width:80px;">Kho</th>
                        <th style="width:130px;" class="text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($manageData as $item): ?>
                    <tr>
                        <td class="ps-3 text-muted fw-bold"><?= $item['id'] ?></td>
                        <td>
                            <!-- Hiển thị ảnh sản phẩm (đường dẫn từ gốc htdocs/) -->
                        <?php
                            // DB lưu tên file thuần (vd: iphone_123.jpg)
                            // Hoặc đường dẫn cũ (./assets/...) — xử lý cả 2 trường hợp
                            if (!empty($item['image'])) {
                                $img = $item['image'];
                                // Nếu là đường dẫn cũ (chứa '/' hoặc '.'), dùng ltrim
                                if (strpos($img, '/') !== false || strpos($img, '.') === 0) {
                                    $imgSrc = '../' . ltrim($img, './');
                                } else {
                                    // Tên file thuần mới
                                    $imgSrc = '../assets/products/' . $img;
                                }
                            } else {
                                $imgSrc = '../assets/products/no-image.png';
                            }
                        ?>
                            <img src="<?= htmlspecialchars($imgSrc) ?>" alt=""
                                 style="width:44px;height:44px;object-fit:contain;border-radius:6px;background:#f8f8f8;padding:2px;">
                        </td>
                        <!-- Form update inline từng dòng -->
                        <form method="POST" enctype="multipart/form-data"
                              action="products.php?id=<?= $item['id'] ?>">
                        <td>
                            <input type="text" name="name-<?= $item['id'] ?>"
                                   value="<?= htmlspecialchars($item['name']) ?>"
                                   class="form-control form-control-sm" required>
                        </td>
                        <td>
                            <select name="brand-<?= $item['id'] ?>" class="form-select form-select-sm">
                                <?php foreach ($brandData as $b): ?>
                                <option value="<?= $b['id'] ?>"
                                    <?= ($item['brand'] ?? '') == $b['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($b['brand']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <select name="category-<?= $item['id'] ?>" class="form-select form-select-sm mt-1" style="font-size:11px;">
                                <?php foreach ($categoriesData as $c): ?>
                                <option value="<?= htmlspecialchars($c['slug']) ?>" <?= $item['category'] == $c['slug'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <div class="input-group input-group-sm">
                                <input type="number" name="price-<?= $item['id'] ?>"
                                       value="<?= (int)$item['price'] ?>" step="1000"
                                       class="form-control form-control-sm text-danger fw-bold">
                                <span class="input-group-text" style="font-size:11px;">đ</span>
                            </div>
                            <!-- Ảnh mới (tuỳ chọn) -->
                            <input type="file" name="image-<?= $item['id'] ?>" accept="image/*"
                                   class="form-control form-control-sm mt-1" style="font-size:11px;">
                        </td>
                        <td>
                            <input type="number" name="stock-<?= $item['id'] ?>"
                                   value="<?= (int)$item['stock'] ?>" min="0"
                                   class="form-control form-control-sm <?= $item['stock'] < 10 ? 'border-danger text-danger bg-danger bg-opacity-10' : '' ?>">
                            <?php if ($item['stock'] < 10): ?>
                                <small class="text-danger fw-bold d-block mt-1" style="font-size:10px;">Sắp hết!</small>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <div class="d-flex gap-1 justify-content-center">
                                <button type="submit" name="manage-update"
                                        class="btn btn-warning btn-sm" title="Lưu thay đổi">
                                    <i class="fas fa-save"></i>
                                </button>
                                <button type="submit" name="manage-delete"
                                        class="btn btn-danger btn-sm" title="Xóa"
                                        onclick="return confirm('Xóa sản phẩm #<?= $item['id'] ?>?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($manageData)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="fas fa-box-open fa-2x mb-2 d-block opacity-25"></i>
                            Chưa có sản phẩm nào
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- ── Mobile: Hiển thị dạng card (có ảnh) ─────────────── -->
        <div class="d-block d-md-none p-3">
            <div class="row g-3">
                <?php foreach ($manageData as $item): ?>
                <div class="col-12">
                    <div style="background:#f8f9fa;border-radius:12px;padding:14px;border:1px solid #e9ecef;display:flex;gap:12px;align-items:center;">
                        <!-- Ảnh sản phẩm -->
                        <?php
                            if (!empty($item['image'])) {
                                $img = $item['image'];
                                if (strpos($img, '/') !== false || strpos($img, '.') === 0) {
                                    $imgSrc = '../' . ltrim($img, './');
                                } else {
                                    $imgSrc = '../assets/products/' . $img;
                                }
                            } else {
                                $imgSrc = '../assets/products/no-image.png';
                            }
                        ?>
                        <img src="<?= htmlspecialchars($imgSrc) ?>" alt=""
                             style="width:60px;height:60px;object-fit:contain;border-radius:8px;background:#fff;flex-shrink:0;">

                        <!-- Thông tin -->
                        <div style="flex:1;min-width:0;">
                            <div class="fw-bold" style="font-size:14px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                <?= htmlspecialchars($item['name']) ?>
                            </div>
                            <div class="text-muted small"><?= htmlspecialchars($item['brand_name'] ?? '') ?></div>
                            <div class="text-danger fw-bold">
                                <?= number_format($item['price'], 0, ',', '.') ?>đ
                            </div>
                        </div>

                        <!-- Nút xóa -->
                        <form method="POST" action="products.php?id=<?= $item['id'] ?>">
                            <button type="submit" name="manage-delete"
                                    class="btn btn-sm btn-outline-danger"
                                    onclick="return confirm('Xóa?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($manageData)): ?>
                <div class="col-12 text-center text-muted py-4">Chưa có sản phẩm nào.</div>
                <?php endif; ?>
            </div>
        </div>

    </div><!-- /card-body -->
</div><!-- /admin-card -->

<?php require_once('footer.php'); ?>
