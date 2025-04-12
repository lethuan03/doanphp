<?php
session_start();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập</title>
    <link rel="stylesheet" href="../css/header_styles.css">
    <link rel="stylesheet" href="../css/login_styles.css">
</head>
<body>
<?php include __DIR__ . '/../views/header.php'; ?>

    <h2>Đăng Nhập</h2>
    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $api_url = "http://localhost/doanphp/Back-end/api/auth.php?action=login";
        $data = json_encode(["email" => $email, "password" => $password]);

        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);

        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);

        if (isset($result["user_id"])) {
            // Lưu thông tin đăng nhập vào session
            $_SESSION["user_id"] = $result["user_id"];
            $_SESSION["username"] = $result["username"];
            $_SESSION["role_id"] = $result["role_id"];

            // Chuyển hướng về trang home
            header("Location: /doanphp/home.php");
            exit();
        } else {
            echo "<p style='color: red;'>Lỗi đăng nhập: " . ($result['error'] ?? "Thông tin không hợp lệ") . "</p>";
        }
    }
    ?>
    <form method="POST">
        <label>Email:</label>
        <input type="email" name="email" required>
        <label>Mật khẩu:</label>
        <input type="password" name="password" required>
        <button type="submit">Đăng Nhập</button>
    </form>
    <p>Chưa có tài khoản? <a href="register.php">Đăng ký</a></p>
    <?php include __DIR__ . '/../views/footer.php'; ?>
</body>
</html>
