<?php
require_once '../../Back-end/config/database.php';
$database = new Database();
$pdo = $database->getConnection();

// Kiểm tra nếu có story_id
if (!isset($_GET['story_id'])) {
    die("Truyện không tồn tại.");
}

$story_id = $_GET['story_id'];

// Lấy thông tin truyện
$stmt = $pdo->prepare("SELECT title FROM Stories WHERE story_id = ?");
$stmt->execute([$story_id]);
$story = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$story) {
    die("Truyện không tồn tại.");
}

// Xóa chương
if (isset($_GET['delete_id'])) {
    $stmt = $pdo->prepare("DELETE FROM chapters WHERE chapter_id = ?");
    $stmt->execute([$_GET['delete_id']]);
    header("Location: manage_chapters.php?story_id=" . $story_id);
    exit();
}

// Lấy danh sách chương
$stmt = $pdo->prepare("SELECT * FROM chapters WHERE story_id = ? ORDER BY chapter_number ASC");
$stmt->execute([$story_id]);
$chapters = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Chương - <?= htmlspecialchars($story['title']) ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/header_styles.css">

    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #2d1b69;
            color: white;
        }
        .btn {
            padding: 5px 10px;
            margin: 2px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
        }
        .btn-edit {
            background-color: #ffcc00;
            color: black;
        }
        .btn-delete {
            background-color: red;
            color: white;
        }
        .btn-add {
            background-color: green;
            color: white;
        }
    </style>
</head>
<body>
    <?php include '../views/header.php'; ?>

    <h2 style="text-align: center;">Quản lý Chương: <?= htmlspecialchars($story['title']) ?></h2>
    <div style="text-align: center; margin-bottom: 10px;">
        <a href="add_chapter.php?story_id=<?= $story_id ?>" class="btn btn-add">Thêm Chương</a>
    </div>

    <table>
        <tr>
            <th>Số Chương</th>
            <th>Tiêu đề</th>
            <th>Hành động</th>
        </tr>
        <?php if (empty($chapters)) : ?>
            <tr><td colspan="3">Chưa có chương nào.</td></tr>
        <?php else : ?>
            <?php foreach ($chapters as $chapter) : ?>
                <tr>
                    <td>Chương <?= $chapter['chapter_number'] ?></td>
                    <td><?= htmlspecialchars($chapter['title']) ?></td>
                    <td>
                    <a href="edit_chapter.php?chapter_id=<?= $chapter['chapter_id'] ?>" class="btn btn-edit">Sửa</a>

                        <a href="?story_id=<?= $story_id ?>&delete_id=<?= $chapter['chapter_id'] ?>" 
                           class="btn btn-delete" onclick="return confirm('Bạn có chắc muốn xóa chương này?');">Xóa</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </table>

    <?php include '../views/footer.php'; ?>
</body>
</html>
