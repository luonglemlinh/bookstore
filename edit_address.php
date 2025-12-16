<?php
include 'connect.php';
session_start();

/* ================================
   Bắt buộc bật hiển thị lỗi PHP
================================ */
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id'])) {
    echo "Bạn chưa đăng nhập.";
    exit();
}

$user_id = intval($_SESSION['user_id']);
$addr_id = intval($_GET['id'] ?? 0);
$msg = "";
$addr = null;

/* ====================================
   1) VALIDATE ID
==================================== */
if ($addr_id <= 0) {
    $msg = "ID địa chỉ không hợp lệ.";
} else {

    /* ====================================
       2) LẤY ĐỊA CHỈ
    ==================================== */
    $sql = mysqli_query($ocon,
        "SELECT * FROM addresses 
         WHERE address_id = $addr_id 
         AND user_id = $user_id"
    );

    if ($sql && mysqli_num_rows($sql) > 0) {
        $addr = mysqli_fetch_assoc($sql);
    } else {
        $msg = "Không tìm thấy địa chỉ hoặc bạn không có quyền sửa.";
    }
}

/* ====================================
   3) UPDATE
==================================== */
if ($addr && isset($_POST['update_address'])) {

    $name     = mysqli_real_escape_string($ocon, $_POST["receiver_name"]);
    $phone    = mysqli_real_escape_string($ocon, $_POST["receiver_phone"]);
    $province = mysqli_real_escape_string($ocon, $_POST["province"]);
    $district = mysqli_real_escape_string($ocon, $_POST["district"]);
    $ward     = mysqli_real_escape_string($ocon, $_POST["ward"]);
    $detail   = mysqli_real_escape_string($ocon, $_POST["specific_address"]);

    $update = "
        UPDATE addresses
        SET receiver_name = '$name',
            receiver_phone = '$phone',
            province = '$province',
            district = '$district',
            ward = '$ward',
            specific_address = '$detail'
        WHERE address_id = $addr_id 
        AND user_id = $user_id
    ";

    if (mysqli_query($ocon, $update)) {
        $msg = "Cập nhật thành công!";
        
        // Load lại dữ liệu mới
        $sql = mysqli_query($ocon,
            "SELECT * FROM addresses 
             WHERE address_id = $addr_id 
             AND user_id = $user_id"
        );
        $addr = mysqli_fetch_assoc($sql);

    } else {
        $msg = "Lỗi sửa địa chỉ: " . mysqli_error($ocon);
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Sửa địa chỉ</title>
<link rel="stylesheet" href="CSS/style.css">
<link rel="stylesheet" href="CSS/add_address.css">
</head>

<body>

<?php include 'header.php'; ?>

<div class="address-container">

    <h2>Sửa địa chỉ</h2>

    <?php if ($msg != ""): ?>
        <p class="msg" style="color:red;"><?= $msg ?></p>
    <?php endif; ?>

    <?php if ($addr): ?>
    <form method="POST">

        <label>Họ và tên</label>
        <input type="text" name="receiver_name" required value="<?= $addr['receiver_name'] ?>">

        <label>Số điện thoại</label>
        <input type="text" name="receiver_phone" required value="<?= $addr['receiver_phone'] ?>">

        <label>Tỉnh / Thành phố</label>
        <input type="text" name="province" required value="<?= $addr['province'] ?>">

        <label>Quận / Huyện</label>
        <input type="text" name="district" required value="<?= $addr['district'] ?>">

        <label>Phường / Xã</label>
        <input type="text" name="ward" required value="<?= $addr['ward'] ?>">

        <label>Địa chỉ cụ thể</label>
        <input type="text" name="specific_address" required value="<?= $addr['specific_address'] ?>">

        <button type="submit" name="update_address">Cập nhật</button>
        <a href="checkout.php" class="back-btn">Quay lại</a>

    </form>
    <?php endif; ?>

</div>

<?php include 'footer.php'; ?>

</body>
</html>
