<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chỉnh sửa truyện</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .preview-img {
            max-width: 150px;
            margin-top: 10px;
        }
    </style>
</head>
<body class="container py-4">
    <h2 class="mb-4">Chỉnh sửa truyện</h2>

    <form id="editStoryForm" enctype="multipart/form-data">
        <input type="hidden" name="story_id" id="story_id">

        <div class="mb-3">
            <label for="title" class="form-label">Tiêu đề</label>
            <input type="text" class="form-control" id="title" name="title" required>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Mô tả</label>
            <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
        </div>

        <div class="mb-3">
            <label for="cover_image" class="form-label">Ảnh bìa</label>
            <input type="file" class="form-control" id="cover_image" name="cover_image">
            <img id="currentImage" class="preview-img" src="" alt="Ảnh hiện tại">
            <img id="newImagePreview" class="preview-img d-none" alt="Ảnh mới">
        </div>

        <div class="mb-3">
            <label for="author_id" class="form-label">Tác giả</label>
            <select class="form-select" id="author_id" name="author_id" required></select>
        </div>

        <div class="mb-3">
            <label for="type" class="form-label">Thể loại</label>
            <select class="form-select" id="type" name="type" required></select>
        </div>

        <button type="submit" class="btn btn-warning">Cập nhật truyện</button>
    </form>

    <script>
        const storyId = new URLSearchParams(window.location.search).get("story_id");
        const form = document.getElementById("editStoryForm");
        const currentImg = document.getElementById("currentImage");
        const previewImg = document.getElementById("newImagePreview");

        async function loadAuthors() {
            try {
                const res = await fetch("http://localhost/doanphp/Back-end/api/author.php");
                const authors = await res.json();
                document.getElementById("author_id").innerHTML = authors.map(a => 
                    `<option value="${a.user_id}">${a.username}</option>`).join("");
            } catch (error) {
                console.error('Lỗi tải danh sách tác giả:', error);
            }
        }

        async function loadGenres() {
            try {
                const res = await fetch("http://localhost/doanphp/Back-end/api/genre.php");
                const { data } = await res.json();
                document.getElementById("type").innerHTML = data.map(g =>
                    `<option value="${g.genre_id}">${g.name}</option>`).join("");
            } catch (error) {
                console.error('Lỗi tải danh sách thể loại:', error);
            }
        }

        async function loadStory() {
            try {
                const res = await fetch(`http://localhost/doanphp/Back-end/api/story.php?story_id=${storyId}`);
                const text = await res.text();
                console.log('Story response:', text);
                const story = JSON.parse(text);
                if (story.message) throw new Error(story.message);

                document.getElementById("story_id").value = story.story_id;
                document.getElementById("title").value = story.title;
                document.getElementById("description").value = story.description;
                document.getElementById("author_id").value = story.author_id;
                document.getElementById("type").value = story.type;
                currentImg.src = story.cover_image ? `http://localhost/doanphp/Back-end/${story.cover_image}` : '';
            } catch (error) {
                console.error('Lỗi tải thông tin truyện:', error);
                alert('Lỗi: ' + error.message);
            }
        }

        document.getElementById("cover_image").addEventListener("change", () => {
            const file = document.getElementById("cover_image").files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = () => {
                    previewImg.src = reader.result;
                    previewImg.classList.remove("d-none");
                };
                reader.readAsDataURL(file);
            }
        });

        form.addEventListener("submit", async (e) => {
            e.preventDefault();
            const formData = new FormData(form);
            formData.append("_method", "PUT");

            try {
                const res = await fetch("http://localhost/doanphp/Back-end/api/story.php", {
                    method: "POST",
                    body: formData
                });
                const text = await res.text();
                console.log('Update response:', text);
                const result = JSON.parse(text);
                alert(result.message);
                if (!result.message.includes('Lỗi')) {
                    window.location.reload();
                }
            } catch (error) {
                console.error('Lỗi cập nhật:', error);
                alert('Lỗi: ' + error.message);
            }
        });

        Promise.all([loadAuthors(), loadGenres(), loadStory()]);
    </script>
</body>
</html>