<?php
require_once '../config/database.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$database = new Database();
$pdo = $database->getConnection();

// Lấy type từ query string, mặc định là 'month'
$type = isset($_GET['type']) ? $_GET['type'] : 'month';

// Map type => khoảng thời gian
$intervals = [
    'day' => '1 DAY',
    'week' => '7 DAY',
    'month' => '30 DAY',
    'year' => '365 DAY'
];

$interval = isset($intervals[$type]) ? $intervals[$type] : '30 DAY';

try {
    // Truy vấn lấy top 5 truyện đọc nhiều nhất theo khoảng thời gian
    $sql = "
        SELECT 
            s.story_id, 
            s.title, 
            s.cover_image, 
            COUNT(r.history_id) AS views
        FROM Stories s
        JOIN Chapters c ON s.story_id = c.story_id
        LEFT JOIN Reading_History r 
            ON c.chapter_id = r.chapter_id 
            AND r.read_at >= NOW() - INTERVAL $interval
        GROUP BY s.story_id
        ORDER BY views DESC
        LIMIT 5
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $stories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => "success",
        "type" => $type,
        "data" => $stories
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Query failed: " . $e->getMessage()
    ]);
}
?>
