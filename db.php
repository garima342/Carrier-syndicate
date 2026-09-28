<?php
$servername = "127.0.0.1";
$username   = "root";
$password   = "";
$database   = "internhub_new";
$port       = 3306;

$conn = mysqli_connect($servername, $username, $password, $database, $port);

if (!$conn) {
    die("Connection Failed: " . mysqli_connect_error());
}
?>
