<?php
session_start();
if (!isset($_SESSION['UserID'])) {
    header("Location: LOGIN.html");
    exit();
}

include('dbConnection.php');

// Fetch recipes only for the logged-in user
$sql = "SELECT r.*, m.URL 
        FROM recipes r 
        LEFT JOIN media m ON r.RecipeID = m.RecipeID 
        WHERE r.UserID = :userid
        GROUP BY r.RecipeID";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':userid', $_SESSION['UserID']);

$stmt->execute();

// Get distinct cuisines from user's recipes
$cuisineSQL = "SELECT DISTINCT Cuisine FROM recipes WHERE UserID = :userid AND Cuisine IS NOT NULL AND Cuisine <> '' ORDER BY Cuisine";
$cuisineStmt = $conn->prepare($cuisineSQL);
$cuisineStmt->bindParam(':userid', $_SESSION['UserID']);
$cuisineStmt->execute();
$cuisines = $cuisineStmt->fetchAll(PDO::FETCH_COLUMN);

// Fetch distinct dietary tags from user's recipes
$tags = [];
$tagQuery = "SELECT DISTINCT DietaryTags FROM recipes WHERE UserID = :userid AND DietaryTags IS NOT NULL AND DietaryTags <> ''";
$tagStmt = $conn->prepare($tagQuery);
$tagStmt->bindParam(':userid', $_SESSION['UserID']);
$tagStmt->execute();
$tagResults = $tagStmt->fetchAll(PDO::FETCH_COLUMN);

foreach ($tagResults as $tagLine) {
    foreach (explode(',', $tagLine) as $tag) {
        $clean = trim($tag);
        if ($clean && !in_array($clean, $tags)) {
            $tags[] = $clean;
        }
    }
}
sort($tags);

$recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Recipes | Recipe Explorer</title>
    <style>
        :root {
            --primary-color: #FF6B6B;
            --secondary-color: #4ECDC4;
            --dark-color: #292F36;
            --light-color: #F7FFF7;
            --accent-color: #FFE66D;
            --card-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f9f9f7;
            color: var(--dark-color);
            line-height: 1.6;
            padding: 0;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background-color: white;
            padding: 20px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .logo {
            display: flex;
            align-items: center;
            color: var(--primary-color);
            font-size: 24px;
            font-weight: 700;
            text-decoration: none;
        }
        
        .logo-icon {
            margin-right: 12px;
            font-size: 32px;
        }
        
        .search-container {
            display: flex;
            max-width: 800px;
            margin: 0 auto 30px;
        }
        
        .search-input {
            flex: 1;
            padding: 12px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 30px 0 0 30px;
            font-size: 16px;
            outline: none;
            transition: all 0.3s;
        }
        
        .search-input:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(78, 205, 196, 0.2);
        }
        
        .search-btn {
            padding: 0 25px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 0 30px 30px 0;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .search-btn:hover {
            background-color: #ff5252;
        }
        
        .filter-bar {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .filter-btn {
            padding: 8px 16px;
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 20px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .filter-btn:hover, .filter-btn.active {
            background-color: var(--secondary-color);
            color: white;
            border-color: var(--secondary-color);
        }
        
        .recipe-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .recipe-card {
            background-color: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--card-shadow);
            transition: transform 0.3s, box-shadow 0.3s;
            position: relative;
        }
        
        .recipe-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .recipe-actions {
            position: absolute;
            top: 10px;
            right: 10px;
            display: flex;
            gap: 5px;
        }
        
        .recipe-action-btn {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.9);
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .recipe-action-btn:hover {
            background-color: var(--primary-color);
            color: white;
        }
        
        .recipe-image {
            height: 180px;
            background-color: #eee;
            background-size: cover;
            background-position: center;
        }
        
        .recipe-content {
            padding: 20px;
        }
        
        .recipe-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--dark-color);
        }
        
        .recipe-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 14px;
            color: #666;
        }
        
        .recipe-cuisine {
            font-weight: 500;
            color: var(--secondary-color);
        }
        
        .recipe-date {
            color: #999;
        }
        
        .recipe-desc {
            font-size: 14px;
            color: #555;
            margin-bottom: 15px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            line-clamp: 3;
            box-orient: vertical;
            overflow: hidden;
        }
        
        .recipe-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        
        .tag {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .gluten-free {
            background-color: #d4edda;
            color: #155724;
        }
        
        .spicy {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .vegetarian {
            background-color: #e2e3e5;
            color: #383d41;
        }
        
        .soup {
            background-color: #cce5ff;
            color: #004085;
        }
        
        .view-btn {
            display: inline-block;
            margin-top: 15px;
            padding: 8px 16px;
            background-color: var(--primary-color);
            color: white;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .view-btn:hover {
            background-color: #ff5252;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 40px;
        }
        
        .page-btn {
            padding: 8px 16px;
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .page-btn:hover {
            background-color: #f0f0f0;
        }
        
        .page-btn.active {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        .status-bar {
            text-align: center;
            color: #666;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #666;
        }
        
        .empty-state-icon {
            font-size: 60px;
            margin-bottom: 20px;
            color: #ddd;
        }
        
        .add-recipe-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 24px;
            background-color: var(--primary-color);
            color: white;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s;
        }
        
        .add-recipe-btn:hover {
            background-color: #ff5252;
        }
        
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 15px;
            }
            
            .recipe-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <a href="INDEXX.php" class="logo">
                <span class="logo-icon">👨‍🍳</span>
                <span>Recipe Explorer</span>
            </a>
            <nav>
    <a href="INDEXX.php" style="margin-right: 15px; color: var(--dark-color); text-decoration: none;">Browse</a>
    <a href="MYRECIPE.php" style="margin-right: 15px; color: var(--dark-color); text-decoration: none;">My Recipes</a>
    <a href="PROFILE.php" style="color: var(--dark-color); text-decoration: none;">Profile</a>
</nav>

             <?php if (isset($_SESSION['UserName'])): ?>
    <div style="display: flex; align-items: center; gap: 15px; margin-left: 15px;">
        <span style="font-weight: bold; color: var(--dark-color);">
            👋 Hello, <?= htmlspecialchars($_SESSION['UserName']) ?>
        </span>
        <a href="logout.php" style="color: white; background-color: var(--primary-color); padding: 6px 14px; border-radius: 6px; text-decoration: none; font-size: 14px;">
            Logout
        </a>
    </div>
<?php endif; ?>
        </div>
    </header>
    
    <div class="container">
        <div class="search-container">
            <input type="text" class="search-input" placeholder="Search my recipes...">
            <button class="search-btn">Search</button>
        </div>
        
        <div class="filter-bar">
            <label for="cuisine-select">Filter by Cuisine:</label>
            <select id="cuisine-select" style="padding: 8px 16px; border-radius: 20px; font-size: 14px;">
                <option value="">All Cuisines</option>
                <?php foreach ($cuisines as $cuisine): ?>
                    <option value="<?= htmlspecialchars($cuisine) ?>"><?= htmlspecialchars($cuisine) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="filter-bar">
            <label for="tag-select">Filter by Dietary Tag:</label>
            <select id="tag-select" style="padding: 8px 16px; border-radius: 20px; font-size: 14px;">
                <option value="">All Tags</option>
                <?php foreach ($tags as $tag): ?>
                    <option value="<?= htmlspecialchars($tag) ?>"><?= htmlspecialchars($tag) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="text-align: right; margin-bottom: 20px;">
    <a href="ADDRECIPE.php" class="add-recipe-btn" style="background-color: var(--secondary-color);">
        ➕ New Recipe
    </a>
</div>

        <div class="status-bar">
            Showing <?= count($recipes) ?> of your recipes
        </div>
        
        <?php if (count($recipes) > 0): ?>
            <div class="recipe-grid">
                <?php foreach ($recipes as $recipe): ?>
                   <div class="recipe-card" 
     data-id="<?= $recipe['RecipeID'] ?>" 
     data-cuisine="<?= htmlspecialchars($recipe['Cuisine']) ?>" 
     data-tags="<?= htmlspecialchars($recipe['DietaryTags']) ?>">

                        <div class="recipe-actions">
                            <button class="recipe-action-btn" title="Edit">✏️</button>
                            <button class="recipe-action-btn" title="Delete">🗑️</button>
                        </div>
                        <?php
                            $imagePath = $recipe['URL'];
if ($imagePath && !preg_match('#^(/|https?://)#', $imagePath)) {
    $imagePath = '/G04_06_RecipeHub/' . ltrim($imagePath, '/');
}


                        ?>
                        <div class="recipe-image" style="background-image: url('<?= htmlspecialchars($imagePath ?: "placeholder.jpg") ?>')"></div>
                        <div class="recipe-content">
                            <h3 class="recipe-title"><?= htmlspecialchars($recipe['Title']) ?></h3>
                            <div class="recipe-meta">
                                <span class="recipe-cuisine"><?= htmlspecialchars($recipe['Cuisine']) ?></span>
                                <span class="recipe-date">
                                    <?= !empty($recipe['DateRecipe']) ? date('M j, Y', strtotime($recipe['DateRecipe'])) : 'Unknown Date' ?>
                                </span>
                            </div>
                            <p class="recipe-desc"><?= htmlspecialchars($recipe['Description']) ?></p>
                            <div class="recipe-tags">
                                <?php if (!empty($recipe['DietaryTags']) && str_contains($recipe['DietaryTags'], 'gluten-free')): ?>
                                    <span class="tag gluten-free">gluten-free</span>
                                <?php endif; ?>
                                <?php if (!empty($recipe['DietaryTags']) && str_contains($recipe['DietaryTags'], 'spicy')): ?>
                                    <span class="tag spicy">spicy</span>
                                <?php endif; ?>
                                <?php if (!empty($recipe['DietaryTags']) && str_contains($recipe['DietaryTags'], 'vegetarian')): ?>
                                    <span class="tag vegetarian">vegetarian</span>
                                <?php endif; ?>
                                <?php if (!empty($recipe['DietaryTags']) && str_contains($recipe['DietaryTags'], 'soup')): ?>
                                    <span class="tag soup">soup</span>
                                <?php endif; ?>
                            </div>
                           <a href="#" class="view-btn" data-id="<?= $recipe['RecipeID'] ?>">View Recipe</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">🍳</div>
                <h3>You haven't added any recipes yet</h3>
                <p>Start building your recipe collection by adding your first recipe</p>
                <a href="add_recipe.html" class="add-recipe-btn">+ Add Recipe</a>
            </div>
        <?php endif; ?>
        
        <?php if (count($recipes) > 0): ?>
            <div class="pagination">
                <button class="page-btn">&laquo; Prev</button>
                <button class="page-btn active">1</button>
                <button class="page-btn">Next &raquo;</button>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Filter functionality for user's recipes
        document.getElementById('cuisine-select').addEventListener('change', filterRecipes);
        document.getElementById('tag-select').addEventListener('change', filterRecipes);
        document.querySelector('.search-btn').addEventListener('click', filterRecipes);
        document.querySelector('.search-input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') filterRecipes();
        });

        function filterRecipes() {
            const selectedCuisine = document.getElementById('cuisine-select').value.toLowerCase();
            const selectedTag = document.getElementById('tag-select').value.toLowerCase();
            const searchTerm = document.querySelector('.search-input').value.toLowerCase();

            document.querySelectorAll('.recipe-card').forEach(card => {
                const cuisine = card.dataset.cuisine?.toLowerCase() || '';
                const tags = card.dataset.tags?.toLowerCase() || '';
                const title = card.querySelector('.recipe-title')?.textContent.toLowerCase() || '';
                const desc = card.querySelector('.recipe-desc')?.textContent.toLowerCase() || '';

                const matchesCuisine = !selectedCuisine || cuisine === selectedCuisine;
                const matchesTag = !selectedTag || tags.includes(selectedTag);
                const matchesSearch = !searchTerm || title.includes(searchTerm) || desc.includes(searchTerm);

                if (matchesCuisine && matchesTag && matchesSearch) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });

            // Update status bar
            const visibleCount = document.querySelectorAll('.recipe-card[style="display: block"]').length;
            document.querySelector('.status-bar').textContent = `Showing ${visibleCount} of your recipes`;
        }

        // Add click handlers for edit/delete buttons
        // Add click handlers for edit/delete buttons
document.querySelectorAll('.recipe-action-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.stopPropagation();
        const card = this.closest('.recipe-card');
        const recipeId = card.dataset.id;
        
        if (this.textContent.includes('✏️')) {
            const card = this.closest('.recipe-card');
            const recipeId = card.dataset.id;

            fetch(`fetch_full_recipe.php?id=${recipeId}`)
                .then(res => res.json())
                .then(data => {
                    if (!data.success) {
                        Swal.fire("Error", "Could not load recipe details.", "error");
                        return;
                    }

                    // Build SweetAlert form
                    let ingredientsHTML = '';
                    data.ingredients.forEach((item, index) => {
                        ingredientsHTML += `
                            <div class="custom-ingredient">
                                <input type="text" id="ing-name-${index}" class="custom-input" placeholder="Name" value="${item.name}">
                                <input type="text" id="ing-amount-${index}" class="custom-input" placeholder="Amount" value="${item.amount}">
                                <button type="button" class="remove-btn" onclick="removeField(this, 'ingredient')">×</button>
                            </div>
                        `;
                    });

                    let stepsHTML = '';
                    data.steps.forEach((step, index) => {
                        stepsHTML += `
                            <div class="step-item">
                                <textarea id="step-${index}" class="custom-textarea" placeholder="Step ${index + 1}">${step}</textarea>
                                <button type="button" class="remove-btn" onclick="removeField(this, 'step')">×</button>
                            </div>
                        `;
                    });

                    Swal.fire({
                        title: 'Edit Recipe',
                        html: `
                            <style>
                                .edit-form-container {
                                    display: grid;
                                    grid-template-columns: 1fr;
                                    gap: 15px;
                                    max-height: 70vh;
                                    overflow-y: auto;
                                    padding-right: 10px;
                                }
                                
                                .form-section {
                                    background: #f9f9f9;
                                    border-radius: 8px;
                                    padding: 15px;
                                    margin-bottom: 15px;
                                }
                                
                                .form-section h4 {
                                    margin-top: 0;
                                    margin-bottom: 12px;
                                    color: var(--secondary-color);
                                    display: flex;
                                    align-items: center;
                                    gap: 8px;
                                }
                                
                                .top-grid {
                                    display: grid;
                                    grid-template-columns: 1fr 1fr;
                                    gap: 12px;
                                }
                                
                                .custom-input, .edit-form-full {
                                    padding: 10px 12px;
                                    font-size: 14px;
                                    border: 1px solid #ddd;
                                    border-radius: 6px;
                                    width: 100%;
                                    transition: all 0.2s;
                                    background: white;
                                }
                                
                                .custom-input:focus, .custom-textarea:focus {
                                    border-color: var(--secondary-color);
                                    box-shadow: 0 0 0 3px rgba(78, 205, 196, 0.2);
                                    outline: none;
                                }
                                
                                textarea.custom-input, textarea.custom-textarea {
                                    min-height: 80px;
                                    resize: vertical;
                                }
                                
                                .custom-ingredient {
                                    display: grid;
                                    grid-template-columns: 2fr 1fr auto;
                                    gap: 8px;
                                    align-items: center;
                                    margin-bottom: 8px;
                                }
                                
                                .step-item {
                                    display: grid;
                                    grid-template-columns: 1fr auto;
                                    gap: 8px;
                                    margin-bottom: 8px;
                                }
                                
                                .remove-btn {
                                    background: #ff6b6b;
                                    color: white;
                                    border: none;
                                    border-radius: 4px;
                                    width: 28px;
                                    height: 28px;
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                    cursor: pointer;
                                    transition: all 0.2s;
                                }
                                
                                .remove-btn:hover {
                                    background: #ff5252;
                                }
                                
                                .add-btn {
                                    background: var(--secondary-color);
                                    color: white;
                                    border: none;
                                    border-radius: 6px;
                                    padding: 8px 12px;
                                    font-size: 14px;
                                    cursor: pointer;
                                    transition: all 0.2s;
                                    display: inline-flex;
                                    align-items: center;
                                    gap: 6px;
                                }
                                
                                .add-btn:hover {
                                    background: #3dbeb6;
                                }
                                
                                .swal2-popup {
                                    width: 800px !important;
                                    padding: 2rem !important;
                                }
                            </style>
                            
                            <div class="edit-form-container">
                                <div class="form-section">
                                    <h4>📝 Basic Information</h4>
                                    <div class="top-grid">
                                        <div>
                                            <label for="edit-title">Title</label>
                                            <input id="edit-title" class="custom-input" placeholder="Recipe title" value="${data.title}">
                                        </div>
                                        <div>
                                            <label for="edit-date">Date</label>
                                            <input type="date" id="edit-date" class="custom-input" value="${data.date}">
                                        </div>
                                    </div>
                                    
                                    <div class="top-grid" style="margin-top: 12px;">
                                        <div>
                                            <label for="edit-cuisine">Cuisine</label>
                                            <select id="edit-cuisine" class="custom-input">
                                                <option value="">-- Select Cuisine --</option>
                                                ${data.cuisineOptions.map(opt => `<option value="${opt}" ${opt === data.cuisine ? 'selected' : ''}>${opt}</option>`).join('')}
                                            </select>
                                        </div>
                                        <div>
                                            <label for="edit-tags">Dietary Tags</label>
                                            <select id="edit-tags" class="custom-input">
                                                <option value="">-- Select Dietary Tag --</option>
                                                ${data.tagOptions.map(opt => `<option value="${opt}" ${opt === data.tags ? 'selected' : ''}>${opt}</option>`).join('')}
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div style="margin-top: 12px;">
                                        <label for="edit-desc">Description</label>
                                        <textarea id="edit-desc" class="custom-input" placeholder="Describe your recipe...">${data.description}</textarea>
                                    </div>
                                </div>
                                
                                <div class="form-section">
                                    <h4>🧂 Ingredients</h4>
                                    <div id="ingredient-section">
                                        ${ingredientsHTML}
                                    </div>
                                    <button type="button" onclick="addIngredientField()" class="add-btn">
                                        <span>+</span> Add Ingredient
                                    </button>
                                </div>
                                
                                <div class="form-section">
                                    <h4>👨‍🍳 Instructions</h4>
                                    <div id="step-section">
                                        ${stepsHTML}
                                    </div>
                                    <button type="button" onclick="addStepField()" class="add-btn">
                                        <span>+</span> Add Step
                                    </button>
                                </div>

                               <div style="margin-top: 12px;">
    <label for="edit-image">Upload New Image</label>
    <input type="file" id="edit-image" class="custom-input" accept="image/*">
</div>


                            </div>
                        `,
                        width: '800px',
                        confirmButtonText: 'Update Recipe',
                        confirmButtonColor: 'var(--secondary-color)',
                        showCancelButton: true,
                        cancelButtonText: 'Cancel',
                        focusConfirm: false,
                        preConfirm: () => {
                            const updatedData = {
                                id: recipeId,
                                title: document.getElementById('edit-title').value,
                                cuisine: document.getElementById('edit-cuisine').value,
                                tags: document.getElementById('edit-tags').value,
                                date: document.getElementById('edit-date').value,
                                description: document.getElementById('edit-desc').value,
                                ingredients: [],
                                steps: []
                            };

                            // Collect ingredient inputs
                            for (let i = 0; document.getElementById(`ing-name-${i}`); i++) {
                                const name = document.getElementById(`ing-name-${i}`).value.trim();
                                const amount = document.getElementById(`ing-amount-${i}`).value.trim();
                                if (name !== '') {
                                    updatedData.ingredients.push({ name, amount });
                                }
                            }

                            // Collect steps
                            for (let i = 0; document.getElementById(`step-${i}`); i++) {
                                const step = document.getElementById(`step-${i}`).value.trim();
                                if (step !== '') updatedData.steps.push(step);
                            }

                           const formData = new FormData();
formData.append('id', updatedData.id);
formData.append('title', updatedData.title);
formData.append('cuisine', updatedData.cuisine);
formData.append('tags', updatedData.tags);
formData.append('date', updatedData.date);
formData.append('description', updatedData.description);
formData.append('image', document.getElementById('edit-image')?.files[0]);

formData.append('ingredients', JSON.stringify(updatedData.ingredients));
formData.append('steps', JSON.stringify(updatedData.steps));

return fetch('update_full_recipe.php', {
    method: 'POST',
    body: formData
})
.then(res => res.json())
.then(response => {
    if (!response.success) throw new Error(response.message);
    return response;
})
.catch(err => {
    Swal.showValidationMessage(err.message);
});

                        }
                    }).then(result => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                title: 'Updated!',
                                text: 'Recipe has been updated successfully.',
                                icon: 'success',
                                confirmButtonColor: 'var(--secondary-color)'
                            }).then(() => location.reload());
                        }
                    });
                });
            }
        });
    });


    // ✅ View Recipe Button Handler
document.querySelectorAll('.view-btn').forEach(btn => {
    btn.addEventListener('click', function (e) {
        e.preventDefault();
        const recipeId = this.dataset.id;

        fetch(`fetch_full_recipe.php?id=${recipeId}`)
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    Swal.fire("Error", "Could not load recipe details.", "error");
                    return;
                }

                let ingredientsHTML = data.ingredients.map((i, idx) => 
                    `<li>${i.amount} of ${i.name}</li>`
                ).join("");

                let stepsHTML = data.steps.map((step, idx) =>
                    `<li>Step ${idx + 1}: ${step}</li>`
                ).join("");

                Swal.fire({
                    title: data.title,
                    html: `
                        <p><strong>Cuisine:</strong> ${data.cuisine}</p>
                        <p><strong>Date:</strong> ${data.date}</p>
                        <p><strong>Tags:</strong> ${data.tags}</p>
                        <p><strong>Description:</strong><br>${data.description}</p>
                        <hr>
                        <h4>Ingredients</h4>
                        <ul style="text-align: left;">${ingredientsHTML}</ul>
                        <h4>Steps</h4>
                        <ol style="text-align: left;">${stepsHTML}</ol>
                    `,
                    width: '700px',
                    confirmButtonColor: 'var(--secondary-color)'
                });
            });
    });
});

function addIngredientField() {
    const container = document.getElementById('ingredient-section');
    const index = container.children.length;
    const div = document.createElement('div');
    div.className = 'custom-ingredient';
    div.innerHTML = `
        <input type="text" id="ing-name-${index}" class="custom-input" placeholder="Name">
        <input type="text" id="ing-amount-${index}" class="custom-input" placeholder="Amount">
        <button type="button" class="remove-btn" onclick="removeField(this, 'ingredient')">×</button>
    `;
    container.appendChild(div);
}

function addStepField() {
    const container = document.getElementById('step-section');
    const index = container.children.length;
    const div = document.createElement('div');
    div.className = 'step-item';
    div.innerHTML = `
        <textarea id="step-${index}" class="custom-textarea" placeholder="Step ${index + 1}"></textarea>
        <button type="button" class="remove-btn" onclick="removeField(this, 'step')">×</button>
    `;
    container.appendChild(div);
}

function removeField(btn, type) {
    const item = btn.closest(type === 'ingredient' ? '.custom-ingredient' : '.step-item');
    item.remove();
    
    // Re-index remaining fields for consistency
    const container = item.parentElement;
    Array.from(container.children).forEach((child, index) => {
        if (type === 'ingredient') {
            child.querySelector('input').id = `ing-name-${index}`;
            child.querySelectorAll('input')[1].id = `ing-amount-${index}`;
        } else {
            child.querySelector('textarea').id = `step-${index}`;
            child.querySelector('textarea').placeholder = `Step ${index + 1}`;
        }
    });
}


    </script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</body>
</html>
