<?php
include 'connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];

if (!isset($_GET['id'])) {
    die("Thiếu mã đơn hàng");
}

$order_id = (int)$_GET['id'];

// --------------------------------------------------
// 1. Kiểm tra có tồn tại yêu cầu trả hàng không
// --------------------------------------------------
$check = $ocon->prepare(
    "SELECT id FROM return_requests 
     WHERE order_id = ? AND user_id = ? 
     LIMIT 1"
);

if (!$check) {
    die("Lỗi SQL check return: " . $ocon->error);
}

$check->bind_param("ii", $order_id, $user_id);
$check->execute();
$check->store_result();

if ($check->num_rows == 0) {
    die("Không tồn tại yêu cầu trả hàng.");
}
$check->close();

// --------------------------------------------------
// 2. Xóa yêu cầu trả hàng
// --------------------------------------------------
$del = $ocon->prepare(
    "DELETE FROM return_requests 
     WHERE order_id = ? AND user_id = ?"
);

if (!$del) {
    die("Lỗi SQL delete return: " . $ocon->error);
}

$del->bind_param("ii", $order_id, $user_id);
$del->execute();
$del->close();

// --------------------------------------------------
// 3. Khôi phục trạng thái đơn hàng
// (chọn trạng thái hợp lý nhất cho hệ thống)
// --------------------------------------------------
$restore_status = "completed"; // hoặc 'delivered'

$upd = $ocon->prepare(
    "UPDATE orders 
     SET order_status = ? 
     WHERE order_id = ? AND user_id = ?"
);

if (!$upd) {
    die("Lỗi SQL update order: " . $ocon->error);
}

$upd->bind_param("sii", $restore_status, $order_id, $user_id);
$upd->execute();
$upd->close();

// --------------------------------------------------
// 4. Tạo thông báo cho admin
// --------------------------------------------------
$title   = "Hủy yêu cầu trả hàng";
$message = "Khách hàng #$user_id đã hủy yêu cầu trả hàng cho đơn #$order_id";
$type    = "return_cancel";

$sql_noti = "
    INSERT INTO notifications
    (title, message, type, order_id, is_read, created_at)
    VALUES (?, ?, ?, ?, 0, NOW())
";

$ntf = $ocon->prepare($sql_noti);

if (!$ntf) {
    die("Lỗi SQL thông báo: " . $ocon->error);
}

$ntf->bind_param("sssi", $title, $message, $type, $order_id);
$ntf->execute();
$ntf->close();

// --------------------------------------------------
// 5. Quay lại chi tiết đơn hàng
// --------------------------------------------------
header("Location: order_detail.php?id=$order_id&cancelled=1");
exit();
