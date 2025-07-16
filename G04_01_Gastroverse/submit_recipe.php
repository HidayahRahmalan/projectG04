<?php
// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include('connect.php');

// Use consistent session key for user ID
if (!isset($_SESSION['UserID'])) {
    echo "<script>
        sessionStorage.setItem('unsuccess_insert_recipe', 'User not logged in.');
        window.location.href = 'recipe.php';
        </script>";
    exit();
}
$userId = $_SESSION['UserID'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and retrieve form data
    $title = trim($_POST['recipe_title'] ?? '');
    $description = trim($_POST['recipe_description'] ?? '');
    $cuisine = trim($_POST['recipe_cuisine'] ?? '');
    $dietary = trim($_POST['recipe_dietary'] ?? '');
    $uploadDate = date('Y-m-d');

    // Validate required fields
    if (empty($title) || empty($description) || empty($cuisine) || empty($dietary)) {
        echo "<script>
            sessionStorage.setItem('unsuccess_insert_recipe', 'Missing required fields.');
            window.location.href = 'recipe.php';
            </script>";
        exit();
    }

    // Validate image upload 
    if (empty($_FILES['recipe_images']['name'][0])) {
        echo "<script>
            sessionStorage.setItem('image_upload', 'Image is not inserted');
            window.location.href = 'recipe.php';
            </script>";
        exit();
    }
    if (count($_FILES['recipe_images']['name']) > 4) {
        echo "<script>
            sessionStorage.setItem('image_upload', 'Image inserted is more than 4');
            window.location.href = 'recipe.php';
            </script>";
        exit();
    }

    // Validate steps
    $stepCount = intval($_POST['step_count'] ?? 0);
    if ($stepCount < 1) {
        echo "<script>
            sessionStorage.setItem('step_verify', 'You must add at least one step.');
            window.location.href = 'recipe.php';
            </script>";
        exit();
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        // Insert recipe
        $stmt = $conn->prepare("INSERT INTO recipe (Recipe_Title, Recipe_Description, Recipe_CuisineType, Recipe_DietaryType, Recipe_UploadDate, User_ID) VALUES (?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("sssssi", $title, $description, $cuisine, $dietary, $uploadDate, $userId);

        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        $recipeId = $stmt->insert_id;
        $stmt->close();

        // Insert audit trail for recipe addition
        $auditAction = 'Add';
        $auditRecipeID = null;
        $auditDetails = json_encode([
            'Recipe_Title' => $title,
            'Recipe_Cuisine' => $cuisine,
            'Recipe_Dietary' => $dietary,
            'Recipe_Description' => $description
        ], JSON_UNESCAPED_UNICODE);

        $auditStmt = $conn->prepare("INSERT INTO audit_trail (User_ID, Action, Recipe_ID, Recipe_Title, Details, Timestamp) VALUES (?, ?, ?, ?, ?, NOW())");
        if (!$auditStmt) {
            throw new Exception("Audit prepare failed: " . $conn->error);
        }
        $auditStmt->bind_param("isiss", $userId, $auditAction, $auditRecipeID, $title, $auditDetails);
        if (!$auditStmt->execute()) {
            throw new Exception("Audit execute failed: " . $auditStmt->error);
        }
        $auditStmt->close();

        // Upload and insert images
        if (!empty($_FILES['recipe_images']['name'][0])) {
            $imageCount = count($_FILES['recipe_images']['name']);
            for ($i = 0; $i < $imageCount && $i < 4; $i++) {
                if ($_FILES['recipe_images']['error'][$i] === UPLOAD_ERR_OK) {
                    $tmpName = $_FILES['recipe_images']['tmp_name'][$i];
                    $originalName = $_FILES['recipe_images']['name'][$i];
                    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                    $uniqueName = uniqid('img_', true) . '.' . $ext;
                    $targetPath = "uploads/recipe_images/" . $uniqueName;

                    $allowedTypes = ['jpg', 'jpeg', 'png'];
                    if (!in_array($ext, $allowedTypes)) {
                        continue;
                    }

                    // Ensure target directory exists
                    if (!is_dir(dirname($targetPath))) {
                        mkdir(dirname($targetPath), 0777, true);
                    }

                    if (move_uploaded_file($tmpName, $targetPath)) {
                        // Initialize metadata as empty string
                        $metadata = '';

                        // [Previous metadata collection code remains the same...]
                        if (in_array($ext, ['jpg', 'jpeg', 'tiff'])) {
                        if (function_exists('exif_read_data')) {
                            $exif = @exif_read_data($targetPath, 0, true);
                            if ($exif !== false && !empty($exif)) {
                                $cleanExif = [];
                                foreach ($exif as $section => $data) {
                                    if (is_array($data)) {
                                        foreach ($data as $key => $value) {
                                            if (is_string($value) && strlen($value) < 1000 && mb_check_encoding($value, 'UTF-8')) {
                                                $cleanExif[$section][$key] = $value;
                                            } elseif (is_numeric($value)) {
                                                $cleanExif[$section][$key] = $value;
                                            }
                                        }
                                    }
                                }
                                if (!empty($cleanExif)) {
                                    $metadata = json_encode($cleanExif, JSON_UNESCAPED_UNICODE);
                                    if (json_last_error() !== JSON_ERROR_NONE) {
                                        $metadata = '';
                                    }
                                }
                            }
                        }
                    }
                    // For other image types, get basic image info
                    if (empty($metadata)) {
                        $imageInfo = @getimagesize($targetPath);
                        if ($imageInfo !== false) {
                            $basicInfo = [
                                'width' => $imageInfo[0],
                                'height' => $imageInfo[1],
                                'type' => $imageInfo[2],
                                'mime' => $imageInfo['mime'] ?? '',
                                'file_size' => filesize($targetPath),
                                'bits' => $imageInfo['bits'] ?? null,
                                'channels' => $imageInfo['channels'] ?? null,
                                'aspect_ratio' => $imageInfo[1] ? round($imageInfo[0] / $imageInfo[1], 2) : 0,
                                'orientation' => $imageInfo[0] > $imageInfo[1] ? 'landscape' : ($imageInfo[0] < $imageInfo[1] ? 'portrait' : 'square'),
                                'resolution' => $imageInfo[0] . 'x' . $imageInfo[1],
                                'file_extension' => $ext,
                                'original_filename' => $originalName,
                                'unique_filename' => $uniqueName,
                                'upload_date' => date('Y-m-d H:i:s'),
                                'image_type_name' => image_type_to_mime_type($imageInfo[2]),
                                'megapixels' => round(($imageInfo[0] * $imageInfo[1]) / 1000000, 2),
                                'file_size_mb' => round(filesize($targetPath) / 1048576, 2),
                                'color_depth' => ($imageInfo['bits'] ?? 0) * ($imageInfo['channels'] ?? 1)
                            ];
                            $metadata = json_encode($basicInfo);
                        }
                    }

                        // Insert into database
                        $stmt = $conn->prepare("INSERT INTO image (Image_Path, Image_Metadata, Recipe_ID) VALUES (?, ?, ?)");
                        if ($stmt) {
                            $stmt->bind_param("ssi", $targetPath, $metadata, $recipeId);
                            if (!$stmt->execute()) {
                                @unlink($targetPath);
                                throw new Exception("Image insert failed: " . $stmt->error);
                            }
                            $stmt->close();
                        } else {
                            @unlink($targetPath);
                            throw new Exception("Image prepare failed: " . $conn->error);
                        }
                    }
                }
            }
        }

        // Insert steps
        for ($i = 1; $i <= $stepCount; $i++) {
            $instruction = $_POST["step_instruction_$i"] ?? '';

            // [Previous step media handling code remains the same...]
 // Handle step video upload
        $videoPath = '';
        if (isset($_FILES["step_video_$i"]) && $_FILES["step_video_$i"]['error'] === UPLOAD_ERR_OK) {
            $videoExt = pathinfo($_FILES["step_video_$i"]['name'], PATHINFO_EXTENSION);
            $uniqueVideoName = uniqid('vid_', true) . '.' . $videoExt;
            $videoPath = "uploads/steps/videos/" . $uniqueVideoName;
            if (!is_dir(dirname($videoPath))) {
                mkdir(dirname($videoPath), 0777, true);
            }
            move_uploaded_file($_FILES["step_video_$i"]['tmp_name'], $videoPath);
        }

        // Handle step image upload
        $imagePath = '';
        if (isset($_FILES["step_image_$i"]) && $_FILES["step_image_$i"]['error'] === UPLOAD_ERR_OK) {
            $imageExt = pathinfo($_FILES["step_image_$i"]['name'], PATHINFO_EXTENSION);
            $uniqueImageName = uniqid('img_', true) . '.' . $imageExt;
            $imagePath = "uploads/steps/images/" . $uniqueImageName;
            if (!is_dir(dirname($imagePath))) {
                mkdir(dirname($imagePath), 0777, true);
            }
            move_uploaded_file($_FILES["step_image_$i"]['tmp_name'], $imagePath);
        }

        // Handle step audio upload
        $audioPath = '';
        if (isset($_FILES["step_audio_$i"]) && $_FILES["step_audio_$i"]['error'] === UPLOAD_ERR_OK) {
            $audioExt = pathinfo($_FILES["step_audio_$i"]['name'], PATHINFO_EXTENSION);
            $uniqueAudioName = uniqid('aud_', true) . '.' . $audioExt;
            $audioPath = "uploads/steps/audio/" . $uniqueAudioName;
            if (!is_dir(dirname($audioPath))) {
                mkdir(dirname($audioPath), 0777, true);
            }
            move_uploaded_file($_FILES["step_audio_$i"]['tmp_name'], $audioPath);
        }

            $stmt = $conn->prepare("INSERT INTO step (Step_Number, Step_Instruction, Step_VideoPath, Step_ImagePath, Step_AudioPath, Recipe_ID) VALUES (?, ?, ?, ?, ?, ?)");
            if (!$stmt) {
                throw new Exception("Step prepare failed: " . $conn->error);
            }
            $stmt->bind_param("issssi", $i, $instruction, $videoPath, $imagePath, $audioPath, $recipeId);
            if (!$stmt->execute()) {
                throw new Exception("Step execute failed: " . $stmt->error);
            }
            $stmt->close();
        }

        // Insert into upload_log
        $logDate = date('Y-m-d');
        $logDay = date('l');
        $stmt = $conn->prepare("INSERT INTO upload_log (Log_Date, Log_Day, Log_RecipeID, Log_RecipeTitle, User_ID) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Log prepare failed: " . $conn->error);
        }
        $stmt->bind_param("ssisi", $logDate, $logDay, $recipeId, $title, $userId);
        if (!$stmt->execute()) {
            throw new Exception("Log execute failed: " . $stmt->error);
        }
        $stmt->close();

        // Commit transaction
        $conn->commit();

        echo "<script>
            sessionStorage.setItem('success_insert_recipe', 'New recipe added!');
            window.location.href = 'recipe.php';
            </script>";
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        error_log("Recipe submission error: " . $e->getMessage());
        echo "<script>
            sessionStorage.setItem('unsuccess_insert_recipe', 'Error: " . addslashes($e->getMessage()) . "');
            window.location.href = 'recipe.php';
            </script>";
        exit();
    }
} else {
    echo "<script>
        sessionStorage.setItem('unsuccess_insert_recipe', 'Invalid request.');
        window.location.href = 'recipe.php';
        </script>";
    exit();
}
?>