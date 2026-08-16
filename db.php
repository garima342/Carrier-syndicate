<?php

$servername = "localhost";
$username   = "root";
$password   = "";
$database   = "company_registration";

$conn = mysqli_connect($servername, $username, $password, $database);

if (!$conn) {
    die("Connection Failed : " . mysqli_connect_error());
}

// echo "Database Connected Successfully";

?>