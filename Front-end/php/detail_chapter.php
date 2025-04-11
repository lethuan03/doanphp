<?php
require_once '../../Back-end/config/database.php';

$database = new Database();
$pdo = $database->getConnection();

// Kiểm tra nếu không có ID hoặc ID không hợp lệ
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Chương không tồn tại.");
}

$chapter_id = (int)$_GET['id'];

// Truy vấn lấy thông tin chương
$stmt = $pdo->prepare("
    SELECT c.*, s.title AS story_title 
    FROM chapters c 
    JOIN stories s ON c.story_id = s.story_id 
    WHERE c.chapter_id = ?
");
$stmt->execute([$chapter_id]);
$chapter = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$chapter) {
    die("Chương không tồn tại.");
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($chapter['story_title'] . ' - ' . $chapter['title']); ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/header_styles.css">

</head>
<body>
    <?php include '../views/header.php'; ?>
    
    <div class="container">
        <h2><?php echo htmlspecialchars($chapter['story_title']); ?></h2>
        <h3>Chương <?php echo htmlspecialchars($chapter['chapter_number']); ?>: <?php echo htmlspecialchars($chapter['title']); ?></h3>
        <p><?php echo nl2br(htmlspecialchars($chapter['content'])); ?></p>
    </div>
    
    <?php include '../views/footer.php'; ?>
</body>
</html>
