<?php
include 'connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = intval($_SESSION['user_id']);

// ======= LẤY DANH SÁCH THÔNG BÁO CHƯA ĐỌC =======
$sql_unread = "
    SELECT *
    FROM notifications_customer
    WHERE user_id = $user_id AND is_read = 0
    ORDER BY created_at DESC
";
$unread = mysqli_query($ocon, $sql_unread);

// ======= LẤY DANH SÁCH THÔNG BÁO ĐÃ ĐỌC =======
$sql_read = "
    SELECT *
    FROM notifications_customer
    WHERE user_id = $user_id AND is_read = 1
    ORDER BY created_at DESC
";
$read = mysqli_query($ocon, $sql_read);

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thông báo | Babo Bookstore</title>

    <link rel="stylesheet" href="CSS/style.css">
    <link rel="stylesheet" href="CSS/notification_customer.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

<?php include 'header.php'; ?>

<div class="notif_page">

    <h2 class="notif_title">🔔 Thông báo</h2>

    <!-- ========================== -->
    <!-- THÔNG BÁO CHƯA ĐỌC -->
    <!-- ========================== -->
    <div class="notif_section">
        <h3>Thông báo mới</h3>

        <?php if ($unread && mysqli_num_rows($unread) > 0): ?>
            <?php while ($n = mysqli_fetch_assoc($unread)): ?>
                <a href="notification_view.php?id=<?= $n['notification_id'] ?>" class="notif_item unread">
                    <div class="notif_icon"><i class="fa-solid fa-bell"></i></div>
                    <div class="notif_info">
                        <div class="notif_msg"><?= htmlspecialchars($n['message']) ?></div>
                        <div class="notif_time"><?= $n['created_at'] ?></div>
                    </div>
                </a>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="empty_notif">Không có thông báo mới.</p>
        <?php endif; ?>
    </div>

    <!-- ========================== -->
    <!-- THÔNG BÁO ĐÃ ĐỌC -->
    <!-- ========================== -->
    <div class="notif_section">
        <h3>Thông báo đã xem</h3>

        <?php if ($read && mysqli_num_rows($read) > 0): ?>
            <?php while ($n = mysqli_fetch_assoc($read)): ?>
                <a href="notification_view.php?id=<?= $n['notification_id'] ?>" class="notif_item">
                    <div class="notif_icon read"><i class="fa-regular fa-bell"></i></div>
                    <div class="notif_info">
                        <div class="notif_msg"><?= htmlspecialchars($n['message']) ?></div>
                        <div class="notif_time"><?= $n['created_at'] ?></div>
                    </div>
                </a>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="empty_notif">Không có thông báo cũ.</p>
        <?php endif; ?>
    </div>

</div>

<?php include 'footer.php'; ?>

</body>
</html>
