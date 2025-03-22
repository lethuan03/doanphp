<?php
require_once '../config/database.php';

header("Content-Type: application/json");

// Kết nối database
$database = new Database();
$pdo = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (isset($_GET['story_id'])) {
        $stmt = $pdo->prepare("SELECT * FROM Stories WHERE story_id = ?");
        $stmt->execute([$_GET['story_id']]);
        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
    } else {
        $stmt = $pdo->query("SELECT * FROM Stories");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
}

// Nhận dữ liệu JSON từ client
$data = json_decode(file_get_contents("php://input"), true);

if ($method === 'POST') {
    if (isset($data['title'], $data['description'], $data['cover_image'], $data['author_id'], $data['type'], $data['status'])) {
        $stmt = $pdo->prepare("INSERT INTO Stories (title, description, cover_image, author_id, type, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$data['title'], $data['description'], $data['cover_image'], $data['author_id'], $data['type'], $data['status']]);
        echo json_encode(["message" => "Thêm truyện thành công!"]);
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
