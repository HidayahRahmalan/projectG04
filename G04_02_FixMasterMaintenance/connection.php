<?php
$servername = "localhost";
$username = "group2";
$password = "@Lyana2003";
$dbname = "p25_MaintenanceDB";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
