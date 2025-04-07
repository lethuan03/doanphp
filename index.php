<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Website Đọc Truyện</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <h1>Danh Sách Truyện</h1>
    <div id="stories"></div>

    <h2>Thêm Truyện Mới</h2>
    <input type="text" id="new-title" placeholder="Tiêu đề truyện">
    <textarea id="new-content" placeholder="Nội dung truyện"></textarea>
    <button onclick="addStory()">Thêm Truyện</button>

    <script>
        function loadStories() {
            $.get("api.php?action=getStories", function(data) {
                if (data.status === "success") {
                    let storyList = "";
                    data.stories.forEach(story => {
                        storyList += `<div>
                            <h2>${story.title}</h2>
                            <p>${story.content}</p>
                            <button onclick="loadComments(${story.id})">Xem Bình Luận</button>
                            <div id="comments-${story.id}"></div>
                            <input type="text" id="username-${story.id}" placeholder="Tên của bạn">
                            <textarea id="comment-${story.id}" placeholder="Nhập bình luận"></textarea>
                            <button onclick="addComment(${story.id})">Gửi Bình Luận</button>
                        </div>`;
                    });
                    $("#stories").html(storyList);
                } else {
                    $("#stories").html("<p>Không có truyện nào!</p>");
                }
            });
        }

        function loadComments(storyId) {
            $.get(`api.php?action=getComments&story_id=${storyId}`, function(data) {
                let commentList = "<h3>Bình luận:</h3>";
                if (data.status === "success") {
                    data.comments.forEach(comment => {
                        commentList += `<p><strong>${comment.username}</strong>: ${comment.comment}</p>`;
                    });
                } else {
                    commentList += "<p>Chưa có bình luận nào.</p>";
                }
                $(`#comments-${storyId}`).html(commentList);
            });
        }

        function addComment(storyId) {
            let username = $(`#username-${storyId}`).val();
            let comment = $(`#comment-${storyId}`).val();

            if (username && comment) {
                $.ajax({
                    url: "api.php?action=addComment",
                    type: "POST",
                    contentType: "application/json",
                    data: JSON.stringify({ story_id: storyId, username: username, comment: comment }),
                    success: function(response) {
                        if (response.status === "success") {
                            loadComments(storyId);
                            alert("Bình luận đã được gửi!");
                        } else {
                            alert("Lỗi khi gửi bình luận!");
                        }
                    }
                });
            } else {
                alert("Vui lòng nhập tên và bình luận!");
            }
        }

        function addStory() {
            let title = $("#new-title").val();
            let content = $("#new-content").val();

            if (title && content) {
                $.ajax({
                    url: "api.php?action=addStory",
                    type: "POST",
                    contentType: "application/json",
                    data: JSON.stringify({ title: title, content: content }),
                    success: function(response) {
                        if (response.status === "success") {
                            loadStories();
                            alert("Truyện đã được thêm!");
                        } else {
                            alert("Lỗi khi thêm truyện!");
                        }
                    }
                });
            } else {
                alert("Vui lòng nhập tiêu đề và nội dung!");
            }
        }

        $(document).ready(loadStories);
    </script>
</body>
</html>
