<?php

if (isset ($ENV[''])){
    $host=$ENV['tramway.proxy.rlwy.net'];
    $user=$ENV['root'];
    $pass=$ENV['uktMiTvrWuwlgoGamKvRmDObGKJKtsKY'];
    $db=$ENV['railway'];
    $port = $ENV['3306'];
    
    $ocon=new mysqli($host,$user,$pass, $db);
} else {
    $user = "root";
    $pass = "";
    $host = "127.0.0.1";
    $db = "qlbansach";

    $ocon=new mysqli($host,$user,$pass, $db);
}

if ($ocon->connect_error){
    die("Kết nối lỗi ".$ocon->connect_error);
}

?>