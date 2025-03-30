<?php
session_start();
require_once '../../Back-end/config/database.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Lấy danh sách truyện
$query = "SELECT story_id, title FROM stories";
$stmt = $pdo->prepare($query);
$stmt->execute();
$stories = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Chapter</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../views/header.php'; ?>
    
    <div class="container">
        <h2>Thêm Chapter Mới</h2>
        <form action="process_add_chapter.php" method="POST">
            <label for="story_id">Chọn truyện:</label>
            <select name="story_id" required>
                <?php foreach ($stories as $story) : ?>
                    <option value="<?php echo $story['story_id']; ?>">
                        <?php echo htmlspecialchars($story['title']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <label for="chapter_title">Tiêu đề chương:</label>
            <input type="text" name="chapter_title" required>
            
            <label for="content">Nội dung:</label>
            <textarea name="content" rows="10" required></textarea>
            
            <button type="submit">Thêm Chapter</button>
        </form>
    </div>
    
    <?php include '../views/footer.php'; ?>
</body>
</html>
