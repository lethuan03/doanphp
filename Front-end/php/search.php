<?php
header('Content-Type: text/html; charset=UTF-8');
require_once '../../Back-end/config/database.php';

// Khởi tạo kết nối cơ sở dữ liệu
$database = new Database();
$pdo = $database->getConnection();

// Xử lý tìm kiếm nếu có tham số keyword
$stories = [];
$error = '';
$keyword = $_GET['keyword'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($keyword)) {
    $query = "SELECT story_id, title, description, cover_image, author_id, type, status, views, created_at, updated_at
              FROM Stories
              WHERE title LIKE :keyword
              ORDER BY created_at DESC";

    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute(['keyword' => '%' . $keyword . '%']);
        $stories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($stories)) {
            $error = 'Không tìm thấy câu chuyện nào.';
        }
    } catch (PDOException $e) {
        $error = 'Lỗi cơ sở dữ liệu: ' . $e->getMessage();
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['keyword']) && empty($keyword)) {
    $error = 'Vui lòng nhập từ khóa';
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Tìm kiếm những câu chuyện thú vị theo từ khóa. Khám phá ngay!">
    <title>Tìm kiếm câu chuyện</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            max-width: 900px;
            width: 100%;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 40px;
            margin: 20px auto;
        }

        h1 {
            text-align: center;
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 30px;
            font-size: 28px;
        }

        .search-box {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 30px;
            position: relative;
        }

        .search-box input {
            flex: 1;
            padding: 14px 20px;
            font-size: 16px;
            border: none;
            border-radius: 50px;
            background: #f1f3f5;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .search-box input:focus {
            outline: none;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }

        .search-box button {
            padding: 14px 30px;
            font-size: 16px;
            font-weight: 500;
            background: #3498db;
            color: #fff;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .search-box button:hover {
            background: #2980b9;
        }

        .search-box button i {
            margin-right: 8px;
        }

        .error {
            color: #e74c3c;
            background: #ffe6e6;
            padding: 12px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 20px;
            font-size: 14px;
            display: <?php echo $error ? 'block' : 'none'; ?>;
        }

        .results {
            display: grid;
            gap: 20px;
        }

        .story {
            display: flex;
            gap: 20px;
            padding: 20px;
            border-radius: 12px;
            background: #f9f9f9;
            text-decoration: none;
            color: #2c3e50;
            transition: all 0.3s ease;
        }

        .story:hover {
            background: #fff;
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .story img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 10px;
            border: 2px solid #eee;
        }

        .story-content {
            flex: 1;
        }

        .story-content h3 {
            margin: 0 0 10px;
            font-size: 20px;
            font-weight: 600;
            color: #2c3e50;
        }

        .story-content p {
            margin: 0;
            font-size: 14px;
            color: #7f8c8d;
            line-height: 1.6;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* Responsive Design */
        @media (max-width: 600px) {
            .container {
                padding: 20px;
            }

            h1 {
                font-size: 24px;
            }

            .search-box {
                flex-direction: column;
            }

            .search-box input,
            .search-box button {
                width: 100%;
            }

            .story {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .story img {
                width: 100%;
                max-width: 150px;
                height: auto;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Tìm kiếm câu chuyện</h1>
        <form method="GET" action="">
            <div class="search-box">
                <input type="text" name="keyword" placeholder="Nhập từ khóa tìm kiếm..." value="<?php echo htmlspecialchars($keyword); ?>" />
                <button type="submit"><i class="fas fa-search"></i> Tìm kiếm</button>
            </div>
        </form>
        <div id="error" class="error"><?php echo htmlspecialchars($error); ?></div>
        <div id="results" class="results">
            <?php foreach ($stories as $story): ?>
                <a href="detail_story.php?story_id=<?php echo htmlspecialchars($story['story_id']); ?>" class="story">
                    <img loading="lazy" src="http://localhost/doanphp/Back-end/<?php echo htmlspecialchars($story['cover_image']); ?>" alt="<?php echo htmlspecialchars($story['title']); ?>">
                    <div class="story-content">
                        <h3><?php echo htmlspecialchars($story['title']); ?></h3>
                        <p><?php echo htmlspecialchars($story['description'] ?: 'Không có mô tả'); ?></p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Font Awesome for icons -->
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html>