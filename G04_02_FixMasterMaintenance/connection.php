<?php
$servername = "localhost";
$username = "group2";
$password = "group2";
$dbname = "p25_maintenancedb";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
