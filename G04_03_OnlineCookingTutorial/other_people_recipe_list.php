<?php
session_start();  // ✅ Required to access $_SESSION
require_once 'db_connect.php';

// Get filter values from GET
$cuisine = $_GET['cuisine'] ?? '';
$dietary = $_GET['dietary_type'] ?? '';

// Build query with filters
$sql = "SELECT * FROM RECIPE WHERE 1";
$params = [];
$types = "";

if ($cuisine !== '') {
    $sql .= " AND cuisine_type = ?";
    $params[] = $cuisine;
    $types .= "s";
}

if ($dietary !== '') {
    $sql .= " AND dietary_type = ?";
    $params[] = $dietary;
    $types .= "s";
}

$recipes_stmt = $conn->prepare($sql);
if (!$recipes_stmt) {
    die("Prepare failed: " . $conn->error . " | SQL: " . $sql);
}

if (!empty($params)) {
    if (!$recipes_stmt->bind_param($types, ...$params)) {
        die("Bind param failed: " . $recipes_stmt->error);
    }
}


$recipes_stmt->execute();

$recipes_result = $recipes_stmt->get_result();

$recipes = [];
while ($recipe = $recipes_result->fetch_assoc()) {
    $media_stmt = $conn->prepare("SELECT media_type, file_path FROM media WHERE recipeID = ?");
    $media_stmt->bind_param("i", $recipe['recipeID']);
    $media_stmt->execute();
    $media_result = $media_stmt->get_result();
    $media = [];
    while ($m = $media_result->fetch_assoc()) {
        $media[] = $m;
    }
    $media_stmt->close();

    $recipe['media'] = $media;
    $recipes[] = $recipe;
}
$recipes_stmt->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>All Recipes</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .card-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
            padding: 30px;
        }

        .recipe-card {
            background: #fffaf4;
            border: 1px solid #e67e22;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
            width: 300px;
            padding: 20px;
            box-sizing: border-box;
        }

        .recipe-card h3 {
            margin-top: 0;
            color: #d35400;
        }

        .recipe-meta {
            font-size: 0.9em;
            margin-bottom: 10px;
            color: #555;
        }

        .recipe-card p {
            margin: 0 0 10px;
        }

        .media-preview img,
        .media-preview video {
            max-width: 100%;
            border-radius: 8px;
            margin-top: 10px;
        }

        .btn-view {
            display: inline-block;
            margin-top: 15px;
            padding: 8px 14px;
            background-color: #d35400;
            color: white;
            text-decoration: none;
            border-radius: 6px;
        }

        .btn-view:hover {
            background-color: #e67e22;
        }
    
        form.filter-form {
            text-align: center;
            margin: 20px auto; /* center the form horizontally */
            width: 50%;        /* make the form 80% of the page width */
            max-width: 1000px; /* optional: limit max width for large screens */
        }

        form.filter-form select {
            padding: 5px;
            margin-right: 10px;
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<h2 style="text-align: center; margin-top: 20px;">Explore Public Recipes</h2>

<!-- Filter Form (same as recipe_list.php style) -->
<div style="display: flex; justify-content: center;">
    <form method="GET" action="other_people_recipe_list.php" class="filter-form" style="margin-bottom: 20px;">
        <div style="display: flex; align-items: center; justify-content: flex-start;">
            <label for="cuisine" style="margin-right: 5px;"><strong>Cuisine:</strong></label>
            <select name="cuisine" id="cuisine" style="padding: 5px; margin-right: 20px;">
                <option value="">-- All --</option>
                <option value="Italian" <?= ($cuisine == 'Italian') ? 'selected' : '' ?>>Italian</option>
                <option value="Mexican" <?= ($cuisine == 'Mexican') ? 'selected' : '' ?>>Mexican</option>
                <option value="Indian" <?= ($cuisine == 'Indian') ? 'selected' : '' ?>>Indian</option>
                <option value="Chinese" <?= ($cuisine == 'Chinese') ? 'selected' : '' ?>>Chinese</option>
            </select>

            <label for="dietary_type" style="margin-right: 5px;"><strong>Dietary Type:</strong></label>
            <select name="dietary_type" id="dietary_type" style="padding: 5px; margin-right: 20px;">
                <option value="">-- All --</option>
                <option value="Vegan" <?= ($dietary == 'Vegan') ? 'selected' : '' ?>>Vegan</option>
                <option value="Gluten-Free" <?= ($dietary == 'Gluten-Free') ? 'selected' : '' ?>>Gluten-Free</option>
                <option value="Vegetarian" <?= ($dietary == 'Vegetarian') ? 'selected' : '' ?>>Vegetarian</option>
                <option value="Keto" <?= ($dietary == 'Keto') ? 'selected' : '' ?>>Keto</option>
            </select>

            <button type="submit" style="margin-right: 10px;">Filter</button>
            <a href="other_people_recipe_list.php" style="text-decoration: none;">Clear</a>
        </div>
    </form>
</div>





<!-- Recipe Cards -->
<div class="card-container">
    <?php if (empty($recipes)): ?>
        <p style="text-align:center;">No recipes found.</p>
    <?php else: ?>
        <?php foreach ($recipes as $recipe): ?>
            <div class="recipe-card">
                <h3><?= htmlspecialchars($recipe['title']) ?></h3>
                <div class="recipe-meta">
                    <strong>Diet:</strong> <?= htmlspecialchars($recipe['dietary_type']) ?: 'N/A' ?><br>
                    <strong>Cuisine:</strong> <?= htmlspecialchars($recipe['cuisine_type']) ?: 'N/A' ?>
                </div>
                <p><strong>Description:</strong><br><?= nl2br(htmlspecialchars($recipe['ingredient'])) ?></p>
                <p><strong>Instructions:</strong><br><?= nl2br(htmlspecialchars(mb_strimwidth($recipe['step'], 0, 150, '...'))) ?></p>

                <?php if (!empty($recipe['media'])): ?>
                    <div class="media-preview">
                        <?php foreach ($recipe['media'] as $m): ?>
                            <?php if ($m['media_type'] === 'image'): ?>
                                <img src="<?= htmlspecialchars($m['file_path']) ?>" alt="Recipe image">
                                <?php break; ?>
                            <?php elseif ($m['media_type'] === 'video'): ?>
                                <video controls>
                                    <source src="<?= htmlspecialchars($m['file_path']) ?>" type="video/mp4">
                                </video>
                                <?php break; ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <a class="btn-view" href="view_other_people_recipe.php?recipeID=<?= $recipe['recipeID'] ?>">View Full</a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
