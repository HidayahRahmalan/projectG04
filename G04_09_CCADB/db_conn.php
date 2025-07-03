<?php
// --- Database Connection File ---

$servername = "localhost";
$username   = "b032210266";
$password   = "021130010593";
$dbname     = "student_b032210266";

// Create Connection using MySQLi
$conn = new mysqli($servername, $username, $password, $dbname);

// Check Connection
if ($conn->connect_error) {
  // Stop the script and show an error if connection fails
  die("Database Connection Failed: " . $conn->connect_error);
}