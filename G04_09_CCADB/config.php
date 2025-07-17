<?php
// config.php - Central Configuration File

// --- 1. Base URL ---
// Define the root URL of your project. 
// This makes all links and redirects dynamic and easy to change.
// IMPORTANT: Make sure to include the trailing slash '/'
define('BASE_URL', 'http://localhost/psm/');

// --- 2. Database Credentials ---
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'b032210266');
define('DB_PASSWORD', '021130010593');
define('DB_NAME', 'student_b032210266');

// --- 3. Database Connection (Moved from db_conn.php) ---
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check Connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}