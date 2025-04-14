<?php
require_once '../config/database.php';

header("Content-Type: application/json");

$pdo = (new Database())->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (isset($_GET['author_id'])) {
        // Lấy thông tin tác giả theo ID
        $stmt = $pdo->prepare("SELECT user_id, username, email, avatar, created_at FROM Users WHERE user_id = ?");
        $stmt->execute([$_GET['author_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode($user ?: ["error" => "Không tìm thấy tác giả"]);
    } else {
        // Lấy danh sách tất cả tác giả
        $stmt = $pdo->query("SELECT user_id, username, email, avatar, created_at FROM Users");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
}

if ($method === 'POST') {
    // Kiểm tra dữ liệu form-data
    if (isset($_POST['username'], $_POST['email'], $_POST['password'])) {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $avatar = null;

        // Xử lý file ảnh nếu có
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/'; // Thư mục lưu ảnh
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $maxFileSize = 5 * 1024 * 1024; // 5MB

            $file = $_FILES['avatar'];
            $fileType = mime_content_type($file['tmp_name']);
            $fileSize = $file['size'];

            // Kiểm tra định dạng và kích thước
            if (!in_array($fileType, $allowedTypes)) {
                echo json_encode(["error" => "Chỉ hỗ trợ file JPEG, PNG, GIF"]);
                exit;
            }
            if ($fileSize > $maxFileSize) {
                echo json_encode(["error" => "File quá lớn, tối đa 5MB"]);
                exit;
            }

            // Tạo tên file duy nhất
            $fileExt = pathinfo($file['name'], PATHINFO_EXTENSION);
            $fileName = uniqid('avatar_') . '.' . $fileExt;
            $filePath = $uploadDir . $fileName;

            // Di chuyển file vào thư mục uploads
            if (move_uploaded_file($file['tmp_name'], $filePath)) {
                $avatar = 'uploads/' . $fileName; // Lưu đường dẫn tương đối
            } else {
                echo json_encode(["error" => "Lỗi khi lưu file ảnh"]);
                exit;
            }
        }

        // Thêm vào database
        try {
            $stmt = $pdo->prepare("INSERT INTO Users (username, email, password, avatar, role_id) VALUES (?, ?, ?, ?, 2)");
            if ($stmt->execute([$username, $email, $password, $avatar])) {
                echo json_encode(["message" => "Tác giả đã được thêm"]);
            } else {
                echo json_encode(["error" => "Lỗi khi thêm tác giả"]);
            }
        } catch (PDOException $e) {
            echo json_encode(["error" => "Lỗi cơ sở dữ liệu: " . $e->getMessage()]);
        }
    } else {
        echo json_encode(["error" => "Thiếu dữ liệu username, email hoặc password"]);
    }
}

if ($method === 'PUT') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data['author_id'], $data['username'], $data['email'])) {
        $stmt = $pdo->prepare("UPDATE Users SET username = ?, email = ?, avatar = ? WHERE user_id = ?");
        if ($stmt->execute([$data['username'], $data['email'], $data['avatar'] ?? null, $data['author_id']])) {
            echo json_encode(["message" => "Cập nhật thành công"]);
        } else {
            echo json_encode(["error" => "Lỗi khi cập nhật"]);
        }
    } else {
        echo json_encode(["error" => "Thiếu dữ liệu"]);
    }
}

if ($method === 'DELETE') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data['author_id'])) {
        $stmt = $pdo->prepare("DELETE FROM Users WHERE user_id = ?");
        if ($stmt->execute([$data['author_id']])) {
            echo json_encode(["message" => "Tác giả đã bị xóa"]);
        } else {
            echo json_encode(["error" => "Lỗi khi xóa"]);
        }
    } else {
        echo json_encode(["error" => "Thiếu dữ liệu"]);
    }
}
?>
