<?php
require_once "../config/database.php";
require_once "../../vendor/autoload.php";

use Smalot\PdfParser\Parser;

// Cấu hình log lỗi
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'C:/xampp/php/logs/php_error.log'); // Thay bằng đường dẫn thực tế

header("Content-Type: application/json");

// Hàm trả về JSON và dừng script
function respond($data) {
    echo json_encode($data);
    error_log("Response: " . json_encode($data));
    exit;
}

try {
    error_log("START: Script initialized");

    $database = new Database();
    $pdo = $database->getConnection();
    error_log("Database connected");

    ini_set('memory_limit', '512M');
    ini_set('max_execution_time', 300); // Tăng thời gian xử lý

    $method = $_SERVER['REQUEST_METHOD'];
    error_log("Method: $method");

    if ($method === 'POST') {
        error_log("POST request received");

        // Xử lý thêm truyện mới
        if (!isset($_POST['_method']) || $_POST['_method'] !== 'PUT') {
            error_log("Adding new story");
            if (isset($_POST['title'], $_POST['description'], $_POST['author_id'], $_POST['type'])) {
                $title = $_POST['title'];
                $description = $_POST['description'];
                $author_id = $_POST['author_id'];
                $type = $_POST['type'];
                $cover_image = null;
                $audio_file = null;

                error_log("Data: title=$title, author_id=$author_id, type=$type");

                // Kiểm tra trùng lặp
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM Stories WHERE title = ? AND author_id = ?");
                $stmt->execute([$title, $author_id]);
                if ($stmt->fetchColumn() > 0) {
                    respond(["message" => "Truyện đã tồn tại với tiêu đề và tác giả này."]);
                }

                // Xử lý ảnh bìa
                if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
                    error_log("Uploading cover image");
                    $target_dir = "../Uploads/";
                    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
                    $target_file = $target_dir . basename($_FILES["cover_image"]["name"]);
                    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
                    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

                    if (in_array($imageFileType, $allowed_types)) {
                        if (move_uploaded_file($_FILES["cover_image"]["tmp_name"], $target_file)) {
                            $cover_image = 'Uploads/' . basename($_FILES["cover_image"]["name"]);
                            error_log("Cover image uploaded: $cover_image");
                        } else {
                            respond(["message" => "Lỗi khi tải ảnh bìa."]);
                        }
                    } else {
                        respond(["message" => "Ảnh không hợp lệ."]);
                    }
                }

                // Xử lý file PDF và tạo audio
                if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {
                    error_log("Uploading PDF");
                    $pdf_dir = "../Uploads/pdf/";
                    $audio_dir = "../Uploads/audio/";
                    if (!is_dir($pdf_dir)) mkdir($pdf_dir, 0777, true);
                    if (!is_dir($audio_dir)) mkdir($audio_dir, 0777, true);

                    if (!is_writable($pdf_dir) || !is_writable($audio_dir)) {
                        respond(["message" => "Thư mục không ghi được."]);
                    }

                    $pdf_file = $pdf_dir . basename($_FILES["pdf_file"]["name"]);
                    $pdfFileType = strtolower(pathinfo($pdf_file, PATHINFO_EXTENSION));

                    if ($pdfFileType === 'pdf') {
                        if (move_uploaded_file($_FILES["pdf_file"]["tmp_name"], $pdf_file)) {
                            try {
                                $parser = new Parser();
                                $pdf = $parser->parseFile($pdf_file);
                                $text = $pdf->getText();
                                error_log("PDF text length: " . strlen($text));

                                if (empty($text)) {
                                    respond(["message" => "Không trích xuất được văn bản từ PDF."]);
                                }

                                $text = substr($text, 0, 5000);

                                $text_file = $pdf_dir . "temp_text.txt";
                                if (!file_put_contents($text_file, $text)) {
                                    respond(["message" => "Lỗi khi tạo file văn bản tạm."]);
                                }

                                $audio_filename = "audio_" . time() . ".mp3";
                                $audio_path = $audio_dir . $audio_filename;

                                $python_path = "python";
                                $script_path = __DIR__ . "/../../generate_audio.py";
                                $python_script = escapeshellcmd("$python_path $script_path " . escapeshellarg($text_file) . " " . escapeshellarg($audio_path));
                                error_log("Running Python: $python_script");
                                exec($python_script . " 2>&1", $output, $return_var);
                                error_log("Python result: return=$return_var, output=" . implode("\n", $output));

                                if ($return_var === 0 && file_exists($audio_path)) {
                                    $audio_file = 'Uploads/audio/' . $audio_filename;
                                    error_log("Audio generated: $audio_file");
                                } else {
                                    respond(["message" => "Lỗi khi tạo file audio. Output: " . implode(", ", $output)]);
                                }

                                unlink($text_file);
                            } catch (Exception $e) {
                                error_log("PDF error: " . $e->getMessage());
                                respond(["message" => "Lỗi khi trích xuất PDF: " . $e->getMessage()]);
                            }
                        } else {
                            respond(["message" => "Lỗi khi tải file PDF."]);
                        }
                    } else {
                        respond(["message" => "Chỉ chấp nhận file PDF."]);
                    }
                }

                // Thêm vào database
                try {
                    $stmt = $pdo->prepare("INSERT INTO Stories (title, description, author_id, type, cover_image, audio_file) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$title, $description, $author_id, $type, $cover_image, $audio_file]);
                    error_log("Database insert successful");
                    respond(["message" => "Truyện đã được thêm"]);
                } catch (Exception $e) {
                    error_log("Database error: " . $e->getMessage());
                    respond(["message" => "Lỗi khi thêm vào database: " . $e->getMessage()]);
                }
            } else {
                error_log("Missing fields: " . json_encode($_POST));
                respond(["message" => "Dữ liệu không hợp lệ."]);
            }
        }

        // Xử lý PUT (chỉnh sửa truyện)
        if (isset($_POST['_method']) && $_POST['_method'] === 'PUT') {
            error_log("Updating story");
            if (isset($_POST['story_id'], $_POST['title'], $_POST['description'], $_POST['type'])) {
                $story_id = $_POST['story_id'];
                $title = $_POST['title'];
                $description = $_POST['description'];
                $type = $_POST['type'];
                $cover_image = $_POST['cover_image'] ?? null;

                if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
                    error_log("Uploading new cover image");
                    $target_dir = "../Uploads/";
                    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
                    $target_file = $target_dir . basename($_FILES["cover_image"]["name"]);
                    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
                    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

                    if (in_array($imageFileType, $allowed_types)) {
                        if (move_uploaded_file($_FILES["cover_image"]["tmp_name"], $target_file)) {
                            $cover_image = 'Uploads/' . basename($_FILES["cover_image"]["name"]);
                            error_log("New cover image: $cover_image");
                        } else {
                            respond(["message" => "Lỗi khi tải ảnh bìa."]);
                        }
                    } else {
                        respond(["message" => "Ảnh không hợp lệ."]);
                    }
                }

                try {
                    $stmt = $pdo->prepare("UPDATE Stories SET title = ?, description = ?, type = ?, cover_image = ? WHERE story_id = ?");
                    $stmt->execute([$title, $description, $type, $cover_image, $story_id]);
                    error_log("Database update successful");
                    respond(["message" => "Cập nhật thành công"]);
                } catch (Exception $e) {
                    error_log("Database update error: " . $e->getMessage());
                    respond(["message" => "Lỗi khi cập nhật: " . $e->getMessage()]);
                }
            } else {
                error_log("Missing PUT fields: " . json_encode($_POST));
                respond(["message" => "Dữ liệu không hợp lệ."]);
            }
        }
    } elseif ($method === 'GET') {
        error_log("GET request");
        if (isset($_GET['story_id'])) {
            $stmt = $pdo->prepare("SELECT * FROM Stories WHERE story_id = ?");
            $stmt->execute([$_GET['story_id']]);
            $story = $stmt->fetch(PDO::FETCH_ASSOC);
            respond($story ?: ["message" => "Không tìm thấy truyện"]);
        } else {
            $stmt = $pdo->query("SELECT * FROM Stories");
            respond($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
    } else {
        error_log("Unsupported method: $method");
        respond(["message" => "Phương thức không được hỗ trợ."]);
    }
} catch (Exception $e) {
    error_log("Global error: " . $e->getMessage());
    respond(["message" => "Lỗi server: " . $e->getMessage()]);
}
?>