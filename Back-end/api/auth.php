<?php
header("Content-Type: application/json");
require_once "../config/database.php";

$database = new Database();
$conn = $database->getConnection();

// Khóa bí mật và thời gian hết hạn
define('JWT_SECRET', 'moimoimoimoimoimoimoimoi'); 
define('JWT_EXPIRATION', 3600); // 1 giờ

// Hàm khởi tạo roles mặc định
function initializeRoles($conn) {
    try {
        // Kiểm tra và thêm role User nếu chưa tồn tại
        $stmt = $conn->prepare("SELECT COUNT(*) FROM roles WHERE role_id = 1");
        $stmt->execute();
        if ($stmt->fetchColumn() == 0) {
            $stmt = $conn->prepare("INSERT INTO roles (role_id, role_name) VALUES (1, 'User')");
            $stmt->execute();
        }

        // Kiểm tra và thêm role Admin nếu chưa tồn tại
        $stmt = $conn->prepare("SELECT COUNT(*) FROM roles WHERE role_id = 2");
        $stmt->execute();
        if ($stmt->fetchColumn() == 0) {
            $stmt = $conn->prepare("INSERT INTO roles (role_id, role_name) VALUES (2, 'Admin')");
            $stmt->execute();
        }

        // Kiểm tra và thêm role Author nếu chưa tồn tại
        $stmt = $conn->prepare("SELECT COUNT(*) FROM roles WHERE role_id = 3");
        $stmt->execute();
        if ($stmt->fetchColumn() == 0) {
            $stmt = $conn->prepare("INSERT INTO roles (role_id, role_name) VALUES (3, 'Author')");
            $stmt->execute();
        }
    } catch (PDOException $e) {
        // Ghi log lỗi để dễ debug
        error_log("Error in initializeRoles: " . $e->getMessage());
    }
}

// Hàm khởi tạo tài khoản admin mặc định
function initializeAdmin($conn) {
    try {
        // Kiểm tra xem tài khoản admin đã tồn tại chưa
        $stmt = $conn->prepare("SELECT COUNT(*) FROM Users WHERE email = 'admin@example.com'");
        $stmt->execute();
        
        if ($stmt->fetchColumn() == 0) {
            // Nếu chưa có, tạo tài khoản admin
            $email = 'admin@example.com';
            $username = 'admin';
            $password = password_hash('Admin123!', PASSWORD_DEFAULT);
            $role_id = 2; // Role Admin

            $stmt = $conn->prepare("INSERT INTO Users (email, username, password, role_id) VALUES (:email, :username, :password, :role_id)");
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $password);
            $stmt->bindParam(':role_id', $role_id);
            $stmt->execute();
        }
    } catch (PDOException $e) {
        // Ghi log lỗi để dễ debug
        error_log("Error in initializeAdmin: " . $e->getMessage());
    }
}

// Hàm khởi tạo tài khoản author mặc định
function initializeAuthor($conn) {
    try {
        // Kiểm tra xem tài khoản author đã tồn tại chưa
        $stmt = $conn->prepare("SELECT COUNT(*) FROM Users WHERE email = 'author@example.com'");
        $stmt->execute();
        
        if ($stmt->fetchColumn() == 0) {
            // Nếu chưa có, tạo tài khoản author
            $email = 'author@example.com';
            $username = 'author';
            $password = password_hash('Author123!', PASSWORD_DEFAULT);
            $role_id = 3; // Role Author

            $stmt = $conn->prepare("INSERT INTO Users (email, username, password, role_id) VALUES (:email, :username, :password, :role_id)");
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $password);
            $stmt->bindParam(':role_id', $role_id);
            $stmt->execute();
        }
    } catch (PDOException $e) {
        // Ghi log lỗi để dễ debug
        error_log("Error in initializeAuthor: " . $e->getMessage());
    }
}

// Khởi tạo roles, admin và author khi chương trình chạy
initializeRoles($conn);
initializeAdmin($conn);
initializeAuthor($conn);

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
    case 'logout':
        if ($method === 'POST') logout($conn);
        break;
    case 'delete_user':
        if ($method === 'DELETE') deleteUser($conn);
        break;
    case 'list_users':
        if ($method === 'GET') listUsers($conn);
        break;
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint không hợp lệ']);
        break;
}

// Hàm tạo JWT
function generateJWT($user_id, $role_id) {
    $header = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
    $payload = base64_encode(json_encode([
        'iat' => time(),
        'exp' => time() + JWT_EXPIRATION,
        'sub' => $user_id,
        'role_id' => $role_id
    ]));
    $signature = hash_hmac('sha256', "$header.$payload", JWT_SECRET, true);
    $signature = base64_encode($signature);

    return "$header.$payload.$signature";
}

// Hàm xác thực JWT
function verifyJWT($conn) {
    $headers = getallheaders();
    if (!isset($headers['Authorization'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Thiếu token xác thực']);
        exit;
    }

    $token = str_replace('Bearer ', '', $headers['Authorization']);
    $token_parts = explode('.', $token);

    if (count($token_parts) !== 3) {
        http_response_code(401);
        echo json_encode(['error' => 'Token không hợp lệ']);
        exit;
    }

    list($header, $payload, $signature) = $token_parts;
    $decoded_payload = json_decode(base64_decode($payload), true);

    $expected_signature = hash_hmac('sha256', "$header.$payload", JWT_SECRET, true);
    $expected_signature = base64_encode($expected_signature);

    if ($signature !== $expected_signature) {
        http_response_code(401);
        echo json_encode(['error' => 'Chữ ký token không hợp lệ']);
        exit;
    }

    if ($decoded_payload['exp'] < time()) {
        http_response_code(401);
        echo json_encode(['error' => 'Token đã hết hạn']);
        exit;
    }

    return $decoded_payload;
}

// Hàm đăng ký
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
        $stmt = $conn->prepare("SELECT COUNT(*) FROM Users WHERE email = :email OR username = :username");
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        if ($stmt->fetchColumn() > 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Email hoặc username đã tồn tại']);
            return;
        }

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

// Hàm đăng nhập
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
        $stmt = $conn->prepare("
            SELECT u.user_id, u.username, u.password, u.role_id, r.role_name 
            FROM Users u 
            JOIN roles r ON u.role_id = r.role_id 
            WHERE u.email = :email
        ");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $token = generateJWT($user['user_id'], $user['role_id']);
            echo json_encode([
                'message' => 'Đăng nhập thành công',
                'token' => $token,
                'user_id' => $user['user_id'],
                'username' => $user['username'],
                'role_id' => $user['role_id'],
                'role_name' => $user['role_name']
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

// Hàm lấy profile
function getProfile($conn) {
    $decoded = verifyJWT($conn);
    $current_user_id = $decoded['sub'];
    $current_role_id = $decoded['role_id'];

    if (!isset($_GET['user_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Thiếu user_id']);
        return;
    }

    $user_id = intval($_GET['user_id']);

    if ($user_id !== $current_user_id && $current_role_id != 2) {
        http_response_code(403);
        echo json_encode(['error' => 'Không có quyền truy cập']);
        return;
    }

    try {
        $stmt = $conn->prepare("
            SELECT u.user_id, u.email, u.username, u.role_id, r.role_name 
            FROM Users u 
            JOIN roles r ON u.role_id = r.role_id 
            WHERE u.user_id = :user_id
        ");
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

// Hàm đăng xuất
function logout($conn) {
    verifyJWT($conn);
    echo json_encode(['message' => 'Đăng xuất thành công']);
}

// Hàm liệt kê tất cả người dùng
function listUsers($conn) {
    $decoded = verifyJWT($conn);
    $current_role_id = $decoded['role_id'];

    if ($current_role_id != 2) {
        http_response_code(403);
        echo json_encode(['error' => 'Chỉ Admin mới có quyền xem danh sách người dùng']);
        return;
    }

    try {
        $stmt = $conn->prepare("
            SELECT u.user_id, u.email, u.username, u.role_id, r.role_name 
            FROM Users u 
            JOIN roles r ON u.role_id = r.role_id
        ");
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($users);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Lỗi server', 'details' => $e->getMessage()]);
    }
}

// Hàm xóa người dùng
function deleteUser($conn) {
    $decoded = verifyJWT($conn);
    $current_user_id = $decoded['sub'];
    $current_role_id = $decoded['role_id'];

    if ($current_role_id != 2) {
        http_response_code(403);
        echo json_encode(['error' => 'Chỉ Admin mới có quyền xóa người dùng']);
        return;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!isset($input['user_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Thiếu user_id']);
        return;
    }

    $user_id_to_delete = intval($input['user_id']);

    if ($user_id_to_delete === $current_user_id) {
        http_response_code(403);
        echo json_encode(['error' => 'Không thể tự xóa chính mình']);
        return;
    }

    try {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM Users WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $user_id_to_delete);
        $stmt->execute();

        if ($stmt->fetchColumn() == 0) {
            http_response_code(404);
            echo json_encode(['error' => 'Người dùng không tồn tại']);
            return;
        }

        $stmt = $conn->prepare("DELETE FROM Users WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $user_id_to_delete);
        $stmt->execute();

        echo json_encode(['message' => 'Xóa người dùng thành công']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Lỗi server', 'details' => $e->getMessage()]);
    }
}
?>