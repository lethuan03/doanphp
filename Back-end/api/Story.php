<?php
header("Content-Type: application/json");
require_once '../config/database.php';
require_once '../utils/auth.php';

// Kết nối database
$database = new Database();
$pdo = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

// Hàm kiểm tra quyền sở hữu truyện
function checkStoryOwnership($pdo, $story_id, $user_id, $role_id) {
    if ($role_id == 2) { // Admin có quyền với mọi truyện
        return true;
    }

    $stmt = $pdo->prepare("SELECT author_id FROM Stories WHERE story_id = ?");
    $stmt->execute([$story_id]);
    $story = $stmt->fetch(PDO::FETCH_ASSOC);

    return $story && $story['author_id'] == $user_id; // Chỉ Author của truyện được phép
}

try {
    switch ($method) {
        case 'GET':
            // Xem truyện: Công khai
            if (isset($_GET['story_id'])) {
                $stmt = $pdo->prepare("SELECT * FROM Stories WHERE story_id = ?");
                $stmt->execute([$_GET['story_id']]);
                $story = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($story) {
                    sendResponse(200, $story);
                } else {
                    sendResponse(404, ["message" => "Không tìm thấy truyện."]);
                }
            } else {
                $stmt = $pdo->query("SELECT * FROM Stories");
                $stories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                sendResponse(200, $stories);
            }
            break;

        case 'POST':
            // Thêm truyện: Chỉ Admin hoặc Author được phép
            $decoded = verifyJWT($pdo);
            $user_id = $decoded['sub'];
            $role_id = $decoded['role_id'];

            if (!in_array($role_id, [2, 3])) {
                sendResponse(403, ["message" => "Chỉ Admin hoặc Author được phép thêm truyện."]);
                break;
            }

            if (isset($_POST['title'], $_POST['description'], $_POST['author_id'], $_POST['type'])) {
                // Kiểm tra quyền: Author chỉ được thêm truyện với author_id là chính họ
                if ($role_id == 3 && $_POST['author_id'] != $user_id) {
                    sendResponse(403, ["message" => "Author chỉ được thêm truyện cho chính mình."]);
                    break;
                }

                // Kiểm tra xem author_id tồn tại trong Users
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM Users WHERE user_id = ?");
                $stmt->execute([$_POST['author_id']]);
                if ($stmt->fetchColumn() == 0) {
                    sendResponse(400, ["message" => "Author không tồn tại."]);
                    break;
                }

                $cover_image = null;

                // Xử lý ảnh nếu có
                if (isset($_FILES['cover_image']) && !empty($_FILES['cover_image']['name'])) {
                    $target_dir = "../Uploads/";
                    if (!file_exists($target_dir)) {
                        mkdir($target_dir, 0777, true);
                    }
                    $target_file = $target_dir . basename($_FILES["cover_image"]["name"]);
                    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
                    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

                    if (in_array($imageFileType, $allowed_types)) {
                        if (move_uploaded_file($_FILES["cover_image"]["tmp_name"], $target_file)) {
                            $cover_image = 'Uploads/' . basename($_FILES["cover_image"]["name"]);
                        } else {
                            sendResponse(500, ["message" => "Lỗi khi tải lên ảnh."]);
                            break;
                        }
                    } else {
                        sendResponse(400, ["message" => "Ảnh không hợp lệ. Chỉ chấp nhận jpg, jpeg, png, gif."]);
                        break;
                    }
                }

                // Thêm vào database
                $stmt = $pdo->prepare("INSERT INTO Stories (title, description, author_id, type, cover_image) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['title'],
                    $_POST['description'],
                    $_POST['author_id'],
                    $_POST['type'],
                    $cover_image
                ]);

                sendResponse(201, ["message" => "Truyện đã được thêm"]);
            } else {
                sendResponse(400, ["message" => "Dữ liệu không hợp lệ."]);
            }
            break;

        case 'PUT':
            // Cập nhật truyện: Chỉ Admin hoặc Author của truyện được phép
            $decoded = verifyJWT($pdo);
            $user_id = $decoded['sub'];
            $role_id = $decoded['role_id'];

            if (!in_array($role_id, [2, 3])) {
                sendResponse(403, ["message" => "Chỉ Admin hoặc Author được phép cập nhật truyện."]);
                break;
            }

            $data = json_decode(file_get_contents("php://input"), true);

            if (isset($data['story_id'], $data['title'], $data['description'], $data['type'])) {
                // Kiểm tra quyền sở hữu
                if (!checkStoryOwnership($pdo, $data['story_id'], $user_id, $role_id)) {
                    sendResponse(403, ["message" => "Bạn không có quyền cập nhật truyện này."]);
                    break;
                }

                $cover_image = $data['cover_image'] ?? null;

                // Xử lý ảnh nếu có
                if (isset($_FILES['cover_image']) && !empty($_FILES['cover_image']['name'])) {
                    $target_dir = "../Uploads/";
                    if (!file_exists($target_dir)) {
                        mkdir($target_dir, 0777, true);
                    }
                    $target_file = $target_dir . basename($_FILES["cover_image"]["name"]);
                    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
                    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

                    if (in_array($imageFileType, $allowed_types)) {
                        if (move_uploaded_file($_FILES["cover_image"]["tmp_name"], $target_file)) {
                            $cover_image = 'Uploads/' . basename($_FILES["cover_image"]["name"]);
                        } else {
                            sendResponse(500, ["message" => "Lỗi khi tải lên ảnh."]);
                            break;
                        }
                    } else {
                        sendResponse(400, ["message" => "Ảnh không hợp lệ. Chỉ chấp nhận jpg, jpeg, png, gif."]);
                        break;
                    }
                }

                // Cập nhật truyện trong database
                $stmt = $pdo->prepare("UPDATE Stories SET title = ?, description = ?, type = ?, cover_image = ? WHERE story_id = ?");
                $stmt->execute([$data['title'], $data['description'], $data['type'], $cover_image, $data['story_id']]);

                sendResponse(200, ["message" => "Cập nhật truyện thành công"]);
            } else {
                sendResponse(400, ["message" => "Dữ liệu không hợp lệ."]);
            }
            break;

        case 'DELETE':
            // Xóa truyện: Chỉ Admin hoặc Author của truyện được phép
            $decoded = verifyJWT($pdo);
            $user_id = $decoded['sub'];
            $role_id = $decoded['role_id'];

            if (!in_array($role_id, [2, 3])) {
                sendResponse(403, ["message" => "Chỉ Admin hoặc Author được phép xóa truyện."]);
                break;
            }

            $data = json_decode(file_get_contents("php://input"), true);

            if (isset($data['story_id'])) {
                // Kiểm tra quyền sở hữu
                if (!checkStoryOwnership($pdo, $data['story_id'], $user_id, $role_id)) {
                    sendResponse(403, ["message" => "Bạn không có quyền xóa truyện này."]);
                    break;
                }

                // Xóa các chương liên quan
                $stmt = $pdo->prepare("DELETE FROM Chapters WHERE story_id = ?");
                $stmt->execute([$data['story_id']]);

                // Xóa các liên kết thể loại (nếu có)
                $stmt = $pdo->prepare("DELETE FROM Story_Genres WHERE story_id = ?");
                $stmt->execute([$data['story_id']]);

                // Xóa truyện
                $stmt = $pdo->prepare("DELETE FROM Stories WHERE story_id = ?");
                $stmt->execute([$data['story_id']]);

                sendResponse(200, ["message" => "Truyện đã bị xóa"]);
            } else {
                sendResponse(400, ["message" => "Thiếu story_id."]);
            }
            break;

        default:
            sendResponse(405, ["message" => "Phương thức không được hỗ trợ."]);
            break;
    }
} catch (Exception $e) {
    error_log("Error in stories.php: " . $e->getMessage());
    sendResponse(500, ["message" => "Lỗi server: " . $e->getMessage()]);
}
?>