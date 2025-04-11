<?php
require_once '../config/database.php';

header("Content-Type: application/json");

// Kết nối database
$database = new Database();
$pdo = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (isset($_GET['chapter_id'])) {
        $stmt = $pdo->prepare("SELECT title, content FROM Chapters WHERE chapter_id = ?");
        $stmt->execute([$_GET['chapter_id']]);
        $chapter = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($chapter) {
            echo json_encode($chapter);
        } else {
            echo json_encode(["error" => "Không tìm thấy chương."]);
        }
    } else {
        echo json_encode(["error" => "Thiếu chapter_id"]);
    }
}


// Nhận dữ liệu JSON từ client
$data = json_decode(file_get_contents("php://input"), true);

if ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data['story_id'], $data['title'], $data['content'])) {

        // Lấy số chương hiện tại của truyện
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM Chapters WHERE story_id = ?");
        $countStmt->execute([$data['story_id']]);
        $currentChapterCount = (int) $countStmt->fetchColumn();

        // Tăng chapter_number lên 1
        $newChapterNumber = $currentChapterCount + 1;

        // Thêm chương mới với chapter_number tự động
        $stmt = $pdo->prepare("INSERT INTO Chapters (story_id, title, content, chapter_number) VALUES (?, ?, ?, ?)");
        $stmt->execute([$data['story_id'], $data['title'], $data['content'], $newChapterNumber]);

        echo json_encode(["message" => "Thêm chương thành công!", "chapter_number" => $newChapterNumber]);
    } else {
        echo json_encode(["error" => "Thiếu dữ liệu!"]);
    }
}


if ($method === 'PUT') {
    if (isset($data['story_id'], $data['title'], $data['description'], $data['type'], $data['status'])) {
        $stmt = $pdo->prepare("UPDATE Stories SET title = ?, description = ?, type = ?, status = ? WHERE story_id = ?");
        $stmt->execute([$data['title'], $data['description'], $data['type'], $data['status'], $data['story_id']]);
        echo json_encode(["message" => "Cập nhật thành công!"]);
    } else {
        echo json_encode(["error" => "Thiếu dữ liệu!"]);
    }
}

if ($method === 'DELETE') {
    if (isset($data['story_id'])) {
        $stmt = $pdo->prepare("DELETE FROM Stories WHERE story_id = ?");
        $stmt->execute([$data['story_id']]);
        echo json_encode(["message" => "Xóa truyện thành công!"]);
    } else {
        echo json_encode(["error" => "Thiếu dữ liệu!"]);
    }
}
?>
