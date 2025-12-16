<?php
// check_payment.php
include 'connect.php';
session_start();

$code = trim($_GET['code'] ?? '');
if ($code === '') exit(json_encode(['status'=>'ERROR','msg'=>'missing code']));

$code_esc = mysqli_real_escape_string($ocon, $code);

// Lấy record online_queue mới nhất
$q = mysqli_query($ocon, "SELECT * FROM online_queue WHERE trans_code = '$code_esc' LIMIT 1");
if (!$q || mysqli_num_rows($q) == 0) {
    exit(json_encode(['status'=>'NOTFOUND']));
}
$row = mysqli_fetch_assoc($q);

// Nếu đã success hoặc done
if ($row['status'] === 'success' || $row['status'] === 'done') {
    exit(json_encode(['status'=>'PAID']));
}

// Nếu hết hạn
$expire_ts = strtotime($row['expire_at']);
if ($expire_ts < time()) {
    mysqli_query($ocon, "UPDATE online_queue SET status='expired' WHERE queue_id = " . intval($row['queue_id']));
    exit(json_encode(['status'=>'EXPIRED']));
}

// Nếu vẫn pending -> gọi API kiểm tra giao dịch
$CHAZ_API_URL = "https://api.chaz.pro/check"; // <-- THAY bằng endpoint bạn dùng
$BANK_NAME = "VietinBank"; // hoặc theo spec
$ACCOUNT_NO = "THAY_STK"; // <-- THAY STK CỦA BẠN

$params = http_build_query([
    'bank' => $BANK_NAME,
    'stk' => $ACCOUNT_NO,
    'amount' => floatval($row['amount']),
    'code' => $row['trans_code']
]);

$api_url = $CHAZ_API_URL . '?' . $params;
$resp = @file_get_contents($api_url);

if ($resp === false) {
    // Không gọi được API
    exit(json_encode(['status'=>'PENDING']));
}

$resp = trim($resp);

// Một số dịch vụ trả "true"/"1" nếu tìm thấy giao dịch khớp
if ($resp === 'true' || strtolower($resp) === 'true' || $resp === '1') {

    // Cập nhật online_queue.status -> success
    mysqli_query($ocon, "UPDATE online_queue SET status='success' WHERE queue_id = " . intval($row['queue_id']));

    // Cập nhật transaction_history (status -> success)
    $trans_code_esc = mysqli_real_escape_string($ocon, $row['trans_code']);
    mysqli_query($ocon, "UPDATE transaction_history SET status='success' WHERE transaction_code = '$trans_code_esc'");

    exit(json_encode(['status'=>'PAID']));
    }else{
    // Chưa thấy giao dịch
    exit(json_encode(['status'=>'PENDING']));
    }

    
