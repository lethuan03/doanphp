<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đọc Truyện</title>
    <link rel="stylesheet" href="Front-end/css/header_styles.css">
</head>

<body>
    <header>    
        <nav>
            <ul>
            <img src="/doanphp/Front-end/img/Picture1_1.png" alt="Logo" class="logo" style="width: 45px; height: auto;">

                <li><a href="/doanphp/home.php">Trang chủ</a></li> 
                <li><a href="/doanphp/Front-end/php/favorites.php">Trang yêu thích</a></li>
                
                <li><a href="/doanphp/Front-end/php/search.php">Tìm kiếm</a></li>
                
                <?php if (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 2): ?>
                    <li><a href="/doanphp/Front-end/php/admin.php" class="admin-btn">Admin</a></li>
                <?php endif; ?>


                <li id="user-info">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="/doanphp/Front-end/php/profile.php">Tài khoản</a> | 
                    <a href="/doanphp/Front-end/php/logout.php">Đăng xuất</a>
                <?php else: ?>
                    <a href="/doanphp/Front-end/php/login.php" class="login-btn">Đăng nhập</a>
                <?php endif; ?>
                </li>
            </ul>
        </nav>
    </header>
</body>

</html>
