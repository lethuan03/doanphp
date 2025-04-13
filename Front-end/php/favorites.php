<?php
$user_id = 1; // Giả sử user_id cố định (sau này bạn có thể lấy từ session)
$apiUrl = "http://localhost/doanphp/Back-end/api/favorite.php?user_id=$user_id";

$response = @file_get_contents($apiUrl);
$data = json_decode($response, true);

$favorites = $data['data'] ?? [];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Truyện yêu thích</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include '../views/header.php'; ?>

<div class="favorite-container">
    <h2>Truyện yêu thích của bạn</h2>

    <?php if (count($favorites) === 0): ?>
        <p>Bạn chưa có truyện yêu thích nào.</p>
    <?php else: ?>
        <?php foreach ($favorites as $fav): ?>
            <div class="favorite-item" data-id="<?= $fav['favorite_id'] ?>">
                <img src="../uploads/<?= htmlspecialchars($fav['cover_image']) ?>" alt="Ảnh bìa">
                <div class="favorite-info">
                    <h3><a href="detail_story.php?story_id=<?= $fav['story_id'] ?>">
                        <?= htmlspecialchars($fav['title']) ?>
                    </a></h3>
                    <p>Thêm vào: <?= date('d/m/Y H:i', strtotime($fav['created_at'])) ?></p>
                </div>
                <button class="remove-btn" onclick="removeFavorite(<?= $fav['favorite_id'] ?>)">Xóa</button>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
function removeFavorite(favoriteId) {
    if (!confirm("Bạn có chắc muốn xóa truyện này khỏi yêu thích?")) return;

    fetch("http://localhost/doanphp/Back-end/api/favorite.php", {
        method: "DELETE",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ favorite_id: favoriteId })
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            document.querySelector(`[data-id="${favoriteId}"]`).remove();
        } else {
            alert(data.message || 'Lỗi khi xóa yêu thích');
        }
    })
    .catch(err => alert('Lỗi kết nối đến API'));
}
</script>

<?php include '../views/footer.php'; ?>
</body>
</html>
