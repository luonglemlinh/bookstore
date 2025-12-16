<?php
session_start();
include 'connect.php';
date_default_timezone_set('Asia/Ho_Chi_Minh');
// Nếu chưa đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Nếu không phải ADMIN → chặn
if ($_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Gán tên admin
$admin_name = $_SESSION['username'] ?? "Admin";

// Đánh dấu trang hiện tại để active menu
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard | Babo Bookstore</title>
    <link rel="stylesheet" href="CSS/admin.css">
    <link rel="stylesheet" 
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
<?php include 'admin_header.php'; ?>
<?php include 'admin_nav.php'; ?>

<main class="admin_main">

    <div class="admin_topbar">
        <h2>Dashboard</h2>
    </div>

    <div class="admin_dashboard_grid">

        <!-- ======================== ĐƠN ĐANG CHỜ ======================== -->
        <?php
        $pending_total = 0;

        $pending = mysqli_query($ocon, 
            "SELECT total_amount FROM orders WHERE order_status = 'pending'"
        );

        if ($pending) {
            while ($row = mysqli_fetch_assoc($pending)) {
                $pending_total += $row['total_amount'];
            }
        }
        ?>

        <div class="admin_card">
            <i class="fas fa-clock"></i>
            <h3><?= number_format($pending_total,0,',','.') ?>₫</h3>
            <p>Tổng doanh thu đơn đang chờ xử lý</p>
        </div>

        <!-- ======================== ĐƠN HOÀN THÀNH ======================== -->
        <?php
        $completed_total = 0;

        $completed = mysqli_query($ocon, 
            "SELECT total_amount FROM orders WHERE order_status = 'completed'"
        );

        if ($completed) {
            while ($row = mysqli_fetch_assoc($completed)) {
                $completed_total += $row['total_amount'];
            }
        }
        ?>

        <div class="admin_card">
            <i class="fas fa-check"></i>
            <h3><?= number_format($completed_total,0,',','.') ?>₫</h3>
            <p>Tổng doanh thu đơn đã hoàn thành</p>
        </div>

        <!-- ======================== DOANH THU HÔM NAY ======================== -->
        <?php
        $today_revenue = 0;

        $today_query = mysqli_query($ocon,
            "SELECT total_amount FROM orders 
             WHERE order_status = 'completed' 
             AND DATE(order_date) = CURDATE()"
        );

        if ($today_query) {
            while ($row = mysqli_fetch_assoc($today_query)) {
                $today_revenue += $row['total_amount'];
            }
        }
        ?>
        <div class="admin_card">
            <i class="fas fa-coins"></i>
            <h3><?= number_format($today_revenue,0,',','.') ?>₫</h3>
            <p>Doanh thu hôm nay</p>
        </div>

        <!-- ======================== DOANH THU THÁNG NÀY ======================== -->
        <?php
        $month_revenue = 0;

        $month_query = mysqli_query($ocon,
            "SELECT total_amount FROM orders 
             WHERE order_status = 'completed'
             AND MONTH(order_date) = MONTH(CURDATE())
             AND YEAR(order_date) = YEAR(CURDATE())"
        );

        if ($month_query) {
            while ($row = mysqli_fetch_assoc($month_query)) {
                $month_revenue += $row['total_amount'];
            }
        }
        ?>
        <div class="admin_card">
            <i class="fas fa-chart-line"></i>
            <h3><?= number_format($month_revenue,0,',','.') ?>₫</h3>
            <p>Doanh thu tháng này</p>
        </div>


        <!-- ======================== TỔNG SÁCH ======================== -->
        <?php
        $book_query = mysqli_query($ocon, "SELECT book_id FROM books");
        $total_books = $book_query ? mysqli_num_rows($book_query) : 0;
        ?>

        <div class="admin_card">
            <i class="fas fa-book"></i>
            <h3><?= $total_books ?></h3>
            <p>Sách hiện có</p>
        </div>

        <!-- ======================== TỔNG NGƯỜI DÙNG ======================== -->
        <?php
        $user_query = mysqli_query($ocon, "SELECT user_id FROM users WHERE role = 'customer'");
        $total_users = $user_query ? mysqli_num_rows($user_query) : 0;
        ?>

        <div class="admin_card">
            <i class="fas fa-users"></i>
            <h3><?= $total_users ?></h3>
            <p>Người dùng</p>
        </div>

        <!-- ======================== TỔNG DANH MỤC ======================== -->
        <?php
        $cat_query = mysqli_query($ocon, "SELECT category_id FROM categories");
        $total_categories = $cat_query ? mysqli_num_rows($cat_query) : 0;
        ?>

        <div class="admin_card">
            <i class="fas fa-tags"></i>
            <h3><?= $total_categories ?></h3>
            <p>Danh mục</p>
        </div>

        <!-- ======================== TỔNG ĐƠN HÀNG ======================== -->
        <?php
        $order_query = mysqli_query($ocon, "SELECT order_id FROM orders");
        $total_orders = $order_query ? mysqli_num_rows($order_query) : 0;
        ?>

        <div class="admin_card">
            <i class="fas fa-truck"></i>
            <h3><?= $total_orders ?></h3>
            <p>Tổng đơn hàng</p>
        </div>

    </div> <!-- END GRID -->

</main>

</body>
</html>