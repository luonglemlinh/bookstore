<?php
include 'connect.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Bạn không có quyền thực hiện thao tác này!");
}


if (!isset($_GET['id'])) {
    die("Thiếu mã đơn hàng!");
}

$order_id = intval($_GET['id']);

// Lấy thông tin đơn hàng
$sql = mysqli_query($ocon, "
    SELECT previous_status 
    FROM orders 
    WHERE order_id = $order_id 
    LIMIT 1
");

if (!$sql || mysqli_num_rows($sql) == 0) {
    die("Không tìm thấy đơn hàng!");
}

// DUYỆT HỦY → đổi trạng thái
mysqli_query($ocon, "
    UPDATE orders 
    SET order_status = 'canceled'
    WHERE order_id = $order_id
");

// Cập nhật cancel_requests
mysqli_query($ocon, "
    UPDATE cancel_requests
    SET status = 'approved'
    WHERE order_id = $order_id
");
//insert vào bảng thông báo
//lấy id khách từ id order
$q = mysqli_query($ocon, "SELECT user_id FROM orders WHERE order_id = $order_id LIMIT 1");

if (!$q || mysqli_num_rows($q) == 0) {
    die("Không tìm thấy đơn hàng để gửi thông báo!");
}

$row = mysqli_fetch_assoc($q);
$user_id = intval($row['user_id']);

$type = "cancel_approved";
$message = "Yêu cầu hủy đơn hàng #$order_id của bạn đã được chấp nhận.";
//Insert
$insert_sql = "
    INSERT INTO notifications_customer (user_id, type, message, reference_id)
    VALUES ($user_id, '$type', '$message', $order_id)
";

$ok = mysqli_query($ocon, $insert_sql);

if (!$ok) {
    die("LỖI SQL KHI INSERT THÔNG BÁO: " . mysqli_error($ocon));
}

// Chuyển về trang chi tiết
header("Location: admin_order_detail.php?id=$order_id&msg=approved");
exit();
?>
