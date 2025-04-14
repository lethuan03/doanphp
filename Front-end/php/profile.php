<?php
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông Tin Cá Nhân</title>
    <link rel="stylesheet" href="../css/header_styles.css">
    <link rel="stylesheet" href="../css/user_management.css">
</head>
<body>
    <?php include '../views/header.php'; ?>

    <div class="container profile-container">
        <h2>Thông Tin Cá Nhân</h2>
        <div class="profile-card" id="profileInfo">
            <p class="loading">Đang tải thông tin...</p>
        </div>
    </div>

    <script>
        async function loadProfile() {
            const userId = <?php echo json_encode($user_id); ?>;
            const profileInfo = document.getElementById('profileInfo');

            try {
                const res = await fetch(`http://localhost/doanphp/Back-end/api/author.php?author_id=${userId}`);
                const user = await res.json();

                if (user.error) {
                    profileInfo.innerHTML = `<p class="error-message">${user.error}</p>`;
                    return;
                }

                // Định dạng created_at
                const createdAt = new Date(user.created_at).toLocaleString('vi-VN', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });

                profileInfo.innerHTML = `
                    <div class="avatar-container">
                        ${user.avatar ? 
                            `<img src="/doanphp/Back-end/${user.avatar}" alt="Avatar" class="profile-avatar">` : 
                            `<div class="no-avatar">Không có ảnh đại diện</div>`}
                    </div>
                    <div class="info-container">
                        <p class="info-item"><span class="info-label">Tên người dùng:</span> ${user.username}</p>
                        <p class="info-item"><span class="info-label">Email:</span> ${user.email}</p>
                        <p class="info-item"><span class="info-label">Ngày tham gia:</span> ${createdAt}</p>
                    </div>
                `;
            } catch (error) {
                console.error('Lỗi khi tải thông tin:', error);
                profileInfo.innerHTML = `<p class="error-message">Lỗi khi tải thông tin: ${error.message}</p>`;
            }
        }

        // Gọi khi trang tải
        document.addEventListener('DOMContentLoaded', loadProfile);
    </script>

    <?php include '../views/footer.php'; ?>
</body>
</html>