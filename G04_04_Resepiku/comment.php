<?php
include 'connection.php';
session_start();

header('Content-Type: application/json');

// Step 1: Ensure request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
    exit;
}

// Step 2: Get submitted data
$comment  = $_POST['comment'] ?? '';
$recipeID = $_POST['recipeID'] ?? '';
$userID   = $_SESSION['user_id'] ?? ''; // User must be logged in

// Step 3: Validate required fields
if (empty($comment) || empty($recipeID) || empty($userID)) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Missing required fields.",
        "debug" => [
            "comment"   => $comment,
            "recipeID"  => $recipeID,
            "userID"    => $userID,
            "_POST"     => $_POST,
            "_SESSION"  => $_SESSION
        ]
    ]);
    exit;
}

// Step 4: Fetch FoodID based on RecipeID
$foodID = null;
$foodStmt = $conn->prepare("SELECT FoodID FROM RECIPE WHERE RecipeID = ?");
$foodStmt->bind_param("s", $recipeID);
$foodStmt->execute();
$foodResult = $foodStmt->get_result();

if ($foodRow = $foodResult->fetch_assoc()) {
    $foodID = $foodRow['FoodID'];
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Invalid RecipeID: No matching FoodID found."]);
    exit;
}
$foodStmt->close();

// Step 5: Handle optional file uploads
$imageData = null;
$videoData = null;

if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
    $imageData = file_get_contents($_FILES['image']['tmp_name']);
}

if (isset($_FILES['video']) && $_FILES['video']['error'] === 0) {
    $videoData = file_get_contents($_FILES['video']['tmp_name']);
}

// Step 6: Insert feedback into database
$sql = "INSERT INTO FEEDBACK (
    CommentType,
    ComDateTime,
    Comment,
    VideoAttachment,
    ImageAttachment,
    UserID,
    RecipeID,
    FoodID
) VALUES (?, NOW(), ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$commentType = 'Maklum Balas';

// s = string, so 8 parameters = "ssssssss"
$stmt->bind_param("sssssss", $commentType, $comment, $videoData, $imageData, $userID, $recipeID, $foodID);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Maklum balas berjaya dihantar."]);
} else {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Failed to save feedback.",
        "error" => $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>
