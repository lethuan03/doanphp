<?php
header('Content-Type: application/json');
require_once "../config/database.php";

$database = new Database();
$pdo = $database->getConnection();

// GET: Lấy danh sách yêu thích của 1 user
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $user_id = $_GET['user_id'] ?? null;

    if (!$user_id) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'User ID is required'
        ]);
        exit;
    }

    $query = "SELECT f.*, s.title, s.cover_image 
              FROM Favorites f
              JOIN Stories s ON f.story_id = s.story_id
              WHERE f.user_id = :user_id
              ORDER BY f.created_at DESC";

    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute(['user_id' => $user_id]);
        $favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);

        http_response_code(200);
        echo json_encode([
            'status' => 'success',
            'data' => $favorites
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
}

// POST: Thêm truyện vào danh sách yêu thích (JSON)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    $user_id = $input['user_id'] ?? null;
    $story_id = $input['story_id'] ?? null;

    if (!$user_id || !$story_id) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'User ID and Story ID are required'
        ]);
        exit;
    }

    $query = "INSERT INTO Favorites (user_id, story_id, created_at) 
              VALUES (:user_id, :story_id, NOW())";

    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            'user_id' => $user_id,
            'story_id' => $story_id
        ]);

        http_response_code(201);
        echo json_encode([
            'status' => 'success',
            'message' => 'Added to favorites'
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
}

// DELETE: Xóa yêu thích theo favorite_id (JSON)
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $input = json_decode(file_get_contents("php://input"), true);
    $favorite_id = $input['favorite_id'] ?? null;

    if (!$favorite_id) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Favorite ID is required'
        ]);
        exit;
    }

    $query = "DELETE FROM Favorites WHERE favorite_id = :favorite_id";

    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute(['favorite_id' => $favorite_id]);

        if ($stmt->rowCount() > 0) {
            http_response_code(200);
            echo json_encode([
                'status' => 'success',
                'message' => 'Favorite deleted'
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                'status' => 'error',
                'message' => 'Favorite not found'
            ]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
}
