<?php
include 'connect.php';
session_start();

$user_id = $_SESSION['user_id'] ?? 0;

/* Nếu chưa đăng nhập */
if ($user_id == 0) {
    header("Location: login.php");
    exit();
}

/* LẤY CART ID */
$cartQuery = mysqli_query($ocon, "SELECT cart_id FROM carts WHERE user_id = $user_id");

if (!$cartQuery) {
    die("Lỗi truy vấn carts: " . mysqli_error($ocon));
}

$cart = mysqli_fetch_assoc($cartQuery);
$cart_id = $cart['cart_id'] ?? 0;

/* Nếu chưa có cart -> tạo mới */
if ($cart_id == 0) {
    mysqli_query($ocon, "INSERT INTO carts (user_id) VALUES ($user_id)");
    $cart_id = mysqli_insert_id($ocon);
}

/* LẤY SẢN PHẨM TRONG GIỎ */
$sql = "
SELECT 
    ci.cart_item_id,
    b.book_id,
    b.title,
    b.discounted_price AS price,
    ci.quantity,
    (b.discounted_price * ci.quantity) AS subtotal,
    (SELECT url FROM images WHERE book_id = b.book_id LIMIT 1) AS image
FROM cart_items ci
JOIN books b ON ci.book_id = b.book_id
WHERE ci.cart_id = $cart_id
";

$items = mysqli_query($ocon, $sql);

if (!$items) {
    die("Lỗi truy vấn items: " . mysqli_error($ocon));
}

/* XÓA SẢN PHẨM */
if (isset($_GET['remove'])) {
    $remove_id = intval($_GET['remove']);
    mysqli_query($ocon, "DELETE FROM cart_items WHERE cart_item_id = $remove_id");
    header("Location: cart.php");
    exit();
}

/* CẬP NHẬT SỐ LƯỢNG */
if (isset($_POST['update_qty'])) {
    $item_id = intval($_POST['item_id']);
    $qty = max(1, intval($_POST['qty']));
    mysqli_query($ocon, "UPDATE cart_items SET quantity = $qty WHERE cart_item_id = $item_id");
    header("Location: cart.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Giỏ hàng</title>

    <link rel="stylesheet" href="CSS/style.css">
    <link rel="stylesheet" href="CSS/cart.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

<?php include 'header.php'; ?>

<div class="cart_container">

    <h2 class="cart_title">Giỏ hàng</h2>

    <?php if (mysqli_num_rows($items) == 0): ?>
        <p class="empty_cart">Giỏ hàng của bạn đang trống.</p>

    <?php else: ?>

        <table class="cart_table">
            <tr>
                <th>Ảnh</th>
                <th>Sách</th>
                <th>Giá</th>
                <th>Số lượng</th>
                <th>Tạm tính</th>
                <th>Xóa</th>
            </tr>

            <?php 
                $total = 0;
                while ($row = mysqli_fetch_assoc($items)): 
                    $total += $row['subtotal'];
            ?>

            <tr>
                <td><img src="<?php echo $row['image']; ?>" class="cart_img"></td>

                <td><?php echo $row['title']; ?></td>

                <td><?php echo number_format($row['price'], 0, ',', '.'); ?>₫</td>

                <td>
                    <form method="post">
                        <input type="hidden" name="item_id" value="<?php echo $row['cart_item_id']; ?>">
                        <input type="number" name="qty" value="<?php echo $row['quantity']; ?>" min="1">
                        <button type="submit" name="update_qty" class="update_btn">
                            Cập nhật
                        </button>
                    </form>
                </td>

                <td><?php echo number_format($row['subtotal'], 0, ',', '.'); ?>₫</td>

                <td>
                    <a href="cart.php?remove=<?php echo $row['cart_item_id']; ?>" class="remove_btn"
                       onclick="return confirm('Xóa sản phẩm này?');">
                        <i class="fa fa-trash"></i>
                    </a>
                </td>
            </tr>

            <?php endwhile; ?>

        </table>
<div class="cart_bottom">

    <div class="cart_total_row">
        <span>Tổng cộng:</span>
        <span><?php echo number_format($total, 0, ',', '.'); ?>₫</span>
    </div>

    <div class="cart_buttons">
        <a href="index.php" class="more_btn">Mua thêm</a>
        <a href="checkout.php" class="checkout_btn">Thanh toán</a>
    </div>

</div>


    <?php endif; ?>

</div>

<?php include 'footer.php'; ?>

</body>
</html>
