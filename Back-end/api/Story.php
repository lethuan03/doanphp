<?php
require_once '../config/database.php';

// Kết nối đến cơ sở dữ liệu
$database = new Database();
$pdo = $database->getConnection();

// Xác định phương thức HTTP
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Nếu có truyền story_id => lấy chi tiết truyện
    if (isset($_GET['story_id'])) {
        $stmt = $pdo->prepare("SELECT * FROM Stories WHERE story_id = ?");
        $stmt->execute([$_GET['story_id']]);
        $story = $stmt->fetch(PDO::FETCH_ASSOC);

        // Lấy danh sách thể loại của truyện
        $stmt_genres = $pdo->prepare("SELECT g.genre_id, g.name FROM Genres g 
                                      JOIN Story_Genres sg ON g.genre_id = sg.genre_id 
                                      WHERE sg.story_id = ?");
        $stmt_genres->execute([$_GET['story_id']]);
        $genres = $stmt_genres->fetchAll(PDO::FETCH_ASSOC);
        $story['genres'] = array_map(function($genre) {
            return $genre['name'];
        }, $genres);

        echo json_encode(['status' => 'success', 'data' => $story]);

    // Nếu có truyền rank => lấy danh sách top truyện theo tuần/tháng/năm
    } elseif (isset($_GET['rank'])) {
        $rankType = $_GET['rank'];
        $interval = "";

        if ($rankType === "week") {
            $interval = "INTERVAL 7 DAY";
        } elseif ($rankType === "month") {
            $interval = "INTERVAL 30 DAY";
        } elseif ($rankType === "year") {
            $interval = "INTERVAL 365 DAY";
        } else {
            echo json_encode(["success" => false, "message" => "Loại xếp hạng không hợp lệ"]);
            exit;
        }

        // Truy vấn các truyện có lượt đọc nhiều nhất trong khoảng thời gian tương ứng
        $stmt = $pdo->prepare("
            SELECT s.story_id, s.title, COUNT(r.rating_id) AS views
            FROM Stories s
            LEFT JOIN Reading_History r ON s.story_id = r.story_id
            WHERE r.date_read >= NOW() - $interval
            GROUP BY s.story_id
            ORDER BY views DESC
            LIMIT 10
        ");
        $stmt->execute();
        $stories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(["success" => true, "stories" => $stories]);

    // Nếu không có tham số gì => lấy toàn bộ danh sách truyện
    } else {
        $stmt = $pdo->query("SELECT * FROM Stories");
        $stories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Lấy thể loại tương ứng với mỗi truyện
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

        echo json_encode(['status' => 'success', 'data' => $stories]);
    }
}

if ($method === 'POST') {
    // Kiểm tra dữ liệu POST (dùng $_POST vì có thể gửi form-data kèm ảnh)
    if (isset($_POST['title'], $_POST['description'], $_POST['author_id'], $_POST['type'])) {
        $cover_image = null;

        // Xử lý upload ảnh bìa nếu có
        if (isset($_FILES['cover_image']) && !empty($_FILES['cover_image']['name'])) {
            $target_dir = "../uploads/";
            $target_file = $target_dir . basename($_FILES["cover_image"]["name"]);
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array($imageFileType, $allowed_types)) {
                move_uploaded_file($_FILES["cover_image"]["tmp_name"], $target_file);
                $cover_image = 'uploads/' . basename($_FILES["cover_image"]["name"]);
            } else {
                echo json_encode(["message" => "Ảnh không hợp lệ."]);
                return;
            }
        }

        try {
            // Thêm truyện và thể loại vào database
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO Stories (title, description, author_id, type, cover_image) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $_POST['title'],
                $_POST['description'],
                $_POST['author_id'],
                $_POST['type'],
                $cover_image
            ]);

            $story_id = $pdo->lastInsertId();

            // Thêm các thể loại liên kết nếu có
            if (isset($_POST['genres']) && is_array($_POST['genres'])) {
                $insert_genres_stmt = $pdo->prepare("INSERT INTO Story_Genres (story_id, genre_id) VALUES (?, ?)");
                foreach ($_POST['genres'] as $genre_id) {
                    $insert_genres_stmt->execute([$story_id, $genre_id]);
                }
            }

            $pdo->commit();

            echo json_encode([
                "success" => true,
                "message" => "Truyện đã được thêm thành công",
                "story_id" => $story_id
            ]);
        } catch (PDOException $e) {
            $pdo->rollBack();
            echo json_encode(["success" => false, "message" => "Lỗi khi thêm truyện: " . $e->getMessage()]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Dữ liệu không hợp lệ."]);
    }
}

if ($method === 'PUT') {
    // Nhận dữ liệu JSON từ body
    $data = json_decode(file_get_contents("php://input"), true);

    // Kiểm tra dữ liệu cần thiết
    if (isset($data['story_id'], $data['title'], $data['description'], $data['type'])) {
        $cover_image = $data['cover_image'] ?? null;

        // Cập nhật thông tin truyện
        $stmt = $pdo->prepare("UPDATE Stories SET title = ?, description = ?, type = ?, cover_image = ? WHERE story_id = ?");
        $stmt->execute([$data['title'], $data['description'], $data['type'], $cover_image, $data['story_id']]);

        // Cập nhật thể loại nếu có
        if (isset($data['genre_ids']) && is_array($data['genre_ids'])) {
            $stmt_delete = $pdo->prepare("DELETE FROM Story_Genres WHERE story_id = ?");
            $stmt_delete->execute([$data['story_id']]);

            $stmt_genre = $pdo->prepare("INSERT INTO Story_Genres (story_id, genre_id) VALUES (?, ?)");
            foreach ($data['genre_ids'] as $genre_id) {
                $stmt_genre->execute([$data['story_id'], $genre_id]);
            }
        }

        echo json_encode(["status" => "success", "message" => "Cập nhật thành công"]);
    } else {
        echo json_encode(["message" => "Dữ liệu không hợp lệ."]);
    }
}

if ($method === 'DELETE') {
    // Nhận dữ liệu JSON từ body
    $data = json_decode(file_get_contents("php://input"), true);

    // Kiểm tra story_id hợp lệ
    if (isset($data['story_id'])) {
        $stmt = $pdo->prepare("DELETE FROM Stories WHERE story_id = ?");
        $stmt->execute([$data['story_id']]);
        echo json_encode(["status" => "success", "message" => "Truyện đã bị xóa"]);
    } else {
        echo json_encode(["status" => "error", "message" => "story_id không hợp lệ."]);
    }
}
?>
