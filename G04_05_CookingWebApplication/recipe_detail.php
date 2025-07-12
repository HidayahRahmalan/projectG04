<?php 
session_start();
include('connect.php');

// Sanitize and validate recipe_id
$recipe_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($recipe_id <= 0) {
    header("Location: recipes.php");
    exit();
}

// Fetch the main recipe with aliases for consistency
$stmt = $conn->prepare("SELECT 
    r.recipe_id, r.title, r.description, r.cuisine, r.dietary_type,
    r.date_time, r.difficulty, r.preparation_time, r.meal_type,
    r.user_id, r.doc_id, r.image_id,
    u.username, 
    i.image_name,
    d.doc_name
    FROM recipe r 
    JOIN user u ON r.user_id = u.user_id 
    LEFT JOIN image i ON r.image_id = i.image_id 
    LEFT JOIN document d ON r.doc_id = d.doc_id 
    WHERE r.recipe_id = ?");
$stmt->execute([$recipe_id]);
$recipe = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$recipe) {
    header("Location: recipes.php");
    exit();
}

// Tags
$tags_stmt = $conn->prepare("SELECT tag_text FROM tag WHERE recipe_id = ?");
$tags_stmt->execute([$recipe_id]);
$tags = $tags_stmt->fetchAll(PDO::FETCH_COLUMN);

// Comments with aliases
$comment_stmt = $conn->prepare("SELECT 
    c.comment_content AS comment_content,
    c.comment_datetime AS comment_datetime,
    u.username AS username
    FROM comment c 
    JOIN user u ON c.user_id = u.user_id 
    WHERE c.recipe_id = ? 
    ORDER BY c.comment_datetime DESC");
$comment_stmt->execute([$recipe_id]);
$comments = $comment_stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle new comment
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("INSERT INTO comment (comment_content, recipe_id, user_id) VALUES (?, ?, ?)");
    $stmt->execute([
        htmlspecialchars($_POST['comment_content']),
        $recipe_id,
        $_SESSION['user_id']
    ]);
    header("Location: recipe_detail.php?id=" . $recipe_id);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($recipe['title']); ?> - CookingApp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .recipe-image { max-height: 500px; object-fit: cover; }
        #audioControls { display: none; background: #f8f9fa; padding: 15px; border-radius: 5px; }
        .speech-controls { display: flex; gap: 10px; margin-top: 10px; }
        .highlight { background-color: yellow; transition: background-color 0.3s; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="INDEX.php">CookingApp</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="recipes.php">Recipes</a></li>
                <li class="nav-item"><a class="nav-link" href="team.php">Our Team</a></li>
            </ul>
            <ul class="navbar-nav">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item"><a class="nav-link" href="add_recipe.php">Add Recipe</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="signup.php">Sign Up</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <div class="card mb-4">
        <img src="get_image.php?id=<?php echo urlencode($recipe['image_id']); ?>" 
             class="card-img-top recipe-image" 
             alt="<?php echo htmlspecialchars($recipe['image_name'] ?? 'Recipe Image'); ?>" 
             onerror="this.onerror=null;this.src='assets/default_food.jpg';">
        <div class="card-body">
            <h1><?php echo htmlspecialchars($recipe['title']); ?></h1>
            <p class="text-muted">By <?php echo htmlspecialchars($recipe['username']); ?> on <?php echo date('F j, Y', strtotime($recipe['date_time'])); ?></p>

            <div class="mb-3">
                <?php foreach ($tags as $tag): ?>
                    <span class="badge bg-secondary"><?php echo htmlspecialchars($tag); ?></span>
                <?php endforeach; ?>
            </div>

            <div class="row mb-3">
                <div class="col-md-3"><div class="card"><div class="card-body"><h6>Cuisine</h6><p><?php echo htmlspecialchars($recipe['cuisine']); ?></p></div></div></div>
                <div class="col-md-3"><div class="card"><div class="card-body"><h6>Dietary Type</h6><p><?php echo htmlspecialchars($recipe['dietary_type']); ?></p></div></div></div>
                <div class="col-md-3"><div class="card"><div class="card-body"><h6>Difficulty</h6><p><?php echo htmlspecialchars($recipe['difficulty']); ?></p></div></div></div>
                <div class="col-md-3"><div class="card"><div class="card-body"><h6>Preparation Time</h6><p><?php echo htmlspecialchars($recipe['preparation_time']); ?></p></div></div></div>
            </div>

            <h4>Description</h4>
            <p><?php echo nl2br(htmlspecialchars($recipe['description'])); ?></p>

            <h4 class="mt-4">Tutorial</h4>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <p class="mb-0"><?php echo htmlspecialchars($recipe['doc_name']); ?></p>
                <div>
                    <a href="get_document.php?id=<?php echo $recipe['doc_id']; ?>" class="btn btn-primary me-2" download>Download</a>
                    <button id="readBtn" class="btn btn-success" onclick="toggleReading('<?php echo $recipe['doc_id']; ?>')">
                        <span id="readText">Read</span>
                        <span id="readSpinner" class="spinner-border spinner-border-sm d-none"></span>
                    </button>
                </div>
            </div>

            <div id="audioControls">
                <div class="speech-controls">
                    <button onclick="speechSynthesis.pause()" class="btn btn-sm btn-outline-secondary">Pause</button>
                    <button onclick="speechSynthesis.resume()" class="btn btn-sm btn-outline-secondary">Resume</button>
                    <button onclick="stopReading()" class="btn btn-sm btn-danger">Stop</button>
                </div>
                <div class="mt-2">
                    <small>Speed:</small>
                    <button onclick="changeRate(0.8)" class="btn btn-sm btn-outline-secondary">Slower</button>
                    <button onclick="changeRate(1.0)" class="btn btn-sm btn-outline-secondary">Normal</button>
                    <button onclick="changeRate(1.2)" class="btn btn-sm btn-outline-secondary">Faster</button>
                </div>
                <div id="textPreview" class="mt-3 p-2 border rounded"></div>
            </div>
        </div>
    </div>

    <!-- Comments -->
    <div class="card">
        <div class="card-header"><h3>Comments</h3></div>
        <div class="card-body">
            <?php if (isset($_SESSION['user_id'])): ?>
                <form method="POST" class="mb-4">
                    <div class="mb-3">
                        <label for="comment_content" class="form-label">Add Comment</label>
                        <textarea class="form-control" id="comment_content" name="comment_content" rows="3" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Post Comment</button>
                </form>
            <?php else: ?>
                <div class="alert alert-info">Please <a href="login.php">login</a> to leave a comment.</div>
            <?php endif; ?>

            <?php if (!empty($comments)): ?>
                <div class="list-group">
                    <?php foreach ($comments as $comment): ?>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <h6><?php echo htmlspecialchars($comment['username']); ?></h6>
                                <small class="text-muted"><?php echo date('F j, Y g:i a', strtotime($comment['comment_datetime'])); ?></small>
                            </div>
                            <p><?php echo nl2br(htmlspecialchars($comment['comment_content'])); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info">No comments yet. Be the first to comment!</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
let currentUtterance = null;
let rate = 1.0;

async function toggleReading(docId) {
    const btn = document.getElementById('readBtn');
    const text = document.getElementById('readText');
    const spinner = document.getElementById('readSpinner');

    if (currentUtterance) {
        stopReading();
        return;
    }

    text.textContent = 'Stop';
    btn.classList.remove('btn-success');
    btn.classList.add('btn-danger');
    spinner.classList.remove('d-none');

    try {
        const res = await fetch(`get_document.php?doc_id=${docId}&action=extract`);
        const data = await res.json();

        if (data.success) {
            currentUtterance = new SpeechSynthesisUtterance(data.text);
            currentUtterance.rate = rate;
            speechSynthesis.speak(currentUtterance);

            document.getElementById('textPreview').textContent = data.text.substring(0, 200) + '...';
            document.getElementById('audioControls').style.display = 'block';

            currentUtterance.onend = () => stopReading();
        } else {
            alert(data.error || 'Failed to read tutorial.');
            stopReading();
        }
    } catch (err) {
        console.error(err);
        alert('Error processing document.');
        stopReading();
    } finally {
        spinner.classList.add('d-none');
    }
}

function stopReading() {
    if (currentUtterance) speechSynthesis.cancel();
    currentUtterance = null;
    const btn = document.getElementById('readBtn');
    btn.classList.remove('btn-danger');
    btn.classList.add('btn-success');
    document.getElementById('readText').textContent = 'Read';
    document.getElementById('audioControls').style.display = 'none';
}

function changeRate(newRate) {
    rate = newRate;
    if (currentUtterance) {
        speechSynthesis.cancel();
        currentUtterance.rate = rate;
        speechSynthesis.speak(currentUtterance);
    }
}
</script>
</body>
</html>
