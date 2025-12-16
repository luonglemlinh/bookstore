<?php
include 'connect.php';
session_start();

if (!isset($_GET['id'])) {
    header("Location: admin_orders.php");
    exit;
}

$order_id = intval($_GET['id']);

$q_order = mysqli_query($ocon, "
    SELECT order_status, user_id 
    FROM orders 
    WHERE order_id = $order_id LIMIT 1
");
$order = mysqli_fetch_assoc($q_order);

if (!$order) {
    die("Đơn hàng không tồn tại!");
}

// Chỉ từ chối khi pending
if ($order['order_status'] != 'pending') {
    die("Không thể từ chối đơn này!");
}

// Update trạng thái
mysqli_query($ocon, "
    UPDATE orders 
    SET order_status = 'canceled'
    WHERE order_id = $order_id
");

// Thông báo
$user_id = $order['user_id'];
$type = "order_rejected";
$message = "Đơn hàng #$order_id của bạn đã bị từ chối.";

// Insert noti
mysqli_query($ocon, "
    INSERT INTO notifications_customer (user_id, type, message, reference_id)
    VALUES ($user_id, '$type', '$message', $order_id)
");

header("Location: admin_order_detail.php?id=$order_id&msg=rejected");
exit;
?>
