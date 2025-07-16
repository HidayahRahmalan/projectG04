<?php
$servername = "localhost";
$username = "gastroverse";
$password = "gastroverse123";
$dbname = "p25_gastroverse";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>