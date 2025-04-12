<?php
require_once '../config/database.php';

// Nhận route từ URL
$request = $_GET['route'] ?? '';

// Điều hướng đến file API tương ứng
switch ($request) {
    case 'genre':
        require 'genre.php';
        break;
    case 'story':
        require 'story.php';
        break;
    case 'chapter':
        require 'chapter.php';
        break;
    default:
        echo json_encode(["error" => "Endpoint không hợp lệ"]);
}
?>
