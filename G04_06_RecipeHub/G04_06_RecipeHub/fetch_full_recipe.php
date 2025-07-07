<?php
include('dbConnection.php');
header('Content-Type: application/json');

$id = $_GET['id'] ?? null;
if (!$id) {
    echo json_encode(['success' => false]);
    exit;
}

// Fetch recipe info
$stmt = $conn->prepare("
    SELECT r.*, m.URL 
    FROM recipes r 
    LEFT JOIN media m ON r.RecipeID = m.RecipeID 
    WHERE r.RecipeID = ?
");

$stmt->execute([$id]);
$recipe = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch steps table (combined)
$stepStmt = $conn->prepare("SELECT Ingredient, Instruction FROM step WHERE RecipeID = ?");
$stepStmt->execute([$id]);
$stepRow = $stepStmt->fetch(PDO::FETCH_ASSOC);

// Split ingredients and steps
$ingredients = [];
foreach (explode(',', $stepRow['Ingredient']) as $item) {
    $item = trim($item);
    if ($item !== '') {
        $parts = explode(' ', $item, 2);
        $amount = $parts[0] ?? '';
        $name = $parts[1] ?? '';
        $ingredients[] = ['amount' => $amount, 'name' => $name];
    }
}

$steps = array_map('trim', explode(',', $stepRow['Instruction']));

echo json_encode([
    'success' => true,
    'title' => $recipe['Title'],
    'cuisine' => $recipe['Cuisine'],
    'tags' => $recipe['DietaryTags'],
    'image' => $recipe['URL'],
    'date' => $recipe['DateRecipe'],
    'description' => $recipe['Description'],
    'ingredients' => $ingredients,
    'steps' => $steps,
    'cuisineOptions' => ['American','British','Chinese','French','Greek','Indian','Italian','Japanese','Korean','Lebanese','Malaysian','Mexican','Middle Eastern','Moroccan','Spanish','Thai','Turkish','Vietnamese'],
    'tagOptions' => ['vegetarian','vegan','gluten-free','dairy-free','nut-free','halal','kosher','pescatarian','low-carb','keto','paleo','sugar-free','high-protein','spicy','soup']
]);
