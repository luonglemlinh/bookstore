<?php
include 'connect.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$unread_notif = 0;
// 1. Thông báo (message)
if (isset($message)) {
    foreach ($message as $m) {
        echo '
        <div class="message">
            <span>'.$m.'</span>
            <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
        </div>
        ';
    }
}

// =========================
// 2. LẤY USER & CART
// =========================
$cart_id = 0;
$user_name = "";
$user_role = "";

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Lấy thông tin user
    $user_query = mysqli_query($ocon, "SELECT full_name, role FROM users WHERE user_id = '$user_id'");
    if ($user = mysqli_fetch_assoc($user_query)) {
        $user_name = $user['full_name'];
        $user_role = $user['role'];
    }

// 4. ĐẾM THÔNG BÁO CHƯA ĐỌC 
$unread_notif = 0;

if (!empty($_SESSION['user_id'])) {
    $user_id = intval($_SESSION['user_id']);
    $q = mysqli_query($ocon,
        "SELECT COUNT(*) AS total 
         FROM notifications_customer 
         WHERE user_id=$user_id AND is_read=0"
    );
    $unread_notif = ($q && $row = mysqli_fetch_assoc($q)) ? $row['total'] : 0;
}

    // Lấy giỏ hàng
    $cart_query = mysqli_query($ocon, "SELECT cart_id FROM carts WHERE user_id = '$user_id'");
    
    if (mysqli_num_rows($cart_query) > 0) {
        $cart = mysqli_fetch_assoc($cart_query);
        $cart_id = $cart['cart_id'];
    } else {
        mysqli_query($ocon, "INSERT INTO carts(user_id) VALUES('$user_id')");
        $cart_id = mysqli_insert_id($ocon);
    }
}

// 3. Đếm số sản phẩm trong giỏ
$total_cart_items = 0;

if ($cart_id > 0) {
    $count_cart_items = mysqli_query($ocon, "SELECT * FROM cart_items WHERE cart_id = '$cart_id'");
    $total_cart_items = mysqli_num_rows($count_cart_items);
}
?>

<!-- LINK CSS -->
<link rel="stylesheet" href="CSS/header.css">

<header class="user_header">
    <div class="header_1">
        <div class="user_flex">

            <a href="index.php" class="book_logo">
                <i class="fas fa-book-open"></i> Babo Bookstore
            </a>

            <nav class="navbar">
                <a href="index.php">Trang chủ</a>
                <div class="category_menu">
    <a href="javascript:void(0)" class="category_btn">Danh mục <i class="fas fa-chevron-down"></i></a>

    <div class="category_dropdown">
        <?php
        $categories = mysqli_query($ocon, "SELECT * FROM categories");
        if(mysqli_num_rows($categories) > 0){
            while($cat = mysqli_fetch_assoc($categories)){
        ?>
            <a href="category.php?id=<?php echo $cat['category_id']; ?>">
                <?php echo $cat['name']; ?>
            </a>
        <?php
            }
        } else {
            echo "<span>Chưa có danh mục</span>";
        }
        ?>
    </div>
</div>
                <a href="contact.php">Liên hệ</a>
                <a href="order.php">Đơn hàng</a>
                <a href="purchase_history.php">Lịch sử mua hàng</a>
            </nav>

            <div class="icons">

                <a href="cart.php">
                    <i class="fas fa-shopping-cart"></i>
                    <span>(<?php echo $total_cart_items; ?>)</span>
                </a>

                <!-- ICON THÔNG BÁO -->
                <a href="notification_customer.php" class="notif_icon">
                    <i class="fas fa-bell"></i>
                    <?php if ($unread_notif > 0): ?>
                        (<span class="notif_count"><?= $unread_notif ?></span>)
                    <?php endif; ?>
                </a>


                <!-- USER DROPDOWN -->
                <div class="user-menu">
                    <div id="user-btn" class="user_box">
    <i class="fas fa-user"></i>
    <span><?= $user_name ?: "Tài khoản" ?></span>
</div>

                    <div class="user-dropdown">
                        <a href="account.php">Thông tin tài khoản</a>

    <?php if ($user_role === "admin") {?>
                            <a href="admin_dashboard.php">Quản trị</a>
                        <?php } ?>

                        <a href="logout.php">Đăng xuất</a>
                    </div>
                </div>

            </div>

        </div>
    </div>
</header>

<!-- LINK JS -->
<script src="js/header.js"></script>


<!-- JS DROPDOWN -->
<script>
const userBtn = document.getElementById("user-btn");
const dropdown = document.querySelector(".user-dropdown");

// Mở/đóng khi bấm vào nút user
userBtn.onclick = function(event) {
    event.stopPropagation(); // không cho lan lên document
    dropdown.classList.toggle("active");
};

// Tự tắt dropdown khi click ra ngoài
document.addEventListener("click", function(event) {
    if (!dropdown.contains(event.target) && !userBtn.contains(event.target)) {
        dropdown.classList.remove("active");
    }
});
</script>

