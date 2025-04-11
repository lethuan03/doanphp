<?php
require_once '../config/database.php';


$database = new Database();
$pdo = $database->getConnection();



$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (isset($_GET['story_id'])) {
        $stmt = $pdo->prepare("SELECT * FROM Stories WHERE story_id = ?");
        $stmt->execute([$_GET['story_id']]);
        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
    } else {
        $stmt = $pdo->query("SELECT * FROM Stories");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
}

if ($method === 'POST') {
    // Dùng $_POST thay vì php://input nếu upload file
    if (isset($_POST['title'], $_POST['description'], $_POST['author_id'], $_POST['type'])) {
        $cover_image = null;

        // Xử lý ảnh nếu có
        if (isset($_FILES['cover_image']) && !empty($_FILES['cover_image']['name'])) {
            $target_dir = "../uploads/";
            $target_file = $target_dir . basename($_FILES["cover_image"]["name"]);
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array($imageFileType, $allowed_types)) {
                move_uploaded_file($_FILES["cover_image"]["tmp_name"], $target_file);
                $cover_image = 'uploads/' . basename($_FILES["cover_image"]["name"]); // <-- thêm thư mục ở đây
            } else {
                echo json_encode(["message" => "Ảnh không hợp lệ."]);
                return;
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

        echo json_encode(["message" => "Truyện đã được thêm"]);
    } else {
        echo json_encode(["message" => "Dữ liệu không hợp lệ."]);
    }
}


if ($method === 'PUT') {
    // Đọc dữ liệu JSON từ body
    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data['story_id'], $data['title'], $data['description'], $data['type'])) {
        $cover_image = $data['cover_image'] ?? null;

        // Kiểm tra và xử lý upload ảnh nếu có
        if (isset($_FILES['cover_image']) && !empty($_FILES['cover_image']['name'])) {
            $target_dir = "../uploads/";
            $target_file = $target_dir . basename($_FILES["cover_image"]["name"]);
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array($imageFileType, $allowed_types)) {
                move_uploaded_file($_FILES["cover_image"]["tmp_name"], $target_file);
                $cover_image = basename($_FILES["cover_image"]["name"]);
            } else {
                echo json_encode(["message" => "Ảnh không hợp lệ."]);
                return;
            }
        }

        // Cập nhật truyện trong database
        $stmt = $pdo->prepare("UPDATE Stories SET title = ?, description = ?, type = ?, cover_image = ? WHERE story_id = ?");
        $stmt->execute([$data['title'], $data['description'], $data['type'], $cover_image, $data['story_id']]);

        echo json_encode(["message" => "Cập nhật thành công"]);
    } else {
        echo json_encode(["message" => "Dữ liệu không hợp lệ."]);
    }
}


if ($method === 'DELETE') {
    parse_str(file_get_contents("php://input"), $data);
    if (isset($data['story_id'])) {
        $stmt = $pdo->prepare("DELETE FROM Stories WHERE story_id = ?");
        $stmt->execute([$data['story_id']]);
        echo json_encode(["message" => "Truyện đã bị xóa"]);
    }
}
?>
