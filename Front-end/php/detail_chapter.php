<?php
$chapter_id = $_GET['chapter_id'] ?? null;
if (!$chapter_id) {
    die("Chương không tồn tại.");
}

// Gọi API để lấy dữ liệu chương
$chapterApiUrl = "http://localhost/doanphp/Back-end/api/chapter.php?chapter_id=" . urlencode($chapter_id);
$chapterResponse = @file_get_contents($chapterApiUrl);
$chapterData = json_decode($chapterResponse, true);

if (!$chapterData || isset($chapterData['error'])) {
    die("Không tìm thấy chương.");
}

$title = htmlspecialchars($chapterData['title']);
$content = nl2br(htmlspecialchars($chapterData['content']));

// Lấy bình luận
$commentApiUrl = "http://localhost/doanphp/Back-end/api/comment.php?chapter_id=" . urlencode($chapter_id);
$commentResponse = @file_get_contents($commentApiUrl);
$commentData = json_decode($commentResponse, true);
$comments = $commentData['data'] ?? [];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/header_styles.css">
    <style>
        .chapter-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .chapter-title {
            font-size: 28px;
            font-weight: bold;
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        .chapter-content {
            font-size: 18px;
            color: #444;
            line-height: 1.8;
            margin-bottom: 40px;
        }

        .comment-section {
            margin-top: 50px;
        }

        .comment-form textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            resize: vertical;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        .comment-form button {
            padding: 10px 20px;
            background-color: #007bff;
            border: none;
            color: white;
            border-radius: 6px;
            cursor: pointer;
        }

        .comment-item {
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .comment-item img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            vertical-align: middle;
            margin-right: 10px;
        }

        .comment-item strong {
            font-size: 16px;
        }

        .comment-item p {
            margin: 5px 0 0 50px;
        }

        @media (max-width: 768px) {
            .chapter-container {
                padding: 20px;
            }

            .chapter-title {
                font-size: 22px;
            }

            .chapter-content {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <?php include '../views/header.php'; ?>

    <div class="chapter-container">
        <div class="chapter-title"><?= $title ?></div>
        <div class="chapter-content"><?= $content ?></div>

        <div class="comment-section">
            <h3>Bình luận</h3>
            <div id="comment-list"></div>
            <form class="comment-form" onsubmit="submitComment(event)">
                <textarea id="comment-content" placeholder="Nhập bình luận..." required></textarea>
                <button type="submit">Gửi bình luận</button>
            </form>

            <div id="comment-list">
                <?php foreach ($comments as $comment): ?>
                    <div class="comment-item">
                        <img src="../<?= htmlspecialchars($comment['avatar']) ?>" alt="avatar">
                        <strong><?= htmlspecialchars($comment['username']) ?></strong> 
                        <small><?= date('d/m/Y H:i', strtotime($comment['created_at'])) ?></small>
                        <p><?= nl2br(htmlspecialchars($comment['content'])) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <?php include '../views/footer.php'; ?>

    <script>
        function submitComment(e) {
            e.preventDefault();
            const content = document.getElementById('comment-content').value.trim();
            const chapterId = <?= json_encode($chapter_id) ?>;
            const userId = <?= isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 'null' ?>;

            if (!userId) {
                alert('Bạn cần đăng nhập để bình luận.');
                return;
            }

            if (content === '') {
                alert('Nội dung bình luận không được để trống.');
                return;
            }

            fetch("http://localhost/doanphp/Back-end/api/comment.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    user_id: userId,
                    chapter_id: chapterId,
                    content: content
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    location.reload(); // Hoặc gọi lại fetch để lấy danh sách comment mới
                } else {
                    alert(data.error || 'Đã xảy ra lỗi khi gửi bình luận.');
                }
            })
            .catch(err => {
                console.error(err);
                alert('Lỗi khi gửi bình luận.');
            });
        }
        const chapterId = new URLSearchParams(window.location.search).get('chapter_id');

fetch(`http://localhost/doanphp/Back-end/api/comment.php?chapter_id=${chapterId}`)
  .then(res => res.json())
  .then(data => {
    const list = document.getElementById('comment-list');
    if (!Array.isArray(data)) {
      list.innerHTML = "<p>Không có bình luận</p>";
      return;
    }

    if (data.length === 0) {
      list.innerHTML = "<p>Chưa có bình luận nào.</p>";
    } else {
      data.forEach(comment => {
        const item = document.createElement('div');
        item.classList.add('comment-item');
        item.innerHTML = `
          <strong>${comment.username}</strong> - <em>${comment.created_at}</em><br>
          <p>${comment.content}</p>
          <hr>
        `;
        list.appendChild(item);
      });
    }
  })
  .catch(err => {
    console.error("Lỗi khi tải bình luận:", err);
  });
    </script>
</body>
</html>
