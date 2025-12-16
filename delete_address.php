<?php
include 'connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = intval($_SESSION['user_id']);
$addr_id = intval($_GET['id'] ?? 0);

if ($addr_id <= 0) {
    die("ID địa chỉ không hợp lệ.");
}

// Xóa chỉ khi địa chỉ thuộc user
mysqli_query($ocon, "DELETE FROM addresses WHERE address_id = $addr_id AND user_id = $user_id");

header("Location: checkout.php");
exit();
?>
