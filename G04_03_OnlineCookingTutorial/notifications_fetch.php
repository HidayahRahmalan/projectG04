<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');
session_start();
include 'db_connect.php';

// Get the logged-in user ID from the session
$userID = $_SESSION['userID'] ?? null;
if (!$userID) {
    echo json_encode(['success' => false, 'message' => 'User not logged in', 'notifications' => []]);
    exit;
}

// Fetch notifications and join to review and recipe for display details
$sql = "SELECT n.notificationID, n.message, n.created_at, n.reviewID,
               r.recipeID, r.rating, r.review_text,
               rc.title AS recipe_title
        FROM notifications n
        LEFT JOIN review r ON n.reviewID = r.reviewID
        LEFT JOIN recipe rc ON r.recipeID = rc.recipeID
        WHERE n.userID = ?
        ORDER BY n.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}

echo json_encode(['success' => true, 'notifications' => $notifications]);
?>