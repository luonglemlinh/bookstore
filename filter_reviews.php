<?php
include 'connect.php';

$book_id = isset($_GET['book_id']) ? intval($_GET['book_id']) : 0;
$rating = $_GET['rating'] ?? 'all';

if ($book_id <= 0) exit("Sách không tồn tại!");

$where = "r.book_id = $book_id AND r.status='approved'";
if ($rating !== 'all') {
    $rating = intval($rating);
    $where .= " AND r.rating = $rating";
}

$query = "
    SELECT r.review_id, r.rating, r.comment, r.created_at,
           u.full_name AS username
    FROM reviews r
    INNER JOIN users u ON u.user_id = r.user_id
    WHERE $where
    ORDER BY r.created_at DESC
";

$result = mysqli_query($ocon, $query);
if (!$result) exit("Lỗi truy vấn reviews: " . mysqli_error($ocon));

if (mysqli_num_rows($result) == 0) {
    echo '<p class="no_review">Không có đánh giá nào.</p>';
} else {
    while ($rv = mysqli_fetch_assoc($result)) {
        echo '<div class="review_item">';
        echo '<div class="review_user"><strong>'.htmlspecialchars($rv['username']).'</strong>';
        echo '<span class="review_stars">';
        for ($i = 1; $i <= 5; $i++) {
            $active = ($i <= $rv['rating']) ? 'active' : '';
            echo '<i class="fa fa-star '.$active.'"></i>';
        }
        echo '</span></div>';
        echo '<p>'.nl2br(htmlspecialchars($rv['comment'])).'</p>';
        echo '<small>'.$rv['created_at'].'</small>';
        echo '</div>';
    }
}
if (!$result) {
    die("Lỗi truy vấn reviews: " . mysqli_error($ocon));
}
?>
