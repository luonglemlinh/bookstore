<?php
include "connect.php";
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* ============================
   1. NHẬN DỮ LIỆU TỪ FORM
============================ */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (!isset($_POST['book_id']) || !isset($_POST['rating']) || !isset($_POST['comment'])) {
        die("Thiếu dữ liệu đánh giá!");
    }

    $book_id = intval($_POST['book_id']);
    $rating = intval($_POST['rating']);
    $comment = mysqli_real_escape_string($ocon, $_POST['comment']);

    /* ============================
       2. KIỂM TRA SÁCH CÓ TỒN TẠI
    ============================ */
    $check_book = mysqli_query($ocon, "SELECT * FROM books WHERE book_id = $book_id LIMIT 1");
    if (!$check_book || mysqli_num_rows($check_book) == 0) {
        die("Sách không tồn tại!");
    }

    /* ============================
       3. KIỂM TRA NGƯỜI DÙNG ĐÃ ĐÁNH GIÁ CHƯA
    ============================ */
    $check_review = mysqli_query($ocon, "
        SELECT review_id 
        FROM reviews 
        WHERE user_id = $user_id AND book_id = $book_id
        LIMIT 1
    ");

    if (mysqli_num_rows($check_review) > 0) {
        // Đã đánh giá → chuyển sang edit_review
        $r = mysqli_fetch_assoc($check_review);
        header("Location: edit_review.php?id=" . $r['review_id']);
        exit();
    }

    /* ============================
       4. THÊM ĐÁNH GIÁ
    ============================ */
    $insert = "
        INSERT INTO reviews (user_id, book_id, rating, comment, created_at)
        VALUES ($user_id, $book_id, $rating, '$comment', NOW())
    ";

    if (!mysqli_query($ocon, $insert)) {
        die("Lỗi thêm đánh giá: " . mysqli_error($ocon));
    }

    /* ============================
       5. CHUYỂN HƯỚNG SAU KHI GỬI
    ============================ */
    $_SESSION['review_success'] = "Cảm ơn bạn đã gửi đánh giá!";
    header("Location: purchase_history.php");
    exit();
}

echo "Truy cập không hợp lệ!";
