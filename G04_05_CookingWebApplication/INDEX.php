<?php 
include('connect.php');
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cooking Tutorial Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .hero-section {
            background: url('https://images.unsplash.com/photo-1504674900247-0877df9cc836') no-repeat center center;
            background-size: cover;
            height: 60vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
            position: relative;
        }
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
        }
        .hero-content {
            position: relative;
            z-index: 1;
        }
        .team-member {
            transition: transform 0.3s;
            cursor: pointer;
        }
        .team-member:hover {
            transform: scale(1.05);
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
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
                        <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                        <li class="nav-item"><a class="nav-link" href="signup.php">Sign Up</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content">
            <h1 class="display-4">Discover & Share Cooking Tutorials</h1>
            <p class="lead">Join our community of chefs and culinary students</p>
            <?php if(!isset($_SESSION['user_id'])): ?>
                <a href="signup.php" class="btn btn-primary btn-lg">Get Started</a>
            <?php endif; ?>
        </div>
    </section>

    <!-- Featured Recipes -->
    <section class="container my-5">
        <h2 class="text-center mb-4">Featured Recipes</h2>
        <div class="row">
            <?php
            $stmt = $conn->prepare("SELECT r.recipe_id, r.title, r.description, r.image_id, u.username, i.image_name 
           FROM recipe r 
           JOIN user u ON r.user_id = u.user_id 
           LEFT JOIN image i ON r.image_id = i.image_id 
           ORDER BY r.date_time DESC LIMIT 3");


            $stmt->execute();
            $recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($recipes as $recipe) {
                $imageId = $recipe['image_id'] ?? '';
                $imageName = $recipe['image_name'] ?? 'Recipe image';
                $title = $recipe['title'] ?? 'Untitled';
                $description = $recipe['description'] ?? 'No description available.';
                $username = $recipe['username'] ?? 'Unknown user';
                $recipeId = $recipe['recipe_id'] ?? 0;

                echo '<div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <img src="get_image.php?id=' . htmlspecialchars($imageId) . '" class="card-img-top" alt="' . htmlspecialchars($imageName) . '" style="height: 200px; object-fit: cover;" onerror="this.onerror=null;this.src=\'assets/default_food.jpg\'">
                            <div class="card-body">
                                <h5 class="card-title">' . htmlspecialchars($title) . '</h5>
                                <p class="card-text">' . substr(htmlspecialchars($description), 0, 100) . '...</p>
                                <p class="text-muted">By ' . htmlspecialchars($username) . '</p>
                            </div>
                            <div class="card-footer">
                                <a href="recipe_detail.php?id=' . htmlspecialchars($recipeId) . '" class="btn btn-primary">View Recipe</a>
                            </div>
                        </div>
                    </div>';
            }

            ?>
        </div>
        <div class="text-center mt-3">
            <a href="recipes.php" class="btn btn-outline-primary">View All Recipes</a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container text-center">
            <p>&copy; 2025 CookingApp. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>