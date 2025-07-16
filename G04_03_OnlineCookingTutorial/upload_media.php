<?php
session_start();
require_once 'db_connect.php';

// Dummy login: fetch any valid user from the database
if (!isset($_SESSION['user'])) {
    $result = $conn->query("SELECT userID FROM user LIMIT 1");

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $_SESSION['user'] = $row['userID'];
    } else {
        die("No users exist in the database. Please create one.");
    }
}

$userID = $_SESSION['user'];

// Get recipeID from query string
if (!isset($_GET['recipeID']) || !is_numeric($_GET['recipeID'])) {
    die("Invalid recipe ID.");
}
$recipeID = intval($_GET['recipeID']);
$message = "";

// Upload handling
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['media']) && $_FILES['media']['error'] === UPLOAD_ERR_OK) {
        $tmpPath = $_FILES['media']['tmp_name'];
        $originalName = $_FILES['media']['name'];
        $fileType = $_FILES['media']['type'];
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'mov', 'avi'];
        if (!in_array($ext, $allowed)) {
            $message = "Unsupported file type.";
        } else {
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            $newName = uniqid('media_', true) . '.' . $ext;
            $destPath = $uploadDir . $newName;

            if (move_uploaded_file($tmpPath, $destPath)) {
                $mediaType = (strpos($fileType, 'video') !== false) ? 'video' : 'image';
                $metadata = json_encode([
                    'original_name' => $originalName,
                    'size' => $_FILES['media']['size'],
                    'type' => $fileType
                ]);

                // Insert into MEDIA table
                $stmt = $conn->prepare("INSERT INTO MEDIA (userID, recipeID, reviewID, media_type, file_path, metadata) VALUES (?, ?, NULL, ?, ?, ?)");
                $stmt->bind_param("iisss", $userID, $recipeID, $mediaType, $destPath, $metadata);

                if ($stmt->execute()) {
                    $message = "Media uploaded successfully.";
                } else {
                    $message = "Database error: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $message = "Failed to move uploaded file.";
            }
        }
    } else {
        $message = "No file uploaded.";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Upload Recipe Media</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .upload-form-wrapper {
            max-width: 700px;
            margin: 30px auto;
            padding: 20px;
            background: #fffefc;
            border: 2px dashed #2c3e50;
            border-radius: 8px;
            text-align: center;
        }

        .btn-upload {
            background-color: #27ae60;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-upload:hover {
            background-color: #219150;
        }

        .message-box {
            margin-top: 15px;
            color: #c0392b;
        }
    </style>
</head>

<body>
    <?php include 'header.php'; ?>

    <h2>Upload Media for Recipe #<?= htmlspecialchars($recipeID); ?></h2>

    <div class="upload-form-wrapper">
        <?php if ($message): ?>
            <div class="message-box"><?= htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <input type="file" name="media" accept="image/*,video/*" required>
            <br><br>
            <button type="submit" class="btn-upload">Upload</button>
        </form>

        <br>
        <a href="view_recipe.php?recipeID=<?= $recipeID ?>" style="text-decoration: underline;">Back to Recipe</a>
    </div>

    <?php include 'footer.php'; ?>
</body>

</html>