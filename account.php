<?php
include 'connect.php';
session_start();

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// --- PHẦN 1: XỬ LÝ KHI NGƯỜI DÙNG BẤM LƯU (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy dữ liệu từ form
    $full_name = mysqli_real_escape_string($ocon, $_POST['full_name']);
    $phone     = mysqli_real_escape_string($ocon, $_POST['phone']); // Số mới nhập
    $gender    = mysqli_real_escape_string($ocon, $_POST['gender']);
    $dob       = mysqli_real_escape_string($ocon, $_POST['dob']);
    
    $province  = mysqli_real_escape_string($ocon, $_POST['province']);
    $district  = mysqli_real_escape_string($ocon, $_POST['district']);
    $ward      = mysqli_real_escape_string($ocon, $_POST['ward']);
    $detail    = mysqli_real_escape_string($ocon, $_POST['specific_address']);

    $sql_user = "UPDATE users 
                 SET full_name = '$full_name', 
                     phone = '$phone', 
                     gender = '$gender', 
                     dob = '$dob' 
                 WHERE user_id = '$user_id'";
    mysqli_query($ocon, $sql_user);
    
    // Kiểm tra xem đã có địa chỉ chưa
    $check = mysqli_query($ocon, "SELECT address_id FROM addresses WHERE user_id = '$user_id'");
    
    if (mysqli_num_rows($check) > 0) {
        $sql_addr = "UPDATE addresses 
                     SET receiver_name = '$full_name',
                         receiver_phone = '$phone',
                         province = '$province',
                         district = '$district',
                         ward = '$ward',
                         specific_address = '$detail'
                     WHERE user_id = '$user_id'"; 
                     // Nếu muốn chỉ update địa chỉ mặc định thì thêm: AND is_default = 1
    } else {
        // Nếu chưa có -> INSERT
        $sql_addr = "INSERT INTO addresses (user_id, receiver_name, receiver_phone, province, district, ward, specific_address, is_default)
                     VALUES ('$user_id', '$full_name', '$phone', '$province', '$district', '$ward', '$detail', 1)";
    }
    mysqli_query($ocon, $sql_addr);

    // Refresh lại trang để hiện thông báo và cập nhật lại dữ liệu hiển thị
    header("Location: account.php?updated=1");
    exit();
}

// --- PHẦN 2: LẤY DỮ LIỆU ĐỂ HIỂN THỊ RA FORM (GET) ---

// Lấy thông tin cá nhân
$q_user = mysqli_query($ocon, "SELECT * FROM users WHERE user_id = '$user_id' LIMIT 1");
$user_data = mysqli_fetch_assoc($q_user);

$full_name = $user_data['full_name'];
$email     = $user_data['email'];
$phone     = $user_data['phone'];
$gender    = $user_data['gender'];
$dob       = $user_data['dob'];
$role      = $user_data['role'];

// Lấy thông tin địa chỉ (Ưu tiên địa chỉ mặc định)
$q_addr = mysqli_query($ocon, "SELECT * FROM addresses WHERE user_id = '$user_id' ORDER BY is_default DESC LIMIT 1");
$addr_data = mysqli_fetch_assoc($q_addr);

// Xử lý null để tránh lỗi khi user mới chưa có địa chỉ
$province = $addr_data['province'] ?? '';
$district = $addr_data['district'] ?? '';
$ward     = $addr_data['ward'] ?? '';
$detail   = $addr_data['specific_address'] ?? '';

// Nếu user chưa có sđt ở bảng users nhưng bảng address lại có (trường hợp hiếm), thì lấy ở address
if (empty($phone) && !empty($addr_data['receiver_phone'])) {
    $phone = $addr_data['receiver_phone'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Thông tin tài khoản</title>
    <link rel="stylesheet" href="css/account.css">
    <link rel="stylesheet" href="css/review.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<?php include 'header.php'; ?>

<?php if (isset($_GET['updated'])): ?>
<div class="alert-success" style="padding: 10px; background-color: #d4edda; color: #155724; margin: 20px auto; width: 80%; border-radius: 5px; text-align: center;">
    Đã cập nhật thông tin và số điện thoại giao hàng thành công!
</div>
<?php endif; ?>

<div class="account_wrapper">
    <h2>Thông tin tài khoản</h2>

    <form action="" method="POST" class="account_form">

        <label>Họ và tên:</label>
        <input type="text" name="full_name" value="<?= htmlspecialchars($full_name) ?>" required>

        <label>Email (không sửa):</label>
        <input type="email" value="<?= htmlspecialchars($email) ?>" disabled style="background-color: #eee;">

        <label>Số điện thoại:</label>
        <input type="text" name="phone" value="<?= htmlspecialchars($phone) ?>" required>

        <label>Tỉnh / Thành phố</label>
        <input type="text" name="province" value="<?= htmlspecialchars($province) ?>" required>

        <label>Quận / Huyện</label>
        <input type="text" name="district" value="<?= htmlspecialchars($district) ?>" required>

        <label>Phường / Xã</label>
        <input type="text" name="ward" value="<?= htmlspecialchars($ward) ?>" required>

        <label>Địa chỉ cụ thể</label>
        <input type="text" name="specific_address" value="<?= htmlspecialchars($detail) ?>" required>

        <label>Giới tính:</label>
        <select name="gender">
            <option value="Nam" <?= $gender=="Nam"?"selected":"" ?>>Nam</option>
            <option value="Nữ" <?= $gender=="Nữ"?"selected":"" ?>>Nữ</option>
            <option value="Khác" <?= $gender=="Khác"?"selected":"" ?>>Khác</option>
        </select>

        <label>Ngày sinh:</label>
        <input type="date" name="dob" value="<?= $dob ?>">

        <label>Quyền:</label>
        <input type="text" value="<?= $role ?>" disabled style="background-color: #eee;">

        <button class="save_btn" type="submit">Lưu thay đổi</button>
    </form>
</div>

<?php include 'footer.php'; ?>

</body>
</html>