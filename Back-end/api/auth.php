<?php
header("Content-Type: application/json");
require_once "../config/database.php";

$database = new Database();
$conn = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'register':
        if ($method === 'POST') register($conn);
        break;
    case 'login':
        if ($method === 'POST') login($conn);
        break;
    case 'profile':
        if ($method === 'GET') getProfile($conn);
        break;
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint không hợp lệ']);
        break;
}

// 📌 Hàm đăng ký
function register($conn) {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['email'], $input['username'], $input['password'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Thiếu thông tin đăng ký']);
        return;
    }

    $email = trim($input['email']);
    $username = trim($input['username']);
    $password = password_hash($input['password'], PASSWORD_DEFAULT);
    $role_id = 1; // Mặc định role User

    try {
        // Kiểm tra email hoặc username đã tồn tại
        $stmt = $conn->prepare("SELECT COUNT(*) FROM Users WHERE email = :email OR username = :username");
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        if ($stmt->fetchColumn() > 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Email hoặc username đã tồn tại']);
            return;
        }

        // Thêm user mới
        $stmt = $conn->prepare("INSERT INTO Users (email, username, password, role_id) VALUES (:email, :username, :password, :role_id)");
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':role_id', $role_id);
        $stmt->execute();

        http_response_code(201);
        echo json_encode(['message' => 'Đăng ký thành công']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Lỗi server', 'details' => $e->getMessage()]);
    }
}

// 📌 Hàm đăng nhập
function login($conn) {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['email'], $input['password'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Email và mật khẩu là bắt buộc']);
        return;
    }

    $email = trim($input['email']);
    $password = $input['password'];

    try {
        $stmt = $conn->prepare("SELECT user_id, username, password, role_id FROM Users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            echo json_encode([
                'message' => 'Đăng nhập thành công',
                'user_id' => $user['user_id'],
                'username' => $user['username'],
                'role_id' => $user['role_id']
            ]);
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'Sai email hoặc mật khẩu']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Lỗi server', 'details' => $e->getMessage()]);
    }
}

// 📌 Hàm lấy profile (có thể thêm auth sau này)
function getProfile($conn) {
    if (!isset($_GET['user_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Thiếu user_id']);
        return;
    }

    $user_id = intval($_GET['user_id']);

    try {
        $stmt = $conn->prepare("SELECT user_id, email, username, role_id FROM Users WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            echo json_encode($user);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'User không tồn tại']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Lỗi server', 'details' => $e->getMessage()]);
    }
}
?>
