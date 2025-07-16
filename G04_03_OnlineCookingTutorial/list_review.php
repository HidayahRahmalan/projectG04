<?php
session_start();
include 'db_connect.php';


// Get recipeID from query string
$recipeID = isset($_GET['recipeID']) ? intval($_GET['recipeID']) : null;

$sql = "SELECT r.reviewID, r.rating, r.review_text, r.media_path, r.created_at, 
               u.username, u.profile_pic
        FROM review r
        JOIN user u ON r.userID = u.userID
        WHERE r.recipeID = ?
        ORDER BY r.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $recipeID);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>CookBook - Recipe Reviews</title>
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #fff8f0;
      margin: 0;
      padding: 20px;
    }
    .container {
      max-width: 800px;
      margin: auto;
      background: white;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 6px 15px rgba(0,0,0,0.1);
    }
    h1 {
      color: #d35400;
      text-align: center;
      margin-bottom: 30px;
    }
    .review {
      border-bottom: 1px solid #f0cda7;
      padding: 15px 0;
      display: flex;
      gap: 15px;
      position: relative;
    }
    .profile-pic {
      flex-shrink: 0;
      width: 60px;
      height: 60px;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid #e67e22;
    }
    .review-content {
      flex-grow: 1;
    }
    .username {
      font-weight: bold;
      color: #d35400;
      margin-bottom: 5px;
    }
    .rating {
      color: #f39c12;
      margin-bottom: 10px;
    }
    .review-text {
      margin-bottom: 10px;
      font-size: 16px;
      line-height: 1.4;
      color: #333;
    }
    .media img, .media video {
      max-width: 100%;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.15);
      margin-bottom: 10px;
    }
    .timestamp {
      font-size: 0.9em;
      color: #999;
    }
    .star {
      font-size: 18px;
      color: #f39c12;
    }
    .star.empty {
      color: #ddd;
    }
    .delete-btn {
      position: absolute;
      top: 15px;
      right: 0;
      background: transparent;
      border: none;
      font-weight: bold;
      font-size: 20px;
      color: #e74c3c;
      cursor: pointer;
      padding: 0 10px;
      line-height: 1;
      user-select: none;
      transition: color 0.3s ease;
    }
    .delete-btn:hover {
      color: #c0392b;
    }
    .btn-add-review {
      display: inline-block;
      background-color: #e67e22;
      color: white;
      padding: 12px 25px;
      text-decoration: none;
      font-size: 16px;
      border-radius: 8px;
      transition: background-color 0.3s ease;
      font-weight: bold;
    }
    .btn-add-review:hover {
      background-color: #cf6f13;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>Reviews for Recipe #<?= htmlspecialchars($recipeID) ?></h1>

    <?php if ($result->num_rows > 0): ?>
      <?php while($row = $result->fetch_assoc()): ?>
        <div class="review">
          <img class="profile-pic" src="<?= htmlspecialchars($row['profile_pic'] ?: 'profile.png') ?>" alt="User profile pic" />
          <div class="review-content">
            <div class="username"><?= htmlspecialchars($row['username']) ?></div>
            <div class="rating">
              <?php
                $filledStars = intval($row['rating']);
                $emptyStars = 5 - $filledStars;
                for ($i = 0; $i < $filledStars; $i++) echo '<span class="star">&#9733;</span>';
                for ($i = 0; $i < $emptyStars; $i++) echo '<span class="star empty">&#9733;</span>';
              ?>
            </div>
            <div class="review-text"><?= nl2br(htmlspecialchars($row['review_text'])) ?></div>
            <?php if ($row['media_path']): ?>
              <div class="media">
                <?php
                  $ext = strtolower(pathinfo($row['media_path'], PATHINFO_EXTENSION));
                  if (in_array($ext, ['mp4', 'webm', 'ogg'])) {
                    echo '<video controls src="' . htmlspecialchars($row['media_path']) . '"></video>';
                  } else {
                    echo '<img src="' . htmlspecialchars($row['media_path']) . '" alt="Review media" />';
                  }
                ?>
              </div>
            <?php endif; ?>
            <div class="timestamp">Posted on <?= date("F j, Y, g:i a", strtotime($row['created_at'])) ?></div>
          </div>
          <form method="POST" action="delete_review.php" onsubmit="return confirm('Are you sure you want to delete this review?');">
            <input type="hidden" name="reviewID" value="<?= $row['reviewID'] ?>">
            <button type="submit" class="delete-btn" title="Delete review">&times;</button>
          </form>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p>No reviews yet for this recipe. Be the first to 
        <a href="add_review.php?recipeID=<?= urlencode($recipeID) ?>">write a review!</a>
      </p>
    <?php endif; ?>

    <div style="text-align: center; margin-top: 30px;">
      <a href="add_review.php?recipeID=<?= urlencode($recipeID) ?>" class="btn-add-review">➕ Add Your Review</a>
    </div>
  </div>
</body>
</html>
