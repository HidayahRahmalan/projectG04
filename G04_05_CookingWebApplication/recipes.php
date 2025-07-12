<?php 
session_start();
include('connect.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Recipes - CookingApp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .filter-section { background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 20px;}
        .recipe-card { transition: transform 0.3s; margin-bottom: 20px;}
        .recipe-card:hover { transform: translateY(-5px);}
    </style>
</head>
<body>
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
    <h1 class="mb-4">Recipes</h1>
    <div class="filter-section">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="cuisine" class="form-label">Cuisine</label>
                <select class="form-select" id="cuisine" name="cuisine">
                    <option value="">All Cuisines</option>
                    <?php
                    $cuisines = $conn->query("SELECT DISTINCT CUISINE FROM RECIPE ORDER BY CUISINE");
                    while ($cuisine = $cuisines->fetch(PDO::FETCH_ASSOC)) {
                        $selected = (isset($_GET['cuisine']) && $_GET['cuisine'] == $cuisine['CUISINE']) ? 'selected' : '';
                        echo '<option value="' . htmlspecialchars($cuisine['CUISINE']) . '" ' . $selected . '>' . htmlspecialchars($cuisine['CUISINE']) . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="dietary" class="form-label">Dietary Type</label>
                <select class="form-select" id="dietary" name="dietary">
                    <option value="">All Dietary Types</option>
                    <?php
                    $dietary_types = $conn->query("SELECT DISTINCT DIETARY_TYPE FROM RECIPE ORDER BY DIETARY_TYPE");
                    while ($dietary = $dietary_types->fetch(PDO::FETCH_ASSOC)) {
                        $selected = (isset($_GET['dietary']) && $_GET['dietary'] == $dietary['DIETARY_TYPE']) ? 'selected' : '';
                        echo '<option value="' . htmlspecialchars($dietary['DIETARY_TYPE']) . '" ' . $selected . '>' . htmlspecialchars($dietary['DIETARY_TYPE']) . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="difficulty" class="form-label">Difficulty</label>
                <select class="form-select" id="difficulty" name="difficulty">
                    <option value="">All Difficulties</option>
                    <option value="EASY" <?= ($_GET['difficulty'] ?? '') === 'EASY' ? 'selected' : '' ?>>Easy</option>
                    <option value="INTERMEDIATE" <?= ($_GET['difficulty'] ?? '') === 'INTERMEDIATE' ? 'selected' : '' ?>>Intermediate</option>
                    <option value="HARD" <?= ($_GET['difficulty'] ?? '') === 'HARD' ? 'selected' : '' ?>>Hard</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="meal_type" class="form-label">Meal Type</label>
                <select class="form-select" id="meal_type" name="meal_type">
                    <option value="">All Meal Types</option>
                    <?php
                    $types = ['BREAKFAST','LUNCH','DINNER','SNACK','DESSERT','APPETIZER','DRINK'];
                    foreach ($types as $type) {
                        $selected = (isset($_GET['meal_type']) && $_GET['meal_type'] == $type) ? 'selected' : '';
                        echo "<option value='$type' $selected>$type</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">Apply Filters</button>
                <a href="recipes.php" class="btn btn-secondary">Reset</a>
            </div>
        </form>
    </div>

    <div class="row">
        <?php
        $query = "SELECT r.*, u.USERNAME, i.IMAGE_NAME, i.IMAGE_ID FROM RECIPE r 
                  JOIN `USER` u ON r.USER_ID = u.USER_ID 
                  JOIN IMAGE i ON r.IMAGE_ID = i.IMAGE_ID";
        $where = [];
        $params = [];
        if (!empty($_GET['cuisine'])) { $where[] = "r.CUISINE = ?"; $params[] = $_GET['cuisine']; }
        if (!empty($_GET['dietary'])) { $where[] = "r.DIETARY_TYPE = ?"; $params[] = $_GET['dietary']; }
        if (!empty($_GET['difficulty'])) { $where[] = "r.DIFFICULTY = ?"; $params[] = $_GET['difficulty']; }
        if (!empty($_GET['meal_type'])) { $where[] = "r.MEAL_TYPE = ?"; $params[] = $_GET['meal_type']; }
        if ($where) { $query .= " WHERE " . implode(" AND ", $where); }
        $query .= " ORDER BY r.DATE_TIME DESC";

        $stmt = $conn->prepare($query);
        $stmt->execute($params);
        $recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$recipes) {
            echo '<div class="col-12"><div class="alert alert-info">No recipes found.</div></div>';
        } else {
            foreach ($recipes as $recipe) {
                $image_id = $recipe['IMAGE_ID'] ?? 0;
                $image_name = $recipe['IMAGE_NAME'] ?? 'No Image';
                $title = $recipe['TITLE'] ?? 'Untitled';
                $description = $recipe['DESCRIPTION'] ?? 'No description.';
                $cuisine = $recipe['CUISINE'] ?? 'N/A';
                $difficulty = $recipe['DIFFICULTY'] ?? 'N/A';
                $username = $recipe['USERNAME'] ?? 'Unknown';
                $recipe_id = $recipe['RECIPE_ID'] ?? 0;

                echo '<div class="col-md-4">
                        <div class="card recipe-card h-100">
                            <img src="get_image.php?id=' . urlencode($image_id) . '" class="card-img-top" alt="' . htmlspecialchars($image_name) . '" style="height:200px;object-fit:cover;" onerror="this.src=\'assets/default_food.jpg\'">
                            <div class="card-body">
                                <h5 class="card-title">' . htmlspecialchars($title) . '</h5>
                                <p class="card-text">' . substr(htmlspecialchars($description), 0, 100) . '...</p>
                                <div class="d-flex justify-content-between">
                                    <span class="badge bg-primary">' . htmlspecialchars($cuisine) . '</span>
                                    <span class="badge bg-secondary">' . htmlspecialchars($difficulty) . '</span>
                                </div>
                            </div>
                            <div class="card-footer bg-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">By ' . htmlspecialchars($username) . '</small>
                                    <a href="recipe_detail.php?id=' . $recipe_id . '" class="btn btn-sm btn-primary">View</a>
                                </div>
                            </div>
                        </div>
                    </div>';
            }
        }
        ?>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>