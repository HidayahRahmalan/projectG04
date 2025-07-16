<?php
include('header.php');

// Database connection
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

// Get filter parameters from GET request
$searchTitle = isset($_GET['search_title']) ? $_GET['search_title'] : '';
$cuisineType = isset($_GET['cuisine_type']) ? $_GET['cuisine_type'] : '';
$dietaryType = isset($_GET['dietary_type']) ? $_GET['dietary_type'] : '';

// Build the SQL query with filters
$sql = "SELECT r.*, u.User_Name, i.Image_Path 
        FROM recipe r 
        LEFT JOIN users u ON r.User_ID = u.User_ID
        LEFT JOIN image i ON r.Recipe_ID = i.Recipe_ID
        WHERE 1=1";

$params = [];

if (!empty($searchTitle)) {
    $sql .= " AND r.Recipe_Title LIKE :searchTitle";
    $params[':searchTitle'] = '%' . $searchTitle . '%';
}

if (!empty($cuisineType)) {
    $sql .= " AND r.Recipe_CuisineType = :cuisineType";
    $params[':cuisineType'] = $cuisineType;
}

if (!empty($dietaryType)) {
    $sql .= " AND r.Recipe_DietaryType = :dietaryType";
    $params[':dietaryType'] = $dietaryType;
}

$sql .= " ORDER BY r.Recipe_UploadDate DESC";

// Prepare and execute the query
$stmt = $pdo->prepare($sql);
foreach ($params as $key => &$val) {
    $stmt->bindParam($key, $val);
}
$stmt->execute();
$recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group images by recipe
$recipeImages = [];
foreach ($recipes as $recipe) {
    if (!empty($recipe['Image_Path'])) {
        $recipeImages[$recipe['Recipe_ID']][] = $recipe['Image_Path'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- ... (keep your head as is) ... -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../G04_01_Gastroverse/toastr.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="../G04_01_Gastroverse/toastr.min.js"></script>
    <title>GastroVerse - Recipe Community</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f9fafb;
            line-height: 1.6;
            color: #374151;
        }

        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 1.5rem;
        }

        .section {
            margin-bottom: 2.5rem;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #1f2937;
        }

        /* Search and Filter Styles */
        .filter-form {
            display: grid;
            gap: 1rem;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        }

        @media (min-width: 768px) {
            .filter-form {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        .form-input, .form-select {
            padding: 0.5rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-input:focus, .form-select:focus {
            outline: none;
            border-color: #ef4444;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
            transform: translateY(-1px);
        }

        .btn-primary {
            background-color: #ef4444;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 0.375rem;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .btn-primary:hover {
            background-color: #dc2626;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        .btn-primary:hover::before {
            left: 100%;
        }

        /* Enhanced Recipe Grid Styles */
        .recipe-grid {
            display: grid;
            gap: 1.5rem;
            grid-template-columns: 1fr;
        }

        @media (min-width: 768px) {
            .recipe-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .recipe-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            cursor: pointer;
            position: relative;
            transform: translateY(0);
            opacity: 0;
            animation: fadeInUp 0.6s ease forwards;
        }

        .recipe-card:nth-child(1) { animation-delay: 0.1s; }
        .recipe-card:nth-child(2) { animation-delay: 0.2s; }
        .recipe-card:nth-child(3) { animation-delay: 0.3s; }
        .recipe-card:nth-child(4) { animation-delay: 0.4s; }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .recipe-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(59, 130, 246, 0.1));
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 1;
        }

        .recipe-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
            border-color: #ef4444;
        }

        .recipe-card:hover::before {
            opacity: 1;
        }

        .recipe-image-container {
            position: relative;
            overflow: hidden;
            height: 14rem;
        }

        .recipe-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .recipe-card:hover .recipe-image {
            transform: scale(1.1);
        }

        .recipe-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom, transparent 0%, rgba(0,0,0,0.7) 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
            display: flex;
            align-items: flex-end;
            padding: 1rem;
            z-index: 2;
        }

        .recipe-card:hover .recipe-overlay {
            opacity: 1;
        }

        .overlay-text {
            color: white;
            font-weight: 600;
            font-size: 0.875rem;
            transform: translateY(20px);
            transition: transform 0.3s ease;
        }

        .recipe-card:hover .overlay-text {
            transform: translateY(0);
        }

        .recipe-content {
            padding: 1.25rem;
            position: relative;
            z-index: 2;
        }

        .recipe-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: #1f2937;
            transition: color 0.3s ease;
        }

        .recipe-card:hover .recipe-title {
            color: #ef4444;
        }

        .recipe-description {
            color: #6b7280;
            margin-bottom: 0.75rem;
            transition: color 0.3s ease;
            line-height: 1.5;
        }

        .recipe-card:hover .recipe-description {
            color: #374151;
        }

        .recipe-meta {
            font-size: 0.875rem;
            color: #9ca3af;
            margin-bottom: 0.75rem;
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .meta-tag {
            background: #f3f4f6;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .recipe-card:hover .meta-tag {
            background: #ef4444;
            color: white;
            transform: translateY(-2px);
        }

        .recipe-review {
            font-style: italic;
            color: #6b7280;
            margin-top: 0.5rem;
            padding: 0.75rem;
            background: #f9fafb;
            border-radius: 0.5rem;
            border-left: 3px solid #e5e7eb;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .recipe-review::before {
            content: '"';
            position: absolute;
            top: -0.5rem;
            left: 0.5rem;
            font-size: 3rem;
            color: #e5e7eb;
            font-family: serif;
            line-height: 1;
        }

        .recipe-card:hover .recipe-review {
            background: #fef2f2;
            border-left-color: #ef4444;
            transform: translateX(5px);
        }

        .recipe-card:hover .recipe-review::before {
            color: #fca5a5;
        }

        /* Floating Action Button */
        .fab {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #ef4444, #dc2626);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            box-shadow: 0 4px 20px rgba(239, 68, 68, 0.3);
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .fab:hover {
            transform: scale(1.1) rotate(90deg);
            box-shadow: 0 6px 25px rgba(239, 68, 68, 0.4);
        }

        /* Loading Animation */
        .loading-skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
        }

        @keyframes loading {
            0% {
                background-position: 200% 0;
            }
            100% {
                background-position: -200% 0;
            }
        }

        /* Table Styles */
        .table-container {
            overflow-x: auto;
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .activity-table {
            width: 100%;
            border-collapse: collapse;
        }

        .table-header {
            background-color: #f3f4f6;
        }

        .table-header th {
            padding: 0.75rem 1rem;
            text-align: left;
            font-weight: 600;
            color: #374151;
            border-bottom: 1px solid #d1d5db;
        }

        .table-row {
            transition: background-color 0.2s ease;
        }

        .table-row:hover {
            background-color: #f9fafb;
        }

        .table-cell {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #e5e7eb;
        }

        /* Footer Styles */
        .footer {
            background-color: #1f2937;
            color: white;
            padding: 1rem;
            text-align: center;
            margin-top: 2.5rem;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .section-title {
                font-size: 1.25rem;
            }

            .recipe-grid {
                grid-template-columns: 1fr;
            }

            .fab {
                bottom: 1rem;
                right: 1rem;
                width: 50px;
                height: 50px;
                font-size: 1.25rem;
            }
        }

        /* Pulse animation for new content */
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.7;
            }
        }

        .pulse {
            animation: pulse 2s infinite;
        }
    </style>
</head>
<body>
  <main class="container">
    <!-- Filters and Search (same as before) -->
    <section class="section">
        <h2 class="section-title">Search & Filter</h2>
        <form class="filter-form" method="GET">
            <input type="text" name="search_title" placeholder="Search by recipe title or ingredient" 
                   class="form-input" value="<?php echo htmlspecialchars($searchTitle); ?>">
            <select name="cuisine_type" class="form-select">
                <option value="">All Cuisines</option>
                <option value="Italian" <?php echo ($cuisineType == 'Italian') ? 'selected' : ''; ?>>Italian</option>
                <option value="Asian" <?php echo ($cuisineType == 'Asian') ? 'selected' : ''; ?>>Asian</option>
                <option value="Mexican" <?php echo ($cuisineType == 'Mexican') ? 'selected' : ''; ?>>Mexican</option>
                <option value="Indian" <?php echo ($cuisineType == 'Indian') ? 'selected' : ''; ?>>Indian</option>
                <option value="French" <?php echo ($cuisineType == 'French') ? 'selected' : ''; ?>>French</option>
                <option value="American" <?php echo ($cuisineType == 'American') ? 'selected' : ''; ?>>American</option>
            </select>
            <select name="dietary_type" class="form-select">
                <option value="">All Dietary Types</option>
                <option value="Vegan" <?php echo ($dietaryType == 'Vegan') ? 'selected' : ''; ?>>Vegan</option>
                <option value="Vegetarian" <?php echo ($dietaryType == 'Vegetarian') ? 'selected' : ''; ?>>Vegetarian</option>
                <option value="Non-Vegetarian" <?php echo ($dietaryType == 'Non-Vegetarian') ? 'selected' : ''; ?>>Non-Vegetarian</option>
                <option value="Gluten-Free" <?php echo ($dietaryType == 'Gluten-Free') ? 'selected' : ''; ?>>Gluten-Free</option>
                <option value="Keto" <?php echo ($dietaryType == 'Keto') ? 'selected' : ''; ?>>Keto</option>
                <option value="Low-Carb" <?php echo ($dietaryType == 'Low-Carb') ? 'selected' : ''; ?>>Low-Carb</option>
            </select>
            <button type="submit" class="btn-primary">Apply</button>
            <?php if (!empty($searchTitle) || !empty($cuisineType) || !empty($dietaryType)): ?>
                <a href="?" class="btn-secondary">Clear Filters</a>
            <?php endif; ?>
        </form>
    </section>

    <!-- Recent Recipes -->
    <section class="section">
        <h2 class="section-title">Recently Uploaded Recipes</h2>
        <div class="recipe-grid">
            <?php if (empty($recipes)): ?>
                <p>No recipes found matching your criteria.</p>
            <?php else: ?>
                <?php 
                $displayedRecipes = [];
                foreach ($recipes as $recipe): 
                    if (in_array($recipe['Recipe_ID'], $displayedRecipes)) continue;
                    $displayedRecipes[] = $recipe['Recipe_ID'];
                    $imagePath = isset($recipeImages[$recipe['Recipe_ID']][0]) ? $recipeImages[$recipe['Recipe_ID']][0] : 'https://via.placeholder.com/400x224?text=No+Image';
                ?>
                <!-- Make the card clickable, linking to recipe.php -->
                <a href="fullrecipe.php?recipe_id=<?php echo $recipe['Recipe_ID']; ?>" style="text-decoration:none;color:inherit;">
                <div class="recipe-card">
                    <div class="recipe-image-container">
                        <img src="<?php echo htmlspecialchars($imagePath); ?>" 
                             alt="<?php echo htmlspecialchars($recipe['Recipe_Title']); ?>" 
                             class="recipe-image">
                        <div class="recipe-overlay">
                            <div class="overlay-text">Click to view full recipe</div>
                        </div>
                    </div>
                    <div class="recipe-content">
                        <h3 class="recipe-title"><?php echo htmlspecialchars($recipe['Recipe_Title']); ?></h3>
                        <p class="recipe-description"><?php echo htmlspecialchars($recipe['Recipe_Description']); ?></p>
                        <div class="recipe-meta">
                            <span class="meta-tag"><?php echo htmlspecialchars($recipe['Recipe_CuisineType']); ?></span>
                            <span class="meta-tag"><?php echo htmlspecialchars($recipe['Recipe_DietaryType']); ?></span>
                        </div>
                        <div class="recipe-review">Uploaded by <?php echo htmlspecialchars($recipe['User_Name']); ?></div>
                    </div>
                </div>
                </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
</main>
<footer class="footer">
    <p>&copy; 2025 GastroVerse. All rights reserved.</p>
</footer>
<script>
    // ... your JS, keep as is, except remove the JS click handler for .recipe-card since cards are <a> now ...
$(document).ready(function() {
        
        toastr.options = {
        "closeButton": false,
        "debug": false,
        "newestOnTop": false,
        "progressBar": false,
        "positionClass": "toast-top-center",
        "preventDuplicates": false,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "5000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
        }

            var name = sessionStorage.getItem('name');  
            console.log("Message from sessionStorage:", name); 
            if (name) {
                toastr.success(name); 
                sessionStorage.removeItem('name');
            }
        });
        
        // Add click handlers for recipe cards
        document.querySelectorAll('.recipe-card').forEach(card => {
            card.addEventListener('click', function() {
                // Add a subtle flash effect
                this.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    this.style.transform = '';
                }, 100);
            });
        });

        // Floating Action Button interaction
        document.querySelector('.fab').addEventListener('click', function() {
            this.style.transform = 'scale(0.9) rotate(90deg)';
            setTimeout(() => {
                this.style.transform = '';
            }, 150);
        });

        // Smooth scroll reveal animation
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animationPlayState = 'running';
                }
            });
        }, observerOptions);

        document.querySelectorAll('.recipe-card').forEach(card => {
            observer.observe(card);
        });

    </script>
</script>
</body>
</html>