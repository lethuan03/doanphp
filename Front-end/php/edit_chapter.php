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

// Kiểm tra nếu có chapter_id
if (!isset($_GET['chapter_id'])) {
    die("Chương không tồn tại.");
}

$chapter_id = $_GET['chapter_id'];

// Lấy thông tin chương
$stmt = $pdo->prepare("SELECT * FROM chapters WHERE chapter_id = ?");
$stmt->execute([$chapter_id]);
$chapter = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$chapter) {
    die("Chương không tồn tại.");
}

// Lấy thông tin truyện để hiển thị tên truyện
$stmt = $pdo->prepare("SELECT title FROM Stories WHERE story_id = ?");
$stmt->execute([$chapter['story_id']]);
$story = $stmt->fetch(PDO::FETCH_ASSOC);

// Xử lý khi người dùng gửi form cập nhật
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $chapter_number = $_POST['chapter_number'] ?? '';
    $chapter_title = $_POST['chapter_title'] ?? '';
    $chapter_content = $_POST['chapter_content'] ?? '';

    if ($chapter_number == '' || $chapter_title == '' || $chapter_content == '') {
        echo "<script>alert('Vui lòng điền đầy đủ thông tin!');</script>";
    } else {
        $stmt = $pdo->prepare("UPDATE chapters SET chapter_number = ?, title = ?, content = ? WHERE chapter_id = ?");
        $stmt->execute([$chapter_number, $chapter_title, $chapter_content, $chapter_id]);

        echo "<script>alert('Cập nhật chương thành công!'); window.location.href='manage_chapters.php?story_id={$chapter['story_id']}';</script>";
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa Chương - <?= htmlspecialchars($story['title']) ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/header_styles.css">

</head>
<body>
    <?php include '../views/header.php'; ?>

    <div class="container">
        <h2>Chỉnh sửa Chương - <?= htmlspecialchars($story['title']) ?></h2>
        <form action="" method="post">
            <label>Số chương:</label>
            <input type="number" name="chapter_number" value="<?= htmlspecialchars($chapter['chapter_number']) ?>" required>

            <label>Tiêu đề chương:</label>
            <input type="text" name="chapter_title" value="<?= htmlspecialchars($chapter['title']) ?>" required>

            <label>Nội dung chương:</label>
            <textarea name="chapter_content" required><?= htmlspecialchars($chapter['content']) ?></textarea>

            <button type="submit">Cập nhật Chương</button>
        </form>
    </div>

    <?php include '../views/footer.php'; ?>
</body>
</html>
