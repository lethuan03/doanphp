<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang Chủ - Đọc Truyện</title>
    <link rel="stylesheet" href="Front-end/css/header_styles.css">
    <link rel="stylesheet" href="Front-end/css/style.css">
</head>

<body>
    <?php include __DIR__ . '/Front-end/views/header.php'; ?>

    <div class="container mt-4">
        <h3>Danh sách truyện</h3>
        <div class="story-grid" id="story-list">
            <!-- Danh sách truyện sẽ được tải ở đây -->
        </div>
    </div>
    
    <script>
        fetch('http://localhost/doanphp/Back-end/api/story.php')
    .then(response => response.json())
    .then(data => {
        let storyList = document.getElementById('story-list');
        storyList.classList.add('story-container');

        data.forEach(story => {
            let storyItem = `
                <div class="story-card">
                    <a href="Front-end/php/detail_story.php?story_id=${story.story_id}">
                        <img src="http://localhost/doanphp/Back-end/${story.cover_image}" alt="${story.title}" width="200" height="250">
                    </a>
                    <h5>${story.title}</h5>
                    <p>Chapter ${story.latest_chapter}</p>
                </div>
            `;
            storyList.innerHTML += storyItem;
        });
    })
    .catch(error => console.error('Lỗi:', error));


        function logout() {
            fetch("http://localhost/doanphp/Back-end/api/auth.php?action=logout")
                .then(response => response.json())
                .then(() => {
                    window.location.href = "login.php";
                })
                .catch(error => console.error("Lỗi đăng xuất:", error));
        }
    </script>
    
    <?php include __DIR__ . '/Front-end/views/footer.php'; ?>
</body>

</html>