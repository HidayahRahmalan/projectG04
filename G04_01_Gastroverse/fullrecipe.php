<?php
include('header.php');
include('connect.php'); 

$host = '127.0.0.1:3306';
$dbname = 'gastroverse1';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

$recipeId = isset($_GET['recipe_id']) ? intval($_GET['recipe_id']) : 0;

// Get user ID from session (use lowercase, as in your system)
$user_id = isset($_SESSION['UserID']) ? (int)$_SESSION['UserID'] : null;

// Handle new comment form submission BEFORE any output or includes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_content']) && $user_id) {
    $content = trim($_POST['comment_content']);
    if ($content !== '') {
        $stmt = $pdo->prepare("INSERT INTO comment (User_ID, Recipe_ID, Comment_Date, Comment_Content) VALUES (?, ?, CURDATE(), ?)");
        $stmt->execute([$user_id, $recipeId, $content]);
        // Redirect to prevent form resubmission and before any output
        header("Location: fullrecipe.php?recipe_id=" . $recipeId);
        exit;
    }
}

// Fetch recipe details
$stmt = $pdo->prepare("SELECT r.*, u.User_Name FROM recipe r
    LEFT JOIN users u ON r.User_ID = u.User_ID
    WHERE r.Recipe_ID = ?");
$stmt->execute([$recipeId]);
$recipe = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$recipe) {
    echo "Recipe not found.";
    exit;
}

// Fetch recipe images
$stmt = $pdo->prepare("SELECT Image_Path FROM image WHERE Recipe_ID = ?");
$stmt->execute([$recipeId]);
$images = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Fetch steps
$stmt = $pdo->prepare("SELECT * FROM step WHERE Recipe_ID = ? ORDER BY Step_Number ASC");
$stmt->execute([$recipeId]);
$steps = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch comments
$stmt = $pdo->prepare("SELECT c.*, u.User_Name FROM comment c
    LEFT JOIN users u ON c.User_ID = u.User_ID
    WHERE c.Recipe_ID = ?
    ORDER BY c.Comment_Date DESC");
$stmt->execute([$recipeId]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($recipe['Recipe_Title']); ?> - Recipe Details</title>
    <link rel="stylesheet" href="../G04_01_Gastroverse/toastr.min.css">
    <style>
        .recipe-details-container {
            max-width: 760px;
            margin: 2rem auto;
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            padding: 2rem;
        }
        .recipe-detail-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .recipe-detail-meta {
            margin-bottom: 1.5rem;
        }
        .recipe-detail-image {
            width: 100%;
            max-height: 340px;
            object-fit: cover;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }
        .recipe-description {
            margin-bottom: 1.5rem;
        }
        .recipe-steps-section {
            margin: 2rem 0;
        }
        .step-block {
            background: #fafbfc;
            border-radius: 0.5rem;
            margin-bottom: 1.25rem;
            padding: 1rem 1.25rem;
            border-left: 4px solid #ef4444;
            box-shadow: 0 1px 4px rgba(0,0,0,0.05);
        }
        .step-title {
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: #ef4444;
        }
        .step-media img, .step-media video, .step-media audio {
            display: block;
            margin: 0.5rem 0;
            max-width: 100%;
            border-radius: 0.25rem;
        }
        .comments-section {
            margin-top: 2rem;
        }
        .comment-card {
            background: #f9fafb;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        .comment-user {
            font-weight: bold;
            color: #ef4444;
        }
        .comment-date {
            color: #888;
            font-size: 0.9rem;
            margin-left: 0.5rem;
        }
        .comment-content {
            margin-top: 0.5rem;
        }
        .comment-form textarea {
            width: 100%;
            min-height: 80px;
            border-radius: 0.5rem;
            border: 1px solid #ccc;
            padding: 0.75rem;
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }
        .comment-form button {
            background: #ef4444;
            color: #fff;
            padding: 0.5rem 1.25rem;
            border: none;
            border-radius: 0.375rem;
            font-size: 1rem;
            cursor: pointer;
        }
        .comment-form button:hover {
            background: #dc2626;
        }
    </style>
</head>
<body>
<div class="recipe-details-container">
    <?php if ($images): ?>
        <img src="<?php echo htmlspecialchars($images[0]); ?>" class="recipe-detail-image" alt="<?php echo htmlspecialchars($recipe['Recipe_Title']); ?>">
    <?php endif; ?>
    <h1 class="recipe-detail-title"><?php echo htmlspecialchars($recipe['Recipe_Title']); ?></h1>
    <div class="recipe-detail-meta">
        <span><strong>Cuisine:</strong> <?php echo htmlspecialchars($recipe['Recipe_CuisineType']); ?></span> | 
        <span><strong>Dietary:</strong> <?php echo htmlspecialchars($recipe['Recipe_DietaryType']); ?></span><br>
        <span><strong>Uploaded by:</strong> <?php echo htmlspecialchars($recipe['User_Name']); ?></span>
    </div>
    <div class="recipe-description">
        <?php echo nl2br(htmlspecialchars($recipe['Recipe_Description'])); ?>
    </div>

    <!-- Steps Section -->
    <div class="recipe-steps-section">
        <h2>Steps</h2>
        <?php if ($steps): ?>
            <?php foreach ($steps as $step): ?>
                <div class="step-block">
                    <div class="step-title">Step <?php echo $step['Step_Number']; ?></div>
                    <div class="step-instruction">
                        <?php echo nl2br(htmlspecialchars($step['Step_Instruction'])); ?>
                    </div>
                    <div class="step-media">
                        <?php if (!empty($step['Step_ImagePath'])): ?>
                            <img src="<?php echo htmlspecialchars($step['Step_ImagePath']); ?>" alt="Step Image">
                        <?php endif; ?>
                        <?php if (!empty($step['Step_VideoPath'])): ?>
                            <video controls>
                                <source src="<?php echo htmlspecialchars($step['Step_VideoPath']); ?>">
                                Your browser does not support the video tag.
                            </video>
                        <?php endif; ?>
                        <?php if (!empty($step['Step_AudioPath'])): ?>
                            <audio controls>
                                <source src="<?php echo htmlspecialchars($step['Step_AudioPath']); ?>">
                                Your browser does not support the audio element.
                            </audio>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No steps found for this recipe.</p>
        <?php endif; ?>
    </div>

    <!-- Comments Section -->
    <div class="comments-section">
        <h2>Comments</h2>
        <?php if ($user_id): ?>
        <form class="comment-form" method="post">
            <textarea name="comment_content" placeholder="Write your comment..." required></textarea>
            <br>
            <button type="submit">Post Comment</button>
        </form>
        <?php else: ?>
        <p><a href="login.php">Login</a> to comment.</p>
        <?php endif; ?>

        <?php if ($comments): ?>
            <?php foreach ($comments as $comment): ?>
    <div class="comment-card">
        <div>
            <span class="comment-user"><?php echo htmlspecialchars($comment['User_Name']); ?></span>
            <span class="comment-date">
                <?php 
                    $date = DateTime::createFromFormat('Y-m-d', $comment['Comment_Date']);
                    echo $date ? $date->format('d-m-Y') : htmlspecialchars($comment['Comment_Date']);
                ?>
            </span>
        </div>
        <div class="comment-content">
            <?php echo nl2br(htmlspecialchars($comment['Comment_Content'])); ?>
        </div>
    </div>
<?php endforeach; ?>
        <?php else: ?>
            <p>No comments yet.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>