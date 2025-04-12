<?php
require_once '../config/database.php';
require_once '../utils/auth.php';

header("Content-Type: application/json");

$database = new Database();
$pdo = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

function checkStoryOwnership($pdo, $story_id, $user_id, $role_id) {
    if ($role_id == 2) {
        return true;
    }

    $stmt = $pdo->prepare("SELECT author_id FROM Stories WHERE story_id = ?");
    $stmt->execute([$story_id]);
    $story = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$story) {
        return false;
    }

    return $story['author_id'] == $user_id;
}

function sendResponse($statusCode, $data) {
    http_response_code($statusCode);
    echo json_encode($data);
}

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['chapter_id'])) {
                $stmt = $pdo->prepare("SELECT title, content, chapter_number, story_id FROM Chapters WHERE chapter_id = ?");
                $stmt->execute([$_GET['chapter_id']]);
                $chapter = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($chapter) {
                    sendResponse(200, ["success" => true, "data" => $chapter]);
                } else {
                    sendResponse(404, ["success" => false, "message" => "Không tìm thấy chương"]);
                }
            } else {
                sendResponse(400, ["success" => false, "message" => "Thiếu chapter_id"]);
            }
            break;

        case 'POST':
            $decoded = verifyJWT($pdo);
            $user_id = $decoded['sub'];
            $role_id = $decoded['role_id'];

            if (!in_array($role_id, [2, 3])) {
                sendResponse(403, ["success" => false, "message" => "Chỉ Admin hoặc Author được thêm chương"]);
                break;
            }

            $data = json_decode(file_get_contents("php://input"), true);
            if (!isset($data['story_id'], $data['title'], $data['content'])) {
                sendResponse(400, ["success" => false, "message" => "Thiếu dữ liệu: story_id, title, content là bắt buộc"]);
                break;
            }

            if (!checkStoryOwnership($pdo, $data['story_id'], $user_id, $role_id)) {
                sendResponse(403, ["success" => false, "message" => "Bạn không có quyền thêm chương cho truyện này"]);
                break;
            }

            $stmt = $pdo->prepare("SELECT COUNT(*) FROM Stories WHERE story_id = ?");
            $stmt->execute([$data['story_id']]);
            if ($stmt->fetchColumn() == 0) {
                sendResponse(404, ["success" => false, "message" => "Truyện không tồn tại"]);
                break;
            }

            $countStmt = $pdo->prepare("SELECT COUNT(*) FROM Chapters WHERE story_id = ?");
            $countStmt->execute([$data['story_id']]);
            $newChapterNumber = (int) $countStmt->fetchColumn() + 1;

            $stmt = $pdo->prepare("INSERT INTO Chapters (story_id, title, content, chapter_number) VALUES (?, ?, ?, ?)");
            $stmt->execute([$data['story_id'], $data['title'], $data['content'], $newChapterNumber]);

            sendResponse(201, ["success" => true, "message" => "Thêm chương thành công", "chapter_number" => $newChapterNumber]);
            break;

        case 'PUT':
            $decoded = verifyJWT($pdo);
            $user_id = $decoded['sub'];
            $role_id = $decoded['role_id'];

            if (!in_array($role_id, [2, 3])) {
                sendResponse(403, ["success" => false, "message" => "Chỉ Admin hoặc Author được cập nhật chương"]);
                break;
            }

            $data = json_decode(file_get_contents("php://input"), true);
            if (!isset($data['chapter_id'], $data['title'], $data['content'])) {
                sendResponse(400, ["success" => false, "message" => "Thiếu dữ liệu: chapter_id, title, content là bắt buộc"]);
                break;
            }

            $stmt = $pdo->prepare("SELECT story_id FROM Chapters WHERE chapter_id = ?");
            $stmt->execute([$data['chapter_id']]);
            $chapter = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$chapter) {
                sendResponse(404, ["success" => false, "message" => "Chương không tồn tại"]);
                break;
            }

            if (!checkStoryOwnership($pdo, $chapter['story_id'], $user_id, $role_id)) {
                sendResponse(403, ["success" => false, "message" => "Bạn không có quyền cập nhật chương này"]);
                break;
            }

            $stmt = $pdo->prepare("UPDATE Chapters SET title = ?, content = ? WHERE chapter_id = ?");
            $stmt->execute([$data['title'], $data['content'], $data['chapter_id']]);

            sendResponse(200, ["success" => true, "message" => "Cập nhật chương thành công"]);
            break;

        case 'DELETE':
            $decoded = verifyJWT($pdo);
            $user_id = $decoded['sub'];
            $role_id = $decoded['role_id'];

            if (!in_array($role_id, [2, 3])) {
                sendResponse(403, ["success" => false, "message" => "Chỉ Admin hoặc Author được xóa chương"]);
                break;
            }

            $data = json_decode(file_get_contents("php://input"), true);
            if (!isset($data['chapter_id'])) {
                sendResponse(400, ["success" => false, "message" => "Thiếu chapter_id"]);
                break;
            }

            $stmt = $pdo->prepare("SELECT story_id FROM Chapters WHERE chapter_id = ?");
            $stmt->execute([$data['chapter_id']]);
            $chapter = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$chapter) {
                sendResponse(404, ["success" => false, "message" => "Chương không tồn tại"]);
                break;
            }

            if (!checkStoryOwnership($pdo, $chapter['story_id'], $user_id, $role_id)) {
                sendResponse(403, ["success" => false, "message" => "Bạn không có quyền xóa chương này"]);
                break;
            }

            $stmt = $pdo->prepare("DELETE FROM Chapters WHERE chapter_id = ?");
            $stmt->execute([$data['chapter_id']]);

            sendResponse(200, ["success" => true, "message" => "Xóa chương thành công"]);
            break;

        default:
            sendResponse(405, ["success" => false, "message" => "Phương thức không được hỗ trợ"]);
            break;
    }
} catch (Exception $e) {
    error_log("Error in chapters.php: " . $e->getMessage());
    sendResponse(500, ["success" => false, "message" => "Lỗi server: " . $e->getMessage()]);
}
?>