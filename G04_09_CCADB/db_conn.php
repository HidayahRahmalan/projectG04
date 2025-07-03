<?php
// --- Database Connection File ---

$servername = "localhost";
$username   = "ahza";
$password   = "abc123";
$dbname     = "p25_CCADB";

// Create Connection using MySQLi
$conn = new mysqli($servername, $username, $password, $dbname);

// Check Connection
if ($conn->connect_error) {
  // Stop the script and show an error if connection fails
  die("Database Connection Failed: " . $conn->connect_error);
}