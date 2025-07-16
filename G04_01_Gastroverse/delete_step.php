<?php
session_start();
include('connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {  // changed to GET
    $stepID = isset($_GET['step_id']) ? intval($_GET['step_id']) : 0;
    $recipeID = isset($_GET['recipe_id']) ? intval($_GET['recipe_id']) : 0;

    if ($stepID > 0 && $recipeID > 0) {

        // Select media paths first
        $stmtSelect = $conn->prepare("SELECT Step_ImagePath, Step_VideoPath, Step_AudioPath FROM step WHERE Step_ID = ?");
        $stmtSelect->bind_param("i", $stepID);
        $stmtSelect->execute();
        $result = $stmtSelect->get_result();

        if ($result && $row = $result->fetch_assoc()) {
            $imagePath = $row['Step_ImagePath'];
            $videoPath = $row['Step_VideoPath'];
            $audioPath = $row['Step_AudioPath'];

            // Delete physical files if they exist
            if (!empty($imagePath) && file_exists($imagePath)) {
                unlink($imagePath);
            }
            if (!empty($videoPath) && file_exists($videoPath)) {
                unlink($videoPath);
            }
            if (!empty($audioPath) && file_exists($audioPath)) {
                unlink($audioPath);
            }
        }
        $stmtSelect->close();

        // Delete the step from database
        $stmtDelete = $conn->prepare("DELETE FROM step WHERE Step_ID = ?");
        $stmtDelete->bind_param("i", $stepID);

        if ($stmtDelete->execute()) {
            header("Location: edit_recipe.php?id=" . $recipeID);
            exit();
        } else {
            echo "Error deleting step: " . $stmtDelete->error;
        }

        $stmtDelete->close();
    } else {
        echo "Invalid data.";
    }
} else {
    echo "Invalid request.";
}

$conn->close();
?>