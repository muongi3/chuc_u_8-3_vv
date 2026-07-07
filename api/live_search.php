<?php
// CLK SHOP - SMART LIVE SEARCH API BACKEND
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../func/DBConnect.php';

$db   = new DBConnect();
$conn = $db->con;

$q = trim($_GET['q'] ?? '');

// Phải có ít nhất 2 ký tự mới tìm
if (mb_strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

$like = '%' . $conn->real_escape_string($q) . '%';

$sql = "SELECT p.id, p.name, p.price, p.image, p.category, m.brand AS brand_name
        FROM product p
        LEFT JOIN manufacturer m ON p.brand = m.id
        WHERE p.name LIKE ? OR m.brand LIKE ?
        ORDER BY p.name ASC
        LIMIT 7";

$stmt = $conn->prepare($sql);
$stmt->bind_param('ss', $like, $like);
$stmt->execute();
$result = $stmt->get_result();

$items = [];
while ($row = $result->fetch_assoc()) {
    // Chuẩn hoá đường dẫn ảnh
    $img = !empty($row['image']) ? ltrim($row['image'], './') : 'assets/products/no-image.png';
    $items[] = [
        'id'         => (int)$row['id'],
        'name'       => $row['name'],
        'price'      => (int)$row['price'],
        'image'      => $img,
        'brand_name' => $row['brand_name'] ?? '',
        'category'   => $row['category']  ?? '',
    ];
}

$stmt->close();
echo json_encode($items, JSON_UNESCAPED_UNICODE);
