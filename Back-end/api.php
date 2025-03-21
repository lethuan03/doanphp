<?php
require_once 'config.php';

$input = json_decode(file_get_contents('php://input'), true);
$method = $_SERVER['REQUEST_METHOD'];
$path = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '/';

switch ($path) {
    case '/register':
        if ($method === 'POST') register($input, $conn);
        break;
    case '/login':
        if ($method === 'POST') login($input, $conn);
        break;
    case '/profile':
        if ($method === 'GET') getProfile($conn);
        break;
    case '/roles':
        if ($method === 'GET') getRoles($conn);
        break;
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
        break;
}

function register($input, $conn) {
    // Kiểm tra các trường bắt buộc: email, username, password
    if (!isset($input['email']) || !isset($input['username']) || !isset($input['password'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Email, username, and password are required']);
        return;
    }

    $email = $input['email'];
    $username = $input['username'];
    $password = password_hash($input['password'], PASSWORD_DEFAULT);
    $avatar = isset($input['avatar']) ? $input['avatar'] : null; // Avatar không bắt buộc
    $roleId = 1; // Default role_id (ví dụ: user)

    // Kiểm tra email đã tồn tại
    $stmt = $conn->prepare("SELECT COUNT(*) FROM Users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    if ($stmt->fetchColumn() > 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Email already exists']);
        return;
    }

    // Kiểm tra username đã tồn tại
    $stmt = $conn->prepare("SELECT COUNT(*) FROM Users WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    if ($stmt->fetchColumn() > 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Username already exists']);
        return;
    }

    try {
        $stmt = $conn->prepare("INSERT INTO Users (email, username, password, avatar, role_id) VALUES (:email, :username, :password, :avatar, :role_id)");
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':avatar', $avatar);
        $stmt->bindParam(':role_id', $roleId);
        $stmt->execute();
        http_response_code(201);
        echo json_encode(['message' => 'Registration successful']);
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to register', 'details' => $e->getMessage()]);
    }
}

function login($input, $conn) {
    if (!isset($input['email']) || !isset($input['password'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Email and password are required']);
        return;
    }
    $email = $input['email'];
    $password = $input['password'];
    
    $stmt = $conn->prepare("SELECT u.user_id, u.email, u.username, u.password, u.role_id, r.role_name 
                            FROM Users u 
                            LEFT JOIN Roles r ON u.role_id = r.role_id 
                            WHERE u.email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        $token = generateToken($user['user_id'], $user['role_id']);
        echo json_encode([
            'message' => 'Login successful',
            'token' => $token,
            'role' => $user['role_name'] ?? 'unknown',
            'username' => $user['username']
        ]);
    } else {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid credentials']);
    }
}

function getProfile($conn) {
    $headers = apache_request_headers();
    if (!isset($headers['Authorization'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Token required']);
        return;
    }
    $token = str_replace('Bearer ', '', $headers['Authorization']);
    $userData = verifyToken($token);
    
    if (!$userData) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid or expired token']);
        return;
    }
    $stmt = $conn->prepare("SELECT u.user_id, u.email, u.username, u.avatar, u.created_at, r.role_name 
                            FROM Users u 
                            LEFT JOIN Roles r ON u.role_id = r.role_id 
                            WHERE u.user_id = :id");
    $stmt->bindParam(':id', $userData['user_id']);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo json_encode($user);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'User not found']);
    }
}

function getRoles($conn) {
    $stmt = $conn->prepare("SELECT role_id, role_name FROM Roles");
    $stmt->execute();
    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($roles);
}
?>