<?php
include('header.php');

if (!isset($_GET['image_id']) || !isset($_GET['recipe_id'])) {
    echo "Invalid request.";
    exit;
}

$image_id = intval($_GET['image_id']);
$recipe_id = intval($_GET['recipe_id']);
$UserID = $_SESSION['UserID'];

// Verify image ownership
$sql = "SELECT Image_Path FROM image WHERE Image_ID = ? AND Recipe_ID = ? 
        AND Recipe_ID IN (SELECT Recipe_ID FROM recipe WHERE User_ID = ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $image_id, $recipe_id, $UserID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Unauthorized or image not found.";
    exit;
}

$row = $result->fetch_assoc();
$imagePath = $row['Image_Path'];

// Delete image file from server
if (file_exists($imagePath)) {
    unlink($imagePath);
}

// Delete from database
$sqlDelete = "DELETE FROM image WHERE Image_ID = ?";
$stmtDelete = $conn->prepare($sqlDelete);
$stmtDelete->bind_param("i", $image_id);
$stmtDelete->execute();

$stmt->close();
$stmtDelete->close();

// Redirect back to edit page
header("Location: edit_recipe.php?id=" . $recipe_id);
exit;
?>