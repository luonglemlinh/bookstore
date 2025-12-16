<?php
include 'connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    die("Thiếu ID đánh giá!");
}

$user_id = $_SESSION['user_id'];
$review_id = intval($_GET['id']);

/* LẤY THÔNG TIN ĐÁNH GIÁ */
$query = "
    SELECT r.*, b.title,
           (SELECT url FROM images WHERE book_id = b.book_id LIMIT 1) AS book_image,
           oi.price_at_order,
           oi.quantity,
           (oi.price_at_order * oi.quantity) AS subtotal
    FROM reviews r
    JOIN books b ON r.book_id = b.book_id
    LEFT JOIN order_items oi ON oi.book_id = b.book_id
    LEFT JOIN orders o ON o.order_id = oi.order_id
    WHERE r.review_id = $review_id
      AND r.user_id = $user_id
    LIMIT 1
";

$result = mysqli_query($ocon, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    die("Không tìm thấy đánh giá hoặc không có quyền!");
}

$review = mysqli_fetch_assoc($result);

$title = $review['title'];
$rating = $review['rating'];
$comment = $review['comment'];

$img = $review['book_image'] ?: "img/placeholder-book.png";
$price = $review['price_at_order'] ?: 0;
$qty = $review['quantity'] ?: 0;
$subtotal = $review['subtotal'] ?: 0;

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sửa đánh giá</title>

    <link rel="stylesheet" href="css/review.css">
    <link rel="stylesheet" href="css/style.css">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
<?php include "header.php"; ?>

<div class="review_container">

    <h2>Sửa đánh giá</h2>

    <!-- BOX THÔNG TIN SẢN PHẨM -->
    <div class="review_item_box">
        <img src="<?= $img ?>" class="review_img">

        <div class="review_info">
            <div class="review_title"><?= htmlspecialchars($title) ?></div>

            <p>Giá mua: <strong><?= number_format($price, 0, ',', '.') ?>₫</strong></p>
            <p>Số lượng: <strong><?= $qty ?></strong></p>
            <p>Tổng tiền:
                <strong class="review_subtotal">
                    <?= number_format($subtotal, 0, ',', '.') ?>₫
                </strong>
            </p>
        </div>
    </div>

    <!-- FORM SỬA ĐÁNH GIÁ -->
    <form action="update_review.php" method="POST" class="review_form">

        <input type="hidden" name="review_id" value="<?= $review_id ?>">
        <input type="hidden" name="book_id" value="<?= $review['book_id'] ?>">

        <label>Chọn số sao:</label>
        <div class="star_rating">

            <input type="radio" name="rating" id="star5" value="5" <?= ($rating==5 ? "checked" : "") ?>>
            <label for="star5" class="fa fa-star"></label>

            <input type="radio" name="rating" id="star4" value="4" <?= ($rating==4 ? "checked" : "") ?>>
            <label for="star4" class="fa fa-star"></label>

            <input type="radio" name="rating" id="star3" value="3" <?= ($rating==3 ? "checked" : "") ?>>
            <label for="star3" class="fa fa-star"></label>

            <input type="radio" name="rating" id="star2" value="2" <?= ($rating==2 ? "checked" : "") ?>>
            <label for="star2" class="fa fa-star"></label>

            <input type="radio" name="rating" id="star1" value="1" <?= ($rating==1 ? "checked" : "") ?>>
            <label for="star1" class="fa fa-star"></label>
        </div>

        <label>Bình luận:</label>
        <textarea name="comment" class="review_textarea" required><?= htmlspecialchars($comment) ?></textarea>

        <button type="submit" class="submit_review_btn">Cập nhật đánh giá</button>

    </form>

</div>

<?php include "footer.php"; ?>

</body>
</html>
