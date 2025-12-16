<?php
session_start();

// Xóa toàn bộ session
session_unset();
session_destroy();

// Điều hướng về trang đăng nhập hoặc trang chủ
header("Location: login.php");
exit();
?>
