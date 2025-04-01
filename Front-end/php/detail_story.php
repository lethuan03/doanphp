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

$stmt_chapters = $pdo->prepare("SELECT * FROM Chapters WHERE story_id = ? ORDER BY chapter_number DESC");
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
    <link rel="stylesheet" href="../css/detail_story.css">
</head>

<body>
    <?php include '../views/header.php'; ?>

    <div class="container">
        <div class="story-detail">
            <div class="story-info">
                <img src="http://localhost/doanphp/uploads/<?php echo htmlspecialchars($story['cover_image']); ?>" alt="<?php echo htmlspecialchars($story['title']); ?>">
                <div class="story-meta">
                    <h2><?php echo htmlspecialchars($story['title']); ?></h2>
                    <p><strong>Tác giả:</strong> <?php echo htmlspecialchars($story['author'] ?? "Đang cập nhật"); ?></p>
                    <p><strong>Tình trạng:</strong> <?php echo htmlspecialchars($story['status'] ?? "Đang cập nhật"); ?></p>
                    <p><strong>Thể loại:</strong> <a href="#"><?php echo htmlspecialchars($story['genre'] ?? "Chưa rõ"); ?></a></p>
                    <p><strong>Xếp hạng:</strong> ⭐ 4.5/5 - 25 lượt đánh giá</p>
                    <div class="buttons">
                        <button class="follow-btn">Theo dõi</button>
                        <button class="read-btn">Đọc từ đầu</button>
                        <button class="read-btn">Đọc mới nhất</button>
                    </div>
                </div>
            </div>

            <div class="story-description">
                <h3>Nội dung truyện</h3>
                <p><?php echo nl2br(htmlspecialchars($story['description'])); ?></p>
            </div>

            <div class="chapter-list">
                <h3>Danh sách chương</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Số chương</th>
                            <th>Cập nhật</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($chapters as $chapter) : ?>
                            <tr>
                                <td><a href="detail_chapter.php?id=<?php echo $chapter['chapter_id']; ?>">Chapter <?php echo htmlspecialchars($chapter['chapter_number']); ?></a></td>
                                <td> <?php echo rand(5, 60); ?> phút trước</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php include '../views/footer.php'; ?>
</body>

</html>
