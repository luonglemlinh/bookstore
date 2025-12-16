<?php
session_start();
include 'connect.php';

// Nếu chưa đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


if (!isset($_GET['id'])) {
    die("Thiếu mã thông báo!");
}

$notif_id = intval($_GET['id']);
$user_id = intval($_SESSION['user_id']);

// Lấy thông báo
$sql = mysqli_query($ocon, "
    SELECT *
    FROM notifications_customer
    WHERE notification_id = $notif_id
      AND user_id = $user_id
    LIMIT 1
");

if (!$sql || mysqli_num_rows($sql) == 0) {
    die("Không tìm thấy thông báo!");
}

$notif = mysqli_fetch_assoc($sql);

// Đánh dấu đã đọc
mysqli_query($ocon, "
    UPDATE notifications_customer
    SET is_read = 1
    WHERE notification_id = $notif_id
");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chi tiết thông báo</title>
    <link rel="stylesheet" href="CSS/notification_view.css">
    <link rel="stylesheet" href="CSS/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

</head>

<body>

<?php include 'header.php'; ?>

<div class="notif_view_container">

    <h2 class="notif_view_title">📩 Chi tiết thông báo</h2>

    <div class="notif_view_box">
        <p class="notif_msg"><?= htmlspecialchars($notif['message']) ?></p>

        <p class="notif_time">Thời gian: <?= $notif['created_at'] ?></p>

        <?php if (!empty($notif['reference_id'])): ?>
            <a href="order_detail.php?id=<?= $notif['reference_id'] ?>" class="notif_btn">
                Xem đơn hàng liên quan
            </a>
        <?php endif; ?>

        <a href="notification_customer.php" class="notif_back">← Quay lại</a>
    </div>

</div>

<?php include 'footer.php'; ?>

</body>
</html>
