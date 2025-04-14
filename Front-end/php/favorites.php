<?php
session_start();

// Kiểm tra xem user đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Chuyển hướng nếu chưa đăng nhập
    exit;
}

$user_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách truyện yêu thích</title>
    <link rel="stylesheet" href="../css/header_styles.css">


    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Comic Neue', cursive;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            min-height: 100vh;
            position: relative;
            color: #333;
        }
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #fff;
            z-index: -2;
            transform: translateZ(0);
            animation: parallax 20s linear infinite;
        }
        body::after {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.3);
            z-index: -1;
        }
        h2 {
            text-align: center;
            color: #ff69b4;
            font-size: 2.5em;
            font-weight: 700;
            margin-bottom: 30px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            position: relative;
            z-index: 1;
        }
        h2 i {
            margin-right: 10px;
            animation: heartBeat 1.5s infinite;
        }
        .favorites-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            padding: 0;
            list-style: none;
            position: relative;
            z-index: 1;
        }
        .favorite-card {
            background: #fff;
            border: 3px solid #ff69b4;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 6px 12px rgba(0,0,0,0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
        }
        .favorite-card:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 20px rgba(255,105,180,0.5);
        }
        .favorite-card::before {
            content: '✨';
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 1.5em;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .favorite-card:hover::before {
            opacity: 1;
        }
        .favorite-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-bottom: 2px solid #ff69b4;
        }
        .favorite-card-content {
            padding: 15px;
            text-align: center;
        }
        .favorite-card-content h3 {
            font-size: 1.3em;
            color: #333;
            margin-bottom: 10px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-weight: 700;
        }
        .favorite-card-content button {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            padding: 10px;
            background: #ff4d4d;
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            width: 100%;
            font-size: 1em;
            transition: background 0.2s ease, transform 0.2s ease;
        }
        .favorite-card-content button:hover {
            background: #e60000;
            transform: scale(1.1);
        }
        .message {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            background: #fff;
            color: #333;
            border: 3px solid #333;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.3);
            font-weight: 700;
            animation: slideIn 0.3s ease;
            z-index: 1000;
            max-width: 300px;
            text-align: center;
        }
        .message.success {
            background: #a2e4b8;
            border-color: #2ecc71;
            color: #155724;
        }
        .message.error {
            background: #ffb3b3;
            border-color: #e74c3c;
            color: #721c24;
        }
        .message::before {
            content: '';
            position: absolute;
            bottom: -10px;
            right: 20px;
            width: 0;
            height: 0;
            border-left: 10px solid transparent;
            border-right: 10px solid transparent;
            border-top: 10px solid #333;
        }
        .no-favorites {
            grid-column: 1 / -1;
            text-align: center;
            padding: 30px;
            background: #fff;
            border: 3px solid #ff69b4;
            border-radius: 15px;
            color: #e74c3c;
            font-size: 1.3em;
            box-shadow: 0 6px 12px rgba(0,0,0,0.2);
        }
        .no-favorites i {
            font-size: 1.5em;
            margin-right: 10px;
        }
        .loading {
            grid-column: 1 / -1;
            text-align: center;
            padding: 30px;
            color: #3498db;
            font-size: 1.3em;
            background: #fff;
            border: 3px solid #ff69b4;
            border-radius: 15px;
        }
        .loading i {
            margin-right: 10px;
        }
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes heartBeat {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }
        @keyframes parallax {
            0% { background-position: center top; }
            100% { background-position: center bottom; }
        }
        @media (max-width: 600px) {
            .favorites-list {
                grid-template-columns: 1fr;
            }
            .favorite-card {
                margin: 0 10px;
            }
            h2 {
                font-size: 2em;
            }
            .message {
                max-width: 80%;
                right: 10px;
                top: 10px;
            }
        }
    </style>
</head>
<body>
<?php include '../views/header.php'; ?>
    <h2><i class="fas fa-heart"></i> Danh sách truyện yêu thích</h2>

    <!-- Thông báo -->
    <div id="message" style="display: none;"></div>

    <!-- Danh sách favorites -->
    <ul id="favoritesList" class="favorites-list"></ul>

    <script>
        const userId = <?php echo json_encode($user_id); ?>;
        const apiUrl = 'http://localhost/doanphp/Back-end/api/favorites.php';

        // Hàm hiển thị thông báo
        function showMessage(text, isError = true) {
            const messageDiv = document.getElementById('message');
            messageDiv.textContent = text;
            messageDiv.className = `message ${isError ? 'error' : 'success'}`;
            messageDiv.style.display = 'block';
            setTimeout(() => {
                messageDiv.style.display = 'none';
                messageDiv.textContent = '';
            }, 3000);
        }

        // Hàm lấy danh sách favorites
        async function fetchFavorites() {
            const list = document.getElementById('favoritesList');
            list.innerHTML = '<li class="loading"><i class="fas fa-spinner fa-spin"></i> Đang tải...</li>';

            try {
                const response = await fetch(`${apiUrl}?user_id=${userId}`);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const data = await response.json();

                list.innerHTML = '';
                if (data.status === 'success' && data.data && data.data.length > 0) {
                    data.data.forEach(favorite => {
                        const li = document.createElement('li');
                        li.className = 'favorite-card';
                        li.innerHTML = `
                            <img src="http://localhost/doanphp/Back-end/${favorite.cover_image}" alt="${favorite.title}" onerror="this.src='http://localhost/doanphp/Back-end/images/default.jpg'">
                            <div class="favorite-card-content">
                                <h3>${favorite.title}</h3>
                                <button onclick="deleteFavorite(${favorite.favorite_id})">
                                    <i class="fas fa-trash"></i> Xóa
                                </button>
                            </div>
                        `;
                        list.appendChild(li);
                    });
                } else {
                    list.innerHTML = '<li class="no-favorites"><i class="fas fa-heart-broken"></i> Chưa có truyện yêu thích nào!</li>';
                }
            } catch (error) {
                console.error('Fetch Error:', error);
                showMessage('Không thể tải danh sách truyện yêu thích. Vui lòng thử lại.');
                list.innerHTML = '<li class="no-favorites"><i class="fas fa-exclamation-circle"></i> Lỗi khi tải dữ liệu!</li>';
            }
        }

        // Hàm xóa favorite
        async function deleteFavorite(favoriteId) {
            if (!confirm('Bạn có chắc muốn xóa truyện này khỏi danh sách yêu thích?')) return;

            try {
                const response = await fetch(apiUrl, {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ favorite_id: favoriteId })
                });
                const data = await response.json();

                if (data.status === 'success') {
                    showMessage('Đã xóa truyện khỏi danh sách yêu thích!', false);
                    fetchFavorites();
                } else {
                    showMessage(data.message || 'Không thể xóa truyện yêu thích!');
                }
            } catch (error) {
                console.error('Delete Error:', error);
                showMessage('Lỗi khi xóa truyện yêu thích!');
            }
        }

        // Load favorites khi trang được tải
        fetchFavorites();
    </script>
    <?php include '../views/footer.php'; ?>
</body>
</html>