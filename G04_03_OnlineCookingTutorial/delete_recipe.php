<?php
session_start();
require_once 'db_connect.php';
if (!isset($_GET['recipeID'])) {
    echo "Recipe ID is required.";
    exit();
}
$userID = $_SESSION['userID'];
$recipeID = intval($_GET['recipeID']);

// Log the deletion
$action = "Delete";
$details = "User $user deleted recipe ID $recipeID";
$log_stmt = $conn->prepare("INSERT INTO LOG (userID, action, details) VALUES (?, ?, ?)");
$log_stmt->bind_param("iss", $userID, $action, $details);
$log_stmt->execute();
$log_stmt->close();

?>

<!DOCTYPE html>
<html>
<head>
    <title>Deleting Recipe...</title>
    <script>
        // Mark recipe as deleted in localStorage
        const recipeID = <?= json_encode($recipeID) ?>;
        localStorage.setItem('deleted_recipe_' + recipeID, '1');
        // Redirect back to recipe list
        window.location.href = 'recipe_list.php';
    </script>
</head>
<body>
</body>
</html>
