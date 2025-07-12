<?php 
include('connect.php');
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user data
$stmt = $conn->prepare("SELECT * FROM USER WHERE USER_ID = :user_id");
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Get user's recipes
$stmt = $conn->prepare("SELECT r.*, i.IMAGE_NAME FROM RECIPE r 
                       JOIN IMAGE i ON r.IMAGE_ID = i.IMAGE_ID 
                       WHERE r.USER_ID = :user_id 
                       ORDER BY r.DATE_TIME DESC");
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - CookingApp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .profile-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 3rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 20px 20px;
        }
        .profile-pic {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border: 5px solid white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .stats-card {
            border-radius: 10px;
            transition: transform 0.3s;
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        .stats-card:hover {
            transform: translateY(-5px);
        }
        .recipe-card {
            transition: transform 0.3s;
            height: 100%;
        }
        .recipe-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="INDEX.php">CookingApp</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="recipes.php">Recipes</a></li>
                    <li class="nav-item"><a class="nav-link" href="team.php">Our Team</a></li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link active" href="profile.php">Profile</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Profile Header -->
    <div class="profile-header">
        <div class="container text-center">
            <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['USERNAME']) ?>&background=random&size=150" 
                 alt="Profile Picture" class="profile-pic rounded-circle mb-3">
            <h1><?= htmlspecialchars($user['USERNAME']) ?></h1>
        </div>
    </div>

    <div class="container mb-5">
        <div class="row mb-4">
            <div class="col-md-12 mb-3">
                <div class="stats-card card text-center p-4">
                    <h3><i class="fas fa-utensils text-primary"></i> <?= count($recipes) ?></h3>
                    <p class="text-muted">Recipes Shared</p>
                </div>
            </div>
        </div>

        <div class="row">
            <?php if (count($recipes) > 0): ?>
                <?php foreach ($recipes as $recipe): ?>
                    <div class="col-md-4 mb-4">
                        <div class="recipe-card card">
                            <img src="get_image.php?id=<?= $recipe['IMAGE_ID'] ?>" class="card-img-top" alt="<?= htmlspecialchars($recipe['IMAGE_NAME']) ?>" style="height: 200px; object-fit: cover;">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($recipe['TITLE']) ?></h5>
                                <p class="card-text"><?= substr(htmlspecialchars($recipe['DESCRIPTION']), 0, 100) ?>...</p>
                            </div>
                            <div class="card-footer bg-white">
                                <a href="recipe_detail.php?id=<?= $recipe['RECIPE_ID'] ?>" class="btn btn-primary">View Recipe</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <h4>You haven't shared any recipes yet</h4>
                    <a href="add_recipe.php" class="btn btn-primary mt-3">Share Your First Recipe</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container text-center">
            <p>&copy; 2025 CookingApp. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
