<?php
require_once '../../Back-end/config/database.php';

$database = new Database();
$pdo = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $story_id = $_POST['story_id'];
    $chapter_title = $_POST['chapter_title'];
    $chapter_content = $_POST['chapter_content'];

    // Lấy số chương mới nhất để tăng chương
    $query = $pdo->prepare("SELECT MAX(chapter_number) AS last_chapter FROM chapters WHERE story_id = ?");
    $query->execute([$story_id]);
    $lastChapter = $query->fetch(PDO::FETCH_ASSOC);
    $newChapterNumber = $lastChapter['last_chapter'] ? $lastChapter['last_chapter'] + 1 : 1;

    // Thêm chương mới vào database
    $stmt = $pdo->prepare("INSERT INTO chapters (story_id, chapter_number, title, content) VALUES (?, ?, ?, ?)");
    $result = $stmt->execute([$story_id, $newChapterNumber, $chapter_title, $chapter_content]);

    if ($result) {
        echo "Thêm chương thành công!";
        header("Location: edit_story.php?story_id=$story_id");
        exit();
    } else {
        echo "Lỗi khi thêm chương!";
    }
} else {
    echo "Yêu cầu không hợp lệ!";
}
?>
