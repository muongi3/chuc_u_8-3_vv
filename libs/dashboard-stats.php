<?php
// =============================================================
// TRANG THỐNG KÊ - CLK APPLE STORE
// Chỉ dành cho Admin
// =============================================================

// Fallback img_url() nếu chưa được định nghĩa (v.d. khi gọi từ admin)
if (!function_exists('img_url')) {
    function img_url($filename) {
        if (empty($filename)) return 'assets/products/no-image.png';
        // Nếu đã là đường dẫn đầy đủ (có /)
        if (strpos($filename, '/') !== false) {
            return ltrim(str_replace('./', '', $filename), '/');
        }
        // Tên file thuần → thêm prefix
        return 'assets/products/' . $filename;
    }
}

$conn = $db->con;

// ─── 1. KPI CARDS ─────────────────────────────────────────────
$total_revenue = 0;
$r = mysqli_query($conn, "SELECT SUM(total_amount) as total FROM orders WHERE status='delivered'");
if($r) $total_revenue = mysqli_fetch_assoc($r)['total'] ?? 0;

$total_orders = 0;
$r = mysqli_query($conn, "SELECT COUNT(*) as total FROM orders");
if($r) $total_orders = mysqli_fetch_assoc($r)['total'] ?? 0;

$total_products = 0;
$r = mysqli_query($conn, "SELECT COUNT(*) as total FROM product");
if($r) $total_products = mysqli_fetch_assoc($r)['total'] ?? 0;

$total_members = 0;
$r = mysqli_query($conn, "SELECT COUNT(*) as total FROM account");
if($r) $total_members = mysqli_fetch_assoc($r)['total'] ?? 0;

$pending_orders = 0;
$r = mysqli_query($conn, "SELECT COUNT(*) as total FROM orders WHERE status='pending'");
if($r) $pending_orders = mysqli_fetch_assoc($r)['total'] ?? 0;

// ─── 2. DOANH THU 7 NGÀY GẦN NHẤT (Bar Chart) ────────────────
$revenue_labels = [];
$revenue_data   = [];
$r7 = mysqli_query($conn,
    "SELECT DATE(order_date) as day, SUM(total_amount) as revenue
     FROM orders
     WHERE order_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
     GROUP BY DATE(order_date)
     ORDER BY day ASC"
);
// Tạo đủ 7 ngày kể cả ngày không có doanh thu
$day_map = [];
if($r7){ while($row = mysqli_fetch_assoc($r7)){ $day_map[$row['day']] = $row['revenue']; } }
for($i = 6; $i >= 0; $i--){
    $day = date('Y-m-d', strtotime("-$i days"));
    $revenue_labels[] = date('d/m', strtotime($day));
    $revenue_data[]   = isset($day_map[$day]) ? (float)$day_map[$day] : 0;
}

// ─── 3. ĐƠN HÀNG THEO TRẠNG THÁI (Donut Chart) ───────────────
$status_labels = ['pending','packing','shipping','delivered'];
$status_vi     = ['Chờ xử lý','Đóng gói','Đang giao','Đã giao'];
$status_data   = [];
$status_map    = [];
$rs = mysqli_query($conn, "SELECT status, COUNT(*) as cnt FROM orders GROUP BY status");
if($rs){ while($row = mysqli_fetch_assoc($rs)){ $status_map[$row['status']] = (int)$row['cnt']; } }
foreach($status_labels as $s){ $status_data[] = $status_map[$s] ?? 0; }

// ─── 4. TOP 5 SẢN PHẨM BÁN CHẠY ──────────────────────────────
$top_products = [];
$rt = mysqli_query($conn,
    "SELECT p.id, p.name, p.image, p.price,
            SUM(od.quantity) as total_qty,
            SUM(od.quantity * od.price) as total_revenue
     FROM order_detail od
     JOIN product p ON od.product_id = p.id
     GROUP BY od.product_id
     ORDER BY total_qty DESC
     LIMIT 5"
);
if($rt){ while($row = mysqli_fetch_assoc($rt)){ $top_products[] = $row; } }

// ─── 5. DOANH THU THEO THƯƠNG HIỆU (Horizontal Bar) ──────────
$brand_labels = [];
$brand_data   = [];
$rb = mysqli_query($conn,
    "SELECT m.brand, SUM(od.quantity * od.price) as revenue
     FROM order_detail od
     JOIN product p ON od.product_id = p.id
     JOIN manufacturer m ON p.brand = m.id
     GROUP BY m.id
     ORDER BY revenue DESC"
);
if($rb){ while($row = mysqli_fetch_assoc($rb)){ $brand_labels[] = $row['brand']; $brand_data[] = (float)$row['revenue']; } }

// ─── 6. ĐƠN HÀNG MỚI NHẤT ────────────────────────────────────
$recent_orders = [];
$rr = mysqli_query($conn,
    "SELECT o.id, o.order_date, o.status, o.total_amount, u.fullname
     FROM orders o
     JOIN user u ON o.user_id = u.id
     ORDER BY o.id DESC
     LIMIT 7"
);
if($rr){ while($row = mysqli_fetch_assoc($rr)){ $recent_orders[] = $row; } }
?>

<!-- ════════════════════════════════════════════════════════════
     CSS RIÊNG CHO TRANG THỐNG KÊ
════════════════════════════════════════════════════════════ -->
<style>
.stats-section { padding: 30px 0 60px; background: #f4f7f9; min-height: 80vh; }
.stats-section .page-title {
    font-size: 1.7rem; font-weight: 700; color: #001C30;
    border-left: 5px solid #DAA520; padding-left: 14px; margin-bottom: 28px;
}

/* KPI Cards */
.kpi-card {
    background: #fff; border-radius: 16px;
    padding: 24px 22px; display: flex; align-items: center; gap: 18px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.07);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border: 1px solid #f0f0f0;
}
.kpi-card:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(0,0,0,0.12); }
.kpi-icon {
    width: 60px; height: 60px; border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.6rem; flex-shrink: 0;
}
.kpi-icon.green  { background: #e8f5e9; color: #2e7d32; }
.kpi-icon.blue   { background: #e3f2fd; color: #1565c0; }
.kpi-icon.orange { background: #fff3e0; color: #e65100; }
.kpi-icon.purple { background: #f3e5f5; color: #6a1b9a; }
.kpi-icon.red    { background: #fce4ec; color: #c62828; }
.kpi-value { font-size: 1.7rem; font-weight: 800; color: #001C30; line-height: 1; }
.kpi-label { font-size: 0.82rem; color: #86868b; margin-top: 4px; text-transform: uppercase; letter-spacing: 0.5px; }
.kpi-badge { font-size: 0.75rem; margin-top: 6px; }

/* Chart Cards */
.chart-card {
    background: #fff; border-radius: 16px; padding: 24px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.07); border: 1px solid #f0f0f0; height: 100%;
}
.chart-card .chart-title { font-weight: 700; font-size: 1rem; color: #001C30; margin-bottom: 18px; }

/* Table */
.stats-table { font-size: 0.88rem; }
.stats-table thead th { background: #001C30; color: #DAA520; font-weight: 600; border: none; padding: 14px 12px; }
.stats-table tbody tr:hover { background-color: #f8f8fc; }
.stats-table tbody td { vertical-align: middle; padding: 12px; border-color: #f0f0f0; }

/* Badge trạng thái */
.badge-status { padding: 5px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
.badge-pending  { background:#fff3e0; color:#e65100; }
.badge-packing  { background:#e3f2fd; color:#1565c0; }
.badge-shipping { background:#e8f0fe; color:#3949ab; }
.badge-delivered{ background:#e8f5e9; color:#2e7d32; }

/* Top product row */
.top-product-img { width: 48px; height: 48px; object-fit: contain; border-radius: 8px; background: #f8f8f8; padding: 2px; }
.rank-badge { width: 26px; height: 26px; border-radius: 50%; display:inline-flex; align-items:center; justify-content:center; font-size:0.75rem; font-weight:700; }
</style>

<!-- Script Chart.js (local — không cần mạng) -->
<script src="../assets/chart.umd.min.js"></script>

<section class="stats-section">
    <div class="container">
        <h2 class="page-title"><i class="fas fa-chart-line me-2"></i>Bảng Thống Kê Tổng Quan</h2>

        <!-- ── KPI CARDS ────────────────────────────────────────── -->
        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="kpi-card">
                    <div class="kpi-icon green"><i class="fas fa-dollar-sign"></i></div>
                    <div>
                        <div class="kpi-value"><?php echo number_format($total_revenue, 0, ',', '.'); ?>đ</div>
                        <div class="kpi-label">Doanh thu (Đã giao)</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="kpi-card">
                    <div class="kpi-icon blue"><i class="fas fa-shopping-bag"></i></div>
                    <div>
                        <div class="kpi-value"><?php echo $total_orders; ?></div>
                        <div class="kpi-label">Tổng đơn hàng</div>
                        <?php if($pending_orders > 0): ?>
                            <span class="badge-status badge-pending kpi-badge"><?php echo $pending_orders; ?> chờ xử lý</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="kpi-card">
                    <div class="kpi-icon orange"><i class="fas fa-mobile-alt"></i></div>
                    <div>
                        <div class="kpi-value"><?php echo $total_products; ?></div>
                        <div class="kpi-label">Tổng sản phẩm</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="kpi-card">
                    <div class="kpi-icon purple"><i class="fas fa-users"></i></div>
                    <div>
                        <div class="kpi-value"><?php echo $total_members; ?></div>
                        <div class="kpi-label">Thành viên</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── 2 CHARTS ─────────────────────────────────────────── -->
        <div class="row g-3 mb-4">
            <!-- Bar: Doanh thu 7 ngày -->
            <div class="col-lg-8">
                <div class="chart-card">
                    <div class="chart-title"><i class="fas fa-chart-bar me-2 text-primary"></i>Doanh thu 7 ngày gần nhất</div>
                    <canvas id="revenueChart" height="100"></canvas>
                </div>
            </div>
            <!-- Donut: Trạng thái đơn -->
            <div class="col-lg-4">
                <div class="chart-card">
                    <div class="chart-title"><i class="fas fa-chart-pie me-2 text-warning"></i>Trạng thái đơn hàng</div>
                    <canvas id="statusChart" height="200"></canvas>
                    <div class="mt-3">
                        <?php
                        $status_colors_vi = [
                            'pending'   => ['Chờ xử lý', '#ff9800'],
                            'packing'   => ['Đóng gói',  '#2196f3'],
                            'shipping'  => ['Đang giao', '#673ab7'],
                            'delivered' => ['Đã giao',   '#4caf50'],
                        ];
                        foreach($status_labels as $i => $s):
                            $count = $status_data[$i];
                            $info  = $status_colors_vi[$s];
                        ?>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span style="font-size:0.8rem;"><span style="display:inline-block;width:12px;height:12px;border-radius:3px;background:<?php echo $info[1]; ?>;margin-right:6px;"></span><?php echo $info[0]; ?></span>
                            <strong style="font-size:0.85rem;"><?php echo $count; ?> đơn</strong>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── TOP PRODUCTS + BRAND CHART ──────────────────────── -->
        <div class="row g-3 mb-4">
            <!-- Top 5 sản phẩm bán chạy -->
            <div class="col-lg-7">
                <div class="chart-card">
                    <div class="chart-title"><i class="fas fa-fire me-2 text-danger"></i>Top 5 Sản Phẩm Bán Chạy</div>
                    <?php if(!empty($top_products)): ?>
                    <div class="table-responsive">
                        <table class="table stats-table mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th colspan="2">Sản phẩm</th>
                                    <th class="text-center">Đã bán</th>
                                    <th class="text-end">Doanh thu</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($top_products as $i => $p): 
                                    $rank_colors = ['#DAA520','#9e9e9e','#cd7f32','#4caf50','#2196f3'];
                                ?>
                                <tr>
                                    <td>
                                        <span class="rank-badge" style="background:<?php echo $rank_colors[$i] ?? '#ccc'; ?>;color:#fff;">
                                            <?php echo $i+1; ?>
                                        </span>
                                    </td>
                                    <td style="width:56px;">
                                        <?php
                                        $img_path = img_url($p['image']);
                                        // Từ admin, đường dẫn cần ra ngoài thư mục admin/
                                        if (strpos($img_path, 'assets/') === 0) {
                                            $img_path = '../' . $img_path;
                                        }
                                        ?>
                                        <img src="<?php echo htmlspecialchars($img_path); ?>" class="top-product-img" alt=""
                                             onerror="this.src='../assets/products/no-image.png'">
                                    </td>
                                    <td>
                                        <a href="../details.php?id=<?php echo $p['id']; ?>" target="_blank" class="text-decoration-none fw-semibold text-dark" style="font-size:0.85rem;">
                                            <?php echo htmlspecialchars($p['name']); ?>
                                        </a>
                                        <div class="text-muted" style="font-size:0.78rem;"><?php echo number_format($p['price'],0,',','.'); ?>đ / cái</div>
                                    </td>
                                    <td class="text-center fw-bold text-danger"><?php echo $p['total_qty']; ?></td>
                                    <td class="text-end fw-bold" style="color:#001C30;"><?php echo number_format($p['total_revenue'],0,',','.'); ?>đ</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                        <p class="text-muted text-center py-4">Chưa có dữ liệu bán hàng.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Doanh thu theo thương hiệu -->
            <div class="col-lg-5">
                <div class="chart-card">
                    <div class="chart-title"><i class="fas fa-tags me-2 text-success"></i>Doanh thu theo thương hiệu</div>
                    <canvas id="brandChart" height="220"></canvas>
                </div>
            </div>
        </div>

        <!-- ── ĐƠN HÀNG MỚI NHẤT ────────────────────────────────── -->
        <div class="chart-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="chart-title mb-0"><i class="fas fa-list-alt me-2 text-info"></i>Đơn hàng mới nhất</div>
                <a href="../admin/orders.php" class="btn btn-sm btn-outline-dark rounded-pill px-3">Xem tất cả</a>
            </div>
            <div class="table-responsive">
                <table class="table stats-table mb-0">
                    <thead>
                        <tr>
                            <th>Mã ĐH</th>
                            <th>Khách hàng</th>
                            <th>Ngày đặt</th>
                            <th class="text-end">Tổng tiền</th>
                            <th class="text-center">Trạng thái</th>
                            <th class="text-center">Chi tiết</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($recent_orders)): foreach($recent_orders as $o): ?>
                        <tr>
                            <td class="fw-bold">#<?php echo str_pad($o['id'], 4, '0', STR_PAD_LEFT); ?></td>
                            <td><?php echo htmlspecialchars($o['fullname']); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($o['order_date'])); ?></td>
                            <td class="text-end fw-bold text-danger"><?php echo number_format($o['total_amount'],0,',','.'); ?>đ</td>
                            <td class="text-center">
                                <?php
                                $badge_map = [
                                    'pending'   => 'badge-pending',
                                    'packing'   => 'badge-packing',
                                    'shipping'  => 'badge-shipping',
                                    'delivered' => 'badge-delivered',
                                ];
                                $label_map = [
                                    'pending'   => 'Chờ xử lý',
                                    'packing'   => 'Đóng gói',
                                    'shipping'  => 'Đang giao',
                                    'delivered' => 'Đã giao',
                                ];
                                $cls = $badge_map[$o['status']] ?? 'badge-pending';
                                $lbl = $label_map[$o['status']] ?? $o['status'];
                                ?>
                                <span class="badge-status <?php echo $cls; ?>"><?php echo $lbl; ?></span>
                            </td>
                            <td class="text-center">
                                <a href="order_detail.php?id=<?php echo $o['id']; ?>" class="btn btn-sm btn-outline-primary rounded-pill" style="font-size:0.75rem;">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted">Chưa có đơn hàng nào.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div><!-- /container -->
</section>

<!-- ════════════════════════════════════════════════════════════
     KHỞI TẠO CHARTS
════════════════════════════════════════════════════════════ -->
<script>
document.addEventListener('DOMContentLoaded', function() {

    // ── 1. BAR CHART: Doanh thu 7 ngày ──────────────────────────
    const ctxRevenue = document.getElementById('revenueChart').getContext('2d');
    new Chart(ctxRevenue, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($revenue_labels); ?>,
            datasets: [{
                label: 'Doanh thu (đ)',
                data: <?php echo json_encode($revenue_data); ?>,
                backgroundColor: 'rgba(0, 113, 227, 0.15)',
                borderColor: '#0071e3',
                borderWidth: 2,
                borderRadius: 8,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ' ' + ctx.parsed.y.toLocaleString('vi-VN') + 'đ'
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#f0f0f0' },
                    ticks: {
                        callback: val => val.toLocaleString('vi-VN') + 'đ',
                        font: { size: 11 }
                    }
                },
                x: { grid: { display: false }, ticks: { font: { size: 12 } } }
            }
        }
    });

    // ── 2. DONUT CHART: Trạng thái đơn ──────────────────────────
    const ctxStatus = document.getElementById('statusChart').getContext('2d');
    new Chart(ctxStatus, {
        type: 'doughnut',
        data: {
            labels: ['Chờ xử lý', 'Đóng gói', 'Đang giao', 'Đã giao'],
            datasets: [{
                data: <?php echo json_encode($status_data); ?>,
                backgroundColor: ['#ff9800', '#2196f3', '#673ab7', '#4caf50'],
                borderWidth: 3,
                borderColor: '#ffffff',
            }]
        },
        options: {
            responsive: true,
            cutout: '65%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ' ' + ctx.label + ': ' + ctx.parsed + ' đơn'
                    }
                }
            }
        }
    });

    // ── 3. HORIZONTAL BAR: Doanh thu theo brand ─────────────────
    const ctxBrand = document.getElementById('brandChart').getContext('2d');
    new Chart(ctxBrand, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($brand_labels); ?>,
            datasets: [{
                label: 'Doanh thu',
                data: <?php echo json_encode($brand_data); ?>,
                backgroundColor: ['#DAA520','#0071e3','#e53935','#43a047','#8e24aa'],
                borderRadius: 6,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ' ' + ctx.parsed.x.toLocaleString('vi-VN') + 'đ'
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    grid: { color: '#f5f5f5' },
                    ticks: {
                        callback: val => (val/1000).toFixed(0) + 'K',
                        font: { size: 11 }
                    }
                },
                y: { grid: { display: false } }
            }
        }
    });

});
</script>

