<?php
require_once '../config/database.php';

header("Content-Type: application/json");

$database = new Database();
$pdo = $database->getConnection();

// Xác định loại top (mặc định: top tháng)
$type = isset($_GET['type']) ? $_GET['type'] : 'month';

// Xác định khoảng thời gian
switch ($type) {
    case 'day':
        $interval = '1 DAY';
        break;
    case 'week':
        $interval = '7 DAY';
        break;
    case 'month':
        $interval = '30 DAY';
        break;
    case 'year':
        $interval = '365 DAY';
        break;
    default:
        $interval = '30 DAY';
        break;
}

// 🔥 Câu truy vấn SQL để lấy top truyện theo lượt xem
$sql = "
    SELECT s.story_id, s.title, s.cover_image, COUNT(r.history_id) AS views
    FROM Stories s
    JOIN Chapters c ON s.story_id = c.story_id
    LEFT JOIN Reading_History r ON c.chapter_id = r.chapter_id
    WHERE r.read_at >= NOW() - INTERVAL $interval
    GROUP BY s.story_id
    ORDER BY views DESC
    LIMIT 10
";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$stories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Trả kết quả JSON
echo json_encode($stories);
?>
