<?php
require_once '../config/database.php';

$database = new Database();
$pdo = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (isset($_GET['story_id'])) {
        // Lấy thông tin truyện theo story_id
        $stmt = $pdo->prepare("SELECT * FROM Stories WHERE story_id = ?");
        $stmt->execute([$_GET['story_id']]);
        $story = $stmt->fetch(PDO::FETCH_ASSOC);

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

        echo json_encode([
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

        echo json_encode([
            'status' => 'success',
            'data' => $stories
        ]);
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
            
            echo json_encode([
                "success" => true,
                "message" => "Truyện đã được thêm thành công",
                "story_id" => $story_id
            ]);
            
        } catch (PDOException $e) {
            // Rollback nếu có lỗi
            $pdo->rollBack();
            echo json_encode([
                "success" => false,
                "message" => "Lỗi khi thêm truyện: " . $e->getMessage()
            ]);
        }
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Dữ liệu không hợp lệ."
        ]);
    }
}


if ($method === 'PUT') {
    // Lấy dữ liệu JSON từ body request
    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data['story_id'], $data['title'], $data['description'], $data['type'])) {
        $cover_image = $data['cover_image'] ?? null;

        // Cập nhật truyện trong database
        $stmt = $pdo->prepare("UPDATE Stories SET title = ?, description = ?, type = ?, cover_image = ? WHERE story_id = ?");
        $stmt->execute([$data['title'], $data['description'], $data['type'], $cover_image, $data['story_id']]);

        // Cập nhật thể loại
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

        echo json_encode([
            'status' => 'success',
            'message' => 'Cập nhật thành công'
        ]);
    } else {
        echo json_encode(["message" => "Dữ liệu không hợp lệ."]);
    }
}

if ($method === 'DELETE') {
    // Lấy dữ liệu từ body request
    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data['story_id'])) {
        $stmt = $pdo->prepare("DELETE FROM Stories WHERE story_id = ?");
        $stmt->execute([$data['story_id']]);
        echo json_encode(["status" => "success", "message" => "Truyện đã bị xóa"]);
    } else {
        echo json_encode(["status" => "error", "message" => "story_id không hợp lệ."]);
    }
}
?>
