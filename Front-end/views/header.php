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
    <!-- SwiperJS CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.css">

</head>

<body>
    <header>    
        <nav>
            <ul>
                <li><a href="/doanphp/home.php">Trang chủ</a></li> 
                <li><a href="theloai.php">Thể Loại</a></li>
                <li><a href="toptruyen.php">Top Truyện</a></li>
                <li><a href="lienhe.php">Liên Hệ</a></li>
                <li><a href="/doanphp/Front-end/php/manage_stories.php">them truyen</a></li>
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
    <!-- Slider Truyện Mới -->
<div class="swiper mySwiper">
    <div class="swiper-wrapper" id="slide-story-list">
        <!-- Truyện sẽ được tải vào đây -->
    </div>
    <!-- Nút điều hướng -->
    <div class="swiper-button-next"></div>
    <div class="swiper-button-prev"></div>
    <div class="swiper-pagination"></div>
</div>

</body>

</html>