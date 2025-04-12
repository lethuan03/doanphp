<?php
require_once '../config/database.php';
require_once '../api/auth.php'; // File chứa verifyJWT và sendResponse

header("Content-Type: application/json");

// Kết nối database
$pdo = (new Database())->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

// Xác thực và kiểm tra quyền Admin
$decoded = verifyJWT($pdo);
$role_id = $decoded['role_id'];

if ($role_id != 2) {
    sendResponse(403, ["error" => "Chỉ Admin được phép thực hiện hành động này."]);
    exit;
}

switch ($method) {
    case 'GET':
        try {
            if (isset($_GET['author_id'])) {
                // Lấy thông tin tác giả theo ID
                $stmt = $pdo->prepare("SELECT user_id, username, email, avatar FROM Users WHERE user_id = ?");
                $stmt->execute([$_GET['author_id']]);
                $author = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($author) {
                    sendResponse(200, $author);
                } else {
                    sendResponse(404, ["error" => "Không tìm thấy tác giả."]);
                }
            } else {
                // Lấy danh sách tất cả tác giả
                $stmt = $pdo->query("SELECT user_id, username, email, avatar FROM Users");
                $authors = $stmt->fetchAll(PDO::FETCH_ASSOC);
                sendResponse(200, $authors);
            }
        } catch (PDOException $e) {
            error_log("Error in GET authors: " . $e->getMessage());
            sendResponse(500, ["error" => "Lỗi server", "details" => $e->getMessage()]);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);

        if (isset($data['username'], $data['email'], $data['password'])) {
            try {
                // Kiểm tra xem email hoặc username đã tồn tại chưa
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM Users WHERE email = ? OR username = ?");
                $stmt->execute([$data['email'], $data['username']]);
                if ($stmt->fetchColumn() > 0) {
                    sendResponse(400, ["error" => "Email hoặc username đã tồn tại."]);
                    break;
                }

                $hashed_password = password_hash($data['password'], PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("INSERT INTO Users (username, email, password, avatar, role_id) VALUES (?, ?, ?, ?, 3)");
                // Lưu ý: role_id = 3 (Author), không phải 2 (Admin) như code gốc
                $stmt->execute([$data['username'], $data['email'], $hashed_password, $data['avatar'] ?? null]);

                sendResponse(201, ["message" => "Tác giả đã được thêm"]);
            } catch (PDOException $e) {
                error_log("Error in POST author: " . $e->getMessage());
                sendResponse(500, ["error" => "Lỗi khi thêm tác giả", "details" => $e->getMessage()]);
            }
        } else {
            sendResponse(400, ["error" => "Thiếu dữ liệu"]);
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"), true);

        if (isset($data['author_id'], $data['username'], $data['email'])) {
            try {
                // Kiểm tra xem tác giả có tồn tại không
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM Users WHERE user_id = ?");
                $stmt->execute([$data['author_id']]);
                if ($stmt->fetchColumn() == 0) {
                    sendResponse(404, ["error" => "Tác giả không tồn tại."]);
                    break;
                }

                $stmt = $pdo->prepare("UPDATE Users SET username = ?, email = ?, avatar = ? WHERE user_id = ?");
                $stmt->execute([$data['username'], $data['email'], $data['avatar'] ?? null, $data['author_id']]);

                sendResponse(200, ["message" => "Cập nhật tác giả thành công"]);
            } catch (PDOException $e) {
                error_log("Error in PUT author: " . $e->getMessage());
                sendResponse(500, ["error" => "Lỗi khi cập nhật tác giả", "details" => $e->getMessage()]);
            }
        } else {
            sendResponse(400, ["error" => "Thiếu dữ liệu"]);
        }
        break;

        case 'DELETE':
            $data = json_decode(file_get_contents("php://input"), true);
        
            if (isset($data['author_id'])) {
                try {
                    // Kiểm tra xem tác giả có tồn tại không
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Users WHERE user_id = ?");
                    $stmt->execute([$data['author_id']]);
                    if ($stmt->fetchColumn() == 0) {
                        sendResponse(404, ["error" => "Tác giả không tồn tại."]);
                        break;
                    }
        
                    // Xóa các chương liên quan
                    $stmt = $pdo->prepare("DELETE FROM Chapters WHERE story_id IN (SELECT story_id FROM Stories WHERE user_id = ?)");
                    $stmt->execute([$data['author_id']]);
        
                    // Xóa các truyện liên quan
                    $stmt = $pdo->prepare("DELETE FROM Stories WHERE user_id = ?");
                    $stmt->execute([$data['author_id']]);
        
                    // Xóa tác giả
                    $stmt = $pdo->prepare("DELETE FROM Users WHERE user_id = ?");
                    $stmt->execute([$data['author_id']]);
        
                    sendResponse(200, ["message" => "Tác giả và nội dung liên quan đã bị xóa"]);
                } catch (PDOException $e) {
                    error_log("Error in DELETE author: " . $e->getMessage());
                    sendResponse(500, ["error" => "Lỗi khi xóa tác giả", "details" => $e->getMessage()]);
                }
            } else {
                sendResponse(400, ["error" => "Thiếu dữ liệu"]);
            }
            break;

    default:
        sendResponse(405, ["error" => "Phương thức không được hỗ trợ"]);
        break;
}
?>