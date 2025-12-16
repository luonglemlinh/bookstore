<?php

if (isset($_ENV['MYSQL_HOST'])) { 
    // Use the standard $_ENV superglobal
    $host = $_ENV['MYSQL_HOST'];
    $user = $_ENV['MYSQL_USER'];
    $pass = $_ENV['MYSQL_PASSWORD'];
    $db   = $_ENV['MYSQL_DATABASE'];
    $port = $_ENV['MYSQL_PORT'];
    
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
