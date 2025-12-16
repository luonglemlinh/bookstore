<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    die("Thiếu mã đơn hàng");
}

$order_id = intval($_GET['id']);
$user_id  = intval($_SESSION['user_id']);
$role     = $_SESSION['role'] ?? 'customer';

/*
 ADMIN: được cập nhật mọi đơn shipping
 CUSTOMER: chỉ cập nhật đơn của mình + shipping
*/

if ($role === 'admin') {

    $check_sql = "
        SELECT order_id 
        FROM orders 
        WHERE order_id = $order_id 
          AND order_status = 'shipping'
        LIMIT 1
    ";

} else {

    $check_sql = "
        SELECT order_id 
        FROM orders 
        WHERE order_id = $order_id 
          AND user_id = $user_id
          AND order_status = 'shipping'
        LIMIT 1
    ";
}

$check_rs = mysqli_query($ocon, $check_sql);

if (mysqli_num_rows($check_rs) == 0) {
    die("Không thể cập nhật đơn hàng!");
}

/* Cập nhật trạng thái */
$update_sql = "
    UPDATE orders
    SET 
        order_status = 'completed',
        payment_status = 'paid'
    WHERE order_id = $order_id
";

mysqli_query($ocon, $update_sql);

/* Điều hướng theo role */
if ($role === 'admin') {
    header("Location: admin_order_detail.php?id=$order_id&msg=completed");
} else {
    header("Location: order_detail.php?id=$order_id&msg=completed");
}
exit();
