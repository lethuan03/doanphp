<div id="comment-section">
    <h3>Bình luận</h3>
    <div id="comment-list"></div>

    <h4>Viết bình luận</h4>
    <input type="text" id="user" placeholder="Tên bạn"><br><br>
    <textarea id="content" placeholder="Nhập nội dung bình luận..."></textarea><br>
    <button onclick="submitComment()">Gửi bình luận</button>
</div>

<script>
const storyId = 1; // giả lập ID truyện

function loadComments() {
    fetch(`../../Back-end/api/comment.php?story_id=${storyId}`)
        .then(res => res.json())
        .then(data => {
            const commentList = document.getElementById("comment-list");
            commentList.innerHTML = "";
            data.forEach(c => {
                commentList.innerHTML += `<p><strong>${c.user}</strong>: ${c.content} <em>(${c.created_at})</em></p>`;
            });
        });
}

function submitComment() {
    const user = document.getElementById("user").value;
    const content = document.getElementById("content").value;
    if (!user || !content) {
        alert("Vui lòng nhập đầy đủ tên và nội dung!");
        return;
    }

    fetch("../../Back-end/api/comment.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ story_id: storyId, user, content })
    })
    .then(res => res.json())
    .then(data => {
        alert("Gửi bình luận thành công!");
        loadComments();
        document.getElementById("content").value = "";
    });
}

loadComments(); // gọi khi load trang
</script>
