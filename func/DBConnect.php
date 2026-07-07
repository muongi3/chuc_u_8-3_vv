<?php

class DBConnect
{
    protected $host = 'localhost';
    protected $user = 'root';
    protected $password = '';
    protected $database = 'mobileclk_store';
    public $con = null;

    public function __construct()
    {
        $this->con = mysqli_connect($this->host, $this->user, $this->password, $this->database);
        if (!$this->con) {
            die("Fail to connect! " . mysqli_connect_error());
        }
        mysqli_set_charset($this->con, 'utf8mb4');
    }

    public function __destruct()
    {
        $this->closeConnection();
    }

    protected function closeConnection()
    {
        if ($this->con != null) {
            $this->con->close();
            $this->con = null;
        }
    }
}

?>