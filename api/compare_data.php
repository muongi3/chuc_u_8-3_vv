<?php
/**
 * API: api/compare_data.php
 * Lấy dữ liệu chi tiết để so sánh sản phẩm
 * Params: ?ids=1,2,3
 * Returns: JSON array sản phẩm
 */
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../func/DBConnect.php';

$db   = new DBConnect();
$conn = $db->con;

// Nhận danh sách ID, lọc chỉ lấy số nguyên
$raw_ids = explode(',', $_GET['ids'] ?? '');
$ids = array_filter(array_map('intval', $raw_ids), fn($v) => $v > 0);
$ids = array_slice(array_unique($ids), 0, 3); // tối đa 3

if (empty($ids)) {
    echo json_encode([]);
    exit;
}

$placeholders = implode(',', $ids); // đã là int, an toàn

// Lấy thông tin sản phẩm + brand + review stats
$sql = "SELECT p.*, m.brand AS brand_name,
               COALESCE(AVG(r.rating), 0) AS avg_rating,
               COUNT(r.id) AS review_count
        FROM product p
        LEFT JOIN manufacturer m ON p.brand = m.id
        LEFT JOIN reviews r ON r.product_id = p.id
        WHERE p.id IN ($placeholders)
        GROUP BY p.id";

$result = $conn->query($sql);
$items  = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $img = !empty($row['image']) ? ltrim($row['image'], './') : 'assets/products/no-image.png';
        $cat_map = [
            'phone'     => 'Điện thoại',
            'headphone' => 'Tai nghe',
            'charger'   => 'Sạc & Cáp',
            'case'      => 'Ốp lưng',
            'powerbank' => 'Pin dự phòng',
        ];

        $brand = $row['brand_name'] ?? 'Khác';
        $cat = $row['category'] ?? 'phone';
        $specs = [];
        
        if ($cat === 'phone') {
            $specs_map = [
                'Apple'   => ['Màn hình' => 'Super Retina XDR OLED', 'Chip' => 'Apple A17 Pro Bionic', 'Camera' => '48MP + 12MP Ultra Wide', 'Pin' => '3,274 mAh, Sạc 20W'],
                'Samsung' => ['Màn hình' => 'Dynamic AMOLED 2X', 'Chip' => 'Snapdragon 8 Gen 3', 'Camera' => '200MP + Zoom 100x', 'Pin' => '4,700 mAh, Sạc 45W'],
                'Redmi'   => ['Màn hình' => 'AMOLED, 120Hz', 'Chip' => 'Dimensity / Snapdragon', 'Camera' => '50MP AI Triple Camera', 'Pin' => '5,000 mAh, Sạc 33W'],
                'Oppo'    => ['Màn hình' => 'Super AMOLED, 120Hz', 'Chip' => 'Snapdragon 778G 5G', 'Camera' => '50MP + 8MP + 2MP', 'Pin' => '4,500 mAh, Sạc 80W'],
            ];
            $specs = $specs_map[$brand] ?? ['Màn hình' => 'Full HD+', 'Chip' => 'Quad-core', 'Camera' => 'Chính 64MP', 'Pin' => '4,000 mAh'];
        } elseif ($cat === 'headphone') {
            $specs = ['Kiểu dáng' => 'Tai nghe không dây', 'Kết nối' => 'Bluetooth 5.3', 'Thời lượng pin' => 'Lên đến 30 giờ', 'Chống ồn' => 'Có (ANC)'];
        } elseif ($cat === 'charger') {
            $specs = ['Công suất' => '20W - 65W', 'Đầu ra' => 'Type-C / Lightning', 'Công nghệ' => 'Sạc nhanh PD/GaN', 'Bảo vệ' => 'Chống quá dòng/nhiệt'];
        } elseif ($cat === 'powerbank') {
            $specs = ['Dung lượng' => '10,000 - 20,000 mAh', 'Cổng sạc' => 'USB-A, Type-C', 'Công suất' => 'Sạc nhanh 22.5W', 'Lõi pin' => 'Polymer an toàn'];
        } elseif ($cat === 'case') {
            $specs = ['Chất liệu' => 'Silicone / Da / Kính', 'Chống sốc' => 'Có, viền cao su', 'Kiểu dáng' => 'Mỏng nhẹ, ôm sát', 'Tương thích' => 'Sạc không dây'];
        } else {
            $specs = ['Đặc điểm' => 'Sản phẩm chính hãng, chất lượng cao'];
        }

        $items[] = [
            'id'           => (int)$row['id'],
            'name'         => $row['name'],
            'price'        => (int)$row['price'],
            'image'        => $img,
            'brand_name'   => $brand,
            'category'     => $cat_map[$row['category']] ?? $row['category'],
            'avg_rating'   => round((float)$row['avg_rating'], 1),
            'review_count' => (int)$row['review_count'],
            'specs'        => $specs,
        ];
    }
}

// Giữ đúng thứ tự ID người dùng chọn
usort($items, fn($a,$b) => array_search($a['id'], $ids) - array_search($b['id'], $ids));

echo json_encode($items, JSON_UNESCAPED_UNICODE);
