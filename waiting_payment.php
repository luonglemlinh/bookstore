<?php
// waiting_payment.php
session_start();
include 'connect.php';

// Lấy mã giao dịch
$code = $_GET['code'] ?? '';
if (!$code) die("Thiếu mã giao dịch.");
$code_esc = mysqli_real_escape_string($ocon, $code);

// Lấy record online_queue
$q = mysqli_query($ocon, "SELECT * FROM online_queue WHERE trans_code='$code_esc' LIMIT 1");
if (!$q || mysqli_num_rows($q) === 0) die("Giao dịch không tồn tại.");
$row = mysqli_fetch_assoc($q);

$queue_id = intval($row['queue_id']);
$amount = floatval($row['amount']);
$expire_at = $row['expire_at'];
$status = $row['status'];

// Xử lý khi nhấn nút "Tôi đã chuyển tiền"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['paid'])) {
    // Cập nhật online_queue và transaction_history
    mysqli_query($ocon, "UPDATE online_queue SET status='success' WHERE queue_id=$queue_id");
    mysqli_query($ocon, "UPDATE transaction_history SET status='success' WHERE transaction_code='$code_esc'");

    // Redirect sang process_success.php
    header("Location: process_success.php?code=" . urlencode($code));
    exit();
}

// Tạo link VietQR (VietinBank)
$bank_code = "VietinBank"; // hoặc mã ngân hàng bạn muốn
$account_no = "0123456789"; // <-- nhập số tài khoản thật
$addInfo = $code;
$qr_url = "https://img.vietqr.io/image/{$bank_code}-{$account_no}-compact2.png?amount={$amount}&addInfo=" . urlencode($addInfo);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8">
<title>Chờ thanh toán</title>
<style>
body {
    font-family: Arial, sans-serif;
    background-color: #f8f8f8;
    display: flex;
    justify-content: center;
    padding: 50px 10px;
}
.container {
    background: #fff;
    padding: 30px 20px;
    border-radius: 10px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    text-align: center;
    max-width: 400px;
    width: 100%;
}
h2 { color: #333; margin-bottom: 20px; }
.qr-box { border: 1px solid #eee; border-radius: 8px; padding: 20px; display: inline-block; margin-bottom: 15px; }
#status { font-weight: 600; margin-top: 10px; color: #555; }
button {
    background-color: #4CAF50;
    color: white;
    padding: 12px 25px;
    border: none;
    border-radius: 5px;
    font-size: 16px;
    cursor: pointer;
    margin-top: 15px;
}
button:hover { background-color: #45a049; }
#countdown { margin-top: 10px; color: #999; font-size: 14px; }
</style>
</head>
<body>
<div class="container">
    <h2>Quét mã QR để thanh toán</h2>
    <div class="qr-box">
        <img src="<?= htmlspecialchars($qr_url) ?>" width="250" alt="QR code">
        <p><strong>Số tiền:</strong> <?= number_format($amount) ?>₫</p>
        <p><strong>Nội dung chuyển khoản:</strong> <code><?= htmlspecialchars($addInfo) ?></code></p>
        <p id="countdown">QR hết hạn: <?= htmlspecialchars($expire_at) ?></p>
        <div id="status">
            <?php
            if ($status === 'success' || $status === 'done') {
                echo "Giao dịch đã thanh toán.";
            } elseif (strtotime($expire_at) < time()) {
                echo "QR đã hết hạn.";
            } else {
                echo "Đang chờ thanh toán...";
            }
            ?>
        </div>
    </div>

    <?php if ($status === 'pending' && strtotime($expire_at) > time()): ?>
    <form method="post">
        <button type="submit" name="paid">Tôi đã chuyển tiền</button>
    </form>
    <?php endif; ?>
</div>

<script>
// Đếm ngược thời gian hết hạn
const countdownEl = document.getElementById('countdown');
const expireTime = new Date("<?= $expire_at ?>").getTime();

function updateCountdown() {
    const now = new Date().getTime();
    const distance = expireTime - now;
    if (distance < 0) {
        countdownEl.innerText = "QR đã hết hạn.";
        document.getElementById('status').innerText = "Giao dịch hết hạn hoặc không hợp lệ.";
        return;
    }
    const minutes = Math.floor(distance / 1000 / 60);
    const seconds = Math.floor((distance / 1000) % 60);
    countdownEl.innerText = `QR hết hạn: ${minutes} phút ${seconds} giây`;
}
setInterval(updateCountdown, 1000);
updateCountdown();
</script>
</body>
</html>
