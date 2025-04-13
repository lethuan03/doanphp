<?php
require_once '../config/database.php';

header("Content-Type: application/json");
session_start();

$database = new Database();
$pdo = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

function sendResponse($statusCode, $data) {
    http_response_code($statusCode);
    echo json_encode($data);
}

function checkAuth() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role_id'])) {
        sendResponse(401, ["status" => "error", "message" => "Bạn chưa đăng nhập"]);
        exit;
    }
    
    return [
        'user_id' => $_SESSION['user_id'],
        'role_id' => $_SESSION['role_id']
    ];
}

function checkStoryOwnership($pdo, $story_id, $user_id, $role_id) {
    if ($role_id == 2) {  // Admin có quyền truy cập tất cả
        return true;
    }

    $stmt = $pdo->prepare("SELECT author_id FROM Stories WHERE story_id = ?");
    $stmt->execute([$story_id]);
    $story = $stmt->fetch(PDO::FETCH_ASSOC);

    return $story && $story['author_id'] == $user_id;
}

try {
    if ($method === 'GET') {
        // GET không cần phân quyền - thông tin công khai
        if (isset($_GET['story_id'])) {
            // Lấy thông tin truyện theo story_id
            $stmt = $pdo->prepare("SELECT * FROM Stories WHERE story_id = ?");
            $stmt->execute([$_GET['story_id']]);
            $story = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$story) {
                sendResponse(404, ["status" => "error", "message" => "Không tìm thấy truyện"]);
                return;
            }

            // Lấy thể loại của truyện
            $stmt_genres = $pdo->prepare("SELECT g.genre_id, g.name FROM Genres g 
                                        JOIN Story_Genres sg ON g.genre_id = sg.genre_id 
                                        WHERE sg.story_id = ?");
            $stmt_genres->execute([$_GET['story_id']]);
            $genres = $stmt_genres->fetchAll(PDO::FETCH_ASSOC);

            // Thêm thông tin thể loại vào dữ liệu trả về
            $story['genres'] = array_map(function($genre) {
                return $genre['name'];
            }, $genres);

            sendResponse(200, [
                'status' => 'success',
                'data' => $story
            ]);
        } else {
            // Lấy tất cả truyện
            $stmt = $pdo->query("SELECT * FROM Stories");
            $stories = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Thêm thông tin thể loại cho mỗi truyện
            foreach ($stories as &$story) {
                $stmt_genres = $pdo->prepare("SELECT g.genre_id, g.name FROM Genres g 
                                            JOIN Story_Genres sg ON g.genre_id = sg.genre_id 
                                            WHERE sg.story_id = ?");
                $stmt_genres->execute([$story['story_id']]);
                $genres = $stmt_genres->fetchAll(PDO::FETCH_ASSOC);
                $story['genres'] = array_map(function($genre) {
                    return $genre['name'];
                }, $genres);
            }

            sendResponse(200, [
                'status' => 'success',
                'data' => $stories
            ]);
        }
    }

    if ($method === 'POST') {
        // Kiểm tra đăng nhập và lấy thông tin người dùng
        $auth = checkAuth();
        $user_id = $auth['user_id'];
        $role_id = $auth['role_id'];

        if (!in_array($role_id, [2, 3])) {
            sendResponse(403, ["status" => "error", "message" => "Chỉ Admin hoặc Author được thêm truyện"]);
            return;
        }

        // Dùng $_POST thay vì php://input nếu upload file
        if (isset($_POST['title'], $_POST['description'], $_POST['author_id'], $_POST['type'])) {
            // Kiểm tra quyền
            if ($role_id != 2 && $_POST['author_id'] != $user_id) {
                sendResponse(403, ["status" => "error", "message" => "Bạn không có quyền thêm truyện cho author khác"]);
                return;
            }

            $cover_image = null;

            // Xử lý ảnh nếu có
            if (isset($_FILES['cover_image']) && !empty($_FILES['cover_image']['name'])) {
                $target_dir = "../uploads/";
                $target_file = $target_dir . basename($_FILES["cover_image"]["name"]);
                $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
                $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

                if (in_array($imageFileType, $allowed_types)) {
                    move_uploaded_file($_FILES["cover_image"]["tmp_name"], $target_file);
                    $cover_image = 'uploads/' . basename($_FILES["cover_image"]["name"]);
                } else {
                    sendResponse(400, ["status" => "error", "message" => "Ảnh không hợp lệ."]);
                    return;
                }
            }

            try {
                // Bắt đầu transaction
                $pdo->beginTransaction();
                
                // Thêm vào database bảng Stories
                $stmt = $pdo->prepare("INSERT INTO Stories (title, description, author_id, type, cover_image) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['title'],
                    $_POST['description'],
                    $_POST['author_id'],
                    $_POST['type'],
                    $cover_image
                ]);
                
                // Lấy ID của truyện vừa thêm
                $story_id = $pdo->lastInsertId();
                
                // Xử lý genres nếu có
                if (isset($_POST['genres']) && is_array($_POST['genres'])) {
                    $insert_genres_stmt = $pdo->prepare("INSERT INTO story_genres (story_id, genre_id) VALUES (?, ?)");
                    
                    foreach ($_POST['genres'] as $genre_id) {
                        $insert_genres_stmt->execute([$story_id, $genre_id]);
                    }
                }
                
                // Commit transaction
                $pdo->commit();
                
                sendResponse(201, [
                    "status" => "success",
                    "message" => "Truyện đã được thêm thành công",
                    "story_id" => $story_id
                ]);
                
            } catch (PDOException $e) {
                // Rollback nếu có lỗi
                $pdo->rollBack();
                sendResponse(500, [
                    "status" => "error",
                    "message" => "Lỗi khi thêm truyện: " . $e->getMessage()
                ]);
            }
        } else {
            sendResponse(400, [
                "status" => "error",
                "message" => "Dữ liệu không hợp lệ, thiếu title, description, author_id hoặc type"
            ]);
        }
    }

    if ($method === 'PUT') {
        // Kiểm tra đăng nhập và lấy thông tin người dùng
        $auth = checkAuth();
        $user_id = $auth['user_id'];
        $role_id = $auth['role_id'];

        if (!in_array($role_id, [2, 3])) {
            sendResponse(403, ["status" => "error", "message" => "Chỉ Admin hoặc Author được cập nhật truyện"]);
            return;
        }

        // Lấy dữ liệu JSON từ body request
        $data = json_decode(file_get_contents("php://input"), true);

        if (isset($data['story_id'], $data['title'], $data['description'], $data['type'])) {
            // Kiểm tra quyền sở hữu truyện
            if (!checkStoryOwnership($pdo, $data['story_id'], $user_id, $role_id)) {
                sendResponse(403, ["status" => "error", "message" => "Bạn không có quyền cập nhật truyện này"]);
                return;
            }

            // Kiểm tra xem truyện có tồn tại không
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM Stories WHERE story_id = ?");
            $stmt->execute([$data['story_id']]);
            if ($stmt->fetchColumn() == 0) {
                sendResponse(404, ["status" => "error", "message" => "Truyện không tồn tại"]);
                return;
            }

            $cover_image = $data['cover_image'] ?? null;

            try {
                // Bắt đầu transaction
                $pdo->beginTransaction();

                // Cập nhật truyện trong database
                $stmt = $pdo->prepare("UPDATE Stories SET title = ?, description = ?, type = ?, cover_image = ? WHERE story_id = ?");
                $stmt->execute([$data['title'], $data['description'], $data['type'], $cover_image, $data['story_id']]);

                // Cập nhật thể loại nếu có
                if (isset($data['genre_ids']) && is_array($data['genre_ids'])) {
                    // Xóa thể loại cũ
                    $stmt_delete = $pdo->prepare("DELETE FROM Story_Genres WHERE story_id = ?");
                    $stmt_delete->execute([$data['story_id']]);

                    // Thêm thể loại mới
                    foreach ($data['genre_ids'] as $genre_id) {
                        $stmt_genre = $pdo->prepare("INSERT INTO Story_Genres (story_id, genre_id) VALUES (?, ?)");
                        $stmt_genre->execute([$data['story_id'], $genre_id]);
                    }
                }

                // Commit transaction
                $pdo->commit();

                sendResponse(200, [
                    'status' => 'success',
                    'message' => 'Cập nhật truyện thành công'
                ]);
            } catch (PDOException $e) {
                // Rollback nếu có lỗi
                $pdo->rollBack();
                sendResponse(500, [
                    "status" => "error",
                    "message" => "Lỗi khi cập nhật truyện: " . $e->getMessage()
                ]);
            }
        } else {
            sendResponse(400, [
                "status" => "error", 
                "message" => "Dữ liệu không hợp lệ, thiếu story_id, title, description hoặc type"
            ]);
        }
    }

    if ($method === 'DELETE') {
        // Kiểm tra đăng nhập và lấy thông tin người dùng
        $auth = checkAuth();
        $user_id = $auth['user_id'];
        $role_id = $auth['role_id'];

        if (!in_array($role_id, [2, 3])) {
            sendResponse(403, ["status" => "error", "message" => "Chỉ Admin hoặc Author được xóa truyện"]);
            return;
        }

        // Lấy dữ liệu từ body request
        $data = json_decode(file_get_contents("php://input"), true);

        if (isset($data['story_id'])) {
            // Kiểm tra quyền sở hữu truyện
            if (!checkStoryOwnership($pdo, $data['story_id'], $user_id, $role_id)) {
                sendResponse(403, ["status" => "error", "message" => "Bạn không có quyền xóa truyện này"]);
                return;
            }

            // Kiểm tra xem truyện có tồn tại không
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM Stories WHERE story_id = ?");
            $stmt->execute([$data['story_id']]);
            if ($stmt->fetchColumn() == 0) {
                sendResponse(404, ["status" => "error", "message" => "Truyện không tồn tại"]);
                return;
            }

            try {
                // Bắt đầu transaction
                $pdo->beginTransaction();

                // Xóa thể loại truyện trước
                $stmt_delete_genres = $pdo->prepare("DELETE FROM Story_Genres WHERE story_id = ?");
                $stmt_delete_genres->execute([$data['story_id']]);

                // Xóa truyện
                $stmt = $pdo->prepare("DELETE FROM Stories WHERE story_id = ?");
                $stmt->execute([$data['story_id']]);

                // Commit transaction
                $pdo->commit();

                sendResponse(200, [
                    "status" => "success", 
                    "message" => "Truyện đã bị xóa"
                ]);
            } catch (PDOException $e) {
                // Rollback nếu có lỗi
                $pdo->rollBack();
                sendResponse(500, [
                    "status" => "error",
                    "message" => "Lỗi khi xóa truyện: " . $e->getMessage()
                ]);
            }
        } else {
            sendResponse(400, [
                "status" => "error", 
                "message" => "Thiếu story_id"
            ]);
        }
    }
} catch (Exception $e) {
    error_log("Error in stories.php: " . $e->getMessage());
    sendResponse(500, ["status" => "error", "message" => "Lỗi server: " . $e->getMessage()]);
}
?>