<?php
session_start();
include 'db_connect.php';

// User must be logged in
if (!isset($_SESSION['userID'])) {
    die("Access denied. Please <a href='login.php'>log in</a>.");
}
$currentUserID = $_SESSION['userID'];

// Get review ID from URL or POST
$reviewID = isset($_GET['reviewID']) ? intval($_GET['reviewID']) : 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reviewID'])) {
    $reviewID = intval($_POST['reviewID']);
}

$recipeID = 0;
$canDelete = false;

// Check permission (get both recipeID, reviewAuthorID, recipeOwnerID)
$stmt = $conn->prepare("
    SELECT r.recipeID, r.userID AS reviewAuthorID, rec.userID AS recipeOwnerID
    FROM review r
    JOIN recipe rec ON r.recipeID = rec.recipeID
    WHERE r.reviewID = ?
");
$stmt->bind_param("i", $reviewID);
$stmt->execute();
$stmt->bind_result($recipeID, $reviewAuthorID, $recipeOwnerID);
if ($stmt->fetch()) {
    if ($currentUserID == $reviewAuthorID || $currentUserID == $recipeOwnerID) {
        $canDelete = true;
    }
}
$stmt->close();

// If POST and user is authorized, delete
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($canDelete) {
        $stmtDelete = $conn->prepare("DELETE FROM review WHERE reviewID = ?");
        $stmtDelete->bind_param("i", $reviewID);
        if ($stmtDelete->execute()) {
            header("Location: view_other_people_recipe.php?recipeID=$recipeID&deleted=1");
            exit();
        } else {
            $message = "Error deleting review.";
        }
        $stmtDelete->close();
    } else {
        $message = "Unauthorized attempt. You cannot delete this review.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Delete Review</title>
  <style>
    body { font-family: Arial; background-color: #f8f8f8; padding: 20px; }
    .container {
      max-width: 480px;
      margin: auto;
      background: white;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
      text-align: center;
    }
    .btn {
      padding: 10px 20px;
      margin: 10px;
      border: none;
      background-color: #e67e22;
      color: white;
      cursor: pointer;
      border-radius: 6px;
    }
    .btn.cancel {
      background-color: #ccc;
      color: black;
    }
    .message { color: red; font-weight: bold; }
  </style>
</head>
<body>
<div class="container">
    <?php if (!empty($message)): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
        <a href="view_other_people_recipe.php?recipeID=<?= $recipeID ?>" class="btn cancel">Back</a>
    <?php elseif ($canDelete): ?>
        <h2>Delete Review #<?= $reviewID ?></h2>
        <p>Are you sure you want to delete this review?</p>
        <form method="post">
            <input type="hidden" name="reviewID" value="<?= $reviewID ?>">
            <button type="submit" class="btn">Yes, Delete</button>
            <a href="view_other_people_recipe.php?recipeID=<?= $recipeID ?>" class="btn cancel">Cancel</a>
        </form>
    <?php else: ?>
        <h2>Access Denied</h2>
        <p>You are not allowed to delete this review.</p>
        <a href="view_other_people_recipe.php?recipeID=<?= $recipeID ?>" class="btn cancel">Back</a>
    <?php endif; ?>
</div>
</body>
</html>
