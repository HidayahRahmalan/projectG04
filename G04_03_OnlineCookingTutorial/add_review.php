<?php
session_start();
include 'db_connect.php';

$successMsg = $errorMsg = "";

// Make sure user is logged in
if (!isset($_SESSION['userID'])) {
    die("You must be logged in to submit a review.");
}

// Get recipeID from URL or POST
$recipeID = $_GET['recipeID'] ?? ($_POST['recipeID'] ?? null);
if (isset($_SESSION['userID'])) {
    $userID = $_SESSION['userID'];
} else {
    die("Please log in to add a review.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipeID = $_POST['recipeID'] ?? null;
    $rating = $_POST['rating'] ?? null;
    $review_text = $_POST['review_text'] ?? '';

    if (!$recipeID || !$rating) {
        die("Missing fields.");
    }

    // Handle file upload if exists
    $media_path = null;
    if (isset($_FILES['media']) && $_FILES['media']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $filename = basename($_FILES['media']['name']);
        $targetPath = $uploadDir . time() . '_' . $filename;

        if (move_uploaded_file($_FILES['media']['tmp_name'], $targetPath)) {
            $media_path = $targetPath;
        } else {
            $errorMsg = "Failed to upload file.";
        }
    }

    // Insert review
    $stmt = $conn->prepare(
        "INSERT INTO review (recipeID, userID, rating, review_text, media_path, created_at) 
         VALUES (?, ?, ?, ?, ?, NOW())"
    );
    $stmt->bind_param("iiiss", $recipeID, $userID, $rating, $review_text, $media_path);
    $stmt->execute();

    $reviewID = $conn->insert_id; // <-- GET THE NEW REVIEW ID HERE

    if ($stmt->affected_rows > 0) {
        $successMsg = "Review submitted successfully.";
    } else {
        $errorMsg = "Failed to submit review.";
    }
    $stmt->close();

    // Get recipe owner's userID
    $ownerQuery = $conn->prepare("SELECT userID FROM recipe WHERE recipeID = ?");
    $ownerQuery->bind_param("i", $recipeID);
    $ownerQuery->execute();
    $ownerResult = $ownerQuery->get_result();

    if ($ownerResult && $ownerResult->num_rows > 0) {
        $ownerRow = $ownerResult->fetch_assoc();
        $ownerID = $ownerRow['userID'];

        if ($ownerID != $userID) {
            $message = "Someone left a review on your recipe (ID: $recipeID).";
            // INSERT NOTIFICATION WITH reviewID!
            $insertNotif = $conn->prepare("INSERT INTO notifications (userID, reviewID, message) VALUES (?, ?, ?)");
            $insertNotif->bind_param("iis", $ownerID, $reviewID, $message);
            if (!$insertNotif->execute()) {
                $errorMsg = "Review saved but notification failed: " . $insertNotif->error;
            }
            $insertNotif->close();
        }
    } else {
        $errorMsg = "Recipe owner not found.";
    }
    $ownerQuery->close();

    if (!$errorMsg) {
        header("Location: view_other_people_recipe.php?recipeID=" . urlencode($recipeID));
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>CookBook - Submit Review</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #fff8f0;
      margin: 0;
      padding: 0;
    }
    .container {
      width: 50%;
      margin: 40px auto;
      background-color: #fff;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    h2 {
      text-align: center;
      color: #d35400;
    }
    label {
      font-weight: bold;
      display: block;
      margin-top: 15px;
      color: #333;
    }
    select, textarea, input[type="file"], button {
      width: 100%;
      padding: 10px;
      margin-top: 5px;
      border: 1px solid #ccc;
      border-radius: 6px;
      box-sizing: border-box;
      font-size: 16px;
    }
    button {
      background-color: #e67e22;
      color: white;
      font-weight: bold;
      cursor: pointer;
      transition: background-color 0.3s ease;
      margin-top: 20px;
    }
    button:hover {
      background-color: #d35400;
    }
    .msg {
      text-align: center;
      padding: 10px;
      border-radius: 5px;
      margin-bottom: 15px;
    }
    .success {
      background-color: #eafaf1;
      color: #2ecc71;
    }
    .error {
      background-color: #fdecea;
      color: #e74c3c;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Leave a Review 🍳</h2>

    <?php if ($successMsg): ?>
      <div class="msg success"><?= htmlspecialchars($successMsg) ?></div>
    <?php elseif ($errorMsg): ?>
      <div class="msg error"><?= htmlspecialchars($errorMsg) ?></div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="userID" value="<?= htmlspecialchars($userID) ?>" />
      <input type="hidden" name="recipeID" value="<?= htmlspecialchars($recipeID) ?>" />

      <label for="rating">Rating (1 to 5 stars):</label>
      <select name="rating" required>
        <option value="">-- Choose --</option>
        <option value="1">1 ⭐</option>
        <option value="2">2 ⭐⭐</option>
        <option value="3">3 ⭐⭐⭐</option>
        <option value="4">4 ⭐⭐⭐⭐</option>
        <option value="5">5 ⭐⭐⭐⭐⭐</option>
      </select>

      <label for="review_text">Your Comment:</label>
      <textarea name="review_text" rows="4" placeholder="What did you think about this recipe?" required></textarea>

      <label for="media">Upload Photo/Video (optional):</label>
      <input type="file" name="media" />

      <button type="submit">Submit Review</button>
    </form>
  </div>
</body>
</html>