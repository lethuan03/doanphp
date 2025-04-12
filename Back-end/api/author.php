<?php
require_once '../config/database.php';

header("Content-Type: application/json");

$pdo = (new Database())->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (isset($_GET['author_id'])) {
        // Lấy thông tin tác giả theo ID
        $stmt = $pdo->prepare("SELECT user_id, username, email, avatar FROM Users WHERE user_id = ?");
        $stmt->execute([$_GET['author_id']]);
        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
    } else {
        // Lấy danh sách tất cả tác giả
        $stmt = $pdo->query("SELECT user_id, username, email, avatar FROM Users");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (isset($data['username'], $data['email'], $data['password'])) {
        $hashed_password = password_hash($data['password'], PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO Users (username, email, password, avatar, role_id) VALUES (?, ?, ?, ?, 2)");
        if ($stmt->execute([$data['username'], $data['email'], $hashed_password, $data['avatar'] ?? null])) {
            echo json_encode(["message" => "Tác giả đã được thêm"]);
        } else {
            echo json_encode(["error" => "Lỗi khi thêm tác giả"]);
        }
    } else {
        echo json_encode(["error" => "Thiếu dữ liệu"]);
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
