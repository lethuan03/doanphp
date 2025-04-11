<?php
require_once '../../Back-end/config/database.php';
$database = new Database();
$pdo = $database->getConnection();

// Lấy danh sách truyện
$stmt = $pdo->query("SELECT story_id, title FROM Stories");
$stories = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm chương mới</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h2 class="mb-4">📚 Thêm Chương Mới</h2>

    <div id="message"></div>

    <form id="addChapterForm" class="card p-4 shadow">
        <div class="mb-3">
            <label for="story_id" class="form-label">Chọn truyện</label>
            <select id="story_id" name="story_id" class="form-select" required>
                <?php foreach ($stories as $story): ?>
                    <option value="<?= $story['story_id'] ?>"><?= htmlspecialchars($story['title']) ?></option>
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
        } else {
            messageDiv.innerHTML = `<div class="alert alert-danger">${data.error || 'Có lỗi xảy ra.'}</div>`;
        }
    })
    .catch(err => {
        console.error(err);
        document.getElementById('message').innerHTML = `<div class="alert alert-danger">Lỗi kết nối server.</div>`;
    });
});
</script>

</body>
</html>
