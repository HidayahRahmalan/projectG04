<?php
$host = 'localhost';
$user = 'Group3';
$pass = 'group3';
$db = 'p25_mmdb';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
