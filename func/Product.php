<?php

// Class xử lý dữ liệu sản phẩm và đánh giá - Bản Full Hoàn Thiện
class Product
{
    public $db = null;

    public function __construct(DBConnect $db)
    {
        if (!isset($db->con)) exit;
        $this->db = $db;
    }

    // 1. Lấy toàn bộ sản phẩm
    public function getData($table = 'product')
    {
        $sql = "SELECT * FROM {$table}";
        $result = $this->db->con->query($sql);
        $resultArray = array();
        if ($result && $result->num_rows > 0) {
            while ($item = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                $resultArray[] = $item;
            }
        }
        return $resultArray;
    }

    // 2. Lấy 1 sản phẩm cụ thể
    public function getProduct($id = null, $table = 'product')
    {
        if ($id != null) {
            $id = (int)$id;
            $sql = "SELECT * FROM {$table} WHERE id={$id}";
            $result = $this->db->con->query($sql);
            if ($result && $result->num_rows == 1) {
                return mysqli_fetch_array($result, MYSQLI_ASSOC);
            }
        }
        return null;
    }

    // 3. Lấy sản phẩm theo category (headphone/charger/case/powerbank)
    public function getProductsByCategory(string $cat, int $page = 1, int $limit = 8, string $sort = 'newest'): array {
        $cat    = $this->db->con->real_escape_string($cat);
        $offset = ($page - 1) * $limit;
        $order  = match($sort) {
            'price_asc'  => 'p.price ASC',
            'price_desc' => 'p.price DESC',
            'name_asc'   => 'p.name ASC',
            default      => 'p.id DESC',
        };
        $sql = "SELECT p.*, m.brand AS brand_name
                FROM product p
                LEFT JOIN manufacturer m ON p.brand = m.id
                WHERE p.category = '$cat'
                ORDER BY $order
                LIMIT $limit OFFSET $offset";
        $result = $this->db->con->query($sql);
        $arr = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) $arr[] = $row;
        }
        return $arr;
    }

    // 3b. Đếm tổng sản phẩm theo category
    public function countByCategory(string $cat): int {
        $cat = $this->db->con->real_escape_string($cat);
        $result = $this->db->con->query(
            "SELECT COUNT(*) AS total FROM product WHERE category = '$cat'"
        );
        if (!$result) return 0;
        $row = $result->fetch_assoc();
        return (int)($row['total'] ?? 0);
    }

    // 4. Tìm kiếm sản phẩm (Search + category)
    public function searchProduct($keyword) {
        $kw = $this->db->con->real_escape_string($keyword);
        $sql = "SELECT p.*, m.brand AS brand_name
                FROM product p
                LEFT JOIN manufacturer m ON p.brand = m.id
                WHERE p.name     LIKE '%$kw%'
                   OR m.brand    LIKE '%$kw%'
                   OR p.category LIKE '%$kw%'
                ORDER BY p.id DESC";
        $result = $this->db->con->query($sql);
        $arr = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) $arr[] = $row;
        }
        return $arr;
    }

    // 5. Lấy sản phẩm liên quan (Cùng hãng nhưng trừ sản phẩm hiện tại)
    public function getRelatedProducts($id, $brand, $limit = 4, $table = 'product') {
        $id = (int)$id;
        $brand = $this->db->con->real_escape_string($brand);
        $sql = "SELECT * FROM {$table} WHERE item_brand='{$brand}' AND id != {$id} ORDER BY RAND() LIMIT {$limit}";
        $result = $this->db->con->query($sql);
        $resultArray = array();
        if ($result) {
            while ($item = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                $resultArray[] = $item;
            }
        }
        return $resultArray;
    }

    // --- PHẦN ĐÁNH GIÁ (REVIEWS) ---

    // 6. Lấy danh sách đánh giá của một sản phẩm (Kèm avatar người dùng)
    public function getReviews($product_id) {
        $product_id = (int)$product_id;
        // LEFT JOIN để lấy thông tin người dùng từ bảng user
        $sql = "SELECT r.*, u.fullname, u.avatar 
                FROM reviews r 
                LEFT JOIN user u ON r.user_id = u.id 
                WHERE r.product_id = $product_id 
                ORDER BY r.created_at DESC";
        
        $result = $this->db->con->query($sql);
        $resultArray = array();
        if ($result && $result->num_rows > 0) {
            while ($item = mysqli_fetch_array($result, MYSQLI_ASSOC)) { 
                $resultArray[] = $item; 
            }
        }
        return $resultArray;
    }

    // 7. Thêm đánh giá mới
    public function addReview($product_id, $user_id, $rating, $comment) {
        if ($this->db->con != null) {
            $p_id = (int)$product_id;
            $u_id = (int)$user_id;
            $rate = (int)$rating;
            $msg = $this->db->con->real_escape_string($comment);
            
            // Đảm bảo không nhập rỗng
            if(empty($msg)) return false;

            $sql = "INSERT INTO reviews (product_id, user_id, rating, comment) VALUES ($p_id, $u_id, $rate, '$msg')";
            return $this->db->con->query($sql);
        }
        return false;
    }

    // 8. Xóa đánh giá (Chỉ cho phép chính chủ xóa)
    public function deleteReview($review_id, $user_id) {
        if ($this->db->con != null) {
            $r_id = (int)$review_id;
            $u_id = (int)$user_id;

            // Bảo mật: Phải khớp cả ID review và ID user
            $sql = "DELETE FROM reviews WHERE id = {$r_id} AND user_id = {$u_id}";
            return $this->db->con->query($sql);
        }
        return false;
    }

    // 9. Lấy thống kê sao (Trung bình sao, tổng số đánh giá)
    public function getReviewStats($product_id) {
        $product_id = (int)$product_id;
        $stats = [
            'average' => 0, 
            'total' => 0, 
            'stars' => [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0]
        ];

        $sql = "SELECT rating, COUNT(*) as count FROM reviews WHERE product_id = {$product_id} GROUP BY rating";
        $result = $this->db->con->query($sql);

        if ($result && $result->num_rows > 0) {
            $total_points = 0; 
            $total_count = 0;
            while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                $r = (int)$row['rating']; 
                $c = (int)$row['count'];
                $stats['stars'][$r] = $c; 
                $total_count += $c; 
                $total_points += ($r * $c);
            }
            $stats['total'] = $total_count;
            if ($total_count > 0) { 
                $stats['average'] = round($total_points / $total_count, 1); 
            }
        }
        return $stats;
    }

    // ─────────────────────────────────────────────────────────
    // 10. Lấy biến thể (RAM/ROM) từ bảng product_variant
    // ─────────────────────────────────────────────────────────
    public function getVariants($product_id) {
        $id  = (int)$product_id;
        $sql = "SELECT * FROM product_variant WHERE product_id = $id ORDER BY price ASC";
        $result = $this->db->con->query($sql);
        $arr = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) { $arr[] = $row; }
        }
        return $arr;
    }

    // ─────────────────────────────────────────────────────────
    // 11. Lấy sản phẩm liên quan theo brand_id (integer)
    // ─────────────────────────────────────────────────────────
    public function getRelatedByBrand($product_id, $brand_id, $limit = 4) {
        $id    = (int)$product_id;
        $brand = (int)$brand_id;
        $limit = (int)$limit;
        $sql   = "SELECT p.*, m.brand AS brand_name
                  FROM product p
                  LEFT JOIN manufacturer m ON p.brand = m.id
                  WHERE p.brand = $brand AND p.id != $id
                  ORDER BY RAND() LIMIT $limit";
        $result = $this->db->con->query($sql);
        $arr = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) { $arr[] = $row; }
        }
        return $arr;
    }

    // ─────────────────────────────────────────────────────────
    // 12. Kiểm tra user đã mua VÀ ĐÃ NHẬN sản phẩm (để được đánh giá)
    //     Chỉ tính đơn hàng admin đã cập nhật thành 'delivered'
    // ─────────────────────────────────────────────────────────
    public function hasPurchased($user_id, $product_id) {
        $u = (int)$user_id;
        $p = (int)$product_id;
        if ($u <= 0 || $p <= 0) return false;
        // Chấp nhận cả 'delivered' lẫn 'Đã giao' (dự phòng DB dùng tiếng Việt)
        $sql = "SELECT COUNT(*) AS cnt
                FROM order_detail od
                JOIN orders o ON od.order_id = o.id
                WHERE o.user_id = $u
                  AND od.product_id = $p
                  AND o.status IN ('delivered', 'Đã giao', 'đã giao', 'completed')";
        $result = $this->db->con->query($sql);
        if ($result) {
            $row = $result->fetch_assoc();
            return (int)$row['cnt'] > 0;
        }
        return false;
    }

    // ─────────────────────────────────────────────────────────
    // 12b. Kiểm tra user đã đặt mua nhưng đơn CHƯA được giao
    //      (pending/confirmed/packing/shipping)
    //      Dùng để hiển thị thông báo "đang chờ giao hàng"
    // ─────────────────────────────────────────────────────────
    public function hasBoughtButNotDelivered($user_id, $product_id) {
        $u = (int)$user_id;
        $p = (int)$product_id;
        if ($u <= 0 || $p <= 0) return false;
        $sql = "SELECT COUNT(*) AS cnt
                FROM order_detail od
                JOIN orders o ON od.order_id = o.id
                WHERE o.user_id = $u
                  AND od.product_id = $p
                  AND o.status IN ('pending','confirmed','packing','shipping',
                                   'Chờ xác nhận','Đã xác nhận','Đóng gói','Đang giao')";
        $result = $this->db->con->query($sql);
        if ($result) {
            $row = $result->fetch_assoc();
            return (int)$row['cnt'] > 0;
        }
        return false;
    }

    // ─────────────────────────────────────────────────────────
    // 13. Kiểm tra user đã đánh giá sản phẩm này chưa (chống spam)
    // ─────────────────────────────────────────────────────────
    public function hasReviewed($user_id, $product_id) {
        $u = (int)$user_id;
        $p = (int)$product_id;
        if ($u <= 0 || $p <= 0) return false;
        $sql = "SELECT id FROM reviews WHERE user_id = $u AND product_id = $p LIMIT 1";
        $result = $this->db->con->query($sql);
        return ($result && $result->num_rows > 0);
    }

    // ─────────────────────────────────────────────────────────
    // 14. Lấy sản phẩm theo brand — JOIN + LIMIT + OFFSET (tối ưu cho nhiều users)
    //     $brand_id = 0 → lấy tất cả; sort được whitelist tránh injection
    // ─────────────────────────────────────────────────────────
    public function getByBrand($brand_id = 0, $page = 1, $limit = 8, $sort = 'newest') {
        $brand_id = (int)$brand_id;
        $limit    = min(max((int)$limit, 1), 50); // giới hạn 1–50/trang
        $offset   = max(0, ((int)$page - 1) * $limit);

        // Whitelist sắp xếp — tuyệt đối không cho user inject ORDER BY
        $order_map = [
            'price_asc'  => 'p.price ASC',
            'price_desc' => 'p.price DESC',
            'newest'     => 'p.id DESC',
            'name_asc'   => 'p.name ASC',
        ];
        $order_by = $order_map[$sort] ?? 'p.id DESC';

        // WHERE: nếu brand_id = 0 thì lấy tất cả
        $where = $brand_id > 0 ? "WHERE p.brand = $brand_id" : '';

        // 1 câu JOIN duy nhất, không cần query riêng cho brand
        $sql = "SELECT p.*, m.brand AS brand_name, m.id AS mfr_id
                FROM product p
                LEFT JOIN manufacturer m ON p.brand = m.id
                $where
                ORDER BY $order_by
                LIMIT $limit OFFSET $offset";

        $result = $this->db->con->query($sql);
        $arr = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) { $arr[] = $row; }
        }
        return $arr;
    }

    // ─────────────────────────────────────────────────────────
    // 15. Đếm tổng sản phẩm theo brand (dùng riêng — chỉ COUNT, không SELECT *)
    //     brand_id = 0 → đếm tất cả
    // ─────────────────────────────────────────────────────────
    public function countByBrand($brand_id = 0) {
        $brand_id = (int)$brand_id;
        $where    = $brand_id > 0 ? "WHERE brand = $brand_id" : '';
        $sql      = "SELECT COUNT(*) AS total FROM product $where";
        $result   = $this->db->con->query($sql);
        if ($result) {
            $row = $result->fetch_assoc();
            return (int)$row['total'];
        }
        return 0;
    }

    // ─────────────────────────────────────────────────────────
    // 16. Lấy review stats cho nhiều sản phẩm 1 lúc (chống N+1 query)
    //     Trả về mảng [product_id => ['average' => x, 'total' => y]]
    // ─────────────────────────────────────────────────────────
    public function getBulkReviewStats(array $product_ids) {
        if (empty($product_ids)) return [];

        // Ép kiểu toàn bộ list — tránh injection
        $safe_ids = implode(',', array_map('intval', $product_ids));
        $sql = "SELECT product_id,
                       ROUND(AVG(rating), 1) AS average,
                       COUNT(*)              AS total
                FROM reviews
                WHERE product_id IN ($safe_ids)
                GROUP BY product_id";

        $result = $this->db->con->query($sql);
        $stats  = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $stats[(int)$row['product_id']] = [
                    'average' => (float)$row['average'],
                    'total'   => (int)$row['total'],
                ];
            }
        }
        return $stats;
    }

    // ─────────────────────────────────────────────────────────
    // 17. Lấy sản phẩm gợi ý mua kèm (Cross-sell)
    //     Lấy ngẫu nhiên sản phẩm KHÁC danh mục với sản phẩm hiện tại
    // ─────────────────────────────────────────────────────────
    public function getCrossSellProducts($current_category, $limit = 4) {
        $cat = $this->db->con->real_escape_string($current_category);
        $limit = (int)$limit;
        
        // Nếu đang xem điện thoại, gợi ý phụ kiện (khác 'phone'). 
        // Nếu đang xem phụ kiện, gợi ý điện thoại hoặc phụ kiện khác.
        $where = $cat === 'phone' ? "p.category != 'phone'" : "p.category = 'phone' OR p.category != '$cat'";
        
        $sql = "SELECT p.*, m.brand AS brand_name
                FROM product p
                LEFT JOIN manufacturer m ON p.brand = m.id
                WHERE $where AND p.stock > 0
                ORDER BY RAND() LIMIT $limit";
                
        $result = $this->db->con->query($sql);
        $arr = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) { $arr[] = $row; }
        }
        return $arr;
    }
}