<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Chỉnh sửa truyện</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/header_styles.css">
  <style>
    .preview-img {
      max-width: 150px;
      margin-top: 10px;
    }
  </style>
</head>
<body class="container py-4">
<?php include '../views/header.php'; ?>
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
      <label for="cover_image" class="form-label">Ảnh bìa (chỉ chọn nếu muốn thay)</label>
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
      const res = await fetch("http://localhost/doanphp/Back-end/api/author.php");
      const authors = await res.json();
      document.getElementById("author_id").innerHTML = authors.map(a =>
        `<option value="${a.user_id}">${a.username}</option>`).join("");
    }

    async function loadGenres() {
      const res = await fetch("http://localhost/doanphp/Back-end/api/genre.php");
      const { data } = await res.json();
      document.getElementById("type").innerHTML = data.map(g =>
        `<option value="${g.genre_id}">${g.name}</option>`).join("");
    }

    async function loadStory() {
      const res = await fetch(`http://localhost/doanphp/Back-end/api/story.php?story_id=${storyId}`);
      const story = await res.json();

      // Kiểm tra nếu không có thể loại, hãy thêm lựa chọn mặc định
      const genreSelect = document.getElementById("type");
      if (story.genres && story.genres.length > 0) {
        // Nếu có thể loại, chọn thể loại đầu tiên trong danh sách
        document.getElementById("type").value = story.genres[0].genre_id;
      } else {
        // Nếu không có thể loại, thêm lựa chọn mặc định
        genreSelect.innerHTML = "<option value=''>Chưa có thể loại</option>";
      }

      document.getElementById("story_id").value = story.story_id;
      document.getElementById("title").value = story.title;
      document.getElementById("description").value = story.description;
      document.getElementById("author_id").value = story.author_id;
      currentImg.src = `../../Back-end/${story.cover_image}`;

      // Nếu không có ảnh bìa, ẩn ảnh hiện tại
      if (!story.cover_image) {
        currentImg.style.display = 'none';
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
      formData.append("_method", "PUT"); // Rất quan trọng

      const res = await fetch("http://localhost/doanphp/Back-end/api/story.php", {
        method: "POST", // POST + _method=PUT để sửa
        body: formData
      });

      const result = await res.json();
      alert(result.message || result.error);
    });

    // Load dữ liệu ban đầu
    loadAuthors().then(loadGenres).then(loadStory);
  </script>
  <?php include '../views/footer.php'; ?>
</body>
</html>
