<?php
require_once '../../Back-end/config/database.php';

$database = new Database();
$pdo = $database->getConnection();

if (!isset($_GET['id'])) {
    die("Chương không tồn tại.");
}

$chapter_id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM Chapters WHERE chapter_id = ?");
$stmt->execute([$chapter_id]);
$chapter = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$chapter) {
    die("Chương không tồn tại.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $chapter_title = $_POST['chapter_title'];
    $chapter_content = $_POST['chapter_content'];
    
    $stmt = $pdo->prepare("UPDATE Chapters SET title = ?, content = ? WHERE chapter_id = ?");
    $stmt->execute([$chapter_title, $chapter_content, $chapter_id]);
    
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-4">
    <h2 class="mb-4">Chỉnh sửa chương</h2>
    <form action="" method="post" class="card p-4 shadow">
        <div class="mb-3">
            <label for="chapter_title" class="form-label">Tiêu đề chương:</label>
            <input type="text" name="chapter_title" id="chapter_title" class="form-control" value="<?php echo htmlspecialchars($chapter['title']); ?>" required>
        </div>
        
        <div class="mb-3">
            <label for="chapter_content" class="form-label">Nội dung chương:</label>
            <textarea name="chapter_content" id="chapter_content" class="form-control" rows="8" required><?php echo htmlspecialchars($chapter['content']); ?></textarea>
        </div>
        
        <button type="submit" class="btn btn-primary">Cập nhật</button>
    </form>
</body>
</html>