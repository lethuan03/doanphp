<?php
require_once '../../Back-end/config/database.php';

$database = new Database();
$pdo = $database->getConnection();

if (!isset($_GET['story_id'])) {
    die("Truyện không tồn tại.");
}

$story_id = $_GET['story_id'];
$stmt = $pdo->prepare("SELECT * FROM Stories WHERE story_id = ?");
$stmt->execute([$story_id]);
$story = $stmt->fetch(PDO::FETCH_ASSOC);
// Lấy danh sách chương của truyện
$query = $pdo->prepare("SELECT * FROM chapters WHERE story_id = ? ORDER BY chapter_number ASC");
$query->execute([$story_id]);
$chapters = $query->fetchAll(PDO::FETCH_ASSOC);

// Nếu không có chương nào, gán biến là mảng rỗng để tránh lỗi
if (!$chapters) {
    $chapters = [];
}
if (!$story) {
    die("Truyện không tồn tại.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $author_id = $_POST['author_id'];
    $type = $_POST['type'];
    $status = $_POST['status'];
    $cover_image = $story['cover_image'];

    if (!empty($_FILES['cover_image']['name'])) {
        $target_dir = "../../uploads/";
        $target_file = $target_dir . basename($_FILES['cover_image']['name']);
        move_uploaded_file($_FILES['cover_image']['tmp_name'], $target_file);
        $cover_image = basename($_FILES['cover_image']['name']);
    }

    $stmt = $pdo->prepare("UPDATE Stories SET title=?, description=?, author_id=?, type=?, status=?, cover_image=? WHERE story_id=?");
    $stmt->execute([$title, $description, $author_id, $type, $status, $cover_image, $story_id]);
    echo "Cập nhật thành công!";
    header("Location: manage_stories.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa truyện</title>
</head>
<body>
    <h2>Chỉnh sửa truyện</h2>
    <form action="" method="post" enctype="multipart/form-data">
        <label>Tiêu đề:</label>
        <input type="text" name="title" value="<?php echo $story['title']; ?>" required><br>
        
        <label>Mô tả:</label>
        <textarea name="description" required><?php echo $story['description']; ?></textarea><br>
        
        <label>Ảnh bìa:</label>
        <input type="file" name="cover_image">
        <img src="../../uploads/<?php echo $story['cover_image']; ?>" width="100"><br>
        
        <label>Tác giả ID:</label>
        <input type="text" name="author_id" value="<?php echo $story['author_id']; ?>" required><br>
        
        <label>Thể loại:</label>
        <input type="text" name="type" value="<?php echo $story['type']; ?>" required><br>
        
        <label>Trạng thái:</label>
        <select name="status">
            <option value="ongoing" <?php if ($story['status'] == 'ongoing') echo 'selected'; ?>>Đang ra</option>
            <option value="completed" <?php if ($story['status'] == 'completed') echo 'selected'; ?>>Hoàn thành</option>
        </select><br>
        <!-- Form thêm chương mới -->
    <h3>Thêm chương mới</h3>
    <form action="process_add_chapter.php" method="post">
        <input type="hidden" name="story_id" value="<?php echo $story_id; ?>">
        <label>Tiêu đề chương:</label>
        <input type="text" name="chapter_title" required>
        
        <label>Nội dung chương:</label>
        <textarea name="chapter_content" required></textarea>
        
        <button type="submit">Thêm Chương</button>
    </form>

  <!-- Danh sách chương -->
<h3>Danh sách chương</h3>
<?php if (!empty($chapters)) : ?>
    <ul>
        <?php foreach ($chapters as $chapter) : ?>
            <li>
                <a href="edit_chapter.php?id=<?php echo $chapter['chapter_id']; ?>">
                    <?php echo "Chương " . htmlspecialchars($chapter['chapter_number']) . ": " . htmlspecialchars($chapter['title']); ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php else : ?>
    <p>Chưa có chương nào.</p>
<?php endif; ?>

        <button type="submit">Cập nhật</button>
    </form>
</body>
</html>
