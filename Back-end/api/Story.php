<?php
require_once '../config/database.php';
require_once '../api/auth.php';

header("Content-Type: application/json");

$database = new Database();
$pdo = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

function sendResponse($statusCode, $data) {
    http_response_code($statusCode);
    echo json_encode($data);
}

function checkStoryOwnership($pdo, $story_id, $user_id, $role_id) {
    if ($role_id == 2) {
        return true;
    }

    $stmt = $pdo->prepare("SELECT author_id FROM Stories WHERE story_id = ?");
    $stmt->execute([$story_id]);
    $story = $stmt->fetch(PDO::FETCH_ASSOC);

    return $story && $story['author_id'] == $user_id;
}

try {
    if ($method === 'GET') {
        if (isset($_GET['story_id'])) {
            $stmt = $pdo->prepare("SELECT * FROM Stories WHERE story_id = ?");
            $stmt->execute([$_GET['story_id']]);
            $story = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($story) {
                sendResponse(200, ["success" => true, "data" => $story]);
            } else {
                sendResponse(404, ["success" => false, "message" => "Không tìm thấy truyện"]);
            }
        } else {
            $stmt = $pdo->query("SELECT * FROM Stories");
            $stories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            sendResponse(200, ["success" => true, "data" => $stories]);
        }
    }

    if ($method === 'POST') {
        $decoded = verifyJWT($pdo);
        $user_id = $decoded['sub'];
        $role_id = $decoded['role_id'];

        if (!in_array($role_id, [2, 3])) {
            sendResponse(403, ["success" => false, "message" => "Chỉ Admin hoặc Author được thêm truyện"]);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);

        if (isset($data['title'], $data['description'], $data['author_id'], $data['type'])) {
            if ($role_id != 2 && $data['author_id'] != $user_id) {
                sendResponse(403, ["success" => false, "message" => "Bạn không có quyền thêm truyện cho author khác"]);
                return;
            }

            $cover_image = $data['cover_image'] ?? null;

            $stmt = $pdo->prepare("INSERT INTO Stories (title, description, author_id, type, cover_image) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $data['title'],
                $data['description'],
                $data['author_id'],
                $data['type'],
                $cover_image
            ]);

            sendResponse(201, ["success" => true, "message" => "Truyện đã được thêm"]);
        } else {
            sendResponse(400, ["success" => false, "message" => "Dữ liệu không hợp lệ, thiếu title, description, author_id hoặc type"]);
        }
    }

    if ($method === 'PUT') {
        $decoded = verifyJWT($pdo);
        $user_id = $decoded['sub'];
        $role_id = $decoded['role_id'];

        if (!in_array($role_id, [2, 3])) {
            sendResponse(403, ["success" => false, "message" => "Chỉ Admin hoặc Author được cập nhật truyện"]);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);

        if (isset($data['story_id'], $data['title'], $data['description'], $data['type'])) {
            if (!checkStoryOwnership($pdo, $data['story_id'], $user_id, $role_id)) {
                sendResponse(403, ["success" => false, "message" => "Bạn không có quyền cập nhật truyện này"]);
                return;
            }

            $stmt = $pdo->prepare("SELECT COUNT(*) FROM Stories WHERE story_id = ?");
            $stmt->execute([$data['story_id']]);
            if ($stmt->fetchColumn() == 0) {
                sendResponse(404, ["success" => false, "message" => "Truyện không tồn tại"]);
                return;
            }

            $cover_image = $data['cover_image'] ?? null;

            $stmt = $pdo->prepare("UPDATE Stories SET title = ?, description = ?, type = ?, cover_image = ? WHERE story_id = ?");
            $stmt->execute([$data['title'], $data['description'], $data['type'], $cover_image, $data['story_id']]);

            sendResponse(200, ["success" => true, "message" => "Cập nhật truyện thành công"]);
        } else {
            sendResponse(400, ["success" => false, "message" => "Dữ liệu không hợp lệ, thiếu story_id, title, description hoặc type"]);
        }
    }

    if ($method === 'DELETE') {
        $decoded = verifyJWT($pdo);
        $user_id = $decoded['sub'];
        $role_id = $decoded['role_id'];

        if (!in_array($role_id, [2, 3])) {
            sendResponse(403, ["success" => false, "message" => "Chỉ Admin hoặc Author được xóa truyện"]);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);

        if (isset($data['story_id'])) {
            if (!checkStoryOwnership($pdo, $data['story_id'], $user_id, $role_id)) {
                sendResponse(403, ["success" => false, "message" => "Bạn không có quyền xóa truyện này"]);
                return;
            }

            $stmt = $pdo->prepare("SELECT COUNT(*) FROM Stories WHERE story_id = ?");
            $stmt->execute([$data['story_id']]);
            if ($stmt->fetchColumn() == 0) {
                sendResponse(404, ["success" => false, "message" => "Truyện không tồn tại"]);
                return;
            }

            $stmt = $pdo->prepare("DELETE FROM Stories WHERE story_id = ?");
            $stmt->execute([$data['story_id']]);

            sendResponse(200, ["success" => true, "message" => "Truyện đã bị xóa"]);
        } else {
            sendResponse(400, ["success" => false, "message" => "Thiếu story_id"]);
        }
    }
} catch (Exception $e) {
    error_log("Error in stories.php: " . $e->getMessage());
    sendResponse(500, ["success" => false, "message" => "Lỗi server: " . $e->getMessage()]);
}
?>