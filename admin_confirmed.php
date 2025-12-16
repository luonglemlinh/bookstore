<?php
include 'connect.php';
session_start();

// Kiểm tra quyền Admin
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    // header("Location: login.php"); // Bỏ comment nếu muốn chặn chặt chẽ
    // exit;
}

if (!isset($_GET['id'])) {
    header("Location: admin_orders.php");
    exit;
}

$order_id = intval($_GET['id']);

// 1. Lấy thông tin đơn hàng
$sql = "SELECT order_status, user_id FROM orders WHERE order_id = $order_id";
$result = mysqli_query($ocon, $sql);
$order = mysqli_fetch_assoc($result);

if (!$order) {
    die("Đơn hàng không tồn tại!");
}

// 2. Chỉ được xác nhận nếu trạng thái đang chờ duyệt
if ($order['order_status'] != 'pending') {
    die("Không thể xác nhận đơn hàng này (Trạng thái hiện tại: " . $order['order_status'] . ")");
}

// 3. Cập nhật trạng thái sang 'confirmed'
$update = "UPDATE orders SET order_status = 'confirmed' WHERE order_id = $order_id";
$check_update = mysqli_query($ocon, $update);

if (!$check_update) {
    die("Lỗi cập nhật trạng thái: " . mysqli_error($ocon));
}

// ===============================
// 4. GỬI THÔNG BÁO CHO KHÁCH HÀNG (Đã sửa)
// ===============================

// Lấy user_id đã có từ bước 1, không cần query lại
$customer_id = intval($order['user_id']); 

$type = "order_confirmed";
$message = "Đơn hàng #$order_id của bạn đã được xác nhận và đang chuẩn bị gửi đi.";

// Sử dụng đúng tên bảng: notifications_customer
// Các cột: user_id, message, type, reference_id
$insert_sql = "
    INSERT INTO notifications_customer (user_id, type, message, reference_id)
    VALUES ($customer_id, '$type', '$message', $order_id)
";

$ok = mysqli_query($ocon, $insert_sql);

if (!$ok) {
    // Không dùng die() ở đây để tránh làm treo luồng nếu chỉ lỗi thông báo
    // Có thể log lỗi ra file hoặc bỏ qua
    error_log("Lỗi insert thông báo: " . mysqli_error($ocon));
}

// 5. Quay lại trang quản trị
header("Location: admin_orders.php?success=1");
exit;
?>