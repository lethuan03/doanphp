<?php
require_once '../../Back-end/config/database.php';
$database = new Database();
$pdo = $database->getConnection();

// Lấy story_id từ URL nếu có
$selected_story_id = $_GET['story_id'] ?? null;

// Lấy danh sách truyện
$stmt = $pdo->query("SELECT story_id, title FROM Stories");
$stories = $stmt->fetchAll();

// Nếu có story_id, lấy danh sách chương
$chapters = [];
if ($selected_story_id) {
    $stmt_chapters = $pdo->prepare("SELECT chapter_id, chapter_number, title, created_at FROM Chapters WHERE story_id = ? ORDER BY chapter_number ASC");
    $stmt_chapters->execute([$selected_story_id]);
    $chapters = $stmt_chapters->fetchAll();
}

// Xử lý xóa chương
if (isset($_GET['delete_id'])) {
    $chapter_id = $_GET['delete_id'];
    $stmt_delete = $pdo->prepare("DELETE FROM Chapters WHERE chapter_id = ?");
    $stmt_delete->execute([$chapter_id]);

    // Sau khi xóa, chuyển hướng lại trang với story_id để không mất dữ liệu
    header("Location: add_chapter.php?story_id=" . $selected_story_id);
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm chương mới</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/header_styles.css">
</head>
<body class="bg-light">
<?php include '../views/header.php'; ?>
<div class="container mt-5">
    <h2 class="mb-4">📚 Thêm Chương Mới</h2>

    <div id="message"></div>

    <form id="addChapterForm" class="card p-4 shadow">
        <div class="mb-3">
            <label for="story_id" class="form-label">Chọn truyện</label>
            <select id="story_id" name="story_id" class="form-select" required onchange="onStoryChange(this.value)">
                <option value="">-- Chọn truyện --</option>
                <?php foreach ($stories as $story): ?>
                    <option value="<?= $story['story_id'] ?>" <?= ($story['story_id'] == $selected_story_id) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($story['title']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="title" class="form-label">Tiêu đề chương</label>
            <input type="text" id="title" name="title" class="form-control" placeholder="Nhập tiêu đề chương" required>
        </div>

        <div class="mb-3">
            <label for="content" class="form-label">Nội dung chương</label>
            <textarea id="content" name="content" rows="8" class="form-control" placeholder="Nhập nội dung chương..." required></textarea>
        </div>

        <button type="submit" class="btn btn-primary">➕ Thêm chương</button>
    </form>

    <!-- Danh sách chương nếu có -->
    <?php if ($selected_story_id): ?>
        <div class="mt-5">
            <h4 class="text-primary">📖 Danh sách chương đã có</h4>
            <table class="table table-striped mt-3">
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Tiêu đề</th>
                        <th>Ngày tạo</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($chapters as $chapter): ?>
                        <tr>
                            <td>Chapter <?= $chapter['chapter_number'] ?></td>
                            <td><?= htmlspecialchars($chapter['title']) ?></td>
                            <td><?= date("d/m/Y", strtotime($chapter['created_at'])) ?></td>
                            <td>
                                <a href="edit_chapter.php?id=<?= $chapter['chapter_id'] ?>" class="btn btn-warning btn-sm">Sửa</a>
                                <a href="?delete_id=<?= $chapter['chapter_id'] ?>&story_id=<?= $selected_story_id ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc muốn xóa chương này?');">Xóa</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<script>
document.getElementById('addChapterForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const story_id = document.getElementById('story_id').value;
    const title = document.getElementById('title').value;
    const content = document.getElementById('content').value;

    fetch('../../Back-end/api/chapter.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ story_id, title, content })
    })
    .then(res => res.json())
    .then(data => {
        const messageDiv = document.getElementById('message');
        if (data.message) {
            messageDiv.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
            document.getElementById('addChapterForm').reset();
            setTimeout(() => {
                location.reload(); // reload để cập nhật danh sách chương
            }, 1000);
        } else {
            messageDiv.innerHTML = `<div class="alert alert-danger">${data.error || 'Có lỗi xảy ra.'}</div>`;
        }
    })
    .catch(err => {
        console.error(err);
        document.getElementById('message').innerHTML = `<div class="alert alert-danger">Lỗi kết nối server.</div>`;
    });
});

// Tự động chuyển trang khi chọn truyện mới
function onStoryChange(storyId) {
    if (storyId) {
        window.location.href = `add_chapter.php?story_id=${storyId}`;
    }
}
</script>
<?php include '../views/footer.php'; ?>
</body>
</html>
