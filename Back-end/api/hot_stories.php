<?php
header('Content-Type: application/json');
require_once "../config/database.php";

$db = new Database();
$conn = $db->getConnection();

if (!$conn) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection failed'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $limit = isset($_GET['limit']) && is_numeric($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $limit = min($limit, 50); // Giới hạn tối đa
        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * $limit;

        $query = "SELECT s.*, u.username as author_name,
                 (SELECT AVG(rating_value) FROM Ratings WHERE story_id = s.story_id) as avg_rating
                 FROM Stories s 
                 LEFT JOIN Users u ON s.author_id = u.user_id 
                 ORDER BY s.views DESC, avg_rating DESC 
                 LIMIT :limit OFFSET :offset";
        
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $hot_stories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        http_response_code(200);
        echo json_encode([
            'status' => 'success',
            'data' => $hot_stories,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => count($hot_stories)
            ]
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Parse JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    $title = $input['title'] ?? null;
    $content = $input['content'] ?? null;
    $author_id = $input['author_id'] ?? null;

    if (!$title || !$content || !$author_id) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Title, content, and author_id are required'
        ]);
        exit;
    }

    $query = "INSERT INTO Stories (title, content, author_id, views, created_at) 
              VALUES (:title, :content, :author_id, 0, NOW())";
    try {
        $stmt = $conn->prepare($query);
        $stmt->execute([
            'title' => $title,
            'content' => $content,
            'author_id' => $author_id
        ]);

        http_response_code(201);
        echo json_encode([
            'status' => 'success',
            'message' => 'Story created successfully',
            'data' => [
                'story_id' => $conn->lastInsertId(),
                'title' => $title,
                'content' => $content,
                'author_id' => $author_id
            ]
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // Parse JSON input or query parameters
    $input = json_decode(file_get_contents('php://input'), true);
    $story_id = $input['story_id'] ?? $_GET['story_id'] ?? null;

    if (!$story_id) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Story ID is required'
        ]);
        exit;
    }

    $query = "DELETE FROM Stories WHERE story_id = :story_id";
    try {
        $stmt = $conn->prepare($query);
        $stmt->execute(['story_id' => $story_id]);

        if ($stmt->rowCount() > 0) {
            http_response_code(200);
            echo json_encode([
                'status' => 'success',
                'message' => 'Story deleted successfully'
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                'status' => 'error',
                'message' => 'Story not found'
            ]);
        }
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