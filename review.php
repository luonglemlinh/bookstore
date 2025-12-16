<?php
include 'connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['book_id']) || !isset($_GET['order_id'])) {
    die("Thiếu dữ liệu!");
}

$user_id = $_SESSION['user_id'];
$book_id = intval($_GET['book_id']);
$order_id = intval($_GET['order_id']);

/* LẤY THÔNG TIN SẢN PHẨM TRONG ĐƠN */
$sql = "
    SELECT 
        b.title,
        oi.price_at_order,
        oi.quantity,
        (oi.price_at_order * oi.quantity) AS subtotal,
        (SELECT url FROM images WHERE book_id = b.book_id LIMIT 1) AS book_image
    FROM order_items oi
    JOIN books b ON oi.book_id = b.book_id
    JOIN orders o ON o.order_id = oi.order_id
    WHERE oi.book_id = $book_id
      AND oi.order_id = $order_id
      AND o.user_id = $user_id
    LIMIT 1
";

$detail = mysqli_query($ocon, $sql);

if (!$detail || mysqli_num_rows($detail) == 0) {
    die("Không tìm thấy sản phẩm trong đơn hàng!");
}

$item = mysqli_fetch_assoc($detail);
$img = $item['book_image'] ?: "img/placeholder-book.png";
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đánh giá sản phẩm</title>

    <link rel="stylesheet" href="css/review.css">
    <link rel="stylesheet" href="css/style.css">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

<?php include "header.php"; ?>

<div class="review_container">

    <h2>Đánh giá sản phẩm</h2>

    <!-- BOX SẢN PHẨM -->
    <div class="review_item_box">
        <img src="<?= $img ?>" class="review_img">

        <div class="review_info">
            <div class="review_title"><?= htmlspecialchars($item['title']) ?></div>

            <p>Giá mua: <strong><?= number_format($item['price_at_order'], 0, ',', '.') ?>₫</strong></p>
            <p>Số lượng: <strong><?= $item['quantity'] ?></strong></p>
            <p>Tổng tiền: 
                <strong class="review_subtotal">
                    <?= number_format($item['subtotal'], 0, ',', '.') ?>₫
                </strong>
            </p>
        </div>
    </div>

    <!-- FORM GỬI ĐÁNH GIÁ -->
    <form action="submit_review.php" method="POST" class="review_form">
        <input type="hidden" name="book_id" value="<?= $book_id ?>">
        <input type="hidden" name="order_id" value="<?= $order_id ?>">

        <label>Chọn số sao:</label>
        <div class="star_rating">
            <input type="radio" name="rating" id="star5" value="5" required>
            <label for="star5" class="fa fa-star"></label>

            <input type="radio" name="rating" id="star4" value="4">
            <label for="star4" class="fa fa-star"></label>

            <input type="radio" name="rating" id="star3" value="3">
            <label for="star3" class="fa fa-star"></label>

            <input type="radio" name="rating" id="star2" value="2">
            <label for="star2" class="fa fa-star"></label>

            <input type="radio" name="rating" id="star1" value="1">
            <label for="star1" class="fa fa-star"></label>
        </div>

        <label>Bình luận:</label>
        <textarea name="comment" class="review_textarea" placeholder="Cảm nhận của bạn..." required></textarea>

        <button type="submit" class="submit_review_btn">Gửi đánh giá</button>
    </form>

</div>

<?php include "footer.php"; ?>

</body>
</html>
