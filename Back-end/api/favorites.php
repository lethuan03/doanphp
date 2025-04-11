<?php
header('Content-Type: application/json');
require_once "../config/database.php";

$db = new Database();
$conn = $db->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $user_id = $_GET['user_id'] ?? 0;
    
    if (!$user_id) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'User ID is required'
        ]);
        exit;
    }
    
    $query = "SELECT s.*, f.created_at as favorited_at 
             FROM Favorites f 
             JOIN Stories s ON f.story_id = s.story_id 
             WHERE f.user_id = :user_id 
             ORDER BY f.created_at DESC";
    
    try {
        $stmt = $conn->prepare($query);
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'] ?? null;
    $story_id = $_POST['story_id'] ?? null;
    
    if (!$user_id || !$story_id) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'User ID and Story ID are required'
        ]);
        exit;
    }
    
    $query = "INSERT INTO Favorites (user_id, story_id) VALUES (:user_id, :story_id)";
    try {
        $stmt = $conn->prepare($query);
        $stmt->execute(['user_id' => $user_id, 'story_id' => $story_id]);
        
        http_response_code(201); // Created
        echo json_encode([
            'status' => 'success',
            'message' => 'Story added to favorites'
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // Parse JSON input if sent in body
    $input = json_decode(file_get_contents('php://input'), true);
    $user_id = $input['user_id'] ?? $_GET['user_id'] ?? null;
    $story_id = $input['story_id'] ?? $_GET['story_id'] ?? null;
    
    if (!$user_id || !$story_id) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'User ID and Story ID are required'
        ]);
        exit;
    }
    
    $query = "DELETE FROM Favorites WHERE user_id = :user_id AND story_id = :story_id";
    try {
        $stmt = $conn->prepare($query);
        $stmt->execute(['user_id' => $user_id, 'story_id' => $story_id]);
        
        if ($stmt->rowCount() > 0) {
            http_response_code(200);
            echo json_encode([
                'status' => 'success',
                'message' => 'Story removed from favorites'
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