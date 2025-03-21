<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

$host = "localhost";
$dbname = "truyenDB"; // Đúng với tên database trong schema
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed', 'details' => $e->getMessage()]);
    exit;
}

function generateToken($userId, $roleId) {
    $secret = 'my-super-secret-key-12345'; // Thay đổi secret key cho an toàn
    $header = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
    $payload = base64_encode(json_encode([
        'user_id' => $userId,
        'role_id' => $roleId,
        'exp' => time() + (60 * 60) // Token hết hạn sau 1 giờ
    ]));
    $signature = hash_hmac('sha256', "$header.$payload", $secret, true);
    $signature = base64_encode($signature);
    return "$header.$payload.$signature";
}

function verifyToken($token) {
    $secret = 'my-super-secret-key-12345'; 
    $parts = explode('.', $token);
    if (count($parts) !== 3) return false;
    
    $header = $parts[0];
    $payload = $parts[1];
    $signature = $parts[2];
    
    $validSignature = base64_encode(hash_hmac('sha256', "$header.$payload", $secret, true));
    if ($signature !== $validSignature) return false;
    
    $payloadData = json_decode(base64_decode($payload), true);
    if ($payloadData['exp'] < time()) return false;
    
    return $payloadData;
}
?>