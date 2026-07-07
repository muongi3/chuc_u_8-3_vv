<?php

// php manage class
class Manage
{
    public $db = null;

    public function __construct(DBConnect $db)
    {
        if (!isset($db->con))
            exit;
        $this->db = $db;
    }

    // fetch product data using getData Method
    public function getData()
    {
       $sql = "SELECT p.*, m.brand as brand_name, m.headquarter AS origin 
            FROM product p
            LEFT JOIN manufacturer m ON p.brand = m.id";
    $result = $this->db->con->query($sql);

        $resultArray = array();

        // fetch manage data one by one
        if ($result->num_rows > 0) {
            while ($item = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                $resultArray[] = $item;
            }
        }

        return $resultArray;
    }

    // fetch brand data using getBrands Method
    public function getBrands()
    {
        $sql = "SELECT * FROM manufacturer";
        $result = $this->db->con->query($sql);

        $resultArray = array();

        // fetch manage data one by one
        if ($result->num_rows > 0) {
            while ($item = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                $resultArray[] = $item;
            }
        }

        return $resultArray;
    }

    // get brand using brand id
    public function getBrand($id = null, $table = 'manufacturer')
    {
        if ($id != null) {
            $sql = "SELECT * FROM {$table} WHERE id={$id}";
            $result = $this->db->con->query($sql);

            $resultArray = array();

            // fetch account data once
            if ($result->num_rows == 1) {
                $resultArray = mysqli_fetch_array($result, MYSQLI_ASSOC);
            }

            return $resultArray;
        }
    }

    // handle image upload
    public function handleImage($image)
    {
        // Dùng đường dẫn tuyệt đối để tránh lỗi khi gọi từ thư mục admin/
        $target_dir = dirname(__DIR__) . '/assets/products/';

        $original_name = basename($image['name']);
        $imageFileType = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        $filename_only = pathinfo($original_name, PATHINFO_FILENAME);

        // Tạo tên file an toàn, tránh trùng lặp bằng timestamp
        $safe_filename = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $filename_only);
        $new_name = $safe_filename . '_' . time() . '.' . $imageFileType;
        $target_file = $target_dir . $new_name;

        // Kiểm tra thư mục tồn tại, nếu không thì tạo
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        // Check file size > 2MB
        if ($image['size'] > 2000000) {
            echo '<script>alert("Lỗi: File quá lớn (tối đa 2MB).")</script>';
            return '';
        }

        // Allow certain file formats
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($imageFileType, $allowed_types)) {
            echo '<script>alert("Lỗi: Chỉ chấp nhận định dạng JPG, JPEG, PNG, GIF & WEBP.")</script>';
            return '';
        }

        if (move_uploaded_file($image['tmp_name'], $target_file)) {
            // Chỉ trả về TÊN FILE để lưu vào DB (không phải toàn bộ path)
            return $new_name;
        } else {
            return '';
        }
    }

    // delete product item using product id
    public function deleteProduct($id = null, $table = 'product')
    {
        if ($id != null) {
            $sql = "DELETE FROM {$table} WHERE id={$id}";
            $result = $this->db->con->query($sql);
            if ($result) {
                // Reload Page
                header('Location: ' . $_SERVER['REQUEST_URI']);
            } else {
                echo '<script>alert("Error")</script>';
            }
            return $result;
        }
    }

    // update product item using product id
    public function updateProduct($id = null, $name = null, $brand = null, $price = null, $image = null, $category = null, $stock = null)
    {
        if ($id != null) {
            // 1. Xử lý ảnh trước (nếu có chọn ảnh mới)
            $imgSQL = "";
            if (!empty($image['name'])) {
                $imgName = $this->handleImage($image);
                if ($imgName != '') {
                    $imgSQL = ", image='{$imgName}'";
                }
            }
            
            $catSQL = "";
            if ($category !== null) {
                $catSQL = ", category='{$category}'";
            }

            $stockSQL = "";
            if ($stock !== null) {
                $stockSQL = ", stock=" . (int)$stock;
            }

            // 2. Gộp tất cả vào 1 câu SQL duy nhất để tối ưu
            $sql = "UPDATE product SET 
                    name='{$name}', 
                    brand='{$brand}', 
                    price={$price}
                    {$catSQL}
                    {$imgSQL}
                    {$stockSQL}
                    WHERE id={$id}";

            $result = $this->db->con->query($sql);
            if ($result) {
                header('Location: ' . $_SERVER['REQUEST_URI']);
                exit();
            } else {
                echo '<script>alert("Lỗi cập nhật: ' . $this->db->con->error . '")</script>';
            }
            return $result;
        }
    }

    // insert product item using product id
    public function insertProduct($name = null, $brand = null, $price = null, $image = null, $category = null, $stock = 100)
    {
        if ($name != null && $brand != null && $price != null && $image != null) {
            $imgName = $this->handleImage($image);
            $catValue = $category !== null ? "'{$category}'" : "'phone'";
            $stock = (int)$stock;
            if ($imgName != '') {
                // Lưu tên file vào DB (không lưu toàn bộ path)
                $sql = "INSERT INTO product (name, brand, price, image, category, stock) VALUES ('{$name}', {$brand}, {$price}, '{$imgName}', {$catValue}, {$stock})";
                $result = $this->db->con->query($sql);
                if ($result) {
                    // Thêm exit() để dừng script ngay khi chuyển hướng
                    header('Location: ' . $_SERVER['REQUEST_URI']);
                    exit(); 
                } else {
                    echo '<script>alert("Insert error!")</script>';
                }
                return $result;
            }
        } else {
            echo '<script>alert("Please fill all fields!")</script>';
        }
    }

    public function insertBrand($name, $hq) {
        $sql = "INSERT INTO manufacturer (brand, headquarter) VALUES ('$name', '$hq')";
        return $this->db->con->query($sql);
    }

    public function updateBrand($id, $name, $hq) {
        $sql = "UPDATE manufacturer SET brand='$name', headquarter='$hq' WHERE id=$id";
        return $this->db->con->query($sql);
    }

    public function deleteBrand($id) {
        // Xóa hãng
        $sql = "DELETE FROM manufacturer WHERE id=$id";
        return $this->db->con->query($sql);
    }

    public function getCategories() {
        $sql = "SELECT * FROM category";
        $result = $this->db->con->query($sql);
        $resultArray = array();
        if ($result && $result->num_rows > 0) {
            while ($item = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                $resultArray[] = $item;
            }
        }
        return $resultArray;
    }

    public function insertCategory($slug, $name, $icon) {
        $sql = "INSERT INTO category (slug, name, icon) VALUES ('$slug', '$name', '$icon')";
        return $this->db->con->query($sql);
    }

    public function updateCategory($id, $slug, $name, $icon) {
        $sql = "UPDATE category SET slug='$slug', name='$name', icon='$icon' WHERE id=$id";
        return $this->db->con->query($sql);
    }

    public function deleteCategory($id) {
        $sql = "DELETE FROM category WHERE id=$id";
        return $this->db->con->query($sql);
    }
}