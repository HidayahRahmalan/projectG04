<?php
session_start();
include('connect.php');

if (!isset($_SESSION['UserID'])) {
    echo "<script>
        sessionStorage.setItem('delete', 'You must be logged in to delete a recipe.');
        window.location.href = 'recipe.php';
        </script>";
    exit();
}

if (isset($_POST['recipe_ID'])) {
    $recipeID = intval($_POST['recipe_ID']);
    $userID = $_SESSION['UserID'];

    // Get recipe details before deletion
    $checkStmt = $conn->prepare("SELECT 
        Recipe_ID, 
        Recipe_Title, 
        Recipe_CuisineType, 
        Recipe_DietaryType, 
        Recipe_Description 
        FROM recipe 
        WHERE Recipe_ID = ? AND User_ID = ?");
    if (!$checkStmt) {
        die("Prepare failed: " . $conn->error);
    }
    $checkStmt->bind_param("ii", $recipeID, $userID);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows === 1) {
        $recipe = $result->fetch_assoc();
        
        // Prepare audit data in JSON format
        $auditDetails = json_encode([
            'Recipe_Title' => $recipe['Recipe_Title'],
            'Recipe_Cuisine' => $recipe['Recipe_CuisineType'],
            'Recipe_Dietary' => $recipe['Recipe_DietaryType'],
            'Recipe_Description' => $recipe['Recipe_Description']
        ]);

        $conn->query("SET FOREIGN_KEY_CHECKS = 0");
        $conn->begin_transaction();

        try {
            // Delete related records first
           $tables = [
                'comment' => 'Recipe_ID',
                'upload_log' => 'Log_RecipeID',
                'step' => 'Recipe_ID',
                'image' => 'Recipe_ID'
            ];

            foreach ($tables as $table => $column) {
                $stmt = $conn->prepare("DELETE FROM `$table` WHERE `$column` = ?");
                if (!$stmt) {
                    throw new Exception("Prepare failed for $table: " . $conn->error);
                }
                $stmt->bind_param("i", $recipeID);
                $stmt->execute();
            }

            // Delete from recipe table
            $recipeStmt = $conn->prepare("DELETE FROM recipe WHERE Recipe_ID = ?");
            if (!$recipeStmt) {
                throw new Exception("Prepare failed for recipe: " . $conn->error);
            }
            $recipeStmt->bind_param("i", $recipeID);
            $recipeStmt->execute();

            // Record the deletion in audit trail
            $action = 'Delete';
            $auditRecipeID = null; // Store 0 in the Recipe_ID column intentionally

            // Add actual deleted Recipe_ID into the JSON details
            $auditDetails = json_encode([
                'Deleted_Recipe_ID' => $recipe['Recipe_ID'],
                'Recipe_Title' => $recipe['Recipe_Title'],
                'Recipe_Cuisine' => $recipe['Recipe_CuisineType'],
                'Recipe_Dietary' => $recipe['Recipe_DietaryType'],
                'Recipe_Description' => $recipe['Recipe_Description']
            ]);

            $auditQuery = "INSERT INTO audit_trail 
                (User_ID, Action, Recipe_ID, Recipe_Title, Details, Timestamp) 
                VALUES (?, ?, ?, ?, ?, NOW())";

            $auditStmt = $conn->prepare($auditQuery);
            $auditStmt->bind_param(
                "isiss", 
                $userID, 
                $action,
                $auditRecipeID, // always 0 for deletions
                $recipe['Recipe_Title'], 
                $auditDetails
            );
            $auditStmt->execute();

            $conn->commit();
            $conn->query("SET FOREIGN_KEY_CHECKS = 1");

            echo "<script>
                sessionStorage.setItem('delete', 'Recipe deleted successfully!');
                window.location.href = 'recipe.php';
                </script>";
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            $conn->query("SET FOREIGN_KEY_CHECKS = 1");
            error_log("Delete Recipe Error: " . $e->getMessage());
            echo "<script>
                sessionStorage.setItem('delete', 'Failed to delete recipe. Please try again.');
                window.location.href = 'recipe.php';
                </script>";
            exit();
        }
    } else {
        echo "<script>
            sessionStorage.setItem('delete', 'Recipe not found or you don\\'t have permission.');
            window.location.href = 'recipe.php';
            </script>";
    }
} else {
    echo "<script>
        sessionStorage.setItem('delete', 'No recipe ID provided.');
        window.location.href = 'recipe.php';
        </script>";
}

$conn->close();
?>