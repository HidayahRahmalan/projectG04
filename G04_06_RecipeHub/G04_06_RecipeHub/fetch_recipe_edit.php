<?php
include('dbConnection.php');
if (!isset($_GET['recipe_id'])) {
    echo json_encode(['success' => false]);
    exit;
}
$id = $_GET['recipe_id'];
$stmt = $conn->prepare("SELECT Title, Cuisine, Description, DietaryTags FROM recipes WHERE RecipeID = ?");
$stmt->execute([$id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if ($data) {
    echo json_encode([
        'success' => true,
        'title' => $data['Title'],
        'cuisine' => $data['Cuisine'],
        'description' => $data['Description'],
        'tags' => $data['DietaryTags']
    ]);
} else {
    echo json_encode(['success' => false]);
}
?>
