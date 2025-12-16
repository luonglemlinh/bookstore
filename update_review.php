<?php
include "connect.php";
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $review_id = intval($_POST['review_id']);
    $book_id = intval($_POST['book_id']);
    $rating = intval($_POST['rating']);
    $comment = mysqli_real_escape_string($ocon, $_POST['comment']);

    // Kiểm tra quyền sửa
    $check = mysqli_query($ocon, "
        SELECT * FROM reviews 
        WHERE review_id = $review_id AND user_id = $user_id 
        LIMIT 1
    ");

    if (!$check || mysqli_num_rows($check) == 0) {
        die("Không có quyền sửa đánh giá này!");
    }

    // Update
    $update = "
        UPDATE reviews
        SET rating = $rating,
            comment = '$comment',
            created_at = NOW()
        WHERE review_id = $review_id
    ";

    if (!mysqli_query($ocon, $update)) {
        die("Lỗi cập nhật: " . mysqli_error($ocon));
    }

    $_SESSION['review_success'] = "Cập nhật đánh giá thành công!";
    header("Location: purchase_history.php");
    exit();
}

echo "Truy cập không hợp lệ!";
