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
</head>

<body>
    <?php include '../views/header.php'; ?>
    <div class="container">
        <h2><?php echo htmlspecialchars($story['title']); ?></h2>
        <img src="http://localhost/doanphp/Back-end/<?php echo htmlspecialchars($story['cover_image']); ?>" alt="<?php echo htmlspecialchars($story['title']); ?>" width="200">

        <!-- Nội dung mô tả -->
        <div class="mt-5">
            
            <h5 class="text-primary border-bottom pb-2">📘 NỘI DUNG TRUYỆN</h5>
            <p class="mt-2"><?php echo nl2br(htmlspecialchars($story['description'])); ?></p>


            <!-- Thông tin bổ sung -->
            <div class="story-meta" style="margin-bottom: 30px; background: #f9f9f9; padding: 20px; border-radius: 6px;">


                <p><strong>Tác giả:</strong>
                    <?php
                    // Lấy tên tác giả nếu có bảng Users
                    $stmt_author = $pdo->prepare("SELECT username FROM Users WHERE user_id = ?");
                    $stmt_author->execute([$story['author_id']]);
                    $author = $stmt_author->fetchColumn();
                    echo htmlspecialchars($author ?: 'Không rõ');
                    ?>
                </p>

                <?php
                $story_id = $_GET['story_id'] ?? null;

                if ($story_id) {
                    $stmt_genres = $pdo->prepare("SELECT g.name 
                                  FROM Genres g 
                                  JOIN Story_Genres sg ON g.genre_id = sg.genre_id 
                                  WHERE sg.story_id = ?");
                    $stmt_genres->execute([$story_id]);
                    $genres = $stmt_genres->fetchAll(PDO::FETCH_COLUMN);

                    echo '<p><strong>Thể loại:</strong> ' .
                        (htmlspecialchars(implode(', ', $genres)) ?: 'Đang cập nhật') .
                        '</p>';
                } else {
                    echo '<p><strong>Thể loại:</strong> Không tìm thấy story_id</p>';
                }
                ?>


                <p><strong>Ngày đăng:</strong> <?php echo date("d/m/Y H:i", strtotime($story['created_at'])); ?></p>
            </div>

        </div>

        <!-- Danh sách chương -->
        <div class="mt-5">
            <h5 class="text-primary border-bottom pb-2">📖 DANH SÁCH CHƯƠNG</h5>
            <table class="table table-striped mt-3">
                <thead>
                    <tr>
                        <th>Số chương</th>
                        <th>Tiêu đề</th>
                        <th>Ngày cập nhật</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($chapters as $chapter): ?>
                        <tr>
                            <td>Chapter <?php echo htmlspecialchars($chapter['chapter_number']); ?></td>
                            <td>
                                <a href="detail_chapter.php?chapter_id=<?php echo $chapter['chapter_id']; ?>">
                                    <?php echo htmlspecialchars($chapter['title']); ?>
                                </a>
                            </td>
                            <td><?php echo date("d/m/Y", strtotime($chapter['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php include '../views/footer.php'; ?>
</body>

</html>