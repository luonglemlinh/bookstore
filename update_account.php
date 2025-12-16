<?php
include 'connect.php';
session_start();

$user_id = $_SESSION['user_id'];

$full_name = $_POST['full_name'];
$phone     = $_POST['phone'];
$gender    = $_POST['gender'];
$dob       = $_POST['dob'];

mysqli_query($ocon, "
    UPDATE users SET 
        full_name = '$full_name',
        phone = '$phone',
        gender = '$gender',
        dob = '$dob'
    WHERE user_id = $user_id
");

header("Location: account.php?updated=1");
exit();
?>
