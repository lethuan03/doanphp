<?php
require_once '../config/database.php';
require_once '../utils/auth.php'; // File chứa hàm verifyJWT và các hàm tiện ích

header("Content-Type: application/json");

// Kết nối database
$database = new Database();
$pdo = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

// Hàm kiểm tra quyền sở hữu truyện
function checkStoryOwnership($pdo, $story_id, $user_id, $role_id) {
    if ($role_id == 2) { // Admin có quyền truy cập mọi truyện
        return true;
    }

    $stmt = $pdo->prepare("SELECT user_id FROM Stories WHERE story_id = ?");
    $stmt->execute([$story_id]);
    $story = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$story) {
        return false; // Truyện không tồn tại
    }

    return $story['user_id'] == $user_id; // Chỉ người tạo truyện được phép
}

switch ($method) {
    case 'GET':
        // Xem chương: Không cần đăng nhập (công khai) hoặc chỉ người dùng đã đăng nhập
        if (isset($_GET['chapter_id'])) {
            $stmt = $pdo->prepare("SELECT title, content FROM Chapters WHERE chapter_id = ?");
            $stmt->execute([$_GET['chapter_id']]);
            $chapter = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($chapter) {
                echo json_encode($chapter);
            } else {
                http_response_code(404);
                echo json_encode(["error" => "Không tìm thấy chương."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["error" => "Thiếu chapter_id"]);
        }
        break;

    case 'POST':
        // Thêm chương: Chỉ Author của truyện hoặc Admin được phép
        $decoded = verifyJWT($pdo);
        $user_id = $decoded['sub'];
        $role_id = $decoded['role_id'];

        // Kiểm tra quyền Author hoặc Admin
        if (!in_array($role_id, [2, 3])) {
            http_response_code(403);
            echo json_encode(["error" => "Chỉ Admin hoặc Author được thêm chương."]);
            break;
        }

        $data = json_decode(file_get_contents("php://input"), true);
        if (isset($data['story_id'], $data['title'], $data['content'])) {
            // Kiểm tra quyền sở hữu truyện
            if (!checkStoryOwnership($pdo, $data['story_id'], $user_id, $role_id)) {
                http_response_code(403);
                echo json_encode(["error" => "Bạn không có quyền thêm chương cho truyện này."]);
                break;
            }

            try {
                // Lấy số chương hiện tại của truyện
                $countStmt = $pdo->prepare("SELECT COUNT(*) FROM Chapters WHERE story_id = ?");
                $countStmt->execute([$data['story_id']]);
                $currentChapterCount = (int) $countStmt->fetchColumn();

                // Tăng chapter_number lên 1
                $newChapterNumber = $currentChapterCount + 1;

                // Thêm chương mới với chapter_number tự động
                $stmt = $pdo->prepare("INSERT INTO Chapters (story_id, title, content, chapter_number) VALUES (?, ?, ?, ?)");
                $stmt->execute([$data['story_id'], $data['title'], $data['content'], $newChapterNumber]);

                http_response_code(201);
                echo json_encode(["message" => "Thêm chương thành công!", "chapter_number" => $newChapterNumber]);
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(["error" => "Lỗi server", "details" => $e->getMessage()]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["error" => "Thiếu dữ liệu!"]);
        }
        break;

    case 'PUT':
        // Cập nhật truyện: Chỉ Author của truyện hoặc Admin được phép
        $decoded = verifyJWT($pdo);
        $user_id = $decoded['sub'];
        $role_id = $decoded['role_id'];

        // Kiểm tra quyền Author hoặc Admin
        if (!in_array($role_id, [2, 3])) {
            http_response_code(403);
            echo json_encode(["error" => "Chỉ Admin hoặc Author được cập nhật truyện."]);
            break;
        }

        $data = json_decode(file_get_contents("php://input"), true);
        if (isset($data['story_id'], $data['title'], $data['description'], $data['type'], $data['status'])) {
            // Kiểm tra quyền sở hữu truyện
            if (!checkStoryOwnership($pdo, $data['story_id'], $user_id, $role_id)) {
                http_response_code(403);
                echo json_encode(["error" => "Bạn không plug quyền cập nhật truyện này."]);
                break;
            }

            try {
                $stmt = $pdo->prepare("UPDATE Stories SET title = ?, description = ?, type = ?, status = ? WHERE story_id = ?");
                $stmt->execute([$data['title'], $data['description'], $data['type'], $data['status'], $data['story_id']]);
                echo json_encode(["message" => "Cập nhật truyện thành công!"]);
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(["error" => "Lỗi server", "details" => $e->getMessage()]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["error" => "Thiếu dữ liệu!"]);
        }
        break;

    case 'DELETE':
        // Xóa truyện: Chỉ Author của truyện hoặc Admin được phép
        $decoded = verifyJWT($pdo);
        $user_id = $decoded['sub'];
        $role_id = $decoded['role_id'];

        // Kiểm tra quyền Author hoặc Admin
        if (!in_array($role_id, [2, 3])) {
            http_response_code(403);
            echo json_encode(["error" => "Chỉ Admin hoặc Author được xóa truyện."]);
            break;
        }

        $data = json_decode(file_get_contents("php://input"), true);
        if (isset($data['story_id'])) {
            // Kiểm tra quyền sở hữu truyện
            if (!checkStoryOwnership($pdo, $data['story_id'], $user_id, $role_id)) {
                http_response_code(403);
                echo json_encode(["error" => "Bạn không có quyền xóa truyện này."]);
                break;
            }

            try {
                // Xóa các chương của truyện trước
                $chapterStmt = $pdo->prepare("DELETE FROM Chapters WHERE story_id = ?");
                $chapterStmt->execute([$data['story_id']]);

                // Xóa truyện
                $stmt = $pdo->prepare("DELETE FROM Stories WHERE story_id = ?");
                $stmt->execute([$data['story_id']]);
                echo json_encode(["message" => "Xóa truyện thành công!"]);
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(["error" => "Lỗi server", "details" => $e->getMessage()]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["error" => "Thiếu dữ liệu!"]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Phương thức không được hỗ trợ"]);
        break;
}
?>