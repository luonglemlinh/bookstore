<?php
include 'connect.php';
session_start();

$order_id = intval($_GET['id']);

$sql = "UPDATE orders SET order_status = 'accept_return' WHERE order_id = ?";
$stmt = $ocon->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();

$sql2 = "UPDATE return_requests SET status = 'accepted' WHERE order_id = ?";
$stmt2 = $ocon->prepare($sql2);
$stmt2->bind_param("i", $order_id);
$stmt2->execute();

// ===============================
// GỬI THÔNG BÁO CHO KHÁCH HÀNG
// ===============================
$q = mysqli_query($ocon, "SELECT user_id FROM orders WHERE order_id = $order_id LIMIT 1");

if (!$q || mysqli_num_rows($q) == 0) {
    die("Không tìm thấy đơn hàng để gửi thông báo!");
}

$row = mysqli_fetch_assoc($q);
$user_id = intval($row['user_id']);

$type = "return_approved";
$message = "Yêu cầu trả hàng cho đơn #$order_id đã được phê duyệt. Vui lòng làm theo hướng dẫn để gửi hàng.";

$insert_sql = "
    INSERT INTO notifications_customer (user_id, type, message, reference_id)
    VALUES ($user_id, '$type', '$message', $order_id)
";

$ok = mysqli_query($ocon, $insert_sql);
if (!$ok) {
    die("LỖI SQL: " . mysqli_error($ocon));
}
header("Location: admin_orders.php?id=$order_id");
exit();
