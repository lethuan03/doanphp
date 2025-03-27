<?php
header('Content-Type: application/json');
require_once "../config/database.php";

$db = new Database();
$conn = $db->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $keyword = isset($_GET['q']) ? $_GET['q'] : '';
    
    $query = "SELECT s.*, u.username as author_name 
             FROM Stories s 
             LEFT JOIN Users u ON s.author_id = u.user_id 
             WHERE s.title LIKE :keyword OR s.description LIKE :keyword 
             LIMIT 10";
    
    $stmt = $conn->prepare($query);
    $stmt->execute(['keyword' => "%$keyword%"]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'status' => 'success',
        'data' => $results
    ]);
}