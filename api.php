<?php
header("Content-Type: application/json");
require 'db_connect.php';

$response = ["status" => "error", "message" => "Invalid request"];
$action = $_GET['action'] ?? '';

try {
    // ✅ Lấy danh sách truyện
    if ($action == 'getStories') {
        $result = $conn->query("SELECT * FROM stories");
        if ($result && $result->num_rows > 0) {
            $response = ["status" => "success", "stories" => $result->fetch_all(MYSQLI_ASSOC)];
        } else {
            http_response_code(404);
            $response = ["status" => "error", "message" => "Không có truyện nào"];
        }

    // ✅ Thêm truyện mới
    } elseif ($action == 'addStory' && $_SERVER['REQUEST_METHOD'] == 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!empty($data['title']) && !empty($data['content'])) {
            $title = htmlspecialchars(strip_tags($data['title']));
            $content = htmlspecialchars(strip_tags($data['content']));

            $stmt = $conn->prepare("INSERT INTO stories (title, content) VALUES (?, ?)");
            $stmt->bind_param("ss", $title, $content);

            if ($stmt->execute()) {
                http_response_code(201);
                $response = ["status" => "success", "message" => "Truyện đã được thêm"];
            } else {
                http_response_code(500);
                $response = ["status" => "error", "message" => "Lỗi khi thêm truyện"];
            }
        } else {
            http_response_code(400);
            $response = ["status" => "error", "message" => "Thiếu tiêu đề hoặc nội dung"];
        }

    // ✅ Lấy bình luận theo truyện
    } elseif ($action == 'getComments' && isset($_GET['story_id'])) {
        $story_id = intval($_GET['story_id']);
        $stmt = $conn->prepare("SELECT * FROM comments WHERE story_id = ? ORDER BY id DESC");
        $stmt->bind_param("i", $story_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $response = ["status" => "success", "comments" => $result->fetch_all(MYSQLI_ASSOC)];
        } else {
            http_response_code(404);
            $response = ["status" => "error", "message" => "Chưa có bình luận nào"];
        }

    // ✅ Thêm bình luận
    } elseif ($action == 'addComment' && $_SERVER['REQUEST_METHOD'] == 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!empty($data['story_id']) && !empty($data['username']) && !empty($data['comment'])) {
            $story_id = intval($data['story_id']);
            $username = htmlspecialchars(strip_tags($data['username']));
            $comment = htmlspecialchars(strip_tags($data['comment']));

            $stmt = $conn->prepare("INSERT INTO comments (story_id, username, comment) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $story_id, $username, $comment);

            if ($stmt->execute()) {
                http_response_code(201);
                $response = ["status" => "success", "message" => "Bình luận đã được thêm"];
            } else {
                http_response_code(500);
                $response = ["status" => "error", "message" => "Lỗi khi thêm bình luận"];
            }
        } else {
            http_response_code(400);
            $response = ["status" => "error", "message" => "Thiếu thông tin cần thiết"];
        }
    }

} catch (Exception $e) {
    http_response_code(500);
    $response = ["status" => "error", "message" => "Lỗi server: " . $e->getMessage()];
}

$conn->close();
echo json_encode($response);
?>
