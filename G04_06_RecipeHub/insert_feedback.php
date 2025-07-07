<?php
session_start();
header('Content-Type: application/json');
include('dbConnection.php');

if (!isset($_SESSION['UserID'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$userID = $_SESSION['UserID'];
$recipeID = $_POST['recipeID'] ?? null;
$comment = trim($_POST['comment'] ?? '');

if (!$recipeID || $comment === '') {
    echo json_encode(['success' => false, 'message' => 'Missing input']);
    exit();
}

$stmt = $conn->prepare("INSERT INTO feedback (RecipeID, UserID, Text, FeedbackTime) VALUES (?, ?, ?, NOW())");
$success = $stmt->execute([$recipeID, $userID, $comment]);

echo json_encode(['success' => $success]);
?>
