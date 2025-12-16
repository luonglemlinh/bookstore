<?php
include 'connect.php';
session_start();

// Chỉ admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    die("Bạn không có quyền truy cập.");
}

$review_id = intval($_GET['id'] ?? 0);
if ($review_id <= 0) {
    die("ID không hợp lệ!");
}

// Kiểm tra bình luận tồn tại
$check = mysqli_query($ocon, "SELECT * FROM reviews WHERE review_id = $review_id LIMIT 1");
if (!$check || mysqli_num_rows($check) == 0) {
    die("Không tìm thấy bình luận!");
}

// Cập nhật trạng thái → hidden
$sql = "UPDATE reviews SET status = 'hidden' WHERE review_id = $review_id";
mysqli_query($ocon, $sql);

// Quay lại trang
header("Location: admin_comments.php?msg=hide_success");
exit();
?>
