<?php
include('dbConnection.php');
header('Content-Type: application/json');

$recipeID = $_GET['recipe'] ?? null;
if (!$recipeID) {
    echo json_encode([]);
    exit;
}

$stmt = $conn->prepare("
    SELECT u.UserName, f.Text, f.FeedbackTime 
    FROM feedback f
    JOIN user u ON f.UserID = u.UserID
    WHERE f.RecipeID = ?
    ORDER BY f.FeedbackTime DESC
");
$stmt->execute([$recipeID]);
$feedback = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($feedback);
?>
