<?php
include('dbConnection.php');

if (!isset($_GET['recipe_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing recipe ID']);
    exit;
}

$recipeID = $_GET['recipe_id'];

// Get title from recipes
$titleStmt = $conn->prepare("SELECT Title FROM recipes WHERE RecipeID = ?");
$titleStmt->execute([$recipeID]);
$titleRow = $titleStmt->fetch(PDO::FETCH_ASSOC);

// Get steps (Instruction and Ingredient)
$stepStmt = $conn->prepare("SELECT Instruction, Ingredient FROM step WHERE RecipeID = ?");
$stepStmt->execute([$recipeID]);
$steps = $stepStmt->fetchAll(PDO::FETCH_ASSOC);

// Combine steps
$instructions = "";
$ingredients = "";
foreach ($steps as $i => $step) {
    $instructions .= ($i + 1) . ". " . $step['Instruction'] . "\n";
    $ingredients .= ($i + 1) . ". " . $step['Ingredient'] . "\n";
}

echo json_encode([
    'success' => true,
    'title' => $titleRow ? $titleRow['Title'] : 'Recipe',
    'instructions' => $instructions ?: 'No instructions available.',
    'ingredients' => $ingredients ?: 'No ingredients listed.'
]);
?>
