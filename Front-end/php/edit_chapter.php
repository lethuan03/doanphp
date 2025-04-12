<?php
require_once '../../Back-end/config/database.php';

$database = new Database();
$pdo = $database->getConnection();

if (!isset($_GET['id'])) {
    die("Chương không tồn tại.");
}

$chapter_id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM chapters WHERE chapter_id = ?");
$stmt->execute([$chapter_id]);
$chapter = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$chapter) {
    die("Chương không tồn tại.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $chapter_title = $_POST['chapter_title'];
    $chapter_content = $_POST['chapter_content'];
    
    $stmt = $pdo->prepare("UPDATE chapters SET title = ?, content = ? WHERE chapter_id = ?");
    $stmt->execute([$chapter_title, $chapter_content, $chapter_id]);
    
    echo "Cập nhật chương thành công!";
    header("Location: edit_story.php?story_id=" . $chapter['story_id']);
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa chương</title>
</head>
<body>
    <h2>Chỉnh sửa chương</h2>
    <form action="" method="post">
        <label>Tiêu đề chương:</label>
        <input type="text" name="chapter_title" value="<?php echo htmlspecialchars($chapter['title']); ?>" required><br>
        
        <label>Nội dung chương:</label>
        <textarea name="chapter_content" required><?php echo htmlspecialchars($chapter['content']); ?></textarea><br>
        
        <button type="submit">Cập nhật</button>
    </form>
</body>
</html>
