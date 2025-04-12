<?php
header('Content-Type: application/json');
require_once('../config/database.php');

$database = new Database();
$pdo = $database->getConnection();

// GET COMMENT BY CHAPTER_ID
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $chapter_id = $_GET['chapter_id'] ?? null;

    if (!$chapter_id) {
        echo json_encode(['error' => 'Thiếu chapter_id']);
        exit;
    }

    $stmt = $pdo->prepare("
        SELECT c.comment_id, c.content, c.created_at, u.username, u.avatar
        FROM comments c
        JOIN users u ON c.user_id = u.user_id
        WHERE c.chapter_id = ?
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$chapter_id]);
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($comments);
    exit;
}

// POST COMMENT
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $user_id = $data['user_id'] ?? null;
    $chapter_id = $data['chapter_id'] ?? null;
    $content = trim($data['content'] ?? '');

    if (!$user_id || !$chapter_id || $content === '') {
        echo json_encode(['error' => 'Thiếu dữ liệu hoặc nội dung rỗng']);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO comments (user_id, chapter_id, content) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $chapter_id, $content]);

    echo json_encode(['status' => 'success', 'message' => 'Bình luận đã được thêm']);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Phương thức không được hỗ trợ']);
