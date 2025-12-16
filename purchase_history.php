<?php
include 'connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* ==========================================
    LẤY TẤT CẢ SẢN PHẨM ĐÃ MUA (ĐƠN HOÀN THÀNH)
========================================== */
$query = "
    SELECT 
        oi.book_id, 
        b.title, 
        oi.price_at_order,
        b.book_id,
        (SELECT url FROM images WHERE book_id = b.book_id LIMIT 1) AS book_image,
        o.order_id,
        o.order_date
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.order_id
    JOIN books b ON oi.book_id = b.book_id
    WHERE o.user_id = $user_id 
      AND o.order_status = 'completed'
    ORDER BY o.order_date DESC
";

$result = mysqli_query($ocon, $query);

function e($v){ return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
$placeholder_img = "img/placeholder-book.png";
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sản phẩm đã mua</title>
    <link rel="stylesheet" href="css/purchase_history.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/order.css">

        
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

</head>
<body>

<?php include 'header.php'; ?>

<div class="purchase_container">
    <h2>Lịch sử mua hàng</h2>
    
    <?php if (isset($_SESSION['review_success'])): ?>
    <p class="success_message"><?= $_SESSION['review_success']; ?></p>
    <?php unset($_SESSION['review_success']); ?>
<?php endif; ?>


    <?php if (mysqli_num_rows($result) == 0): ?>
        <p class="empty">Bạn chưa mua sản phẩm nào.</p>
    <?php else: ?>

        <?php
        while ($row = mysqli_fetch_assoc($result)):

            // kiểm tra user đã đánh giá sách chưa
            $book_id = $row['book_id'];
            $review_q = mysqli_query($ocon, "
                SELECT * FROM reviews 
                WHERE user_id = $user_id AND book_id = $book_id
                LIMIT 1
            ");
            $review = mysqli_fetch_assoc($review_q);

            $img = $row['book_image'] ?: $placeholder_img;
        ?>

        <div class="purchase_item">
            <img src="<?= e($img) ?>" class="ph_img">

            <div class="ph_info">
                <div class="ph_title"><?= e($row['title']) ?></div>
                <div class="ph_date">Mua ngày: <?= e($row['order_date']) ?></div>
                <div class="ph_price"><?= number_format($row['price_at_order'], 0, ',', '.') ?>₫</div>
            </div>

            <div class="ph_actions">
                <!-- NÚT MUA LẠI -->
                <a href="checkout.php?id=<?= $row['book_id'] ?>&qty=1" class="repurchase_btn">Mua lại</a>


                <!-- NÚT ĐÁNH GIÁ -->
               <?php if (!$review): ?>
                    <a href="review.php?book_id=<?= $book_id ?>&order_id=<?= $row['order_id'] ?>" 
                        class="review_btn">
                        Đánh giá
                        </a>

                <?php endif; ?>
                <?php if ($review): ?>
                    <a href="edit_review.php?id=<?= $review['review_id'] ?>" class="review_done_btn">
                        Sửa đánh giá
                    </a>
                <?php endif; ?>


            </div>

        </div>

        <?php endwhile; ?>

    <?php endif; ?>

</div>

<?php include 'footer.php'; ?>

</body>
</html>
