<?php
include 'connect.php';
session_start();

if (!isset($_GET['id'])) {
    header("Location: admin_orders.php");
    exit;
}

$order_id = intval($_GET['id']);

// Lấy thông tin đơn hàng
$sql = "SELECT order_status, user_id FROM orders WHERE order_id = $order_id LIMIT 1";
$result = mysqli_query($ocon, $sql);
$order = mysqli_fetch_assoc($result);

if (!$order) {
    die("Đơn hàng không tồn tại!");
}

// Chỉ được chuyển sang shipping nếu đang là confirmed
if ($order['order_status'] != 'confirmed') {
    die("Chỉ có thể bàn giao khi đơn đang ở trạng thái 'Đã xác nhận'!");
}

// Cập nhật trạng thái sang 'shipping'
$update = "UPDATE orders SET order_status = 'shipping' WHERE order_id = $order_id";
mysqli_query($ocon, $update);


// ===============================
// GỬI THÔNG BÁO CHO KHÁCH HÀNG
// ===============================

$user_id = intval($order['user_id']);
$type = "order_shipping";
$message = "Đơn hàng #$order_id của bạn đã được bàn giao cho đơn vị vận chuyển.";

// Insert vào bảng thông báo
$insert_sql = "
    INSERT INTO notifications_customer (user_id, type, message, reference_id)
    VALUES ($user_id, '$type', '$message', $order_id)
";

$ok = mysqli_query($ocon, $insert_sql);

if (!$ok) {
    die("LỖI SQL KHI INSERT THÔNG BÁO: " . mysqli_error($ocon));
}


// Quay lại trang quản trị
header("Location: admin_orders.php?ship_success=1");
exit;
?>
