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
$stmt = $pdo->prepare("SELECT * FROM Stories WHERE story_id = ?");
$stmt->execute([$story_id]);
$story = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$story) {
    die("Truyện không tồn tại.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $author_id = $_POST['author_id'] ?? '';
    $type = $_POST['type'] ?? '';
    $status = $_POST['status'] ?? '';
    $cover_image = $story['cover_image']; // Giữ ảnh cũ nếu không tải ảnh mới

    // Xử lý ảnh bìa
    if (!empty($_FILES['cover_image']['name'])) {
        $target_dir = "../../uploads/";
        $target_file = $target_dir . basename($_FILES['cover_image']['name']);
        move_uploaded_file($_FILES['cover_image']['tmp_name'], $target_file);
        $cover_image = basename($_FILES['cover_image']['name']);
    }

    // Cập nhật truyện
    $stmt = $pdo->prepare("UPDATE Stories SET title=?, description=?, author_id=?, type=?, status=?, cover_image=? WHERE story_id=?");
    $stmt->execute([$title, $description, $author_id, $type, $status, $cover_image, $story_id]);
    
    echo "<script>alert('Cập nhật thành công!'); window.location.href='manage_stories.php';</script>";
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa truyện</title>
    <link rel="stylesheet" href="../css/header_styles.css">
    <link rel="stylesheet" href="../css/add_story.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../views/header.php'; ?>
    
    <div class="container">
        <h2>Chỉnh sửa Truyện</h2>
        <form action="" method="post" enctype="multipart/form-data">
            <label>Tiêu đề:</label>
            <input type="text" name="title" value="<?php echo htmlspecialchars($story['title']); ?>" required>
            
            <label>Mô tả:</label>
            <textarea name="description" required><?php echo htmlspecialchars($story['description']); ?></textarea>
            
            <label>Ảnh bìa:</label>
            <input type="file" name="cover_image">
            <?php if ($story['cover_image']) : ?>
                <img src="../../uploads/<?php echo $story['cover_image']; ?>" width="100">
            <?php endif; ?>
            
            <label>Tác giả ID:</label>
            <input type="text" name="author_id" value="<?php echo htmlspecialchars($story['author_id']); ?>" required>
            
            <label>Thể loại:</label>
            <input type="text" name="type" value="<?php echo htmlspecialchars($story['type']); ?>" required>
            
            <label>Trạng thái:</label>
            <select name="status">
                <option value="ongoing" <?php if ($story['status'] == 'ongoing') echo 'selected'; ?>>Đang ra</option>
                <option value="completed" <?php if ($story['status'] == 'completed') echo 'selected'; ?>>Hoàn thành</option>
            </select>
            
            <button type="submit">Cập nhật</button>
        </form>
    </div>
    
    <?php include '../views/footer.php'; ?>
</body>
</html>
