<?php
session_start(); // ✅ Ensure session is active
if (!isset($_SESSION['UserID'])) {
    header("Location: LOGIN.html"); // ✅ Block access if not logged in
    exit();
}

include('dbConnection.php'); // ✅ Then continue normal flow


// Fetch all recipes and their image from media table
$sql = "SELECT r.*, m.URL 
        FROM recipes r 
        LEFT JOIN media m ON r.RecipeID = m.RecipeID 
        GROUP BY r.RecipeID";

$stmt = $conn->prepare($sql);
$stmt->execute();

$cuisineSQL = "SELECT DISTINCT Cuisine FROM recipes WHERE Cuisine IS NOT NULL AND Cuisine <> '' ORDER BY Cuisine";
$cuisineStmt = $conn->prepare($cuisineSQL);
$cuisineStmt->execute();
$cuisines = $cuisineStmt->fetchAll(PDO::FETCH_COLUMN);


// Fetch distinct dietary tags (split by comma, deduplicated)
$tags = [];
$tagQuery = "SELECT DISTINCT DietaryTags FROM recipes WHERE DietaryTags IS NOT NULL AND DietaryTags <> ''";
$tagStmt = $conn->prepare($tagQuery);
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
    <title>Recipe Explorer | Cooking Tutorial Database</title>
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
        }
        
        .recipe-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
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
            <a href="#" class="logo">
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
            <input type="text" class="search-input" placeholder="Search for recipes, cuisines, ingredients...">
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


        
         <div class="status-bar">
            Showing <?= count($recipes) ?> of your recipes
        </div>
        
       <div class="recipe-grid">
    <?php foreach ($recipes as $recipe): ?>
     <div class="recipe-card" data-cuisine="<?= htmlspecialchars($recipe['Cuisine']) ?>" data-tags="<?= htmlspecialchars($recipe['DietaryTags']) ?>">
       <?php
   $imagePath = $recipe['URL'];
if ($imagePath && !preg_match('#^https?://#', $imagePath)) {
    $imagePath = 'https://bitp3353.utem.edu.my/BITP3353_2025/projectG04/G04_06_RecipeHub/' . ltrim($imagePath, '/');
}

?>
<div class="recipe-image" style="background-image: url('<?= htmlspecialchars($imagePath ?: "placeholder.jpg") ?>')"></div>

<?php if (!empty($imagePath)): ?>
    <div style="font-size: 12px; color: #777; padding: 5px 10px; word-break: break-all;">
        <strong>Image URL:</strong> 
        <a href="<?= htmlspecialchars($imagePath) ?>" target="_blank" style="color: var(--primary-color); text-decoration: underline;">
            <?= htmlspecialchars($imagePath) ?>
        </a>
    </div>
<?php endif; ?>


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

       <button class="view-btn view-recipe-btn" data-id="<?= $recipe['RecipeID'] ?>">View Recipe</button>
<button class="view-btn feedback-btn" data-recipe-id="<?= $recipe['RecipeID'] ?>" style="background-color: var(--secondary-color); margin-left: 10px;">Feedback</button>


        </div>
      </div>
    <?php endforeach; ?>
  </div>
        
        <div class="pagination">
            <button class="page-btn">&laquo; Prev</button>
            <button class="page-btn active">1</button>
            <button class="page-btn">Next &raquo;</button>
        </div>
    </div>

    <script>
        // Filter functionality
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                // In a real app, this would filter the recipes
                console.log(`Filter by: ${this.textContent}`);
            });
        });
        
        // Search functionality
        document.querySelector('.search-btn').addEventListener('click', function() {
    filterRecipes();
});

        
        // Allow Enter key to trigger search
        document.querySelector('.search-input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.querySelector('.search-btn').click();
            }
        });

        


// Cuisine Dropdown Filtering (already done)

document.getElementById('cuisine-select').addEventListener('change', function () {
    filterRecipes();
});

// Dietary Tag Dropdown Filtering
document.getElementById('tag-select').addEventListener('change', function () {
    filterRecipes();
});

// Unified Filtering Function
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
}

    </script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.querySelectorAll('.view-recipe-btn').forEach(button => {
    button.addEventListener('click', () => {
        const id = button.dataset.id;

        fetch(`fetch_full_recipe.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    Swal.fire("Error", "Could not fetch recipe details.", "error");
                    return;
                }

                const ingredientsHtml = data.ingredients.map(i =>
                    `<li>${i.amount} ${i.name}</li>`
                ).join('');

                const stepsHtml = data.steps.map((s, index) =>
                    `<li><strong>Step ${index + 1}:</strong> ${s}</li>`
                ).join('');

                Swal.fire({
    title: `<h2 style="color:#444; font-weight:700;">${data.title.toUpperCase()}</h2>`,
    html: `
        <div style="text-align:left; font-size: 16px; color: #333; line-height: 1.7;">
            <p><strong style="color:#555;">Cuisine:</strong> ${data.cuisine}</p>
            <p><strong style="color:#555;">Tags:</strong> ${data.tags || '-'}</p>
            <p><strong style="color:#555;">Date:</strong> ${data.date || '-'}</p>
            <p><strong style="color:#555;">Description:</strong> <br> ${data.description || '-'}</p>
            <hr style="margin: 15px 0; border-top: 1px solid #ccc;">
            <h4 style="color: #FF6B6B; margin-bottom: 5px;">Ingredients</h4>
            <ul style="margin-left: 20px; padding-left: 10px;">
                ${data.ingredients.map(i => `<li>${i.amount} ${i.name}</li>`).join('')}
            </ul>
            <h4 style="color: #4ECDC4; margin-top: 20px; margin-bottom: 5px;">Steps</h4>
            <ol style="margin-left: 20px; padding-left: 10px;">
                ${data.steps.map((s, index) => `<li><strong>Step ${index + 1}:</strong> ${s}</li>`).join('')}
            </ol>
        </div>
    `,
    width: 700,
    showCloseButton: true,
    confirmButtonText: 'Close',
    customClass: {
        popup: 'swal2-custom-popup',
        confirmButton: 'swal2-confirm-btn'
    }
});

            })
            .catch(() => {
                Swal.fire("Error", "Something went wrong.", "error");
            });
    });
});
</script>


<script>
document.querySelectorAll('.feedback-btn').forEach(button => {
    button.addEventListener('click', () => {
        const recipeId = button.dataset.recipeId;

        fetch(`fetch_feedback.php?recipe=${recipeId}`)
        .then(res => res.json())
        .then(data => {
            const feedbackList = data.map(f => 
                `<div style="margin-bottom: 10px;"><strong>${f.UserName}</strong> (${f.FeedbackTime}):<br>${f.Text}</div>`
            ).join('') || "<em>No feedback yet.</em>";

            Swal.fire({
                title: "Recipe Feedback",
                html: `
                    <div style="text-align: left; max-height: 300px; overflow-y: auto; margin-bottom: 10px;">
                        ${feedbackList}
                    </div>
                    <textarea id="feedback-input" rows="3" placeholder="Leave your feedback..." style="width: 100%; border: 1px solid #ccc; padding: 8px; resize: vertical;"></textarea>
                    <button id="voice-btn" style="margin-top: 10px; background-color:#4ECDC4; color:white; border:none; padding: 8px 14px; border-radius:6px; cursor:pointer;">🎤 Start Voice Input</button>
                `,
                showCancelButton: true,
                confirmButtonText: "Submit Feedback",
                didOpen: () => {
                    const voiceBtn = document.getElementById('voice-btn');
                    const feedbackInput = document.getElementById('feedback-input');
                    let recognition;

                    if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
                        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
                        recognition = new SpeechRecognition();
                        recognition.lang = 'en-US';
                        recognition.continuous = false;

                        voiceBtn.addEventListener('click', () => {
                            recognition.start();
                            voiceBtn.textContent = "🎙️ Listening... Click to Retry";
                        });

                        recognition.onresult = (event) => {
                            const transcript = event.results[0][0].transcript;
                            feedbackInput.value += transcript;
                        };

                        recognition.onerror = () => {
                            voiceBtn.textContent = "🎤 Try Again";
                        };

                        recognition.onend = () => {
                            voiceBtn.textContent = "🎤 Start Voice Input";
                        };
                    } else {
                        voiceBtn.disabled = true;
                        voiceBtn.textContent = "Speech Recognition Not Supported";
                    }
                },
                preConfirm: () => {
                    const newFeedback = document.getElementById('feedback-input').value.trim();
                    if (!newFeedback) {
                        Swal.showValidationMessage("Please enter your feedback.");
                        return false;
                    }

                    return fetch('insert_feedback.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `recipeID=${encodeURIComponent(recipeId)}&comment=${encodeURIComponent(newFeedback)}`
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (!data.success) {
                            throw new Error(data.message || "Submit failed");
                        }
                        return true;
                    })
                    .catch(() => {
                        Swal.showValidationMessage("❌ Failed to submit feedback.");
                    });
                }
            }).then(result => {
                if (result.isConfirmed) {
                    Swal.fire("✅ Success", "Feedback submitted!", "success").then(() => location.reload());
                }
            });
        })
        .catch(() => {
            Swal.fire("Error", "Could not load feedback.", "error");
        });
    });
});
</script>

</body>
</html>
