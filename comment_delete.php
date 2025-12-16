<?php
include 'connect.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    die("Bạn không có quyền truy cập.");
}

if (!isset($_GET['id'])) {
    die("Thiếu ID bình luận.");
}

$review_id = intval($_GET['id']);

$sql = "DELETE FROM reviews WHERE review_id = ?";
$stmt = $ocon->prepare($sql);
$stmt->bind_param("i", $review_id);
$stmt->execute();

header("Location: admin_comments.php?msg=deleted");
exit();
