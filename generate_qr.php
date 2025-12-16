<?php
// generate_qr.php
// Gọi file này từ checkout khi user chọn thanh toán online
session_start();
include 'connect.php';

// Kiểm tra login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
date_default_timezone_set("Asia/Ho_Chi_Minh");


// Lấy pending info: bạn có thể gửi POST hoặc dùng $_SESSION['pending_order']
$pending = $_SESSION['pending_order'] ?? null;
if (!$pending) {
    // Nếu không có session, bạn có thể build lại từ POST (nếu bạn gửi items + address)
    // Ví dụ: $pending = json_decode($_POST['pending_json'], true);
    die("Không có đơn chờ thanh toán (pending_order).");
}

$user_id = intval($_SESSION['user_id']);
$amount = floatval($pending['total']);
$trans_code = "DH" . time() . rand(100,999);

// expire 15 phút
$expire_at = date("Y-m-d H:i:s", time() + 15*60);

// Lưu vào online_queue (kèm payload để callback có thể tạo order khi detect thành công)
$payload_json = mysqli_real_escape_string($ocon, json_encode($pending, JSON_UNESCAPED_UNICODE));
$trans_code_esc = mysqli_real_escape_string($ocon, $trans_code);

$sql_q = "INSERT INTO online_queue (trans_code, user_id, amount, expire_at, status, payload)
          VALUES ('$trans_code_esc', $user_id, $amount, '$expire_at', 'pending', '$payload_json')";
if (!mysqli_query($ocon, $sql_q)) {
    die("Lỗi khi tạo hàng chờ: " . mysqli_error($ocon));
}
$queue_id = mysqli_insert_id($ocon);

// Lưu lịch sử transaction pending
$method = 'ONLINE_VIETQR';
$sql_th = "INSERT INTO transaction_history (order_id, payment_method, amount, status, transaction_code)
           VALUES (NULL, '$method', $amount, 'pending', '$trans_code_esc')";
if (!mysqli_query($ocon, $sql_th)) {
    die("Lỗi tạo history: " . mysqli_error($ocon));
}
$trans_id = mysqli_insert_id($ocon);

// Ghi trans_id vào online_queue (nếu cần) - optional
mysqli_query($ocon, "UPDATE online_queue SET status='pending' WHERE queue_id = $queue_id");

// Chuyển sang trang hiển thị QR (đi kèm trans_code)
header("Location: waiting_payment.php?code=" . urlencode($trans_code));
exit();
