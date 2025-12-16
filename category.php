<?php
include 'connect.php';

// Khởi động session nếu chưa có để nhận thông báo từ add_to_cart
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_id = $_SESSION['user_id'] ?? null;
$category_id = $_GET['id'] ?? null;

// Nếu không có id danh mục
if(!$category_id){
    header("Location: index.php");
    exit();
}

/* ==========================
   LẤY TÊN DANH MỤC
   ========================== */
$cat_query = mysqli_query($ocon, "SELECT name FROM categories WHERE category_id = '$category_id'");
$category = mysqli_fetch_assoc($cat_query);
$category_name = $category['name'] ?? "Danh mục";
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title><?php echo $category_name; ?> | Babo Bookstore</title>
    <link rel="stylesheet" href="CSS/style.css">
    <link rel="stylesheet" href="CSS/header.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<?php if (!empty($_SESSION['cart_msg'])): ?>
    <div class="cart-alert" style="position: fixed; top: 20px; right: 20px; background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; border: 1px solid #c3e6cb; z-index: 9999; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <i class="fas fa-check-circle"></i> <?= $_SESSION['cart_msg']; ?>
    </div>
    <?php unset($_SESSION['cart_msg']); // Xóa ngay sau khi hiện ?>
<?php endif; ?>
<?php include 'header.php'; ?>

<section class="products_cont">

    <h2>Danh mục: <?php echo $category_name; ?></h2>
    <div class="category_container" data-id="<?php echo $category_id; ?>">

    <div class="pro_box_cont">

        <?php
        $books = mysqli_query($ocon,
            "SELECT b.*, MIN(i.url) AS image
             FROM books b 
             LEFT JOIN images i ON b.book_id = i.book_id
             WHERE b.category_id = '$category_id' AND b.status = 'active'
             GROUP BY b.book_id"
        );

        if(mysqli_num_rows($books) > 0){
            while($b = mysqli_fetch_assoc($books)){
        ?>

        <form action="add_to_cart.php" method="POST" class="pro_box">
            
            <input type="hidden" name="book_id" value="<?php echo $b['book_id']; ?>">

            <span class="badge hot">Hot</span>

            <img src="<?php echo $b['image']; ?>" alt="<?php echo $b['title']; ?>">

            <a href="checkout.php?id=<?php echo $b['book_id']; ?>&qty=1" class="buy_now_btn">
                MUA NGAY
            </a>

            <h3><?php echo $b['title']; ?></h3>

            <div class="price_row">
                <span class="price">
                    <?php echo number_format($b['discounted_price'],0,",","."); ?>₫
                </span>

                <input type="number" name="quantity" min="1" value="1" class="qty_input">
            </div>

            <div class="product_actions">
                <a href="book_detail.php?id=<?php echo $b['book_id']; ?>" class="product_btn detail_btn">
                    <i class="fas fa-eye"></i> Chi tiết
                </a>

                <button type="submit" class="product_btn add_to_cart_btn">
                    <i class="fas fa-shopping-cart"></i> Thêm giỏ hàng
                </button>
            </div>

        </form>

        <?php 
            }
        } else {
            echo "<p class='empty'>Chưa có sách trong danh mục này!</p>";
        }
        ?>

    </div>
    </div>
</section>

<script>
    setTimeout(() => {
        let alertBox = document.querySelector('.cart-alert');
        if(alertBox){
            alertBox.style.transition = "opacity 0.5s ease";
            alertBox.style.opacity = "0";
            setTimeout(() => alertBox.remove(), 500);
        }
    }, 2500);
</script>

<?php include 'footer.php'; ?>

</body>
</html>
