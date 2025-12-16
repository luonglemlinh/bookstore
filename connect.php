<?php
$user="root";
$pass="";
$host="127.0.0.1";
$db="qlbansach";

$ocon=new mysqli($host,$user,$pass, $db);
if ($ocon->connect_error){
    die("Kết nối lỗi ".$ocon->connect_error);
}

?>