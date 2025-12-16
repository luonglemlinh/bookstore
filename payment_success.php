<?php
include 'connect.php';
session_start();

$order_id = intval($_GET['order_id'] ?? 0);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thanh toán thành công</title>
    <link rel="stylesheet" href="CSS/style.css">
    <link rel="stylesheet" href="CSS/checkout.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        .success-container {
            max-width: 600px;
            margin: 80px auto;
            background: #fff;
            padding: 40px;
            border-radius: 16px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            animation: fadeIn 0.4s ease;
        }
        .success-icon {
            font-size: 70px;
            color: #28a745;
            margin-bottom: 20px;
        }
        .success-title {
            font-size: 28px;
            color: #28a745;
            margin-bottom: 10px;
            font-weight: bold;
        }
        .success-order {
            font-size: 18px;
            margin-bottom: 25px;
            color: #333;
        }
        .success-btns a {
            display: inline-block;
            padding: 12px 22px;
            margin: 8px;
            background: #28a745;
            color: #fff;
            border-radius: 8px;
            font-size: 16px;
            text-decoration: none;
            transition: 0.2s;
        }
        .success-btns a:hover {
            opacity: 0.85;
        }
        .home-btn {
            background: #007bff !important;
        }
        @keyframes fadeIn { from {opacity:0;} to {opacity:1;} }
    </style>
</head>

<body>

<?php include 'header.php'; ?>

<div class="success-container">
    <div class="success-icon">
        <i class="fa-solid fa-circle-check"></i>
    </div>

    <div class="success-title">Thanh toán thành công!</div>

    <?php if ($order_id): ?>
        <div class="success-order">
            Mã đơn hàng của bạn: <b>#<?= $order_id ?></b>
        </div>

        <div class="success-btns">
            <a href="order_detail.php?id=<?= $order_id ?>">Xem chi tiết đơn hàng</a>
            <a href="order.php">Danh sách đơn</a>
            <a href="index.php" class="home-btn">Quay về trang chủ</a>
            <a href="transactions.php" class="home-btn">Lịch sử giao dịch</a>
        </div>

    <?php else: ?>
        <div class="success-order">
            Đơn hàng của bạn đã được xác nhận thành công.
        </div>

        <div class="success-btns">
            <a href="order.php">Xem đơn hàng</a>
            <a href="index.php" class="home-btn">Quay về trang chủ</a>
            <a href="transactions.php" class="home-btn">Xem lịch sử giao dịch</a>

        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>

</body>
</html>
