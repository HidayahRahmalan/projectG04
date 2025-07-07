<?php
include('dbConnection.php');

// Fetch all recipes and their image from media table
$sql = "SELECT r.*, m.URL 
        FROM recipes r 
        LEFT JOIN media m ON r.RecipeID = m.RecipeID 
        GROUP BY r.RecipeID";

$stmt = $conn->prepare($sql);
$stmt->execute();
$recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
