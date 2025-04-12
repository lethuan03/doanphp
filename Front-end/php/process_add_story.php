<?php
require_once '../../Back-end/config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $author_id = $_POST['author_id'] ?? '';
    $type = $_POST['type'] ?? '';
    $cover_image = null;

    // Kiểm tra nếu có file ảnh
    if (!empty($_FILES["cover_image"]["name"])) {
        $target_dir = "../../Back-end/uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_name = basename($_FILES["cover_image"]["name"]);
        $target_file = $target_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

        // Kiểm tra định dạng file
        if (in_array($imageFileType, $allowed_types)) {
            if (move_uploaded_file($_FILES["cover_image"]["tmp_name"], $target_file)) {
                $cover_image = "uploads/" . $file_name; // Lưu đường dẫn ảnh
            } else {
                die("Lỗi khi tải ảnh lên.");
            }
        } else {
            die("Chỉ chấp nhận file ảnh JPG, JPEG, PNG, GIF.");
        }
    }

    // Kết nối database
    $database = new Database();
    $pdo = $database->getConnection();

    // Thêm truyện vào database
    $stmt = $pdo->prepare("INSERT INTO Stories (title, description, author_id, type, cover_image) VALUES (?, ?, ?, ?, ?)");
    if ($stmt->execute([$title, $description, $author_id, $type, $cover_image])) {
        // Chuyển hướng về trang thêm truyện
        header("Location: add_story.php?success=1");
        exit();
    } else {
        echo "Lỗi khi thêm truyện.";
    }
} else {
    echo "Phương thức không hợp lệ!";
}
?>
