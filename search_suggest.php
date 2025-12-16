<?php
include 'connect.php';

header('Content-Type: text/html; charset=utf-8');

if (!isset($_POST['keyword']) || trim($_POST['keyword']) === '') {
    echo '';
    exit;
}

$keyword = trim($_POST['keyword']);
$keywordLower = mb_strtolower($keyword, 'UTF-8');
$keywordEsc = mysqli_real_escape_string($ocon, $keywordLower);

/* 1) Tìm các tiêu đề bắt đầu bằng từ nhập */
$sql = "
    SELECT book_id, title
    FROM books
    WHERE status = 'active'
      AND (
            LOWER(title) LIKE '{$keywordEsc}%'
         OR LOWER(title) LIKE '% {$keywordEsc}%'
      )
    ORDER BY title ASC
    LIMIT 10
";

$result = mysqli_query($ocon, $sql);

/* 2) Nếu không có kết quả, fallback */
if (!$result || mysqli_num_rows($result) == 0) {
    $sql2 = "
        SELECT book_id, title
        FROM books
        WHERE status = 'active'
          AND LOWER(title) LIKE '%{$keywordEsc}%'
        ORDER BY title ASC
        LIMIT 10
    ";
    $result = mysqli_query($ocon, $sql2);
}

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {

        $id = $row['book_id'];
        $title = htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8');

        echo '
        <div class="suggest_item" 
             data-id="'.$id.'" 
             onclick="selectSuggestion(this.dataset.id)">
            <i class="fas fa-search"></i> '.$title.'
        </div>';
    }
} else {
    echo '<div class="suggest_item no_result">Sách chưa có trong cửa hàng</div>';
}

?>