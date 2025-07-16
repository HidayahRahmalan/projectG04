<?php
$host = 'localhost';
$username = 'admin';
$password = 'P@55word';
$database = 'p25_db_maintenance';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    error_log("Database Connection Failed: " . $conn->connect_error);
    die("A critical database error occurred. Please contact the administrator.");
}
$conn->set_charset("utf8mb4");
?>