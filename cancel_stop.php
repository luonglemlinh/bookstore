<?php
include 'connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    die("Vui lòng đăng nhập.");
}

$user_id = intval($_SESSION['user_id']);
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($order_id <= 0) {
    die("Thiếu mã đơn hàng.");
}

// Lấy đơn hàng
$q = "SELECT order_id, user_id, order_status, previous_status 
      FROM orders 
      WHERE order_id = $order_id LIMIT 1";
$r = mysqli_query($ocon, $q);

if (!$r || mysqli_num_rows($r) == 0) {
    die("Không tìm thấy đơn hàng.");
}

$order = mysqli_fetch_assoc($r);

if ($order['user_id'] != $user_id) {
    die("Bạn không có quyền thao tác trên đơn này.");
}

// Chỉ cho dừng hủy khi trạng thái đang req_cancel
if ($order['order_status'] !== 'req_cancel') {
    die("Đơn hàng không trong trạng thái yêu cầu hủy.");
}

// Lấy trạng thái trước đó
$previous = $order['previous_status'] ?: 'pending';

// Xóa yêu cầu hủy trong bảng cancel_requests
mysqli_query($ocon, "DELETE FROM cancel_requests WHERE order_id = $order_id");

// Cập nhật đơn về trạng thái cũ
mysqli_query($ocon, "
    UPDATE orders 
    SET order_status = '$previous',
        previous_status = NULL
    WHERE order_id = $order_id
");
/* =============================
   THÔNG BÁO CHO ADMIN
============================= */
$title   = "Người dùng đã hủy yêu cầu hủy đơn";
$message = "Khách hàng #$user_id đã dừng yêu cầu hủy đơn #$order_id";
$type    = "cancel_stop";

$sqlNtf = "
    INSERT INTO notifications (title, message, type, order_id, is_read, created_at)
    VALUES (?, ?, ?, ?, 0, NOW())
";

$ntf = $ocon->prepare($sqlNtf);
$ntf->bind_param("sssi", $title, $message, $type, $order_id);
$ntf->execute();
// Chuyển về trang chi tiết đơn
header("Location: order_detail.php?id=$order_id&msg=stop_cancel_success");
exit();
?>
