<?php
include('connect.php');
session_start();

header('Content-Type: application/json');

$user_id = isset($_SESSION['UserID']) ? intval($_SESSION['UserID']) : 0;
$notifications = [];
$notif_count = 0;

if ($user_id) {
    $sql = "SELECT c.Comment_ID, c.Comment_Content, c.Comment_Date, c.User_ID AS commenter_id, u.User_Name, r.Recipe_Title, r.Recipe_ID 
            FROM comment c
            JOIN recipe r ON c.Recipe_ID = r.Recipe_ID
            JOIN users u ON c.User_ID = u.User_ID
            WHERE r.User_ID = ? AND c.User_ID != ? 
            ORDER BY c.Comment_Date DESC
            LIMIT 10";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $user_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
    $notif_count = count($notifications);
}

echo json_encode([
    'count' => $notif_count,
    'notifications' => $notifications
]);
exit;