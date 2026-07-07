<?php

class Wishlist
{
    public $db = null;

    public function __construct(DBConnect $db)
    {
        if (!isset($db->con))
            exit;
        $this->db = $db;
    }

    public function getWishlist($userid = null)
    {
        if ($userid != null) {
            $stmt = $this->db->con->prepare("SELECT * FROM wishlist WHERE user_id=?");
            $stmt->bind_param("i", $userid);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $resultArray = array();
            while ($item = $result->fetch_array(MYSQLI_ASSOC)) {
                $resultArray[] = $item;
            }
            return $resultArray;
        }
        return array();
    }

    public function toggleWishlist($userid, $itemid, $ajax = false)
    {
        if (isset($userid) && isset($itemid)) {
            $stmt = $this->db->con->prepare("SELECT * FROM wishlist WHERE user_id=? AND item_id=?");
            $stmt->bind_param("ii", $userid, $itemid);
            $stmt->execute();
            $check_res = $stmt->get_result();

            if ($check_res->num_rows > 0) {
                // Already in wishlist, remove it
                $stmt_delete = $this->db->con->prepare("DELETE FROM wishlist WHERE user_id=? AND item_id=?");
                $stmt_delete->bind_param("ii", $userid, $itemid);
                $result = $stmt_delete->execute();
            } else {
                // Not in wishlist, add it
                $stmt_insert = $this->db->con->prepare("INSERT INTO wishlist(user_id, item_id) VALUES(?, ?)");
                $stmt_insert->bind_param("ii", $userid, $itemid);
                $result = $stmt_insert->execute();
            }

            if ($result) {
                if ($ajax) return isset($stmt_insert) ? 'added' : 'removed';
                
                // Set toast based on action
                if (isset($stmt_insert)) {
                    $_SESSION['toast_msg'] = "Đã thêm vào yêu thích!";
                    $_SESSION['toast_type'] = "success";
                } else {
                    $_SESSION['toast_msg'] = "Đã xóa khỏi yêu thích!";
                    $_SESSION['toast_type'] = "danger";
                }
                // Optional: can redirect back
                $referer = $_SERVER['HTTP_REFERER'] ?? $_SERVER['REQUEST_URI'];
                echo "<script>window.location.href='" . $referer . "';</script>";
                exit;
            }
        }
        return false;
    }

    public function deleteWishlist($userid, $itemid, $ajax = false)
    {
        if ($userid != null && $itemid != null) {
            $stmt = $this->db->con->prepare("DELETE FROM wishlist WHERE user_id=? AND item_id=?");
            $stmt->bind_param("ii", $userid, $itemid);
            $result = $stmt->execute();
            
            if ($result) {
                if ($ajax) return true;
                $_SESSION['toast_msg'] = "Đã xóa khỏi yêu thích!";
                $_SESSION['toast_type'] = "danger";
                $referer = $_SERVER['HTTP_REFERER'] ?? $_SERVER['REQUEST_URI'];
                echo "<script>window.location.href='" . $referer . "';</script>";
                exit;
            }
            return $result;
        }
    }

    public function getWishlistId($wishlistArray = null)
    {
        $wishlist_id = array();
        if ($wishlistArray != null) {
            foreach ($wishlistArray as $item) {
                $wishlist_id[] = $item['item_id'];
            }
        }
        return $wishlist_id;
    }
}
?>
