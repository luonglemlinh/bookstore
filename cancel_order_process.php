<?php
include 'connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Yêu cầu không hợp lệ!");
}

$order_id = intval($_POST['order_id']);
$reason   = trim($_POST['reason']);
$user_id  = intval($_SESSION['user_id']);

if ($order_id <= 0 || $reason == "") {
    die("Thiếu dữ liệu!");
}

// Escape lý do
$reason_sql = mysqli_real_escape_string($ocon, $reason);

/* =============================
   1. LẤY TRẠNG THÁI ĐƠN TRƯỚC KHI HỦY
============================= */
$q = mysqli_query($ocon, 
    "SELECT order_status FROM orders WHERE order_id = $order_id AND user_id = $user_id LIMIT 1"
);

if (!$q || mysqli_num_rows($q) == 0) {
    die("Không tìm thấy đơn hoặc không có quyền!");
}

$order = mysqli_fetch_assoc($q);
$prev_status = $order['order_status'];

/* =============================
   2. LƯU YÊU CẦU HỦY
============================= */
mysqli_query($ocon,
    "INSERT INTO cancel_requests(order_id, user_id, reason, status)
     VALUES ($order_id, $user_id, '$reason_sql', 'pending')"
);

/* =============================
   3. CẬP NHẬT ĐƠN → req_cancel
   & LƯU TRẠNG THÁI TRƯỚC KHI HỦY
============================= */
mysqli_query($ocon,
    "UPDATE orders SET 
        previous_status = '$prev_status',
        order_status = 'req_cancel'
     WHERE order_id = $order_id"
);
/* ===================================================
   4. THÊM THÔNG BÁO CHO ADMIN
=================================================== */

$title   = "Yêu cầu hủy đơn hàng";
$message = "Người dùng #$user_id gửi yêu cầu hủy đơn hàng #$order_id";
$type    = "cancel_request";

$sql_noti = "
    INSERT INTO notifications (title, message, type, order_id, is_read, created_at)
    VALUES (?, ?, ?, ?, 0, NOW())
";

$ntf = $ocon->prepare($sql_noti);
$ntf->bind_param("sssi", $title, $message, $type, $order_id);
$ntf->execute();
/* =============================
   4. CHUYỂN HƯỚNG
============================= */
header("Location: order_detail.php?id=$order_id&msg=huy_thanh_cong");
exit();
?>
