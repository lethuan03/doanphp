<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm Truyện Mới</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            padding: 30px;
        }

        .container {
            max-width: 600px;
            background: #fff;
            margin: auto;
            padding: 25px 30px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        h2, h3 {
            text-align: center;
            color: #333;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            margin-top: 15px;
            font-weight: bold;
            color: #555;
        }

        input[type="text"],
        input[type="file"],
        select,
        textarea {
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
            width: 100%;
            box-sizing: border-box;
        }

        textarea {
            resize: vertical;
            height: 100px;
        }

        button {
            margin-top: 20px;
            padding: 12px;
            font-size: 16px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        button:hover {
            background-color: #2980b9;
        }

        #response {
            margin-top: 20px;
            text-align: center;
            font-weight: bold;
            color: green;
        }
    </style>
</head>
<body>
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

            <label for="type">Thể loại:</label>
            <input type="text" name="type" id="type" required>

            <label for="genre">Thể loại chi tiết:</label>
            <select name="genres[]" id="genre" multiple size="5">
                <!-- Các thể loại sẽ được thêm vào đây từ API -->
            </select>

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
                const genres = genresData.data;  // Sử dụng phần "data" trong response
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
</body>
</html>