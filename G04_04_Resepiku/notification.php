<?php
session_start();
include 'connection.php';

date_default_timezone_set("Asia/Kuala_Lumpur");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $comment = $_POST['comment'] ?? '';
    $recipeID = $_POST['recipe_id'] ?? '';
    $userID = $_SESSION['userid'] ?? null; // Must be logged in

    if (!$userID || !$recipeID || trim($comment) === '') {
        echo json_encode(["success" => false, "message" => "Sila log masuk dan isi semua maklumat."]);
        exit();
    }

    $imagePath = null;
    $videoPath = null;
    $targetDir = "uploads/";

    // Upload image
    if (!empty($_FILES['image']['name'])) {
        $imageName = time() . "_" . basename($_FILES['image']['name']);
        $imagePath = $targetDir . $imageName;
        move_uploaded_file($_FILES['image']['tmp_name'], $imagePath);
    }

    // Upload video
    if (!empty($_FILES['video']['name'])) {
        $videoName = time() . "_" . basename($_FILES['video']['name']);
        $videoPath = $targetDir . $videoName;
        move_uploaded_file($_FILES['video']['tmp_name'], $videoPath);
    }

    $datetime = date("Y-m-d H:i:s");
    $commentType = 'Public'; // Default type

    $stmt = $conn->prepare("INSERT INTO FEEDBACK (CommentType, ComDateTime, Comment, VideoAttachment, ImageAttachment, UserID, RecipeID)
                            VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssi", $commentType, $datetime, $comment, $videoPath, $imagePath, $userID, $recipeID);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Maklum balas berjaya dihantar."]);
    } else {
        echo json_encode(["success" => false, "message" => "Ralat ketika menghantar maklum balas."]);
    }
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["success" => false, "message" => "Akses tidak sah."]);
}
?>
