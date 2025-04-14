<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Thêm Tác Giả Mới</title>
    <link rel="stylesheet" href="../css/header_styles.css">
    <link rel="stylesheet" href="../css/user_management.css">
</head>

<body>
    <?php include '../views/header.php'; ?>
    <div class="container">
        <h2>Thêm Tác Giả Mới</h2>
        <form id="addUserForm" enctype="multipart/form-data">
            <label for="username">Tên người dùng:</label>
            <input type="text" name="username" id="username" required>

            <label for="email">Email:</label>
            <input type="email" name="email" id="email" required>

            <label for="password">Mật khẩu:</label>
            <input type="password" name="password" id="password" required>

            <label for="avatar">Ảnh đại diện:</label>
            <input type="file" name="avatar" id="avatar" accept="image/jpeg,image/png,image/gif">

            <button type="submit">Thêm tác giả</button>
        </form>

        <div id="response"></div>
    </div>

    <script>
        const form = document.getElementById('addUserForm');
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData(form);

            try {
                const res = await fetch('http://localhost/doanphp/Back-end/api/author.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await res.json();
                const responseDiv = document.getElementById('response');
                responseDiv.innerText = result.message || result.error;

                if (result.message) {
                    responseDiv.style.color = 'green';
                    setTimeout(() => {
                        window.location.href = '/doanphp/Front-end/php/list_user.php';
                    }, 1000);
                } else {
                    responseDiv.style.color = 'red';
                }
            } catch (error) {
                console.error('Lỗi khi gửi form:', error);
                document.getElementById('response').innerText = 'Đã xảy ra lỗi khi gửi dữ liệu.';
                document.getElementById('response').style.color = 'red';
            }
        });
    </script>
    <?php include '../views/footer.php'; ?>
</body>

</html>