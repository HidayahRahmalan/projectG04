<?php
session_start();
include 'db_connect.php'; // This should define $conn

$user_id = $_SESSION['user']['user_id'];

$stmt = $conn->prepare("SELECT * FROM log WHERE userID = ? ORDER BY timestamp DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

include 'header.php';
echo "<h2>Activity Logs</h2>";

if ($result->num_rows > 0) {
    while ($log = $result->fetch_assoc()) {
        echo "<p>" . htmlspecialchars($log['activity']) . " at " . htmlspecialchars($log['timestamp']) . "</p>";
    }
} else {
    echo "<p>No activity logs found.</p>";
}

$stmt->close();
include 'footer.php';
?>
