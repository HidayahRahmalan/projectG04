<?php
session_start();
include('dbConnection.php');
header('Content-Type: application/json');

// ✅ 1. Ensure user is logged in
if (!isset($_SESSION['UserID'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}
$userID = $_SESSION['UserID'];

// ✅ 2. Validate inputs
if (
    !isset($_POST['id'], $_POST['title'], $_POST['cuisine'], $_POST['tags'], $_POST['date'], $_POST['description']) ||
    !isset($_POST['ingredients']) || !isset($_POST['steps'])
) {
    echo json_encode(['success' => false, 'message' => 'Missing fields']);
    exit;
}

$recipeID = $_POST['id'];
$title = $_POST['title'];
$cuisine = $_POST['cuisine'];
$tags = $_POST['tags'];
$date = $_POST['date'];
$desc = $_POST['description'];
$ingredients = json_decode($_POST['ingredients'], true);
$steps = json_decode($_POST['steps'], true);

if (!$ingredients || !$steps) {
    echo json_encode(['success' => false, 'message' => 'Invalid ingredients or steps']);
    exit;
}

try {
    $conn->beginTransaction();

    // ✅ 3. Update main recipe info
    $stmt = $conn->prepare("UPDATE recipes SET Title=?, Cuisine=?, DietaryTags=?, DateRecipe=?, Description=? WHERE RecipeID=?");
    $stmt->execute([$title, $cuisine, $tags, $date, $desc, $recipeID]);

    // ✅ 4. Rebuild ingredients + steps
    $ingredientList = array_map(function ($i) {
        return trim($i['amount'] . ' ' . $i['name']);
    }, $ingredients);

    $allIngredients = implode(', ', $ingredientList);
    $allSteps = implode(', ', $steps);

    // ✅ 5. Update step table
    $stmt = $conn->prepare("UPDATE step SET Ingredient=?, Instruction=? WHERE RecipeID=?");
    $stmt->execute([$allIngredients, $allSteps, $recipeID]);

    // ✅ 6. Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['image']['tmp_name'];
        $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
        $uploadDir = 'uploads/';
        $targetPath = $uploadDir . $fileName;
        $dbPath = '/G04_06_RecipeHub/' . $targetPath;

        // ✅ Create folder if doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        if (move_uploaded_file($fileTmp, $targetPath)) {
            // ✅ Insert or Replace media with RecipeID, UserID, and proper URL
            $stmt = $conn->prepare("REPLACE INTO media (RecipeID, UserID, URL, Type) VALUES (?, ?, ?, 'image')");
            $stmt->execute([$recipeID, $userID, $dbPath]);
        } else {
            throw new Exception("Failed to move uploaded image.");
        }
    }

    // ✅ 7. Commit all changes
    $conn->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
