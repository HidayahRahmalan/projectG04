<?php
session_start();
require_once 'db_connect.php';

$userID = $_SESSION['userID'];

if (!isset($_GET['recipeID'])) {
    echo "Recipe ID not provided.";
    exit();
}

$recipeID = intval($_GET['recipeID']);

// Fetch recipe data
$stmt = $conn->prepare("SELECT * FROM RECIPE WHERE recipeID = ?");
$stmt->bind_param("i", $recipeID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Recipe not found or unauthorized.";
    exit();
}

$recipe = $result->fetch_assoc();

// Fetch media
$media_stmt = $conn->prepare("SELECT media_type, file_path FROM media WHERE recipeID = ?");
$media_stmt->bind_param("i", $recipeID);
$media_stmt->execute();
$media_result = $media_stmt->get_result();

$media_files = [];
while ($row = $media_result->fetch_assoc()) {
    $media_files[] = $row;
}
$media_stmt->close();
$stmt->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>View Recipe</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .view-wrapper {
            background: #fefefe;
            padding: 30px;
            border: 2px solid #d35400;
            border-radius: 10px;
            max-width: 1200px;
            margin: 30px auto;
        }

        .view-wrapper h2 {
            margin-top: 0;
        }

        .recipe-section {
            margin-bottom: 20px;
        }

        .recipe-label {
            font-weight: bold;
            color: #d35400;
        }

        .recipe-text {
            margin-left: 10px;
        }

        .media-gallery img,
        .media-gallery video {
            max-width: 300px;
            margin: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
        }

        /* Reviews container styling */
        #reviewsContainer {
            margin-top: 20px;
            border: 1px solid #d35400;
            padding: 15px;
            border-radius: 8px;
            max-height: 400px;
            overflow-y: auto;
            background-color: #fff8f0;
            display: none; /* Hidden by default */
        }

        #showReviewsBtn {
            margin-top: 20px;
            padding: 8px 16px;
            background-color: #d35400;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<div class="view-wrapper">
    <h2><?= htmlspecialchars($recipe['title']) ?></h2>

    <div class="recipe-section">
        <div class="recipe-label">Ingredients:</div>
        <div class="recipe-text"><?= nl2br(htmlspecialchars($recipe['ingredient'])) ?></div>
    </div>

    <div class="recipe-section">
        <div class="recipe-label">Dietary Type:</div>
        <div class="recipe-text"><?= htmlspecialchars($recipe['dietary_type']) ?></div>
    </div>

    <div class="recipe-section">
        <div class="recipe-label">Cuisine Type:</div>
        <div class="recipe-text"><?= htmlspecialchars($recipe['cuisine_type']) ?></div>
    </div>

    <div class="recipe-section">
        <div class="recipe-label">Instructions:</div>
        <div class="recipe-text" style="white-space: pre-line;"><?= htmlspecialchars($recipe['step']) ?></div>
    </div>

    <?php if (!empty($media_files)): ?>
        <div class="recipe-section media-gallery">
            <div class="recipe-label">Media:</div>
            <?php foreach ($media_files as $media): ?>
                <?php if ($media['media_type'] === 'image'): ?>
                    <img src="<?= htmlspecialchars($media['file_path']) ?>" alt="Recipe Image">
                <?php elseif ($media['media_type'] === 'video'): ?>
                    <video controls>
                        <source src="<?= htmlspecialchars($media['file_path']) ?>" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Show Reviews Button -->
    <button id="showReviewsBtn">Show Reviews</button>

    <!-- Container where reviews will be loaded -->
    <div id="reviewsContainer"></div>

    <a href="other_people_recipe_list.php" class="btn btn-secondary" style="margin-top: 20px; display: inline-block;">Back</a>
</div>

<script>
document.getElementById('showReviewsBtn').addEventListener('click', function() {
    const reviewsContainer = document.getElementById('reviewsContainer');
    const btn = this;

    if (reviewsContainer.style.display === 'none' || reviewsContainer.style.display === '') {
        // Show reviews container and load reviews
        reviewsContainer.style.display = 'block';
        reviewsContainer.innerHTML = 'Loading reviews...';

        fetch('list_review.php?recipeID=<?= $recipeID ?>')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not OK');
                }
                return response.text();
            })
            .then(html => {
                reviewsContainer.innerHTML = html;
                btn.textContent = 'Hide Reviews';
            })
            .catch(error => {
                reviewsContainer.innerHTML = '<p>Failed to load reviews. Please try again later.</p>';
                console.error('Fetch error:', error);
            });
    } else {
        // Hide reviews container
        reviewsContainer.style.display = 'none';
        btn.textContent = 'Show Reviews';
    }
});
</script>

<?php include 'footer.php'; ?>
</body>
</html>
