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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $author_id = $_POST['author_id'];
    $type = $_POST['type'];
    $status = $_POST['status'];
    
    // Xử lý ảnh bìa
    $cover_image = "";
    if (!empty($_FILES['cover_image']['name'])) {
        $target_dir = "../../uploads/";
        $target_file = $target_dir . basename($_FILES['cover_image']['name']);
        move_uploaded_file($_FILES['cover_image']['tmp_name'], $target_file);
        $cover_image = basename($_FILES['cover_image']['name']);
    }

    // Thêm truyện vào database
    $stmt = $pdo->prepare("INSERT INTO Stories (title, description, author_id, type, status, cover_image) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$title, $description, $author_id, $type, $status, $cover_image]);
    
    // Lấy ID truyện vừa thêm
    $story_id = $pdo->lastInsertId();
    
    // Kiểm tra nếu có chương đầu tiên
    if (!empty($_POST['chapter_title']) && !empty($_POST['chapter_content'])) {
        $chapter_title = $_POST['chapter_title'];
        $chapter_content = $_POST['chapter_content'];

        // Thêm chương đầu tiên
        $stmt = $pdo->prepare("INSERT INTO chapters (story_id, chapter_number, title, content) VALUES (?, 1, ?, ?)");
        $stmt->execute([$story_id, $chapter_title, $chapter_content]);
    }
    
    echo "Thêm truyện thành công!";
    header("Location: manage_stories.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Truyện Mới</title>
    <link rel="stylesheet" href="../css/header_styles.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/add_story.css">

    
</head>
<body>
    <?php include '../views/header.php'; ?>
    
    <div class="container"> 
        <h2>Thêm Truyện Mới</h2>
        <form action="" method="post" enctype="multipart/form-data">
            <label>Tiêu đề:</label>
            <input type="text" name="title" required>
            
            <label>Mô tả:</label>
            <textarea name="description" required></textarea>
            
            <label>Ảnh bìa:</label>
            <input type="file" name="cover_image">
            
            <label>Tác giả ID:</label>
            <input type="text" name="author_id" required>
            
            <label>Thể loại:</label>
            <input type="text" name="type" required>
            
            <label>Trạng thái:</label>
            <select name="status">
                <option value="ongoing">Đang ra</option>
                <option value="completed">Hoàn thành</option>
            </select>
            
            <!-- Form thêm chương ngay khi thêm truyện -->
            <h3>Thêm Chương Đầu Tiên (Tùy chọn)</h3>
            <label>Tiêu đề chương:</label>
            <input type="text" name="chapter_title">
            
            <label>Nội dung chương:</label>
            <textarea name="chapter_content"></textarea>
            
            <button type="submit">Thêm Truyện</button>
        </form>
    </div>
    
    <?php include '../views/footer.php'; ?>
</body>
</html>
