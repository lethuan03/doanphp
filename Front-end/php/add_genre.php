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
            $message = "<div class='alert alert-success'>{$response['message']}</div>";
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-4">
    <h2 class="mb-4">Thêm thể loại truyện</h2>

    <?php echo $message; ?>

    <form method="POST">
        <div class="mb-3">
            <label for="name" class="form-label">Tên thể loại</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <button type="submit" class="btn btn-primary">Thêm thể loại</button>
    </form>
</body>
</html>
