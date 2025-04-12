<?php
header('Content-Type: application/json');
require_once "../config/database.php";

$database = new Database();
$pdo = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $keyword = $_GET['keyword'] ?? '';

    if (empty($keyword)) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Keyword is required'
        ]);
        exit;
    }

    $query = "SELECT story_id, title, description, cover_image, author_id, type, status, views, created_at, updated_at
              FROM Stories
              WHERE title LIKE :keyword
              ORDER BY created_at DESC";

    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute(['keyword' => '%' . $keyword . '%']);
        $stories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        http_response_code(200);
        echo json_encode([
            'status' => 'success',
            'data' => $stories
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
}
