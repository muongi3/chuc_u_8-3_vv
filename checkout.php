<?php
/**
 * File: checkout.php
 * Trang thanh toán chi tiết, chọn phương thức vận chuyển và thanh toán.
 */
ob_start();
include('func/header.php');

$conn = $db->con;

// Bắt buộc đăng nhập
if (!isset($_SESSION['user_id'])) {
    $_SESSION['toast_msg']  = '⚠️ Vui lòng đăng nhập để thanh toán!';
    $_SESSION['toast_type'] = 'warning';
    ob_end_clean();
    header("Location: login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];

// ── Lấy thông tin user ────────────────────────────────────────────
$stmt = $conn->prepare("SELECT u.*, a.email FROM user u JOIN account a ON u.id = a.id WHERE u.id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc() ?? [];

// ── Lấy giỏ hàng ─────────────────────────────────────────────────
$res_cart   = $conn->query(
    "SELECT c.*, p.name, p.price, p.image
     FROM cart c JOIN product p ON c.item_id = p.id
     WHERE c.user_id = $user_id"
);
$cart_items = [];
$subtotal   = 0;
while ($row = $res_cart->fetch_assoc()) {
    $row['qty']  = max(1, (int)($row['quantity'] ?? 1));
    $cart_items[] = $row;
    $subtotal    += $row['price'] * $row['qty'];
}

// ── Lấy mã giảm giá khả dụng ────────────────────────────────────
$res_coupons = $conn->query(
    "SELECT * FROM coupons 
     WHERE status = 1 
       AND (valid_until IS NULL OR valid_until > NOW())
       AND (usage_limit = 0 OR used_count < usage_limit)"
);
$available_coupons = [];
if ($res_coupons) {
    while ($row = $res_coupons->fetch_assoc()) {
        $available_coupons[] = $row;
    }
}

// ── Xử lý đặt hàng ───────────────────────────────────────────────
if (isset($_POST['place-order']) && count($cart_items) > 0) {
    $addr     = trim($conn->real_escape_string($_POST['address']   ?? ''));
    $phone    = trim($conn->real_escape_string($_POST['phone']     ?? ''));
    $shipping = $_POST['shipping'] ?? 'standard';
    $payment  = $_POST['payment']  ?? 'cod';

    $discount_amount = (int)($_POST['discount_amount'] ?? 0);
    $coupon_code = $conn->real_escape_string($_POST['coupon_code'] ?? '');
    $ship_fee    = ($shipping === 'fast') ? 40000 : 20000;
    $final_total = max(0, $subtotal + $ship_fee - $discount_amount);

    $date = date('Y-m-d H:i:s');

    $conn->begin_transaction();
    try {
        // Tăng used_count của coupon nếu có
        if ($discount_amount > 0 && !empty($coupon_code)) {
            $conn->query("UPDATE coupons SET used_count = used_count + 1 WHERE code = '$coupon_code'");
        }

        // Insert orders
        $stmtO = $conn->prepare(
            "INSERT INTO orders (user_id, order_date, status, total_amount, discount_amount, shipping_address, phone, payment_method)
             VALUES (?, ?, 'pending', ?, ?, ?, ?, ?)"
        );
        $stmtO->bind_param("isdisss", $user_id, $date, $final_total, $discount_amount, $addr, $phone, $payment);
        $stmtO->execute();
        $order_id = $conn->insert_id;

        // Insert order_detail
        foreach ($cart_items as $item) {
            $p_id  = (int)$item['item_id'];
            $qty   = (int)$item['qty'];
            // Lấy giá thực từ DB (chống fake giá)
            $pr    = $conn->query("SELECT price FROM product WHERE id = $p_id")->fetch_assoc();
            $price = $pr ? (float)$pr['price'] : (float)$item['price'];
            $conn->query("INSERT INTO order_detail (order_id, product_id, quantity, price)
                          VALUES ($order_id, $p_id, $qty, $price)");
            // Trừ tồn kho
            $conn->query("UPDATE product SET stock = stock - $qty WHERE id = $p_id");
        }

        // Xóa giỏ hàng
        $conn->query("DELETE FROM cart WHERE user_id = $user_id");

        $conn->commit();

        $_SESSION['toast_msg']  = "🎉 Đặt hàng #$order_id thành công! Chúng tôi sẽ sớm xác nhận đơn.";
        $_SESSION['toast_type'] = 'success';
        ob_end_clean();
        header("Location: history.php");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['toast_msg']  = '❌ Lỗi đặt hàng, vui lòng thử lại!';
        $_SESSION['toast_type'] = 'error';
    }
}
?>

<style>
/* ─── Checkout Layout ─── */
.checkout-wrap { max-width: 1000px; margin: 28px auto; padding: 0 16px 60px; }
.checkout-wrap h2 { font-size: 20px; font-weight: 800; color: #111; margin-bottom: 24px; }

.ck-card {
    background: #fff; border-radius: 14px;
    box-shadow: 0 2px 16px rgba(0,0,0,0.07);
    margin-bottom: 18px; overflow: hidden;
}
.ck-card-header {
    padding: 14px 20px; border-bottom: 1px solid #f0f0f0;
    font-weight: 700; font-size: 14px; color: #111;
    display: flex; align-items: center; gap: 8px;
}
.ck-card-body { padding: 18px 20px; }

/* Address */
.addr-display p  { color: #444; font-size: 14px; margin: 0; }
.addr-change-btn {
    background: none; border: 1px solid #001C30; color: #001C30;
    border-radius: 6px; padding: 3px 12px; font-size: 12px; font-weight: 600;
    cursor: pointer; margin-top: 8px; transition: .15s;
}
.addr-change-btn:hover { background: #001C30; color: #fff; }

/* Product list */
.ck-item { display: flex; align-items: center; gap: 14px; padding: 12px 0; border-bottom: 1px solid #f5f5f5; }
.ck-item:last-child { border: none; }
.ck-item img { width: 64px; height: 64px; object-fit: contain; background: #f5f5f7; border-radius: 10px; padding: 6px; }
.ck-item-name  { font-size: 13px; font-weight: 700; color: #111; }
.ck-item-qty   { font-size: 12px; color: #888; margin-top: 2px; }
.ck-item-price { font-size: 14px; font-weight: 800; color: #ef4444; margin-left: auto; white-space: nowrap; }

/* Options */
.ship-option, .pay-option {
    display: flex; align-items: center; gap: 10px;
    padding: 11px 14px; border: 1.5px solid #e5e7eb;
    border-radius: 10px; margin-bottom: 8px; cursor: pointer;
    transition: border-color .2s, background .2s;
}
.ship-option:hover, .pay-option:hover { border-color: #001C30; background: #f8faff; }
.ship-option input, .pay-option input { accent-color: #001C30; }
.ship-option.checked, .pay-option.checked { border-color: #001C30; background: #eef2ff; }
.option-icon { font-size: 20px; }
.option-text  { flex: 1; }
.option-text b { font-size: 13px; display: block; color: #111; }
.option-text small { color: #888; font-size: 11px; }
.option-price { font-weight: 700; font-size: 13px; color: #001C30; }

/* Summary box */
.summary-box {
    background: linear-gradient(160deg,#001C30,#003153);
    border-radius: 14px; padding: 22px 20px; color: #fff;
    position: sticky; top: 80px;
}
.summary-row { display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 10px; }
.summary-row.total { font-size: 17px; font-weight: 800; border-top: 1px solid rgba(255,255,255,.2); padding-top: 12px; margin-top: 4px; }
.summary-row.total span:last-child { color: #DAA520; }
.voucher-select {
    width: 100%; background: rgba(255,255,255,.12); border: 1.5px solid rgba(255,255,255,.25);
    color: #fff; border-radius: 8px; padding: 8px 12px; font-size: 13px;
    margin-bottom: 14px; outline: none;
}
.voucher-select option { color: #111; background: #fff; }
.order-btn {
    width: 100%; background: #DAA520; color: #001C30;
    border: none; border-radius: 10px; padding: 13px;
    font-size: 15px; font-weight: 800; cursor: pointer; transition: .2s;
    letter-spacing: .5px;
}
.order-btn:hover { background: #ffd700; }
.order-btn:disabled { background: #555; color: #888; cursor: not-allowed; }
.coupon-tag {
    background: rgba(218, 165, 32, 0.2);
    border: 1px dashed #DAA520;
    color: #ffd700;
    padding: 6px 10px;
    border-radius: 6px;
    font-size: 11.5px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.coupon-tag:hover {
    background: rgba(218, 165, 32, 0.4);
}
.coupon-desc {
    display: block;
    font-size: 10px;
    color: rgba(255,255,255,0.7);
    margin-top: 2px;
    font-weight: 400;
}

/* Empty cart */
.ck-empty { text-align: center; padding: 40px 20px; }
.ck-empty h4 { font-size: 16px; color: #111; margin: 12px 0 6px; }

/* Layout */
@media(min-width: 768px) {
    .checkout-cols { display: grid; grid-template-columns: 1fr 320px; gap: 20px; align-items: start; }
}

/* Input fields */
.ck-input {
    width: 100%; border: 1.5px solid #e5e7eb; border-radius: 8px;
    padding: 9px 12px; font-size: 13px; color: #111; outline: none;
    transition: border-color .2s; margin-bottom: 8px;
}
.ck-input:focus { border-color: #001C30; }
.ck-label { font-size: 11px; font-weight: 700; text-transform: uppercase; color: #888; margin-bottom: 3px; display: block; }
</style>

<div class="checkout-wrap">
    <h2>🛒 Thanh toán</h2>

    <?php if (empty($cart_items)): ?>
    <!-- Giỏ trống -->
    <div class="ck-card">
        <div class="ck-card-body ck-empty">
            <div style="font-size:48px;">🛍️</div>
            <h4>Giỏ hàng của bạn đang trống</h4>
            <p style="color:#888;font-size:13px;">Hãy thêm sản phẩm vào giỏ trước khi thanh toán.</p>
            <a href="index.php" style="background:#001C30;color:#DAA520;border-radius:8px;padding:10px 28px;font-weight:700;text-decoration:none;font-size:14px;">
                Mua sắm ngay
            </a>
        </div>
    </div>

    <?php else: ?>
    <form method="POST" action="checkout.php" id="checkoutForm">
    <div class="checkout-cols">

        <!-- ══ CỘT TRÁI ══ -->
        <div>

            <!-- 1. Địa chỉ -->
            <div class="ck-card">
                <div class="ck-card-header">
                    📍 Địa chỉ nhận hàng
                </div>
                <div class="ck-card-body">
                    <!-- Hiển thị -->
                    <div id="addr-display">
                        <p>
                            <strong><?= htmlspecialchars($user['fullname'] ?? 'Khách') ?></strong>
                            &nbsp;|&nbsp;
                            <span id="disp-phone"><?= htmlspecialchars($user['phone'] ?? 'Chưa có SĐT') ?></span>
                        </p>
                        <p style="color:#666;font-size:13px;margin-top:4px;" id="disp-addr">
                            <?= htmlspecialchars(trim(($user['address'] ?? '') . (($user['city'] ?? '') ? ', ' . $user['city'] : ''))) ?: 'Chưa có địa chỉ' ?>
                        </p>
                        <button type="button" class="addr-change-btn" onclick="toggleAddr()">
                            ✏️ Thay đổi
                        </button>
                    </div>
                    <!-- Chỉnh sửa -->
                    <div id="addr-edit" style="display:none;margin-top:12px;">
                        <label class="ck-label">Số điện thoại</label>
                        <input type="tel" id="edit-phone" class="ck-input"
                               value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                               placeholder="0xxx xxx xxx">
                        <label class="ck-label">Địa chỉ nhận hàng</label>
                        <input type="text" id="edit-addr" class="ck-input"
                               value="<?= htmlspecialchars($user['address'] ?? '') ?>"
                               placeholder="Số nhà, đường, phường, quận, thành phố">
                        <div style="display:flex;gap:8px;margin-top:4px;">
                            <button type="button" onclick="saveAddr()"
                                style="background:#001C30;color:#DAA520;border:none;border-radius:8px;padding:8px 18px;font-weight:700;font-size:13px;cursor:pointer;">
                                Lưu
                            </button>
                            <button type="button" onclick="toggleAddr()"
                                style="background:#f5f5f5;color:#444;border:none;border-radius:8px;padding:8px 14px;font-size:13px;cursor:pointer;">
                                Hủy
                            </button>
                        </div>
                    </div>
                    <!-- Hidden inputs gửi về server -->
                    <input type="hidden" name="phone"   id="hid-phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                    <input type="hidden" name="address" id="hid-addr"  value="<?= htmlspecialchars($user['address'] ?? '') ?>">
                </div>
            </div>

            <!-- 2. Sản phẩm -->
            <div class="ck-card">
                <div class="ck-card-header">
                    📦 Sản phẩm (<?= count($cart_items) ?>)
                </div>
                <div class="ck-card-body" style="padding-top:4px;padding-bottom:4px;">
                    <?php foreach ($cart_items as $item):
                        $imgSrc = img_url($item['image']);
                    ?>
                    <div class="ck-item">
                        <a href="details.php?id=<?= $item['item_id'] ?>">
                            <img src="<?= htmlspecialchars($imgSrc) ?>"
                                 alt="<?= htmlspecialchars($item['name']) ?>"
                                 onerror="this.src='assets/products/no-image.png'">
                        </a>
                        <div style="flex:1;min-width:0;">
                            <a href="details.php?id=<?= $item['item_id'] ?>" style="text-decoration:none; color:inherit;">
                                <div class="ck-item-name"><?= htmlspecialchars($item['name']) ?></div>
                            </a>
                            <div class="ck-item-qty">Số lượng: <?= $item['qty'] ?></div>
                        </div>
                        <div class="ck-item-price">
                            <?= number_format($item['price'] * $item['qty'], 0, ',', '.') ?>đ
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- 3. Vận chuyển -->
            <div class="ck-card">
                <div class="ck-card-header">🚚 Phương thức vận chuyển</div>
                <div class="ck-card-body">
                    <label class="ship-option checked" id="lbl-standard" onclick="selectShip('standard')">
                        <input type="radio" name="shipping" value="standard" checked onchange="updateTotal()">
                        <span class="option-icon">📮</span>
                        <span class="option-text">
                            <b>Giao hàng tiêu chuẩn</b>
                            <small>Nhận hàng trong 3–5 ngày</small>
                        </span>
                        <span class="option-price">20.000đ</span>
                    </label>
                    <label class="ship-option" id="lbl-fast" onclick="selectShip('fast')">
                        <input type="radio" name="shipping" value="fast" onchange="updateTotal()">
                        <span class="option-icon">⚡</span>
                        <span class="option-text">
                            <b>Giao hàng nhanh</b>
                            <small>Nhận hàng trong 1–2 ngày</small>
                        </span>
                        <span class="option-price">40.000đ</span>
                    </label>
                </div>
            </div>

            <!-- 4. Thanh toán -->
            <div class="ck-card">
                <div class="ck-card-header">💳 Phương thức thanh toán</div>
                <div class="ck-card-body">
                    <?php $methods = [
                        'cod'  => ['💵', 'Thanh toán khi nhận hàng (COD)', 'Trả tiền mặt khi nhận'],
                        'momo' => ['💜', 'Ví MoMo',   'Quét QR MoMo thanh toán'],
                        'bank' => ['🏦', 'Chuyển khoản ngân hàng', 'Chuyển khoản qua STK'],
                    ];
                    $first = true;
                    foreach ($methods as $val => [$icon, $name, $note]):
                    ?>
                    <label class="pay-option <?= $first ? 'checked' : '' ?>"
                           id="lbl-pay-<?= $val ?>" onclick="selectPay('<?= $val ?>')">
                        <input type="radio" name="payment" value="<?= $val ?>" <?= $first ? 'checked' : '' ?>>
                        <span class="option-icon"><?= $icon ?></span>
                        <span class="option-text">
                            <b><?= $name ?></b>
                            <small><?= $note ?></small>
                        </span>
                    </label>
                    <?php $first = false; endforeach; ?>
                </div>
            </div>

        </div><!-- /col-left -->

        <!-- ══ CỘT PHẢI — Tóm tắt đơn ══ -->
        <div>
            <div class="summary-box">
                <div style="font-size:15px;font-weight:800;margin-bottom:16px;">📋 Tóm tắt đơn hàng</div>


                <div class="summary-row">
                    <span style="opacity:.8;">Mã giảm giá</span>
                </div>
                <div style="margin-bottom:14px; display:flex; gap:8px;">
                    <input type="text" id="coupon-code" name="coupon_code" class="ck-input" style="margin:0; background:rgba(255,255,255,.1); border-color:rgba(255,255,255,.2); color:#fff;" placeholder="Nhập mã...">
                    <button type="button" onclick="applyCoupon()" style="background:#fff; color:#001C30; border:none; border-radius:8px; padding:0 14px; font-weight:700; cursor:pointer; font-size:13px; white-space:nowrap;">Áp dụng</button>
                </div>
                <div id="coupon-msg" style="font-size:12px; margin-bottom:10px; display:none;"></div>
                
                <?php if (!empty($available_coupons)): ?>
                <div style="margin-bottom:14px; background: rgba(0,0,0,0.2); padding: 12px; border-radius: 8px;">
                    <div style="font-size:12px; font-weight:700; margin-bottom:8px; color:#DAA520;">🎁 Ưu đãi dành cho bạn:</div>
                    <div style="display:flex; flex-direction:column; gap:8px;">
                        <?php foreach ($available_coupons as $cp): 
                            $min_order = $cp['min_order_value'];
                            $desc = ($cp['discount_type'] == 'percent') ? "Giảm {$cp['discount_value']}%" : "Giảm " . number_format($cp['discount_value'], 0, ',', '.') . "đ";
                            if ($min_order > 0) {
                                $desc .= " cho ĐH từ " . number_format($min_order/1000, 0, ',', '.') . "k";
                            }
                        ?>
                        <div class="coupon-tag" onclick="useCoupon('<?= $cp['code'] ?>')">
                            <div>
                                <i class="fas fa-ticket-alt"></i> <?= htmlspecialchars($cp['code']) ?>
                                <span class="coupon-desc"><?= htmlspecialchars($desc) ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <input type="hidden" name="discount_amount" id="hid-discount" value="0">
                <hr style="border-color:rgba(255,255,255,.2); margin:10px 0;">

                <div class="summary-row">
                    <span style="opacity:.8;">Tiền hàng</span>
                    <span id="s-subtotal"><?= number_format($subtotal, 0, ',', '.') ?>đ</span>
                </div>
                <div class="summary-row">
                    <span style="opacity:.8;">Phí vận chuyển</span>
                    <span id="s-ship">20.000đ</span>
                </div>
                <div class="summary-row" id="row-discount" style="display:none; color:#4ade80;">
                    <span style="opacity:.8;">Giảm giá</span>
                    <span id="s-discount">-0đ</span>
                </div>
                <div class="summary-row total">
                    <span>Tổng cộng</span>
                    <span id="s-total"><?= number_format($subtotal + 20000, 0, ',', '.') ?>đ</span>
                </div>

                <button type="submit" name="place-order" class="order-btn" id="orderBtn">
                    🎉 XÁC NHẬN ĐẶT HÀNG
                </button>

                <a href="cart.php" style="display:block;text-align:center;color:rgba(255,255,255,.6);font-size:12px;margin-top:12px;text-decoration:none;">
                    ← Quay lại giỏ hàng
                </a>
            </div>
        </div>

    </div><!-- /checkout-cols -->
    </form>
    <?php endif; ?>
</div>

<script>
const subtotal = <?= $subtotal ?>;

// JS: Xử lý coupon
let currentDiscount = 0;

function applyCoupon() {
    const code = document.getElementById('coupon-code').value.trim();
    const msg = document.getElementById('coupon-msg');
    if (!code) return;

    fetch('api/apply_coupon.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `code=${encodeURIComponent(code)}&cart_total=${subtotal}`
    })
    .then(r => r.json())
    .then(data => {
        msg.style.display = 'block';
        if (data.status === 'success') {
            msg.innerHTML = `<span style="color:#4ade80;"><i class="fas fa-check-circle"></i> ${data.message}</span>`;
            currentDiscount = data.discount_amount;
            document.getElementById('hid-discount').value = currentDiscount;
            document.getElementById('row-discount').style.display = 'flex';
            document.getElementById('s-discount').innerText = '-' + currentDiscount.toLocaleString('vi-VN') + 'đ';
            document.getElementById('coupon-code').readOnly = true;
        } else {
            msg.innerHTML = `<span style="color:#f87171;"><i class="fas fa-exclamation-circle"></i> ${data.message}</span>`;
            currentDiscount = 0;
            document.getElementById('hid-discount').value = 0;
            document.getElementById('row-discount').style.display = 'none';
        }
        updateTotal();
    })
    .catch(err => console.error(err));
}

function useCoupon(code) {
    document.getElementById('coupon-code').value = code;
    applyCoupon();
}

// Cập nhật tổng
function updateTotal() {
    let shipOpt = document.querySelector('input[name="shipping"]:checked').value;
    let shipFee = (shipOpt === 'fast') ? 40000 : 20000;
    
    document.getElementById('s-ship').innerText = shipFee.toLocaleString('vi-VN') + 'đ';
    let total = subtotal + shipFee - currentDiscount;
    if (total < 0) total = 0;
    document.getElementById('s-total').innerText = total.toLocaleString('vi-VN') + 'đ';
}

function selectShip(val) {
    document.querySelectorAll('.ship-option').forEach(el => el.classList.remove('checked'));
    document.getElementById('lbl-' + val)?.classList.add('checked');
    updateTotal();
}

function selectPay(val) {
    document.querySelectorAll('.pay-option').forEach(el => el.classList.remove('checked'));
    document.getElementById('lbl-pay-' + val)?.classList.add('checked');
}

function toggleAddr() {
    const d = document.getElementById('addr-display');
    const e = document.getElementById('addr-edit');
    d.style.display = d.style.display === 'none' ? 'block' : 'none';
    e.style.display = e.style.display === 'none' ? 'block' : 'none';
}

function saveAddr() {
    const phone = document.getElementById('edit-phone').value.trim();
    const addr  = document.getElementById('edit-addr').value.trim();
    document.getElementById('disp-phone').textContent  = phone || 'Chưa có SĐT';
    document.getElementById('disp-addr').textContent   = addr  || 'Chưa có địa chỉ';
    document.getElementById('hid-phone').value = phone;
    document.getElementById('hid-addr').value  = addr;
    toggleAddr();
}

// Confirm trước khi đặt nếu chưa có địa chỉ
document.getElementById('checkoutForm')?.addEventListener('submit', function(e) {
    const addr = document.getElementById('hid-addr').value.trim();
    if (!addr) {
        e.preventDefault();
        alert('⚠️ Vui lòng nhập địa chỉ nhận hàng!');
        toggleAddr();
    }
});
</script>

<?php include('func/footer.php'); ob_end_flush(); ?>
