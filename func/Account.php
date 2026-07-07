<?php

// php account class
class Account
{
    public $db = null;

    public function __construct(DBConnect $db)
    {
        if (!isset($db->con))
            exit;
        $this->db = $db;
    }

    // fetch account data using getData Method
    public function getData($table = 'account')
    {
        $sql = "SELECT * FROM {$table}";
        $result = $this->db->con->query($sql);

        $resultArray = array();

        // fetch account data one by one
        if ($result->num_rows > 0) {
            while ($item = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                $resultArray[] = $item;
            }
        }

        return $resultArray;
    }

    // get account using id
    public function getAccount($id = null, $table = 'account')
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

    // handle login functionality
    public function login($username = null, $password = null)
    {
        if ($username != null && $password != null) {
            $sql = "SELECT * FROM account WHERE username='{$username}' AND password='{$password}'";
            $result = $this->db->con->query($sql);

            if ($result->num_rows == 1) {
                $row = mysqli_fetch_array($result, MYSQLI_ASSOC);

                // ── SESSION (dùng bởi code mới: review, admin API, cart) ──
                $_SESSION['logged']    = true;
                $_SESSION['username']  = $row['username'];
                $_SESSION['user_id']   = (int)$row['id'];       // ← CẦN cho review & cart
                $_SESSION['privilege'] = (int)$row['privilege']; // ← CẦN cho admin API

                // ── COOKIE (giữ tương thích với code cũ) ──────────────────
                setcookie('user_id',   $row['id'],        time() + (86400 * 1), "/");
                setcookie('user_type', $row['privilege'], time() + (86400 * 1), "/");

                // Admin → vào thẳng trang quản trị, user thường → về trang chủ
                if ((int)$row['privilege'] === 1) {
                    header('Location: admin/products.php');
                } else {
                    header('Location: ' . ($_GET['redirect'] ?? 'index.php'));
                }
                exit;
            } else {
                echo "<script>alert('Tên đăng nhập hoặc mật khẩu không đúng!');</script>";
                return false;
            }
        }
    }

    // handle user logout
    public function logout()
    {
        if ($_SESSION['logged'] == true) {
            $_SESSION['logged'] = false;
            setcookie('user_id', '0', time() + (86400 * 30), "/"); // 86400 = 1 day
            setcookie('user_type', '0', time() + (86400 * 30), "/"); // 86400 = 1 day
            header('Location: ' . $_SERVER['REQUEST_URI']);
            return true;
        }
    }

    // handle user registration
    public function register(
        $fullname = null,
        $username = null,
        $password = null,
        $phone = null,
        $avatar = null,
        $email = null,
        $city = null,
        $gender = null,
        $address = null
    )
    {
        if (
            $fullname != null
            && $username != null
            && $password != null
            && $phone != null
            // && $avatar != null
            && $email != null
            && $city != null
            && $gender != null
            // && $address != null
        ) {
            if ($avatar == null) {
                $avatar = null;
            }
            if ($address == null) {
                $address = null;
            }
            $sqlAccount = "INSERT INTO account(username, password, email) VALUES ('{$username}','{$password}','{$email}')";
            $sqlUser = "INSERT INTO user(fullname, phone, avatar, city, gender, address) VALUES ('{$fullname}','{$phone}','{$avatar}','{$city}','{$gender}','{$address}')";
            $resultAcc = $this->db->con->query($sqlAccount);
            $resultUser = $this->db->con->query($sqlUser);
            if ($resultAcc && $resultUser) {
                header('Location: ' . $_SERVER['REQUEST_URI']);
                return true;
            } else if ($resultUser) {
                echo "<script>alert('Register Account fail');</script>";
                $this->db->con->query("DELETE FROM account WHERE username = '{$username}'");
                return false;
            } else if ($resultAcc) {
                echo "<script>alert('Register User fail');</script>";
                $this->db->con->query("DELETE FROM user WHERE fullname = '{$fullname}'");
                return false;
            } else {
                echo "<script>alert('Register fail');</script>";
                return false;
            }
        }
    }

    // delete account item using account id
    public function deleteAcc($id = null)
    {
        if ($id != null) {
            $id = (int)$id;
            // Xóa cả trong bảng user và account
            $this->db->con->query("DELETE FROM `user` WHERE id = $id");
            $result = $this->db->con->query("DELETE FROM `account` WHERE id = $id");
            return $result;
        }
        return false;
    }

    // update account item using account id
    public function updateAcc($id = null, $username = null, $password = null, $email = null, $privilege = null)
    {
        if ($id != null) {
            $id        = (int)$id;
            $username  = $this->db->con->real_escape_string($username);
            $password  = $this->db->con->real_escape_string($password);
            $email     = $this->db->con->real_escape_string($email);
            $privilege = (int)$privilege;
            $sql = "UPDATE account SET username='$username', password='$password', email='$email', privilege='$privilege' WHERE id=$id";
            return $this->db->con->query($sql);
        }
        return false;
    }

    // insert account item (admin panel)
    public function insertAcc($username = null, $password = null, $email = null, $privilege = 0)
    {
        if (!$username || !$password || !$email) {
            return false;
        }

        $username  = $this->db->con->real_escape_string(trim($username));
        $password  = $this->db->con->real_escape_string(trim($password));
        $email     = $this->db->con->real_escape_string(trim($email));
        $privilege = (int)$privilege;

        // Chèn vào bảng account
        $sqlAcc = "INSERT INTO account (username, password, email, privilege)
                   VALUES ('$username','$password','$email',$privilege)";
        $resAcc = $this->db->con->query($sqlAcc);

        if ($resAcc) {
            // Tự động tạo bản ghi user tương ứng (cùng id)
            $newId   = $this->db->con->insert_id;
            $sqlUser = "INSERT INTO `user` (id, fullname, phone, city, gender, address, money)
                        VALUES ($newId,'$username','','',0,'',0)";
            $this->db->con->query($sqlUser);
            return true;
        }
        return false;
    }

}