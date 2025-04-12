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
        h2 {
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
        button:disabled {
            background-color: #95a5a6;
            cursor: not-allowed;
        }
        button:hover:not(:disabled) {
            background-color: #2980b9;
        }
        #response {
            margin-top: 20px;
            text-align: center;
            font-weight: bold;
        }
        .loading {
            color: #3498db;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Thêm Truyện Mới</h2>
        <form id="addStoryForm" enctype="multipart/form-data">
            <label for="title">Tiêu đề:</label>
            <input type="text" name="title" id="title" required>

            <label for="description">Mô tả:</label>
            <textarea name="description" id="description" required></textarea>

            <label for="cover_image">Ảnh bìa:</label>
            <input type="file" name="cover_image" id="cover_image" accept="image/*">

            <label for="pdf_file">File PDF (nếu có):</label>
            <input type="file" name="pdf_file" id="pdf_file" accept=".pdf">

            <label for="author_id">Tác giả ID:</label>
            <input type="text" name="author_id" id="author_id" required>

            <label for="type">Thể loại:</label>
            <input type="text" name="type" id="type" required>

            <label for="status">Trạng thái:</label>
            <select name="status" id="status">
                <option value="ongoing">Đang ra</option>
                <option value="completed">Hoàn thành</option>
            </select>

            <button type="submit" id="submitButton">Thêm truyện</button>
        </form>

        <div id="response"></div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('addStoryForm');
            const submitButton = document.getElementById('submitButton');
            const responseDiv = document.getElementById('response');

            if (!form) {
                console.error('Không tìm thấy form với ID addStoryForm');
                return;
            }
            console.log('Form được tìm thấy');

            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                console.log('Form submitted');

                // Vô hiệu hóa nút và hiển thị loading
                submitButton.disabled = true;
                submitButton.innerText = 'Đang xử lý...';
                responseDiv.innerText = 'Đang gửi yêu cầu...';
                responseDiv.style.color = 'blue';

                const formData = new FormData(form);

                try {
                    const res = await fetch('http://localhost/doanphp/Back-end/api/story.php', {
                        method: 'POST',
                        body: formData
                    });

                    const text = await res.text();
                    console.log('Raw response:', text || '[empty]');

                    if (!text.trim()) {
                        responseDiv.innerText = 'Lỗi: Server không trả về dữ liệu';
                        responseDiv.style.color = 'red';
                        throw new Error('Phản hồi từ server rỗng');
                    }

                    try {
                        const result = JSON.parse(text);
                        console.log('Parsed result:', result);
                        responseDiv.innerText = result.message || 'Không có thông báo từ server';
                        responseDiv.style.color = result.message && result.message.includes('Lỗi') ? 'red' : 'green';
                        // Cuộn đến thông báo
                        responseDiv.scrollIntoView({ behavior: 'smooth' });
                    } catch (jsonError) {
                        console.error('JSON parse error:', jsonError);
                        responseDiv.innerText = 'Lỗi phân tích dữ liệu: ' + text;
                        responseDiv.style.color = 'red';
                    }
                } catch (error) {
                    console.error('Fetch error:', error);
                    responseDiv.innerText = 'Lỗi kết nối: ' + error.message;
                    responseDiv.style.color = 'red';
                } finally {
                    // Bật lại nút sau khi xử lý xong
                    submitButton.disabled = false;
                    submitButton.innerText = 'Thêm truyện';
                }
            });
        });
    </script>
</body>
</html>