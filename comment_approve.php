<?php
include 'connect.php';
session_start();

// Chỉ admin mới được thao tác
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    die("Bạn không có quyền truy cập.");
}

if (!isset($_GET['id'])) {
    die("Thiếu ID bình luận.");
}

$review_id = intval($_GET['id']);

$sql = "UPDATE reviews SET status = 'approved' WHERE review_id = ?";
$stmt = $ocon->prepare($sql);
$stmt->bind_param("i", $review_id);
$stmt->execute();

header("Location: admin_comments.php?status=pending&msg=approved");
exit();
