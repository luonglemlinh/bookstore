<?php
include 'connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = intval($_SESSION['user_id']);

// Sử dụng LEFT JOIN để hiển thị tất cả giao dịch, kể cả khi đơn hàng chưa tồn tại
$user_id = intval($_SESSION['user_id']);

$q = mysqli_query($ocon, "
    SELECT t.*
    FROM transaction_history t
    LEFT JOIN orders o ON t.order_id = o.order_id
    WHERE o.user_id = $user_id
    ORDER BY t.created_at DESC
    LIMIT 0, 25
");

// Debug nếu SQL lỗi
if (!$q) {
    die("SQL ERROR: " . mysqli_error($ocon));
}



// Debug nếu SQL lỗi
if (!$q) {
    die("SQL ERROR: " . mysqli_error($ocon));
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Lịch sử giao dịch</title>
<link rel="stylesheet" href="CSS/style.css">
<link rel="stylesheet" href="CSS/transactions.css">
</head>
<body>

<?php include 'header.php'; ?>

<div class="trans-container">
    <h2>Lịch sử giao dịch</h2>

    <table class="trans-table">
        <tr>
            <th>Mã GD</th>
            <th>Đơn hàng</th>
            <th>Số tiền</th>
            <th>Phương thức</th>
            <th>Mã thanh toán</th>
            <th>Trạng thái</th>
            <th>Thời gian</th>
        </tr>

        <?php 
        if(mysqli_num_rows($q) > 0):
            while ($t = mysqli_fetch_assoc($q)): ?>
            <tr>
                <td>#<?= $t['trans_id'] ?></td>
                <td>#<?= $t['order_id'] ?></td>
                <td><?= number_format($t['amount'], 2, '.', ',') ?>₫</td>
                <td><?= $t['payment_method'] ?></td>
                <td><?= $t['transaction_code'] ?></td>
                <td style="color: <?= 
                    $t['status'] == 'Thành công' ? 'green' : 
                    ($t['status'] == 'Thất bại' ? 'red' : 'orange') 
                ?>"><?= $t['status'] ?></td>
                <td><?= $t['created_at'] ?></td>
            </tr>
        <?php 
            endwhile; 
        else: ?>
            <tr>
                <td colspan="7" style="text-align:center;">Không có giao dịch nào!</td>
            </tr>
        <?php endif; ?>

    </table>
</div>

<?php include 'footer.php'; ?>

</body>
</html>
