<?php
$hostname = "localhost";
$username = "resepiku";
$password = "123456";
$dbname = "p25_resepiku";

$conn = new mysqli($hostname, $username, $password, $dbname);

 // Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully lah weiii"; 
?>