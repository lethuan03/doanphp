<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Top Truyện Tuần</title>
    <style>
        .top-stories-sidebar {
            position: fixed;
            top: 166px;
            right: 50px;
            width: 300px;
            background-color: #fff;
            border-left: 1px solid #ddd;
            box-shadow: -2px 0 5px rgba(0,0,0,0.05);
            padding: 10px;
            font-family: Arial, sans-serif;
            z-index: 999;
            border-radius: 15px;
        }

        .top-stories-title {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
            border-bottom: 2px solid #f66;
            padding-bottom: 5px;
        }

        .top-story-item {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            border-bottom: 1px dashed #ddd;
            padding-bottom: 8px;
        }

        .top-story-rank {
            font-size: 18px;
            font-weight: bold;
            color: #f66;
            width: 25px;
        }

        .top-story-thumbnail {
            width: 40px;
            height: 50px;
            object-fit: cover;
            margin-right: 8px;
            border-radius: 4px;
        }

        .top-story-info {
            flex: 1;
        }

        .top-story-title {
            font-size: 14px;
            color: #222;
            font-weight: bold;
            display: block;
            text-decoration: none;
        }

        .top-story-chapter {
            font-size: 13px;
            color: #666;
        }
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

<section class="top-stories-sidebar">
    <div class="top-stories-container">
        <h3 class="top-stories-title">Top Truyện Tuần Này</h3>
        <div class="top-stories-list" id="top-stories-week">

            <!-- Dữ liệu có thể lấy từ API -->
            <?php
            // Mẫu dữ liệu tĩnh (bạn có thể thay bằng dữ liệu từ API)
            $topStories = [

                
            ];

            $rank = 1;
            foreach ($topStories as $story): ?>
                <div class="top-story-item">
                    <div class="top-story-rank"><?php echo str_pad($rank, 2, '0', STR_PAD_LEFT); ?></div>
                    <img src="<?php echo $story['image']; ?>" alt="" class="top-story-thumbnail">
                    <div class="top-story-info">
                        <a href="detail_story.php?story_id=<?php echo $story['id']; ?>" class="top-story-title">
                            <?php echo htmlspecialchars($story['title']); ?>
                        </a>
                        <div class="top-story-chapter"><?php echo $story['chapter']; ?></div>
                    </div>
                </div>
            <?php $rank++; endforeach; ?>

        </div>
    </div>
</section>
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
    </script>
</body>
</html>
