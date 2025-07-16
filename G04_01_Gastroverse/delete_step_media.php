<?php
include('header.php');

if (!isset($_GET['step_id']) || !isset($_GET['media_type']) || !isset($_GET['recipe_id'])) {
    echo "Invalid request.";
    exit;
}

$step_id = intval($_GET['step_id']);
$media_type = $_GET['media_type'];
$recipe_id = intval($_GET['recipe_id']);
$UserID = $_SESSION['UserID'];

// Validate media type
$allowed_media = ['image', 'video', 'audio'];
if (!in_array($media_type, $allowed_media)) {
    echo "Invalid media type.";
    exit;
}

// Build proper column name based on media type
$column = '';
switch ($media_type) {
    case 'image':
        $column = 'Step_ImagePath';
        break;
    case 'video':
        $column = 'Step_VideoPath';
        break;
    case 'audio':
        $column = 'Step_AudioPath';
        break;
}

// Verify step ownership and fetch media path
$sql = "SELECT $column FROM step WHERE Step_ID = ? AND Recipe_ID = ? 
        AND Recipe_ID IN (SELECT Recipe_ID FROM recipe WHERE User_ID = ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $step_id, $recipe_id, $UserID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Unauthorized access or step not found.";
    exit;
}

$row = $result->fetch_assoc();
$mediaPath = $row[$column];

// Delete file from server if exists
if (!empty($mediaPath) && file_exists($mediaPath)) {
    unlink($mediaPath);
}

// Update database to remove media path
$sqlUpdate = "UPDATE step SET $column = '' WHERE Step_ID = ?";
$stmtUpdate = $conn->prepare($sqlUpdate);
$stmtUpdate->bind_param("i", $step_id);
$stmtUpdate->execute();

$stmt->close();
$stmtUpdate->close();

// Redirect back to edit page
header("Location: edit_recipe.php?id=" . $recipe_id);
exit;
?>