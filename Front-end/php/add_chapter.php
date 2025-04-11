<?php
session_start();
require_once '../../Back-end/config/database.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$pdo = $database->getConnection();

// Kiểm tra nếu có story_id
if (!isset($_GET['story_id'])) {
    die("Truyện không tồn tại.");
}

$story_id = $_GET['story_id'];

// Lấy thông tin truyện
$stmt = $pdo->prepare("SELECT title FROM Stories WHERE story_id = ?");
$stmt->execute([$story_id]);
$story = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$story) {
    die("Truyện không tồn tại.");
}

// Xử lý khi người dùng gửi form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $chapter_number = $_POST['chapter_number'] ?? '';
    $chapter_title = $_POST['chapter_title'] ?? '';
    $chapter_content = $_POST['chapter_content'] ?? '';

    if ($chapter_number == '' || $chapter_title == '' || $chapter_content == '') {
        echo "<script>alert('Vui lòng điền đầy đủ thông tin!');</script>";
    } else {
        $stmt = $pdo->prepare("INSERT INTO chapters (story_id, chapter_number, title, content) VALUES (?, ?, ?, ?)");
        $stmt->execute([$story_id, $chapter_number, $chapter_title, $chapter_content]);

        echo "<script>alert('Thêm chương thành công!'); window.location.href='manage_chapters.php?story_id=$story_id';</script>";
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Chương - <?= htmlspecialchars($story['title']) ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/header_styles.css">
    
</head>
<body>
    <?php include '../views/header.php'; ?>

    <div class="container">
        <h2>Thêm Chương Mới - <?= htmlspecialchars($story['title']) ?></h2>
        <form action="" method="post">
            <label>Số chương:</label>
            <input type="number" name="chapter_number" required>

            <label>Tiêu đề chương:</label>
            <input type="text" name="chapter_title" required>

            <label>Nội dung chương:</label>
            <textarea name="chapter_content" required></textarea>

            <button type="submit">Thêm Chương</button>
        </form>
    </div>

    <?php include '../views/footer.php'; ?>
</body>
</html>
