<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Thêm Truyện Mới</title>
    <link rel="stylesheet" href="../css/add_story.css">
    <link rel="stylesheet" href="../css/header_styles.css">
</head>

<body>
    <?php include '../views/header.php'; ?>
    <div class="container">
        <h2>Thêm Truyện Mới</h2>
        <form id="addStoryForm">
            <label for="title">Tiêu đề:</label>
            <input type="text" name="title" id="title" required>

            <label for="description">Mô tả:</label>
            <textarea name="description" id="description" required></textarea>

            <label for="cover_image">Ảnh bìa:</label>
            <input type="file" name="cover_image" id="cover_image">

            <label for="author_id">Tác giả ID:</label>
            <input type="text" name="author_id" id="author_id" required>

            <div class="mb-3">
                <label for="type" class="form-label">Thể loại</label>
                <select class="form-select" id="type" name="type" required></select>
            </div>

            <label for="status">Trạng thái:</label>
            <select name="status" id="status">
                <option value="ongoing">Đang ra</option>
                <option value="completed">Hoàn thành</option>
            </select>

            <button type="submit">Thêm truyện</button>
        </form>

        <div id="response"></div>
    </div>

    <script>
        async function loadGenres() {
            try {
                const response = await fetch('http://localhost/doanphp/Back-end/api/genre.php');
                const genresData = await response.json();
                const genres = genresData.data; // Sử dụng phần "data" trong response
                const genresSelect = document.getElementById('genre');
                genres.forEach(genre => {
                    const option = document.createElement('option');
                    option.value = genre.genre_id;
                    option.textContent = genre.name;
                    genresSelect.appendChild(option);
                });
            } catch (error) {
                console.error('Lỗi khi lấy danh sách thể loại:', error);
            }
        }
        async function loadGenres() {
      const res = await fetch("http://localhost/doanphp/Back-end/api/genre.php");
      const { data } = await res.json();
      document.getElementById("type").innerHTML = data.map(g =>
        `<option value="${g.genre_id}">${g.name}</option>`).join("");
    }

        // Gọi hàm loadGenres khi trang web được tải
        document.addEventListener('DOMContentLoaded', loadGenres);

        const form = document.getElementById('addStoryForm');
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData(form);

            try {
                const res = await fetch('http://localhost/doanphp/Back-end/api/story.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await res.json();
                document.getElementById('response').innerText = result.message;

                if (result.success) {
                    document.getElementById('response').style.color = 'green';
                } else {
                    document.getElementById('response').style.color = 'red';
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