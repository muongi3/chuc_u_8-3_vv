<?php
// CLK SHOP - PRODUCT COMPARE MATRIX PAGE
ob_start();
include('func/header.php');
?>
<style>
/* COMPARE PAGE */
.compare-page { max-width: 1100px; margin: 0 auto; padding: 28px 16px 80px; }
.compare-page h1 { font-size: 22px; font-weight: 800; color: #111; margin-bottom: 6px; }
.compare-page .sub { color: #888; font-size: 13px; margin-bottom: 28px; }

/* Bảng */
.compare-table { width: 100%; border-collapse: separate; border-spacing: 12px 0; }
.compare-table th,
.compare-table td { width: 33.33%; vertical-align: top; }

/* Card sản phẩm (header của mỗi cột) */
.cmp-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.07);
    overflow: hidden;
    position: relative;
}
.cmp-card-img-wrap {
    background: #f8f9fa;
    display: flex; align-items: center; justify-content: center;
    height: 180px; padding: 16px;
}
.cmp-card-img { max-height: 150px; max-width: 100%; object-fit: contain; }
.cmp-card-body { padding: 14px 16px 16px; }
.cmp-card-name { font-size: 14px; font-weight: 700; color: #111; margin-bottom: 4px; line-height: 1.4; }
.cmp-card-brand { font-size: 11px; color: #888; margin-bottom: 10px; }
.cmp-card-price { font-size: 18px; font-weight: 800; color: #ef4444; margin-bottom: 12px; }
.cmp-remove-btn {
    position: absolute; top: 8px; right: 8px;
    background: rgba(0,0,0,0.5); color: #fff; border: none;
    width: 26px; height: 26px; border-radius: 50%;
    font-size: 11px; cursor: pointer; display: flex; align-items: center; justify-content: center;
    transition: background 0.2s;
}
.cmp-remove-btn:hover { background: #ef4444; }
.btn-cmp-cart {
    display: block; width: 100%; text-align: center;
    background: #001C30; color: #DAA520;
    border: none; border-radius: 8px; padding: 9px;
    font-size: 12px; font-weight: 700; cursor: pointer;
    transition: background 0.2s; text-decoration: none;
}
.btn-cmp-cart:hover { background: #003153; color: #ffd700; }

/* Slot trống */
.cmp-slot-empty {
    background: #f8f9fa; border-radius: 16px;
    border: 2px dashed #ddd;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    min-height: 280px; color: #aaa; gap: 10px;
}
.cmp-slot-empty i { font-size: 36px; }
.cmp-slot-empty p { font-size: 13px; margin: 0; }
.cmp-slot-empty a { font-size: 12px; color: #001C30; font-weight: 600; text-decoration: none; }
.cmp-slot-empty a:hover { text-decoration: underline; }

/* Hàng thông số */
.compare-section { margin-top: 20px; }
.compare-section-title {
    font-size: 12px; font-weight: 800; color: #888;
    text-transform: uppercase; letter-spacing: 1px;
    padding: 10px 0 6px; border-bottom: 2px solid #f0f0f0;
    margin-bottom: 4px;
}
.cmp-row td {
    padding: 12px 16px;
    font-size: 13px;
    border-bottom: 1px solid #f5f5f5;
    background: #fff;
}
.cmp-row:first-child td:first-child { border-radius: 12px 0 0 0; }
.cmp-row:last-child  td:first-child { border-radius: 0 0 0 12px; }
.cmp-row:first-child td:last-child  { border-radius: 0 12px 0 0; }
.cmp-row:last-child  td:last-child  { border-radius: 0 0 12px 0; }
.cmp-row td:first-child {
    background: #f8f9fa; font-weight: 700; color: #555;
    font-size: 12px; width: 120px; text-align: right; padding-right: 16px;
}
.cmp-val-best { color: #10b981; font-weight: 800; }
.star-row { color: #f59e0b; font-size: 12px; }

/* Empty state */
.compare-empty { text-align: center; padding: 80px 20px; }
.compare-empty i { font-size: 60px; color: #ddd; display: block; margin-bottom: 16px; }
.compare-empty h3 { font-size: 18px; color: #555; }
.compare-empty p { color: #aaa; font-size: 13px; }

@media (max-width: 767px) {
    .compare-table, .compare-table tbody, .compare-table tr, .compare-table td, .compare-table th {
        display: block; width: 100% !important;
    }
    .compare-table tr { margin-bottom: 12px; }
    .cmp-card-img-wrap { height: 140px; }
}
</style>

<div class="compare-page">
    <h1><i class="fas fa-balance-scale me-2 text-primary"></i>So sánh sản phẩm</h1>
    <p class="sub">Chọn tối đa 3 sản phẩm để so sánh thông số bên nhau</p>

    <div id="compare-content">
        <!-- Sẽ được render bởi JS -->
        <div class="compare-empty">
            <i class="fas fa-balance-scale"></i>
            <h3>Chưa có sản phẩm nào để so sánh</h3>
            <p>Hãy bấm nút <strong>⚖️ So sánh</strong> trên trang sản phẩm để thêm vào đây.</p>
            <a href="products.php" class="btn btn-primary mt-3 px-4">
                <i class="fas fa-shopping-bag me-2"></i>Xem sản phẩm
            </a>
        </div>
    </div>
</div>

<script>
(function() {
    const BASE = window.location.pathname.includes('/clkshop/') ? '/Trien_Khai/clkshop1/clkshop/' : './';

    function formatPrice(p) {
        return Number(p).toLocaleString('vi-VN') + 'đ';
    }
    function stars(avg) {
        let s = '';
        for (let i = 1; i <= 5; i++) {
            s += `<i class="${i <= Math.round(avg) ? 'fas' : 'far'} fa-star"></i>`;
        }
        return s;
    }

    function renderCompare(items) {
        const MAX = 3;
        const slots = [...items];
        while (slots.length < MAX) slots.push(null);

        // Build header (cards)
        let headerCols = '';
        slots.forEach((item, idx) => {
            if (!item) {
                headerCols += `
                <th>
                    <div class="cmp-slot-empty">
                        <i class="fas fa-plus-circle"></i>
                        <p>Thêm sản phẩm</p>
                        <a href="products.php">Xem danh mục →</a>
                    </div>
                </th>`;
            } else {
                headerCols += `
                <th>
                    <div class="cmp-card">
                        <button class="cmp-remove-btn" onclick="removeFromCompare(${item.id})" title="Xóa khỏi so sánh">
                            <i class="fas fa-times"></i>
                        </button>
                        <div class="cmp-card-img-wrap">
                            <img src="${item.image}" alt="${item.name}" class="cmp-card-img"
                                 onerror="this.src='assets/products/no-image.png'">
                        </div>
                        <div class="cmp-card-body">
                            <div class="cmp-card-name">${item.name}</div>
                            <div class="cmp-card-brand">${item.brand_name}</div>
                            <div class="cmp-card-price">${formatPrice(item.price)}</div>
                            <a href="details.php?id=${item.id}" class="btn-cmp-cart">
                                <i class="fas fa-eye me-1"></i> Xem chi tiết
                            </a>
                        </div>
                    </div>
                </th>`;
            }
        });

        // Tìm min giá + max tồn kho + max rating để highlight
        const prices  = items.map(i => i.price);
        const stocks  = items.map(i => i.stock);
        const ratings = items.map(i => i.avg_rating);
        const minPrice  = Math.min(...prices);
        const maxStock  = Math.max(...stocks);
        const maxRating = Math.max(...ratings);

        function tdVal(item, val, best) {
            const cls = val === best ? 'cmp-val-best' : '';
            return item ? `<td><span class="${cls}">${val}</span></td>` : '<td style="color:#ddd;">—</td>';
        }

        // Base Rows
        const specs = [
            {
                label: 'Giá',
                render: item => item
                    ? `<td><span class="${item.price === minPrice ? 'cmp-val-best' : ''}">${formatPrice(item.price)}</span></td>`
                    : '<td style="color:#ddd;">—</td>'
            },
            {
                label: 'Thương hiệu',
                render: item => item ? `<td>${item.brand_name}</td>` : '<td style="color:#ddd;">—</td>'
            },
            {
                label: 'Đánh giá',
                render: item => item
                    ? `<td class="star-row"><span class="${item.avg_rating === maxRating && item.avg_rating > 0 ? 'cmp-val-best' : ''}">${stars(item.avg_rating)} ${item.avg_rating > 0 ? item.avg_rating.toFixed(1) : 'Chưa có'} (${item.review_count})</span></td>`
                    : '<td style="color:#ddd;">—</td>'
            }
        ];

        // Lấy tất cả các keys đặc tả kỹ thuật từ các sản phẩm
        let allSpecKeys = [];
        items.forEach(item => {
            if (item && item.specs) {
                Object.keys(item.specs).forEach(key => {
                    if (!allSpecKeys.includes(key)) allSpecKeys.push(key);
                });
            }
        });

        // Thêm các hàng thông số kỹ thuật động
        allSpecKeys.forEach(key => {
            specs.push({
                label: key,
                render: item => item && item.specs && item.specs[key] ? `<td>${item.specs[key]}</td>` : '<td style="color:#ddd;">—</td>'
            });
        });

        let rowsHtml = '';
        specs.forEach(spec => {
            const tds = slots.map(item => spec.render(item)).join('');
            rowsHtml += `
            <tr class="cmp-row">
                <td>${spec.label}</td>
                ${tds}
            </tr>`;
        });

        document.getElementById('compare-content').innerHTML = `
        <div class="table-responsive">
        <table class="compare-table">
            <thead>
                <tr>
                    <th style="width:120px;"></th>
                    ${headerCols}
                </tr>
            </thead>
            <tbody class="compare-section">
                <tr><td colspan="4"><div class="compare-section-title">📊 Thông số so sánh</div></td></tr>
                ${rowsHtml}
            </tbody>
        </table>
        </div>
        <div class="text-center mt-4">
            <a href="products.php" class="btn btn-outline-primary me-2">
                <i class="fas fa-plus me-1"></i>Thêm sản phẩm khác
            </a>
            <button onclick="clearCompare(); window.location.href='products.php';" class="btn btn-outline-danger">
                <i class="fas fa-trash me-1"></i>Xóa tất cả
            </button>
        </div>`;
    }

    function loadCompare() {
        const ids = JSON.parse(localStorage.getItem('compare_ids_v2') || '[]');
        if (!ids.length) return; // giữ empty state

        fetch(`api/compare_data.php?ids=${ids.join(',')}`)
            .then(r => r.json())
            .then(items => {
                if (!items.length) return;
                renderCompare(items);
            })
            .catch(() => {});
    }

    loadCompare();
})();
</script>

<?php include('func/footer.php'); ?>
