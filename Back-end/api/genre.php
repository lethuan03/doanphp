<?php
header("Content-Type: application/json");
require_once "../config/database.php";

// Kết nối database
$database = new Database();
$pdo = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        if (isset($_GET['genre_id'])) {
            // Lấy thể loại theo ID
            $stmt = $pdo->prepare("SELECT * FROM Genres WHERE genre_id = ?");
            $stmt->execute([$_GET['genre_id']]);
            $genre = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($genre) {
                echo json_encode(["success" => true, "data" => $genre]);
            } else {
                echo json_encode(["success" => false, "message" => "Không tìm thấy thể loại"]);
            }
        } else {
            // Lấy tất cả thể loại
            $stmt = $pdo->query("SELECT * FROM Genres");
            $genres = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(["success" => true, "data" => $genres]);
        }
    }

    if ($method === 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['name']) || empty(trim($data['name']))) {
            echo json_encode(["success" => false, "message" => "Tên thể loại không hợp lệ"]);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO Genres (name) VALUES (?)");
        $stmt->execute([$data['name']]);

        echo json_encode(["success" => true, "message" => "Thể loại đã được thêm"]);
    }

    if ($method === 'PUT') {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['genre_id'], $data['name']) || empty(trim($data['name']))) {
            echo json_encode(["success" => false, "message" => "Dữ liệu không hợp lệ"]);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE Genres SET name = ? WHERE genre_id = ?");
        $stmt->execute([$data['name'], $data['genre_id']]);

        echo json_encode(["success" => true, "message" => "Cập nhật thành công"]);
    }

    if ($method === 'DELETE') {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['genre_id'])) {
            echo json_encode(["success" => false, "message" => "Thiếu ID thể loại"]);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM Genres WHERE genre_id = ?");
        $stmt->execute([$data['genre_id']]);

        echo json_encode(["success" => true, "message" => "Thể loại đã bị xóa"]);
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Lỗi: " . $e->getMessage()]);
}
?>
