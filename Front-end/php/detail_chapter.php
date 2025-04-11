<?php
$chapter_id = $_GET['chapter_id'] ?? null;
if (!$chapter_id) {
    die("Chương không tồn tại.");
}

// Gọi API để lấy dữ liệu chương
$apiUrl = "http://localhost/doanphp/Back-end/api/chapter.php?chapter_id=" . urlencode($chapter_id);
$response = @file_get_contents($apiUrl);
$data = json_decode($response, true);

if (!$data || isset($data['error'])) {
    die("Không tìm thấy chương.");
}

$title = htmlspecialchars($data['title']);
$content = nl2br(htmlspecialchars($data['content']));
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .chapter-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 30px;
            background-color: #fff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.8;
        }

        .chapter-title {
            font-size: 28px;
            font-weight: bold;
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }

        .chapter-content {
            font-size: 18px;
            color: #444;
            white-space: pre-line;
        }

        @media (max-width: 768px) {
            .chapter-container {
                padding: 20px;
            }

            .chapter-title {
                font-size: 22px;
            }

            .chapter-content {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <?php include '../views/header.php'; ?>
    
    <div class="chapter-container">
        <div class="chapter-title"><?= $title ?></div>
        <div class="chapter-content"><?= $content ?></div>
    </div>

    <?php include '../views/footer.php'; ?>
</body>
</html>
