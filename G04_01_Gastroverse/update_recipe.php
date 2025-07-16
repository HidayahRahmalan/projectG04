<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include('connect.php');

// Verify user is logged in
if (!isset($_SESSION['UserID'])) {
    echo "<script>
        sessionStorage.setItem('recipe_error', 'You must be logged in to update recipes.');
        window.location.href = 'recipe.php';
        </script>";
    exit();
}

$user_id = $_SESSION['UserID'];

// Validate required fields
if (!isset($_POST['recipe_id']) || !isset($_POST['title']) || !isset($_POST['description']) || 
    !isset($_POST['cuisine']) || !isset($_POST['dietary'])) {
    echo "<script>
        sessionStorage.setItem('recipe_error', 'Missing required fields.');
        window.location.href = 'recipe.php';
        </script>";
    exit();
}

// Sanitize inputs
$recipe_id = intval($_POST['recipe_id']);
$title = trim($_POST['title']);
$description = trim($_POST['description']);
$cuisine = trim($_POST['cuisine']);
$dietary = trim($_POST['dietary']);

// Start transaction
$conn->begin_transaction();

try {
    // First get old recipe values for audit comparison
    $stmt = $conn->prepare("SELECT Recipe_Title, Recipe_Description, Recipe_CuisineType, Recipe_DietaryType FROM recipe WHERE Recipe_ID = ?");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("i", $recipe_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $oldRecipe = $result->fetch_assoc();
    $stmt->close();

    // Update recipe main data
    $stmt = $conn->prepare("UPDATE recipe SET Recipe_Title=?, Recipe_Description=?, Recipe_CuisineType=?, Recipe_DietaryType=? WHERE Recipe_ID=?");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("ssssi", $title, $description, $cuisine, $dietary, $recipe_id);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    $stmt->close();

    // Replace existing images
    if (isset($_POST['existing_image_id'])) {
        foreach ($_POST['existing_image_id'] as $image_id) {
            $fileKey = "replace_image_$image_id";
            if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] == UPLOAD_ERR_OK) {
                $newFileName = uniqid() . '_' . basename($_FILES[$fileKey]['name']);
                $targetPath = "uploads/recipe_images/" . $newFileName;
                
                if (!is_dir('uploads/recipe_images/')) {
                    mkdir('uploads/recipe_images/', 0777, true);
                }

                if (move_uploaded_file($_FILES[$fileKey]['tmp_name'], $targetPath)) {
                    $stmt = $conn->prepare("UPDATE image SET Image_Path=? WHERE Image_ID=?");
                    if (!$stmt) {
                        throw new Exception("Image update prepare failed: " . $conn->error);
                    }
                    $stmt->bind_param("si", $targetPath, $image_id);
                    if (!$stmt->execute()) {
                        throw new Exception("Image update execute failed: " . $stmt->error);
                    }
                    $stmt->close();
                }
            }
        }
    }

    // Insert new images
    $newImageIndex = 0;
    while (isset($_FILES["new_image_$newImageIndex"])) {
        $file = $_FILES["new_image_$newImageIndex"];
        if ($file['error'] == UPLOAD_ERR_OK) {
            $newFileName = uniqid() . '_' . basename($file['name']);
            $targetPath = "uploads/recipe_images/" . $newFileName;
            
            if (!is_dir('uploads/recipe_images/')) {
                mkdir('uploads/recipe_images/', 0777, true);
            }

            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                $stmt = $conn->prepare("INSERT INTO image (Recipe_ID, Image_Path) VALUES (?, ?)");
                if (!$stmt) {
                    throw new Exception("Image insert prepare failed: " . $conn->error);
                }
                $stmt->bind_param("is", $recipe_id, $targetPath);
                if (!$stmt->execute()) {
                    throw new Exception("Image insert execute failed: " . $stmt->error);
                }
                $stmt->close();
            }
        }
        $newImageIndex++;
    }

    // Update existing steps
    foreach ($_POST as $key => $value) {
        if (preg_match('/^step_instruction_(\d+)$/', $key, $matches)) {
            $step_number = intval($matches[1]);
            $instruction = $value;
            $step_id = intval($_POST["step_id_$step_number"]);

            $imagePath = $videoPath = $audioPath = null;

            // Handle step image update
            if (isset($_FILES["step_image_$step_number"]) && $_FILES["step_image_$step_number"]['error'] == UPLOAD_ERR_OK) {
                $file = $_FILES["step_image_$step_number"];
                $newFileName = uniqid() . '_' . basename($file['name']);
                $targetPath = "uploads/steps/images/" . $newFileName;
                if (!is_dir('uploads/steps/images/')) mkdir('uploads/steps/images/', 0777, true);
                if (move_uploaded_file($file['tmp_name'], $targetPath)) $imagePath = $targetPath;
            }

            // Handle step video update
            if (isset($_FILES["step_video_$step_number"]) && $_FILES["step_video_$step_number"]['error'] == UPLOAD_ERR_OK) {
                $file = $_FILES["step_video_$step_number"];
                $newFileName = uniqid() . '_' . basename($file['name']);
                $targetPath = "uploads/steps/videos/" . $newFileName;
                if (!is_dir('uploads/steps/videos/')) mkdir('uploads/steps/videos/', 0777, true);
                if (move_uploaded_file($file['tmp_name'], $targetPath)) $videoPath = $targetPath;
            }

            // Handle step audio update
            if (isset($_FILES["step_audio_$step_number"]) && $_FILES["step_audio_$step_number"]['error'] == UPLOAD_ERR_OK) {
                $file = $_FILES["step_audio_$step_number"];
                $newFileName = uniqid() . '_' . basename($file['name']);
                $targetPath = "uploads/steps/audio/" . $newFileName;
                if (!is_dir('uploads/steps/audio/')) mkdir('uploads/steps/audio/', 0777, true);
                if (move_uploaded_file($file['tmp_name'], $targetPath)) $audioPath = $targetPath;
            }

            // Dynamic SQL for step update
            $sql = "UPDATE step SET Step_Instruction=?";
            $types = "s";
            $params = [$instruction];
            
            if ($imagePath !== null) { $sql .= ", Step_ImagePath=?"; $types .= "s"; $params[] = $imagePath; }
            if ($videoPath !== null) { $sql .= ", Step_VideoPath=?"; $types .= "s"; $params[] = $videoPath; }
            if ($audioPath !== null) { $sql .= ", Step_AudioPath=?"; $types .= "s"; $params[] = $audioPath; }
            
            $sql .= " WHERE Step_ID=?";
            $types .= "i";
            $params[] = $step_id;

            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Step update prepare failed: " . $conn->error);
            }
            $stmt->bind_param($types, ...$params);
            if (!$stmt->execute()) {
                throw new Exception("Step update execute failed: " . $stmt->error);
            }
            $stmt->close();
        }
    }

    // Insert new steps
    foreach ($_POST as $key => $value) {
        if (preg_match('/^new_step_instruction_(\d+)$/', $key, $matches)) {
            $new_step_number = intval($matches[1]);
            $instruction = $value;

            // Get next step number
            $result = $conn->query("SELECT MAX(Step_Number) as max_num FROM step WHERE Recipe_ID = $recipe_id");
            $row = $result->fetch_assoc();
            $nextStepNumber = ($row['max_num'] ?? 0) + 1;

            $imagePath = $videoPath = $audioPath = '';

            // Handle new step image
            if (isset($_FILES["new_step_image_$new_step_number"]) && $_FILES["new_step_image_$new_step_number"]['error'] == UPLOAD_ERR_OK) {
                $file = $_FILES["new_step_image_$new_step_number"];
                $newFileName = uniqid() . '_' . basename($file['name']);
                $targetPath = "uploads/steps/images/" . $newFileName;
                if (!is_dir('uploads/steps/images/')) mkdir('uploads/steps/images/', 0777, true);
                if (move_uploaded_file($file['tmp_name'], $targetPath)) $imagePath = $targetPath;
            }

            // Handle new step video
            if (isset($_FILES["new_step_video_$new_step_number"]) && $_FILES["new_step_video_$new_step_number"]['error'] == UPLOAD_ERR_OK) {
                $file = $_FILES["new_step_video_$new_step_number"];
                $newFileName = uniqid() . '_' . basename($file['name']);
                $targetPath = "uploads/steps/videos/" . $newFileName;
                if (!is_dir('uploads/steps/videos/')) mkdir('uploads/steps/videos/', 0777, true);
                if (move_uploaded_file($file['tmp_name'], $targetPath)) $videoPath = $targetPath;
            }

            // Handle new step audio
            if (isset($_FILES["new_step_audio_$new_step_number"]) && $_FILES["new_step_audio_$new_step_number"]['error'] == UPLOAD_ERR_OK) {
                $file = $_FILES["new_step_audio_$new_step_number"];
                $newFileName = uniqid() . '_' . basename($file['name']);
                $targetPath = "uploads/steps/audio/" . $newFileName;
                if (!is_dir('uploads/steps/audio/')) mkdir('uploads/steps/audio/', 0777, true);
                if (move_uploaded_file($file['tmp_name'], $targetPath)) $audioPath = $targetPath;
            }

            $stmt = $conn->prepare("INSERT INTO step (Recipe_ID, Step_Number, Step_Instruction, Step_ImagePath, Step_VideoPath, Step_AudioPath) VALUES (?, ?, ?, ?, ?, ?)");
            if (!$stmt) {
                throw new Exception("New step prepare failed: " . $conn->error);
            }
            $stmt->bind_param("iissss", $recipe_id, $nextStepNumber, $instruction, $imagePath, $videoPath, $audioPath);
            if (!$stmt->execute()) {
                throw new Exception("New step execute failed: " . $stmt->error);
            }
            $stmt->close();
        }
    }

    // Create audit trail entry with before/after comparison
    $action = "Update";
    $auditRecipeID = null;
    $auditDetails = json_encode([
        'Previous_Values' => $oldRecipe,
        'New_Values' => [
            'Recipe_Title' => $title,
            'Recipe_Description' => $description,
            'Recipe_Cuisine' => $cuisine,
            'Recipe_Dietary' => $dietary
        ],
        'Changed_Fields' => array_diff_assoc(
            ['Title' => $title, 'Description' => $description, 'Cuisine' => $cuisine, 'Dietary' => $dietary],
            ['Title' => $oldRecipe['Recipe_Title'], 'Description' => $oldRecipe['Recipe_Description'], 
             'Cuisine' => $oldRecipe['Recipe_CuisineType'], 'Dietary' => $oldRecipe['Recipe_DietaryType']]
        )
    ], JSON_UNESCAPED_UNICODE);

    $auditStmt = $conn->prepare("INSERT INTO audit_trail (User_ID, Action, Recipe_ID, Recipe_Title, Details, Timestamp) VALUES (?, ?, ?, ?, ?, NOW())");
    if (!$auditStmt) {
        throw new Exception("Audit prepare failed: " . $conn->error);
    }
    $auditStmt->bind_param("isiss", $user_id, $action, $auditRecipeID, $title, $auditDetails);
    if (!$auditStmt->execute()) {
        throw new Exception("Audit execute failed: " . $auditStmt->error);
    }
    $auditStmt->close();

    // Commit transaction if everything succeeded
    $conn->commit();

    echo "<script>
        sessionStorage.setItem('recipe_success', 'Recipe updated successfully!');
        window.location.href = 'edit_recipe.php?id=$recipe_id';
        </script>";
    exit();

} catch (Exception $e) {
    $conn->rollback();
    error_log("Recipe update error: " . $e->getMessage());
    echo "<script>
        sessionStorage.setItem('recipe_error', 'Error updating recipe: " . addslashes($e->getMessage()) . "');
        window.location.href = 'edit_recipe.php?id=$recipe_id';
        </script>";
    exit();
}
?>