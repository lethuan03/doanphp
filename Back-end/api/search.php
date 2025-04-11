<?php
header("Content-Type: application/json");
require_once '../config/database.php';

$database = new Database();
$conn = $database->getConnection();

if (!$conn) {
    echo json_encode(["error" => "Kết nối DB thất bại"]);
    exit;
}

// Lấy các tham số
$q = isset($_GET['q']) ? "%" . $_GET['q'] . "%" : "%";
$author = isset($_GET['author']) ? $_GET['author'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$type = isset($_GET['type']) ? $_GET['type'] : '';

// SQL cơ bản + join để lấy tên tác giả
$sql = "SELECT s.*, u.username AS author_name 
        FROM stories s
        LEFT JOIN users u ON s.author_id = u.user_id
        WHERE s.title LIKE :q";

// Mảng chứa tham số
$params = [":q" => $q];

// Gắn thêm điều kiện nếu có filter
if (!empty($author)) {
    $sql .= " AND u.username = :author";
    $params[":author"] = $author;
}
if (!empty($status)) {
    $sql .= " AND s.status = :status";
    $params[":status"] = $status;
}
if (!empty($type)) {
    $sql .= " AND s.type = :type";
    $params[":type"] = $type;
}

$stmt = $conn->prepare($sql);

// Bind từng tham số
foreach ($params as $key => &$val) {
    $stmt->bindParam($key, $val, PDO::PARAM_STR);
}

$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($data);
