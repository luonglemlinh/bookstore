<?php
include 'connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id  = $_SESSION['user_id'];
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($order_id <= 0) {
    die("Thiếu mã đơn hàng!");
}

// Kiểm tra đơn có thuộc user không (đơn giản)
$sql = mysqli_query($ocon, "SELECT order_id FROM orders WHERE order_id = $order_id AND user_id = $user_id");
if (mysqli_num_rows($sql) == 0) {
    die("Không thể hủy đơn không thuộc quyền sở hữu!");
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Hủy đơn hàng</title>
    <link rel="stylesheet" href="CSS/style.css">
    <style>
        .cancel_box{
            max-width:600px;margin:40px auto;background:#fff;padding:20px;
            border-radius:10px;box-shadow:0 4px 20px rgba(0,0,0,0.1)
        }
        textarea{
            width:100%;height:130px;padding:10px;border:1px solid #ccc;
            border-radius:8px;resize:none
        }
        .btn{
            margin-top:15px;display:inline-block;padding:10px 20px;
            background:#dc2626;color:#fff;border-radius:8px;text-decoration:none;
            cursor:pointer;border:none;font-weight:bold
        }
        .back_btn{
            background:#e5e7eb;color:#111;margin-right:10px
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="cancel_box">
    <h2>Yêu cầu hủy đơn <?= $order_id ?></h2>

    <form action="cancel_order_process.php" method="POST">
        <input type="hidden" name="order_id" value="<?= $order_id ?>">

        <label>Lý do hủy:</label>
        <textarea name="reason" required placeholder="Nhập lý do hủy đơn..."></textarea>

        <br>

        <a href="order_detail.php?id=<?= $order_id ?>" class="btn back_btn">Quay lại</a>
        <button type="submit" class="btn">Gửi yêu cầu hủy</button>
    </form>
</div>

<?php include 'footer.php'; ?>

</body>
</html>
