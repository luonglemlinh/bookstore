<?php
include 'connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = intval($_SESSION['user_id']);
$msg = "";

// --- 1. XỬ LÝ ĐƯỜNG DẪN QUAY LẠI (GIỮ ID VÀ QTY) ---
$back_url = "checkout.php"; // Mặc định về checkout giỏ hàng

// Lấy tham số từ URL (nếu có từ bước 1 truyền sang) hoặc từ Form (nếu đã bấm submit)
$p_id  = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
$p_qty = isset($_REQUEST['qty']) ? intval($_REQUEST['qty']) : 0;

// Nếu có id và qty (nghĩa là đang Mua Ngay), thêm vào link quay lại
if ($p_id > 0 && $p_qty > 0) {
    $back_url = "checkout.php?id=$p_id&qty=$p_qty";
}

// --- 2. XỬ LÝ KHI BẤM NÚT THÊM ---
if (isset($_POST['add_address'])) {

    $name     = mysqli_real_escape_string($ocon, $_POST["receiver_name"]);
    $phone    = mysqli_real_escape_string($ocon, $_POST["receiver_phone"]);
    $province = mysqli_real_escape_string($ocon, $_POST["province"]);
    $district = mysqli_real_escape_string($ocon, $_POST["district"]);
    $ward     = mysqli_real_escape_string($ocon, $_POST["ward"]);
    $detail   = mysqli_real_escape_string($ocon, $_POST["specific_address"]);

    // Thêm địa chỉ mới
    $sql = "
        INSERT INTO addresses (user_id, receiver_name, receiver_phone, province, district, ward, specific_address, is_default)
        VALUES ($user_id, '$name', '$phone', '$province', '$district', '$ward', '$detail', 0)
    ";

    if (mysqli_query($ocon, $sql)) {
        // --- QUAN TRỌNG: Quay về đúng link đã tạo ở trên ---
        header("Location: " . $back_url);
        exit();
    } else {
        $msg = "Lỗi: " . mysqli_error($ocon);
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Thêm địa chỉ</title>
<link rel="stylesheet" href="CSS/style.css">
<link rel="stylesheet" href="CSS/add_address.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

<?php include 'header.php'; ?>

<div class="address-container">
    <h2><i class="fa-solid fa-map-location-dot"></i> Thêm địa chỉ mới</h2>

    <?php if ($msg != ""): ?>
        <p class="msg"><?= $msg ?></p>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="id" value="<?= $p_id ?>">
        <input type="hidden" name="qty" value="<?= $p_qty ?>">

        <label>Họ và tên</label>
        <input type="text" name="receiver_name" required>

        <label>Số điện thoại</label>
        <input type="text" name="receiver_phone" required>

        <label>Tỉnh / Thành phố</label>
        <input type="text" name="province" required>

        <label>Quận / Huyện</label>
        <input type="text" name="district" required>

        <label>Phường / Xã</label>
        <input type="text" name="ward" required>

        <label>Địa chỉ cụ thể</label>
        <input type="text" name="specific_address" required>

        <button type="submit" name="add_address">Thêm địa chỉ</button>

        <a href="checkout.php" class="back-btn">Quay lại</a>
    </form>
</div>

<?php include 'footer.php'; ?>

</body>
</html>