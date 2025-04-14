<?php
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $genreName = trim($_POST['name']);

    if (!empty($genreName)) {
        // Gửi request POST đến API genre.php
        $url = "http://localhost/doanphp/Back-end/api/genre.php";

        $data = json_encode(["name" => $genreName]);

        $options = [
            "http" => [
                "header" => "Content-Type: application/json\r\n",
                "method" => "POST",
                "content" => $data,
            ]
        ];
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        $response = json_decode($result, true);

        if (isset($response['message'])) {
        
            header("Location: admin.php");
            exit();
        } else {
            $message = "<div class='alert alert-danger'>{$response['error']}</div>";
        }
        
    } else {
        $message = "<div class='alert alert-warning'>Vui lòng nhập tên thể loại.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm thể loại</title>
    
    <link rel="stylesheet" href="../css/header_styles.css">
    <style>
.genre-form-container {
    max-width: 700px;
    margin: 30px auto;
    padding: 30px;
    background-color: #f4f4f4;
    font-family: Arial, sans-serif;
    color: #333;
    border-radius: 8px;
}

.genre-form-container h2 {
    text-align: center;
    color: #2c3e50;
    margin-bottom: 20px;
}

.genre-form-container form {
    background-color: #fff;
    padding: 20px;
    border-radius: 6px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}

.genre-form-container .form-label {
    display: block;
    margin-bottom: 8px;
    font-weight: bold;
}

.genre-form-container .form-control {
    width: 100%;
    padding: 10px;
    margin-bottom: 20px;
    border: 1px solid #ccc;
    border-radius: 4px;
}

.genre-form-container .btn-primary {
    background-color: #3498db;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.genre-form-container .btn-primary:hover {
    background-color: #2980b9;
}

.genre-form-container .alert {
    padding: 10px 15px;
    margin-bottom: 15px;
    border-radius: 4px;
}

.genre-form-container .alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.genre-form-container .alert-danger {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.genre-form-container .alert-warning {
    background-color: #fff3cd;
    color: #856404;
    border: 1px solid #ffeeba;
}
</style>


</head>
<body class="container py-4">
<?php include '../views/header.php'; ?>
<div class="genre-form-container">
    <h2 class="mb-4">Thêm thể loại truyện</h2>

    <?php echo $message; ?>

    <form method="POST">
        <div class="mb-3">
            <label for="name" class="form-label">Tên thể loại</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <button type="submit" class="btn btn-primary">Thêm thể loại</button>
    </form>
</div>
    <?php include '../views/footer.php'; ?>
</body>
</html>
