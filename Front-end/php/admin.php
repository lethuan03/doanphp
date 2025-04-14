<?php

?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  
  <title>Trang Quản Trị</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f2f2f2;
      padding: 0px;
      text-align: center;
    }
    h1 {
      color: #333;
    }
    .admin-links {
      display: flex;
      justify-content: center;
      gap: 20px;
      margin-top: 40px;
    }
    .admin-links a {
      padding: 15px 30px;
      background-color: #007BFF;
      color: white;
      text-decoration: none;
      border-radius: 8px;
      transition: 0.3s ease;
    }
    .admin-links a:hover {
      background-color: #0056b3;
    }
  </style>
  <link rel="stylesheet" href="../css/header_styles.css">
</head>
<body>
<?php include '../views/header.php'; ?>
  <h1>🛠 Trang Quản Trị</h1>

  <div class="admin-links">
    <a href="add_story.php">➕ Thêm Truyện</a>
    <a href="add_genre.php">➕ Thêm Thể Loại</a>
    <a href="list_user.php">➕ danh sach user</a>
    <a href="manage_stories.php">➕ Quản lí truyện</a>
    
  </div>
  <?php include '../views/footer.php'; ?>
</body>
</html>
