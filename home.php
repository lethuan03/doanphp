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

    <div class="container">
        <!-- Thanh trượt truyện đề cử -->
        <h3>Truyện Đề Cử HOT</h3>
        <div class="slider">
            <div class="slider-container" id="hot-stories">
                <!-- Truyện HOT sẽ được tải động -->
            </div>
        </div>

        <div class="main-content">
            <!-- Danh sách truyện mới cập nhật -->
            <div class="story-list">
                <h3>Truyện Mới Nhất</h3>
                <div class="story-grid" id="story-list">
                    <!-- Danh sách truyện sẽ được tải ở đây -->
                </div>
            </div>

            <!-- Top truyện bảng xếp hạng -->
            <div class="top-ranking">
                <h3>Top Truyện</h3>
                <ul id="top-stories">
                    <!-- Top truyện sẽ được tải động -->
                </ul>
            </div>
        </div>
    </div>

    <script>
        // Tải danh sách truyện mới nhất
        fetch('http://localhost/doanphp/Back-end/api/story.php')
        .then(response => response.json())
        .then(data => {
            let storyList = document.getElementById('story-list');

            data.forEach(story => {
                let storyItem = `
                    <div class="story-card">
                        <a href="Front-end/php/detail_story.php?story_id=${story.story_id}">
                            <img src="http://localhost/doanphp/uploads/${story.cover_image}" alt="${story.title}">
                        </a>
                        <h5>${story.title}</h5>
                        <p>Chapter ${story.latest_chapter}</p>
                    </div>
                `;
                storyList.innerHTML += storyItem;
            });
        })
        .catch(error => console.error('Lỗi:', error));

        // Tải danh sách truyện HOT
        fetch('http://localhost/doanphp/Back-end/api/story.php')
        .then(response => response.json())
        .then(data => {
            let hotStories = document.getElementById('hot-stories');

            data.forEach(story => {
                let hotItem = `
                    <div class="hot-story">
                        <a href="Front-end/php/detail_story.php?story_id=${story.story_id}">
                            <img src="http://localhost/doanphp/uploads/${story.cover_image}" alt="${story.title}">
                        </a>
                        <h5>${story.title}</h5>
                    </div>
                `;
                hotStories.innerHTML += hotItem;
            });
        })
        .catch(error => console.error('Lỗi:', error));

        // Tải danh sách top truyện
        fetch('http://localhost/doanphp/Back-end/api/top_stories.php')
        .then(response => response.json())
        .then(data => {
            let topStories = document.getElementById('top-stories');

            data.forEach((story, index) => {
                let topItem = `
                    <li>
                        <span class="rank">${index + 1}</span>
                        <a href="Front-end/php/detail_story.php?story_id=${story.story_id}">
                            ${story.title}
                        </a>
                        <p>Chapter ${story.latest_chapter}</p>
                    </li>
                `;
                topStories.innerHTML += topItem;
            });
        })
        .catch(error => console.error('Lỗi:', error));
    </script>

    <?php include __DIR__ . '/Front-end/views/footer.php'; ?>
</body>

</html>
