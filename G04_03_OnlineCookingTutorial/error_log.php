<?php
session_start();
include 'db_connect.php'; // This should define $conn (MySQLi connection)

$result = $conn->query("SELECT * FROM log ORDER BY created_at DESC");

include 'header.php';
echo "<h2>Error Logs</h2>";

if ($result && $result->num_rows > 0) {
    while ($error = $result->fetch_assoc()) {
        echo "<p>" . htmlspecialchars($error['message']) . " at " . htmlspecialchars($error['created_at']) . "</p>";
    }
} else {
    echo "<p>No logs found.</p>";
}

include 'footer.php';
?>
