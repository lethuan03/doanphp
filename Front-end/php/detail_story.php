<?php
session_start(); // Bắt đầu session để lấy user_id

require_once '../../Back-end/config/database.php';

$database = new Database();
$pdo = $database->getConnection();

$story_id = $_GET['story_id'] ?? null;

if (!$story_id) {
    die("Không tìm thấy story_id.");
}

$stmt = $pdo->prepare("SELECT * FROM Stories WHERE story_id = ?");
$stmt->execute([$story_id]);
$story = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$story) {
    die("Truyện không tồn tại.");
}

$stmt_chapters = $pdo->prepare("SELECT * FROM Chapters WHERE story_id = ? ORDER BY chapter_number ASC");
$stmt_chapters->execute([$story_id]);
$chapters = $stmt_chapters->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($story['title']); ?></title>
    <link rel="stylesheet" href="../css/header_styles.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .favorite-btn {
            padding: 8px 16px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-bottom: 20px;
        }
        .favorite-btn:hover {
            background-color: #0056b3;
        }
        .favorite-btn:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }
        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .message.success {
            background-color: #e6ffe6;
            color: green;
        }
        .message.error {
            background-color: #ffe6e6;
            color: red;
        }
    </style>
</head>
<body>
    <?php include '../views/header.php'; ?>
    <div class="container">
        <h2><?php echo htmlspecialchars($story['title']); ?></h2>
        <img src="http://localhost/doanphp/Back-end/<?php echo htmlspecialchars($story['cover_image']); ?>" alt="<?php echo htmlspecialchars($story['title']); ?>" width="200">

        <!-- Nút thêm vào yêu thích -->
        <?php if (isset($_SESSION['user_id'])): ?>
            <div>
                <button class="favorite-btn" onclick="addToFavorites(<?php echo $story_id; ?>)">Thêm vào yêu thích</button>
                <div id="favoriteMessage" class="message" style="display: none;"></div>
            </div>
        <?php else: ?>
            <p class="message error">Vui lòng <a href="login.php">đăng nhập</a> để thêm truyện vào yêu thích.</p>
        <?php endif; ?>

        <!-- Nội dung mô tả -->
        <div class="mt-5">
            <h5 class="text-primary border-bottom pb-2">📘 NỘI DUNG TRUYỆN</h5>
            <p class="mt-2"><?php echo nl2br(htmlspecialchars($story['description'])); ?></p>

            <!-- Thông tin bổ sung -->
            <div class="story-meta" style="margin-bottom: 30px; background: #f9f9f9; padding: 20px; border-radius: 6px;">
                <p><strong>Tác giả:</strong>
                    <?php
                    $stmt_author = $pdo->prepare("SELECT username FROM Users WHERE user_id = ?");
                    $stmt_author->execute([$story['author_id']]);
                    $author = $stmt_author->fetchColumn();
                    echo htmlspecialchars($author ?: 'Không rõ');
                    ?>
                </p>

                <p><strong>Thể loại:</strong>
                    <?php
                    $stmt_genres = $pdo->prepare("SELECT g.name 
                                                 FROM Genres g 
                                                 JOIN Story_Genres sg ON g.genre_id = sg.genre_id 
                                                 WHERE sg.story_id = ?");
                    $stmt_genres->execute([$story_id]);
                    $genres = $stmt_genres->fetchAll(PDO::FETCH_COLUMN);
                    echo htmlspecialchars(implode(', ', $genres) ?: 'Đang cập nhật');
                    ?>
                </p>

                <p><strong>Ngày đăng:</strong> <?php echo date("d/m/Y H:i", strtotime($story['created_at'])); ?></p>
            </div>
        </div>

        <!-- Danh sách chương -->
        <div class="mt-5">
                <h5 class="text-primary border-bottom pb-2">📖 DANH SÁCH CHƯƠNG</h5>
                <table class="table table-striped mt-3">
                    <thead>
                        <tr>
                            <th>Số chương</th>
                            <th>Tiêu đề</th>
                            <th>Ngày cập nhật</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($chapters as $chapter): ?>
                            <tr>
                                <td>Chapter <?php echo htmlspecialchars($chapter['chapter_number']); ?></td>
                                <td>
                                    <a href="detail_chapter.php?chapter_id=<?php echo $chapter['chapter_id']; ?>" 
                                    onclick="addToReadingHistory(<?php echo $chapter['chapter_id']; ?>)">
                                        <?php echo htmlspecialchars($chapter['title']); ?>
                                    </a>
                                </td>
                                <td><?php echo date("d/m/Y", strtotime($chapter['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
    </div>
    <?php include '../views/footer.php'; ?>

    <script>

                async function addToReadingHistory(chapterId) {
                    const userId = <?php echo isset($_SESSION['user_id']) ? json_encode($_SESSION['user_id']) : 'null'; ?>;
                    
                    if (!userId) {
                        return; 
                    }

                    try {
                        const response = await fetch('http://localhost/doanphp/Back-end/api/reading_history.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ user_id: userId, chapter_id: chapterId })
                        });
                        const data = await response.json();

                        if (data.status !== 'success') {
                            console.error('Lỗi khi lưu lịch sử đọc:', data.message);
                        }
                    } catch (error) {
                        console.error('Lỗi khi gửi yêu cầu:', error);
                    }
                }


        async function addToFavorites(storyId) {
            const userId = <?php echo isset($_SESSION['user_id']) ? json_encode($_SESSION['user_id']) : 'null'; ?>;
            const button = document.querySelector('.favorite-btn');
            const messageDiv = document.getElementById('favoriteMessage');

            if (!userId) {
                showMessage('Vui lòng đăng nhập để thêm truyện vào yêu thích.', true);
                return;
            }

            button.disabled = true;
            try {
                const response = await fetch('http://localhost/doanphp/Back-end/api/favorites.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ user_id: userId, story_id: storyId })
                });
                const data = await response.json();

                if (data.status === 'success') {
                    showMessage('Đã thêm vào danh sách yêu thích!', false);
                } else {
                    showMessage(data.message || 'Không thể thêm vào yêu thích.', true);
                }
            } catch (error) {
                showMessage('Lỗi khi thêm vào yêu thích.', true);
            } finally {
                button.disabled = false;
            }
        }

        function showMessage(text, isError) {
            const messageDiv = document.getElementById('favoriteMessage');
            messageDiv.textContent = text;
            messageDiv.className = `message ${isError ? 'error' : 'success'}`;
            messageDiv.style.display = 'block';
            setTimeout(() => {
                messageDiv.style.display = 'none';
                messageDiv.textContent = '';
            }, 3000);
        }
    </script>
</body>
</html>