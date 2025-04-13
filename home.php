<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang Chủ - Đọc Truyện</title>
    <link rel="stylesheet" href="Front-end/css/header_styles.css">
    <link rel="stylesheet" href="Front-end/css/style.css">
    <style>
        /* CSS cho phần top truyện */
        .top-stories-section {
            background-color: #f8f9fa;
            padding: 20px 0;
            margin-bottom: 20px;
        }
        
        .top-stories-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }
        
        .top-stories-title {
            color: #343a40;
            font-size: 24px;
            margin-bottom: 15px;
            border-bottom: 2px solid #dc3545;
            padding-bottom: 8px;
            display: inline-block;
        }
        
        .top-stories-list {
            display: flex;
            overflow-x: auto;
            gap: 15px;
            padding-bottom: 10px;
        }
        
        .top-story-card {
            min-width: 150px;
            max-width: 150px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .top-story-card:hover {
            transform: translateY(-5px);
        }
        
        .top-story-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 8px 8px 0 0;
        }
        
        .top-story-info {
            padding: 10px;
        }
        
        .top-story-info h5 {
            margin: 0 0 5px 0;
            font-size: 14px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .top-story-info p {
            margin: 0;
            font-size: 12px;
            color: #6c757d;
        }
        
        .rank-badge {
            position: absolute;
            top: 5px;
            left: 5px;
            background-color: #dc3545;
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 12px;
        }
    </style>
</head>

<body>
    <?php include __DIR__ . '/Front-end/views/header.php'; ?>

    <!-- Phần Top Truyện Theo Tuần -->
    <section class="top-stories-section">
        <div class="top-stories-container">
            <h3 class="top-stories-title">Top Truyện Tuần Này</h3>
            <div class="top-stories-list" id="top-stories-week">
                <!-- Top truyện sẽ được tải ở đây -->
            </div>
        </div>
    </section>

    <div class="container mt-4">
        <h3>Danh sách truyện</h3>
        <div class="story-grid" id="story-list">
            <!-- Danh sách truyện sẽ được tải ở đây -->
        </div>
    </div>
    
    <script>
    // Tải dữ liệu top truyện theo tuần
    fetch('http://localhost/doanphp/Back-end/api/top.php?type=week')
    .then(response => response.json())
    .then(response => {
        const topStories = response.data || [];
        
        let topStoriesList = document.getElementById('top-stories-week');
        
        // Hiển thị tối đa 10 truyện trong top
        topStories.slice(0, 10).forEach((story, index) => {
            let storyItem = `
                <div class="top-story-card">
                    <div style="position: relative;">
                        <a href="Front-end/php/detail_story.php?story_id=${story.story_id}">
                            <div class="rank-badge">${index + 1}</div>
                            <img src="http://localhost/doanphp/Back-end/${story.cover_image}" alt="${story.title}">
                        </a>
                    </div>
                    <div class="top-story-info">
                        <h5>${story.title}</h5>
                        <p>Lượt đọc: ${story.views || 0}</p>
                    </div>
                </div>
            `;
            topStoriesList.innerHTML += storyItem;
        });
    })
    .catch(error => console.error('Lỗi khi tải top truyện:', error));

    // Tải danh sách truyện (code hiện tại của bạn)
    fetch('http://localhost/doanphp/Back-end/api/story.php')
    .then(response => response.json())
    .then(response => {
        const stories = response.data; // Lấy mảng truyện từ key `data`

        let storyList = document.getElementById('story-list');
        storyList.classList.add('story-container');

        stories.forEach(story => {
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