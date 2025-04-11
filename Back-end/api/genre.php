<?php
header("Content-Type: application/json");
require_once "../config/database.php";
require_once "../utils/auth.php";

// Kết nối database
$database = new Database();
$pdo = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            // Xem thể loại: Công khai, không yêu cầu đăng nhập
            if (isset($_GET['genre_id'])) {
                // Lấy thể loại theo ID
                $stmt = $pdo->prepare("SELECT * FROM Genres WHERE genre_id = ?");
                $stmt->execute([$_GET['genre_id']]);
                $genre = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($genre) {
                    sendResponse(200, ["success" => true, "data" => $genre]);
                } else {
                    sendResponse(404, ["success" => false, "message" => "Không tìm thấy thể loại"]);
                }
            } else {
                // Lấy tất cả thể loại
                $stmt = $pdo->query("SELECT * FROM Genres");
                $genres = $stmt->fetchAll(PDO::FETCH_ASSOC);
                sendResponse(200, ["success" => true, "data" => $genres]);
            }
            break;

        case 'POST':
            // Thêm thể loại: Chỉ Admin được phép
            $decoded = verifyJWT($pdo);
            $role_id = $decoded['role_id'];

            if ($role_id != 2) { // Chỉ Admin
                sendResponse(403, ["success" => false, "message" => "Chỉ Admin được phép thêm thể loại"]);
                break;
            }

            $data = json_decode(file_get_contents("php://input"), true);

            if (!isset($data['name']) || empty(trim($data['name']))) {
                sendResponse(400, ["success" => false, "message" => "Tên thể loại không hợp lệ"]);
                break;
            }

            // Kiểm tra xem tên thể loại đã tồn tại chưa
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM Genres WHERE name = ?");
            $stmt->execute([$data['name']]);
            if ($stmt->fetchColumn() > 0) {
                sendResponse(400, ["success" => false, "message" => "Thể loại đã tồn tại"]);
                break;
            }

            $stmt = $pdo->prepare("INSERT INTO Genres (name) VALUES (?)");
            $stmt->execute([$data['name']]);

            sendResponse(201, ["success" => true, "message" => "Thể loại đã được thêm"]);
            break;

        case 'PUT':
            // Cập nhật thể loại: Chỉ Admin được phép
            $decoded = verifyJWT($pdo);
            $role_id = $decoded['role_id'];

            if ($role_id != 2) {
                sendResponse(403, ["success" => false, "message" => "Chỉ Admin được phép cập nhật thể loại"]);
                break;
            }

            $data = json_decode(file_get_contents("php://input"), true);

            if (!isset($data['genre_id'], $data['name']) || empty(trim($data['name']))) {
                sendResponse(400, ["success" => false, "message" => "Dữ liệu không hợp lệ"]);
                break;
            }

            // Kiểm tra xem thể loại có tồn tại không
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM Genres WHERE genre_id = ?");
            $stmt->execute([$data['genre_id']]);
            if ($stmt->fetchColumn() == 0) {
                sendResponse(404, ["success" => false, "message" => "Thể loại không tồn tại"]);
                break;
            }

            // Kiểm tra xem tên thể loại mới đã tồn tại chưa
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM Genres WHERE name = ? AND genre_id != ?");
            $stmt->execute([$data['name'], $data['genre_id']]);
            if ($stmt->fetchColumn() > 0) {
                sendResponse(400, ["success" => false, "message" => "Tên thể loại đã tồn tại"]);
                break;
            }

            $stmt = $pdo->prepare("UPDATE Genres SET name = ? WHERE genre_id = ?");
            $stmt->execute([$data['name'], $data['genre_id']]);

            sendResponse(200, ["success" => true, "message" => "Cập nhật thể loại thành công"]);
            break;

        case 'DELETE':
            // Xóa thể loại: Chỉ Admin được phép
            $decoded = verifyJWT($pdo);
            $role_id = $decoded['role_id'];

            if ($role_id != 2) {
                sendResponse(403, ["success" => false, "message" => "Chỉ Admin được phép xóa thể loại"]);
                break;
            }

            $data = json_decode(file_get_contents("php://input"), true);

            if (!isset($data['genre_id'])) {
                sendResponse(400, ["success" => false, "message" => "Thiếu ID thể loại"]);
                break;
            }

            // Kiểm tra xem thể loại có tồn tại không
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM Genres WHERE genre_id = ?");
            $stmt->execute([$data['genre_id']]);
            if ($stmt->fetchColumn() == 0) {
                sendResponse(404, ["success" => false, "message" => "Thể loại không tồn tại"]);
                break;
            }

            // Kiểm tra xem thể loại có truyện liên quan không
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM Story_Genres WHERE genre_id = ?");
            $stmt->execute([$data['genre_id']]);
            if ($stmt->fetchColumn() > 0) {
                sendResponse(400, ["success" => false, "message" => "Không thể xóa thể loại vì có truyện liên quan"]);
                break;
            }

            $stmt = $pdo->prepare("DELETE FROM Genres WHERE genre_id = ?");
            $stmt->execute([$data['genre_id']]);

            sendResponse(200, ["success" => true, "message" => "Thể loại đã bị xóa"]);
            break;

        default:
            sendResponse(405, ["success" => false, "message" => "Phương thức không được hỗ trợ"]);
            break;
    }
} catch (Exception $e) {
    error_log("Error in genres.php: " . $e->getMessage());
    sendResponse(500, ["success" => false, "message" => "Lỗi server: " . $e->getMessage()]);
}
?>