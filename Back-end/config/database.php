<?php
$host = 'localhost';          // hoặc 127.0.0.1
$dbname = 'truyenDB';         // tên CSDL bạn đã tạo
$username = 'root';           // tài khoản mặc định XAMPP
$password = '';               // mật khẩu mặc định XAMPP thường để trống

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Kết nối thành công!";
} catch (PDOException $e) {
    die("Kết nối thất bại: " . $e->getMessage());
}
?>
