<?php
require_once '../../Back-end/config/database.php';
$database = new Database();
$pdo = $database->getConnection();

// Xử lý xóa truyện
if (isset($_GET['delete_id'])) {
    $stmt = $pdo->prepare("DELETE FROM Stories WHERE story_id = ?");
    $stmt->execute([$_GET['delete_id']]);
    header("Location: manage_stories.php");
    exit();
}

// Lấy danh sách truyện
$stmt = $pdo->query("SELECT * FROM Stories");
$stories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý truyện</title>
    <link rel="stylesheet" href="../css/style.css">
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
        img {
            width: 120px;
            height: 150px;
            object-fit: cover;
            border-radius: 5px;
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
    </style>
</head>
<body>
    <h2 style="text-align: center;">Quản lý Truyện</h2>
    <div style="text-align: center; margin-bottom: 10px;">
        <a href="add_story.php" class="btn btn-add">Thêm Truyện</a>
    </div>
    <table>
        <tr>
            <th>Ảnh</th>
            <th>Tiêu đề</th>
            <th>Mô tả</th>
            <th>Hành động</th>
        </tr>
        <?php foreach ($stories as $story) : ?>
            <tr>
                <td>
                    <img src="http://localhost/doanphp/Back-end/<?= htmlspecialchars($story['cover_image']) ?>" 
                         alt="<?= htmlspecialchars($story['title']) ?>">
                </td>
                <td><?= htmlspecialchars($story['title']) ?></td>
                <td><?= nl2br(htmlspecialchars($story['description'])) ?></td>
                <td>
                    <a href="add_chapter.php?story_id=<?= $story['story_id'] ?>" class="btn btn-success btn-sm">Thêm chương</a>
                    <a href="edit_story.php?story_id=<?= $story['story_id'] ?>" class="btn btn-edit">Sửa</a>
                    <a href="?delete_id=<?= $story['story_id'] ?>" class="btn btn-delete" onclick="return confirm('Bạn có chắc muốn xóa truyện này?');">Xóa</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
