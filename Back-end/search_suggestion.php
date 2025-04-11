<?php
// File: search_suggestion.php
// Kết nối đến cơ sở dữ liệu
include 'config.php';

// Lấy từ khóa tìm kiếm
$keyword = isset($_POST['keyword']) ? $_POST['keyword'] : '';

// Kiểm tra nếu từ khóa không rỗng (bỏ điều kiện kiểm tra độ dài)
if (!empty($keyword)) {
    // Truy vấn tìm kiếm truyện
    $sql = "SELECT t.id, t.ten_truyen, t.hinh_anh, t.tac_gia, GROUP_CONCAT(tl.ten_the_loai SEPARATOR ', ') as the_loai 
            FROM truyen t
            LEFT JOIN truyen_the_loai ttl ON t.id = ttl.truyen_id
            LEFT JOIN the_loai tl ON ttl.the_loai_id = tl.id
            WHERE t.ten_truyen LIKE ? OR t.tac_gia LIKE ?
            GROUP BY t.id
            LIMIT 5";

    $stmt = $conn->prepare($sql);
    $searchParam = "%$keyword%";
    $stmt->bind_param("ss", $searchParam, $searchParam);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '<a href="truyen.php?id=' . $row['id'] . '" class="search-item">';
            echo '<img src="' . $row['hinh_anh'] . '" alt="' . $row['ten_truyen'] . '">';
            echo '<div class="search-item-info">';
            echo '<div class="search-item-title">' . $row['ten_truyen'] . '</div>';
            echo '<div class="search-item-author">Tác giả: ' . $row['tac_gia'] . '</div>';
            echo '<div class="search-item-genres">Thể loại: ' . $row['the_loai'] . '</div>';
            echo '</div>';
            echo '</a>';
        }
    } else {
        echo '<div class="no-results">Không tìm thấy kết quả</div>';
    }

    $stmt->close();
} else {
    echo '<div class="no-results">Vui lòng nhập từ khóa tìm kiếm</div>';
}

$conn->close();
