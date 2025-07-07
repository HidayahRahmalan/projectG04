<?php
session_start();
include('dbConnection.php');

if (!isset($_GET['id'])) {
    echo "<h2>Invalid access. Recipe ID missing.</h2>";
    exit;
}

$recipeID = $_GET['id'];

// Fetch recipe data
$stmt = $conn->prepare("SELECT r.*, m.URL FROM recipes r LEFT JOIN media m ON r.RecipeID = m.RecipeID WHERE r.RecipeID = ?");
$stmt->execute([$recipeID]);
$recipe = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$recipe) {
    echo "<h2>Recipe not found.</h2>";
    exit;
}

// Fetch steps data
$stepStmt = $conn->prepare("SELECT Ingredient, Instruction FROM step WHERE RecipeID = ?");
$stepStmt->execute([$recipeID]);
$stepRow = $stepStmt->fetch(PDO::FETCH_ASSOC);

// Parse ingredients
$ingredients = [];
foreach (explode(',', $stepRow['Ingredient']) as $item) {
    $item = trim($item);
    if ($item !== '') {
        $parts = explode(' ', $item, 2);
        $ingredients[] = ['amount' => $parts[0] ?? '', 'name' => $parts[1] ?? ''];
    }
}

// Parse instructions
$steps = array_filter(array_map('trim', explode(',', $stepRow['Instruction'])));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($recipe['Title']) ?> - Full Recipe</title>
    <style>
        body {
            font-family: sans-serif;
            background-color: #fff8f2;
            color: #333;
            padding: 20px;
            max-width: 800px;
            margin: auto;
        }
        h1 {
            color: #e06c75;
        }
        img {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        ul, ol {
            margin-left: 20px;
        }
        .tag {
            background: #ddd;
            border-radius: 8px;
            padding: 4px 10px;
            margin-right: 5px;
            font-size: 12px;
            display: inline-block;
        }
        .meta {
            margin-bottom: 10px;
            font-size: 14px;
            color: #666;
        }
    </style>
</head>
<body>
    <h1><?= htmlspecialchars($recipe['Title']) ?></h1>

    <?php if (!empty($recipe['URL'])): ?>
        <img src="<?= htmlspecialchars($recipe['URL']) ?>" alt="Recipe Image">
    <?php endif; ?>

    <div class="meta">
        <strong>Cuisine:</strong> <?= htmlspecialchars($recipe['Cuisine']) ?><br>
        <strong>Date:</strong> <?= htmlspecialchars($recipe['DateRecipe']) ?><br>
        <strong>Tags:</strong>
        <?php foreach (explode(',', $recipe['DietaryTags']) as $tag): ?>
            <span class="tag"><?= htmlspecialchars(trim($tag)) ?></span>
        <?php endforeach; ?>
    </div>

    <p><strong>Description:</strong><br><?= nl2br(htmlspecialchars($recipe['Description'])) ?></p>

    <h3>Ingredients</h3>
    <ul>
        <?php foreach ($ingredients as $ing): ?>
            <li><?= htmlspecialchars($ing['amount'] . ' ' . $ing['name']) ?></li>
        <?php endforeach; ?>
    </ul>

    <h3>Steps</h3>
    <ol>
        <?php foreach ($steps as $index => $step): ?>
            <li><?= htmlspecialchars($step) ?></li>
        <?php endforeach; ?>
    </ol>

    <br>
    <a href="INDEXX.php" style="text-decoration: none; color: #fff; background: #e06c75; padding: 8px 15px; border-radius: 5px;">← Back to Recipes</a>
</body>
</html>
