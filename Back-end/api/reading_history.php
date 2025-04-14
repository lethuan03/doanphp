<?php
header('Content-Type: application/json');
require_once "../config/database.php";

$database = new Database();
$pdo = $database->getConnection();

// Đọc dữ liệu từ yêu cầu
$input = json_decode(file_get_contents('php://input'), true);
$user_id = $input['user_id'] ?? null;
$chapter_id = $input['chapter_id'] ?? null;

if (!$user_id || !$chapter_id) {
    echo json_encode(['status' => 'error', 'message' => 'Thiếu user_id hoặc chapter_id']);
    exit;
}

try {
    // Kiểm tra xem bản ghi đã tồn tại chưa
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM reading_history WHERE user_id = ? AND chapter_id = ?");
    $stmt->execute([$user_id, $chapter_id]);
    $exists = $stmt->fetchColumn();

    if ($exists) {
        echo json_encode(['status' => 'success', 'message' => 'Bản ghi đã tồn tại']);
        exit;
    }

    // Thêm bản ghi mới vào reading_history
    $stmt = $pdo->prepare("INSERT INTO reading_history (user_id, chapter_id) VALUES (?, ?)");
    $stmt->execute([$user_id, $chapter_id]);

    echo json_encode(['status' => 'success', 'message' => 'Đã lưu lịch sử đọc']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>