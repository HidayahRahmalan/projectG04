<?php
// Database configuration
$host = 'localhost'; // Hostname or IP address of the server
$dbname = 'p25_facilitycare'; // Name of the database
$username = 'facilitycare'; // Database username
$password = '123456'; // Database password

try {
    // Create a new PDO instance
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);

    // Set PDO attributes for better error handling
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Optional: Display a success message for debugging (remove in production)
    // echo "Database connection successful.";
} catch (PDOException $e) {
    // Handle connection errors
    die("Database connection failed: " . $e->getMessage());
}
