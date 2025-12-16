<?php
include 'connect.php';
session_start();

/* LẤY ID SÁCH */
$book_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($book_id <= 0) die("Sách không tồn tại!");

$user_id = $_SESSION['user_id'] ?? 0;

/* LẤY THÔNG TIN SÁCH */
$query = "
    SELECT b.*,
           (SELECT url FROM images WHERE book_id = b.book_id LIMIT 1) AS image,
           c.name AS category_name,
           p.name AS publisher_name
    FROM books b
    LEFT JOIN categories c ON c.category_id = b.category_id
    LEFT JOIN publishers p ON p.publisher_id = b.publisher_id
    WHERE b.book_id = $book_id
";

$result = mysqli_query($ocon, $query);
if (!$result) die("Lỗi truy vấn sách: " . mysqli_error($ocon));

$book = mysqli_fetch_assoc($result);
if (!$book) die("Không tìm thấy sách!");


/* LẤY ĐÁNH GIÁ — SỬA: sử dụng full_name */
$reviewQuery = "
    SELECT r.review_id, r.rating, r.comment, r.created_at,
           u.full_name AS username
    FROM reviews r
    INNER JOIN users u ON u.user_id = r.user_id
    WHERE r.book_id = $book_id and r.status='approved'
    ORDER BY r.created_at DESC
";

$reviews = mysqli_query($ocon, $reviewQuery);
if (!$reviews) die("Lỗi truy vấn reviews: " . mysqli_error($ocon));

/* GIÁ */
$price = number_format($book['discounted_price'], 0, ',', '.');
$old_price = number_format($book['price'], 0, ',', '.');

?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title><?php echo $book['title']; ?></title>

    <link rel="stylesheet" href="CSS/style.css">
    <link rel="stylesheet" href="CSS/book_detail.css">
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>


<body>

<?php include 'header.php'; ?>

<div class="detail_container">

    <div class="detail_left">
        <img src="<?php echo $book['image']; ?>" class="detail_img">
    </div>

    <div class="detail_right">

        <h1 class="detail_title"><?php echo $book['title']; ?></h1>

        <div class="detail_price">
            <span class="new_price"><?php echo $price; ?>₫</span>
            <span class="old_price"><?php echo $old_price; ?>₫</span>
        </div>

        <p class="detail_short">
            <?php echo mb_substr($book['description'], 0, 180) . "..."; ?>
        </p>

        <div class="quantity_box">
            <label>Số lượng:</label>
            <input type="number" id="qty" value="1" min="1">
        </div>

        <div class="detail_btns">
            <button class="buy_now_btn"
                    onclick="window.location.href='checkout.php?book_id=<?php echo $book_id; ?>&qty='+document.getElementById('qty').value">
                Mua ngay
            </button>

            <button class="add_cart_btn"
                    onclick="window.location.href='add_to_cart.php?id=<?php echo $book_id; ?>&qty='+document.getElementById('qty').value">
                Thêm vào giỏ
            </button>
        </div>

    </div>
</div>


<!-- ==============================
     THÔNG TIN + ĐÁNH GIÁ
============================== -->
<div class="detail_section">

    <h2 class="section_title">📘 Thông tin chi tiết</h2>

    <div class="info_block">
        <p><strong>Thể loại:</strong> <?php echo $book['category_name']; ?></p>
        <p><strong>Nhà xuất bản:</strong> <?php echo $book['publisher_name']; ?></p>
        <p><strong>Năm xuất bản:</strong> <?php echo $book['publish_year']; ?></p>
        <p><strong>Số trang:</strong> <?php echo $book['pages']; ?></p>

        <p><strong>Mô tả:</strong></p>
        <p><?php echo nl2br($book['description']); ?></p>
    </div>

    <h2 class="section_title">⭐ Đánh giá & Bình luận</h2>
    <div class="review_filter">
    <label for="filter_rating">Lọc theo đánh giá:</label>
    <select id="filter_rating">
        <option value="all">Tất cả</option>
        <option value="5">5 ⭐</option>
        <option value="4">4 ⭐</option>
        <option value="3">3 ⭐</option>
        <option value="2">2 ⭐</option>
        <option value="1">1 ⭐</option>
    </select>
</div>


    <div class="review_block">

        <?php if (mysqli_num_rows($reviews) == 0): ?>

            <p class="no_review">Chưa có đánh giá nào cho cuốn sách này.</p>

        <?php else: ?>

            <?php while ($rv = mysqli_fetch_assoc($reviews)): ?>
                <div class="review_item">

                    <div class="review_user">
                        <strong><?php echo $rv['username']; ?></strong>

                        <span class="review_stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fa fa-star <?php echo ($i <= $rv['rating']) ? 'active' : ''; ?>"></i>
                            <?php endfor; ?>
                        </span>
                    </div>

                    <p><?php echo nl2br($rv['comment']); ?></p>
                    <small><?php echo $rv['created_at']; ?></small>

                </div>
            <?php endwhile; ?>

        <?php endif; ?>

    </div>

</div>
<script>
document.getElementById('filter_rating').addEventListener('change', function() {
    const rating = this.value;
    const bookId = <?php echo $book_id; ?>;

    const xhr = new XMLHttpRequest();
    xhr.open('GET', `filter_reviews.php?book_id=${bookId}&rating=${rating}`, true);
    xhr.onload = function() {
        if (this.status === 200) {
            document.querySelector('.review_block').innerHTML = this.responseText;
        }
    };
    xhr.send();
});
</script>

<?php include 'footer.php'; ?>

</body>
</html>
