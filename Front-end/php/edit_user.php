<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Chỉnh Sửa Tác Giả</title>
    <link rel="stylesheet" href="../css/header_styles.css">
    <link rel="stylesheet" href="../css/user_management.css">
</head>

<body>
    <?php include '../views/header.php'; ?>
    <div class="container">
        <h2>Chỉnh Sửa Tác Giả</h2>
        <form id="editUserForm">
            <input type="hidden" name="author_id" id="author_id">
            <label for="username">Tên người dùng:</label>
            <input type="text" name="username" id="username" required>

            <label for="email">Email:</label>
            <input type="email" name="email" id="email" required>

            <label for="avatar">Ảnh đại diện (URL):</label>
            <input type="text" name="avatar" id="avatar">

            <button type="submit">Cập nhật tác giả</button>
        </form>

        <div id="response"></div>
    </div>

    <script>
        // Lấy author_id từ URL
        const urlParams = new URLSearchParams(window.location.search);
        const authorId = urlParams.get('author_id');

        // Load thông tin tác giả
        async function loadUser() {
            if (!authorId) {
                document.getElementById('response').innerText = 'Thiếu ID tác giả.';
                document.getElementById('response').style.color = 'red';
                return;
            }

            try {
                const res = await fetch(`http://localhost/doanphp/Back-end/api/author.php?author_id=${authorId}`);
                const user = await res.json();
                if (user) {
                    document.getElementById('author_id').value = user.user_id;
                    document.getElementById('username').value = user.username;
                    document.getElementById('email').value = user.email;
                    document.getElementById('avatar').value = user.avatar || '';
                } else {
                    document.getElementById('response').innerText = 'Không tìm thấy tác giả.';
                    document.getElementById('response').style.color = 'red';
                }
            } catch (error) {
                console.error('Lỗi khi lấy thông tin tác giả:', error);
                document.getElementById('response').innerText = 'Lỗi khi tải dữ liệu.';
                document.getElementById('response').style.color = 'red';
            }
        }

        // Gọi loadUser khi trang tải
        document.addEventListener('DOMContentLoaded', loadUser);

        // Xử lý form
        const form = document.getElementById('editUserForm');
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const data = {
                author_id: document.getElementById('author_id').value,
                username: document.getElementById('username').value,
                email: document.getElementById('email').value,
                avatar: document.getElementById('avatar').value || null
            };

            try {
                const res = await fetch('http://localhost/doanphp/Back-end/api/author.php', {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
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