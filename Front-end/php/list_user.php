<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản Lý Tác Giả</title>
    <link rel="stylesheet" href="../css/header_styles.css">
    <link rel="stylesheet" href="../css/user_management.css">
</head>

<body>
    <?php include '../views/header.php'; ?>
    <div class="container">
        <h2>Quản Lý Tác Giả</h2>
        <a href="/doanphp/Front-end/php/add_user.php" class="btn">Thêm Tác Giả Mới</a>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên người dùng</th>
                    <th>Email</th>
                    <th>Ảnh đại diện</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody id="userList"></tbody>
        </table>
        <div id="response"></div>
    </div>

    <script>
        // Load danh sách tác giả
        async function loadUsers() {
            try {
                const res = await fetch('http://localhost/doanphp/Back-end/api/author.php');
                const users = await res.json();

                // Kiểm tra nếu API trả về mảng
                if (!Array.isArray(users)) {
                    throw new Error('Dữ liệu không đúng định dạng mảng');
                }

                const userList = document.getElementById('userList');
                userList.innerHTML = '';

                users.forEach(user => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${user.user_id}</td>
                        <td>${user.username}</td>
                        <td>${user.email}</td>
                        <td>${user.avatar ? `<img src="/doanphp/Back-end/${user.avatar}" width="50" alt="Avatar">` : 'Không có'}</td>
                        <td>
                            <a href="/doanphp/Front-end/php/edit_user.php?author_id=${user.user_id}" class="btn">Sửa</a>
                            <button onclick="deleteUser(${user.user_id})" class="btn btn-danger">Xóa</button>
                        </td>
                    `;
                    userList.appendChild(row);
                });
            } catch (error) {
                console.error('Lỗi khi lấy danh sách tác giả:', error);
                document.getElementById('response').innerText = 'Lỗi khi tải dữ liệu: ' + error.message;
                document.getElementById('response').style.color = 'red';
            }
        }

        // Xóa tác giả
        async function deleteUser(authorId) {
            if (!confirm('Bạn có chắc muốn xóa tác giả này?')) return;

            try {
                const res = await fetch('http://localhost/doanphp/Back-end/api/author.php', {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ author_id: authorId })
                });

                const result = await res.json();
                const responseDiv = document.getElementById('response');
                responseDiv.innerText = result.message || result.error || 'Không có phản hồi từ server';

                if (result.message) {
                    responseDiv.style.color = 'green';
                    loadUsers(); // Tải lại danh sách
                } else {
                    responseDiv.style.color = 'red';
                }
            } catch (error) {
                console.error('Lỗi khi xóa tác giả:', error);
                document.getElementById('response').innerText = 'Lỗi khi xóa tác giả: ' + error.message;
                document.getElementById('response').style.color = 'red';
            }
        }

        // Gọi loadUsers khi trang tải
        document.addEventListener('DOMContentLoaded', loadUsers);
    </script>
    <?php include '../views/footer.php'; ?>
</body>

</html>