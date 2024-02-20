<?php
mysqli_report(MYSQLI_REPORT_OFF);
$host = "mariadb";
$dbname = "login_db";
$username = "myadmin";
$password = "password";

$mysqli = new mysqli(hostname: $host,
                     username: $username,
                     password: $password,
                     database: $dbname);

if ($mysqli->connect_errno){
    die("Connection error: " . $mysqli->connect_error);
}

return $mysqli;