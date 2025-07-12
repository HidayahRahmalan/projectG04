<?php
// Database configuration
$host = "localhost";
$dbname = "p25_cooking_app";
$user = "root"; // Change this if your MySQL username is different
$password = ""; // Change this to your actual MySQL password

// Connection options
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::ATTR_TIMEOUT            => 300, // 5 minutes timeout
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
    PDO::MYSQL_ATTR_COMPRESS     => true, // Enable compression for large data transfers
];

// Function to check if connection is alive
function isConnectionAlive($conn) {
    try {
        return (bool)$conn->query("SELECT 1");
    } catch (PDOException $e) {
        return false;
    }
}

// Function to reconnect if needed
function reconnectDatabase() {
    global $host, $dbname, $user, $password, $options;
    
    try {
        $conn = new PDO(
            "mysql:host=$host;dbname=$dbname;charset=utf8mb4", 
            $user, 
            $password, 
            $options
        );
        
        // Additional settings after connection
        $conn->exec("SET wait_timeout=300");
        $conn->exec("SET interactive_timeout=300");
        $conn->exec("SET max_allowed_packet=67108864"); // 64MB
        
        return $conn;
    } catch (PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        die("Database connection failed. Please try again later.");
    }
}

// Establish initial connection
try {
    $conn = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4", 
        $user, 
        $password, 
        $options
    );
    
    // Set additional MySQL variables
    $conn->exec("SET SESSION wait_timeout=300");
    $conn->exec("SET SESSION interactive_timeout=300");
    $conn->exec("SET GLOBAL max_allowed_packet=67108864"); // 64MB
    
} catch (PDOException $e) {
    error_log("Initial database connection failed: " . $e->getMessage());
    die("Database connection failed. Please try again later.");
}

// Function to get a reliable connection
function getDBConnection() {
    global $conn;
    
    if (!isConnectionAlive($conn)) {
        $conn = reconnectDatabase();
    }
    
    return $conn;
}

// Get the connection object
$conn = getDBConnection();

// Function to safely execute queries with automatic reconnection
function executeQuery($sql, $params = []) {
    global $conn;
    
    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'server has gone away') !== false) {
            // Reconnect and retry once
            $conn = reconnectDatabase();
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        }
        throw $e; // Re-throw other exceptions
    }
}

// Register shutdown function to clean up connection
register_shutdown_function(function() {
    global $conn;
    $conn = null; // Close connection gracefully
});
?>