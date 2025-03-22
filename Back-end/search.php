<?php
// File: search.php
include 'config.php';

$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kết quả tìm kiếm: <?php echo $keyword; ?> - NetTruyen</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
</head>

<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <div class="search-results-page">
            <h1>Kết quả tìm kiếm cho: "<?php echo $keyword; ?>"</h1>

            <div class="truyen-grid">
                <?php
                if (!empty($keyword)) {
                    $sql = "SELECT t.id, t.ten_truyen, t.hinh_anh, t.tac_gia, t.mo_ta, GROUP_CONCAT(tl.ten_the_loai SEPARATOR ', ') as the_loai 
                            FROM truyen t
                            LEFT JOIN truyen_the_loai ttl ON t.id = ttl.truyen_id
                            LEFT JOIN the_loai tl ON ttl.the_loai_id = tl.id
                            WHERE t.ten_truyen LIKE ? OR t.tac_gia LIKE ? OR t.mo_ta LIKE ?
                            GROUP BY t.id";

                    $stmt = $conn->prepare($sql);
                    $searchParam = "%$keyword%";
                    $stmt->bind_param("sss", $searchParam, $searchParam, $searchParam);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo '<div class="truyen-item">';
                            echo '<a href="truyen.php?id=' . $row['id'] . '">';
                            echo '<img src="' . $row['hinh_anh'] . '" alt="' . $row['ten_truyen'] . '">';
                            echo '<div class="truyen-info">';
                            echo '<h3>' . $row['ten_truyen'] . '</h3>';
                            echo '<p class="tac-gia">Tác giả: ' . $row['tac_gia'] . '</p>';
                            echo '<p class="the-loai">Thể loại: ' . $row['the_loai'] . '</p>';
                            echo '</div>';
                            echo '</a>';
                            echo '</div>';
                        }
                    } else {
                        echo '<div class="no-results-full">Không tìm thấy kết quả nào cho từ khóa "' . $keyword . '"</div>';
                    }

                    $stmt->close();
                } else {
                    echo '<div class="no-results-full">Vui lòng nhập từ khóa tìm kiếm</div>';
                }
                ?>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
        $(document).ready(function() {
            // Mã JavaScript cho tìm kiếm (giống như trong index.php)
            $('#search-input').on('keyup', function() {
                var keyword = $(this).val();
                if (keyword.length > 1) {
                    $.ajax({
                        url: 'search_suggestion.php',
                        type: 'POST',
                        data: {
                            keyword: keyword
                        },
                        success: function(data) {
                            $('#search-results').html(data);
                            $('#search-results').show();
                        }
                    });
                } else {
                    $('#search-results').hide();
                }
            });

            $(document).on('click', function(e) {
                if (!$(e.target).closest('.search-container').length) {
                    $('#search-results').hide();
                }
            });
        });
    </script>
</body>

</html>