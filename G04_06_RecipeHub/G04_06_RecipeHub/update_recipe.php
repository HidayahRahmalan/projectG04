<?php
include('dbConnection.php');
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$sql = "UPDATE recipes SET Title = ?, Cuisine = ?, Description = ?, DietaryTags = ? WHERE RecipeID = ?";
$stmt = $conn->prepare($sql);
$success = $stmt->execute([
    $data['title'], 
    $data['cuisine'], 
    $data['description'], 
    $data['tags'], 
    $data['id']
]);

echo json_encode(['success' => $success]);
?>
