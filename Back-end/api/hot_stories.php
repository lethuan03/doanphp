<?php
header('Content-Type: application/json');
require_once "../config/database.php";

$db = new Database();
$conn = $db->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $query = "SELECT s.*, u.username as author_name,
                 (SELECT AVG(rating_value) FROM Ratings WHERE story_id = s.story_id) as avg_rating
                 FROM Stories s 
                 LEFT JOIN Users u ON s.author_id = u.user_id 
                 ORDER BY s.views DESC, avg_rating DESC 
                 LIMIT 10";
        
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $hot_stories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        http_response_code(200);
        echo json_encode([
            'status' => 'success',
            'data' => $hot_stories
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Method not allowed'
    ]);
}