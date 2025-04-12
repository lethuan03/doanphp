<?php
require_once '../../Back-end/config/database.php';

$database = new Database();
$pdo = $database->getConnection();

$story_id = $_GET['story_id'];

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
        .audio-player {
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <?php include '../views/header.php'; ?>
    <div class="container">
        <h2><?php echo htmlspecialchars($story['title']); ?></h2>
        <img src="http://localhost/doanphp/Back-end/<?php echo htmlspecialchars($story['cover_image']); ?>" alt="<?php echo htmlspecialchars($story['title']); ?>" width="200">
        <p><?php echo nl2br(htmlspecialchars($story['description'])); ?></p>

        <!-- Hiển thị audio player nếu có file audio -->
        <?php if (!empty($story['audio_file'])) : ?>
            <div class="audio-player">
                <h4>Nghe truyện</h4>
                <audio controls>
                    <source src="http://localhost/doanphp/Back-end/<?php echo htmlspecialchars($story['audio_file']); ?>" type="audio/mpeg">
                    Trình duyệt của bạn không hỗ trợ phát audio.
                </audio>
            </div>
        <?php endif; ?>

        <!-- Danh sách chương -->
        <h3>Danh sách chương</h3>
        <?php if (!empty($chapters)) : ?>
            <ul>
                <?php foreach ($chapters as $chapter) : ?>
                    <li>
                        <a href="detail_chapter.php?chapter_id=<?php echo $chapter['chapter_id']; ?>">
                            <?php echo "Chương " . htmlspecialchars($chapter['chapter_number']) . ": " . htmlspecialchars($chapter['title']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else : ?>
            <p>Chưa có chương nào.</p>
        <?php endif; ?>
    </div>
    <?php include '../views/footer.php'; ?>
</body>
</html>