<?php
session_start();
if (!isset($_SESSION['UserName'])) {
    header("Location: LOGIN.html");
    exit();
}

$userID = $_SESSION['UserID'];
include('dbConnection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->beginTransaction();

        $title = $_POST['title'] ?? '';
        $cuisine = $_POST['cuisine'] ?? '';
        $dietary = $_POST['dietary-tags'] ?? '';
        $date = $_POST['date'] ?? null;
        $description = $_POST['description'] ?? '';

        // Insert into RECIPES
        $stmt = $conn->prepare("INSERT INTO RECIPES (UserID, Title, Cuisine, DietaryTags, DateRecipe, Description)
                                VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userID, $title, $cuisine, $dietary, $date, $description]);
        $recipeID = $conn->lastInsertId();

        // 🧂 Combine all ingredients
        $ingredientNames = $_POST['ingredient-name'] ?? [];
        $ingredientAmounts = $_POST['ingredient-amount'] ?? [];
        $ingredientList = [];

        for ($i = 0; $i < count($ingredientNames); $i++) {
            $name = trim($ingredientNames[$i]);
            $amount = trim($ingredientAmounts[$i]);
            if ($name !== '') {
                $ingredientList[] = $amount !== '' ? "$amount $name" : $name;
            }
        }

        $allIngredients = implode(', ', $ingredientList); // ✅ all ingredients in one string

        // 🥄 Combine all steps
        $stepList = [];
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'step-') === 0 && trim($value) !== '') {
                $stepList[] = trim($value);
            }
        }

        $allInstructions = implode(', ', $stepList); // ✅ all steps in one string

        // ✅ Insert single STEP row with combined ingredients + instructions
        $stmt = $conn->prepare("INSERT INTO STEP (RecipeID, Ingredient, Instruction) VALUES (?, ?, ?)");
        $stmt->execute([$recipeID, $allIngredients, $allInstructions]);

        $conn->commit();
        echo "<script>alert('✅ Recipe successfully saved!'); window.location.href='INDEXX.php';</script>";
    } catch (Exception $e) {
        $conn->rollBack();
        echo "<script>alert('❌ Failed to save recipe: " . addslashes($e->getMessage()) . "'); window.history.back();</script>";
    }
} 
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Recipe | Recipe Explorer</title>
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
            max-width: 1000px;
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
        
        .recipe-form {
            background-color: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: var(--card-shadow);
        }
        
        .form-title {
            color: var(--primary-color);
            margin-bottom: 25px;
            font-size: 28px;
            text-align: center;
        }
        
        .form-section {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .form-section h3 {
            color: var(--dark-color);
            margin-bottom: 15px;
            font-size: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .required-field::after {
            content: " *";
            color: var(--primary-color);
        }
        
        input[type="text"],
        input[type="date"],
        textarea,
        select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s;
            font-family: inherit;
        }
        
        input[type="text"]:focus,
        input[type="date"]:focus,
        textarea:focus,
        select:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(78, 205, 196, 0.2);
            outline: none;
        }
        
        textarea {
            min-height: 120px;
            resize: vertical;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #ff5252;
        }
        
        .btn-secondary {
            background-color: #f0f0f0;
            color: var(--dark-color);
        }
        
        .btn-secondary:hover {
            background-color: #e0e0e0;
        }
        
        .btn-danger {
            background-color: #ff4444;
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #cc0000;
        }
        
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 30px;
        }
        
        /* Step and Ingredient styling */
        .step-entry, .ingredient-entry {
            margin-bottom: 20px;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
            position: relative;
        }
        
        .step-header, .ingredient-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .step-number {
            font-weight: 600;
            color: var(--secondary-color);
        }
        
        .step-actions, .ingredient-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .step-actions button, .ingredient-actions button {
            padding: 8px 15px;
        }
        
        .ingredient-fields {
            display: flex;
            gap: 15px;
        }
        
        .ingredient-fields input {
            flex: 1;
        }
        
        .add-item-btn {
            margin-top: 10px;
            background-color: var(--secondary-color);
            color: white;
        }
        
        .add-item-btn:hover {
            background-color: #3dbeb6;
        }
        
        /* Tab styling */
        .tab-container {
            margin-bottom: 25px;
        }
        
        .tab-buttons {
            display: flex;
            border-bottom: 2px solid #eee;
            margin-bottom: 20px;
        }
        
        .tab-button {
            padding: 12px 25px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            color: #777;
            position: relative;
        }
        
        .tab-button.active {
            color: var(--primary-color);
        }
        
        .tab-button.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 100%;
            height: 2px;
            background-color: var(--primary-color);
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 15px;
            }
            
            .ingredient-fields {
                flex-direction: column;
                gap: 10px;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
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
             <div style="display: flex; align-items: center; gap: 15px; margin-left: 15px;">
        <span style="font-weight: bold; color: var(--dark-color);">
            👋 Hello, <?= htmlspecialchars($_SESSION['UserName']) ?>
        </span>
        <a href="logout.php" style="color: white; background-color: var(--primary-color); padding: 6px 14px; border-radius: 6px; text-decoration: none; font-size: 14px;">
            Logout
        </a>
    </div>
        </div>
    </header>
    
    <div class="container">
        <div class="recipe-form">
            <h1 class="form-title">Add New Recipe</h1>
            
            <div class="tab-container">
                <div class="tab-buttons">
                    <button class="tab-button active" data-tab="basic-info">Basic Info</button>
                    <button class="tab-button" data-tab="ingredients">Ingredients</button>
                    <button class="tab-button" data-tab="steps">Steps</button>
                </div>
                
                <form id="recipe-form"method="POST">
                    <!-- Basic Info Tab -->
                    <div class="tab-content active" id="basic-info">
                        <div class="form-group">
                            <label for="title" class="required-field">Recipe Title</label>
                            <input type="text" id="title" name="title" required maxlength="255">
                        </div>
                        
                        <div class="form-group">
                            <label for="cuisine">Cuisine</label>
                            <select id="cuisine" name="cuisine">
    <option value="">-- Select Cuisine --</option>
    <option value="American">American</option>
    <option value="British">British</option>
    <option value="Chinese">Chinese</option>
    <option value="French">French</option>
    <option value="Greek">Greek</option>
    <option value="Indian">Indian</option>
    <option value="Italian">Italian</option>
    <option value="Japanese">Japanese</option>
    <option value="Korean">Korean</option>
    <option value="Lebanese">Lebanese</option>
    <option value="Malaysian">Malaysian</option>
    <option value="Mexican">Mexican</option>
    <option value="Middle Eastern">Middle Eastern</option>
    <option value="Moroccan">Moroccan</option>
    <option value="Spanish">Spanish</option>
    <option value="Thai">Thai</option>
    <option value="Turkish">Turkish</option>
    <option value="Vietnamese">Vietnamese</option>
</select>

                        </div>
                        
                        <div class="form-group">
    <label for="dietary-tags">Dietary Tags</label>
    <select id="dietary-tags" name="dietary-tags">
    <option value="">-- Select Dietary Tag --</option>
    <option value="vegetarian">Vegetarian</option>
    <option value="vegan">Vegan</option>
    <option value="gluten-free">Gluten-Free</option>
    <option value="dairy-free">Dairy-Free</option>
    <option value="nut-free">Nut-Free</option>
    <option value="halal">Halal</option>
    <option value="kosher">Kosher</option>
    <option value="pescatarian">Pescatarian</option>
    <option value="low-carb">Low Carb</option>
    <option value="keto">Keto</option>
    <option value="paleo">Paleo</option>
    <option value="sugar-free">Sugar-Free</option>
    <option value="high-protein">High Protein</option>
    <option value="spicy">Spicy</option>
    <option value="soup">Soup</option>
</select>
</div>

                        
                        <div class="form-group">
                            <label for="date">Date</label>
                            <input type="date" id="date" name="date" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" rows="4"></textarea>
                        </div>
                    </div>
                    
                    <!-- Ingredients Tab -->
                    <div class="tab-content" id="ingredients">
                        <div class="form-group">
                            <label>Ingredients</label>
                            <div id="ingredients-container">
                                <!-- Ingredients will be added here dynamically -->
                            </div>
                            <button type="button" id="add-ingredient" class="btn add-item-btn">+ Add Ingredient</button>
                        </div>
                    </div>
                    
                    <!-- Steps Tab -->
                    <div class="tab-content" id="steps">
                        <div class="form-group">
                            <label>Preparation Steps</label>
                            <div id="steps-container">
                                <!-- Steps will be added here dynamically -->
                            </div>
                            <button type="button" id="add-step" class="btn add-item-btn">+ Add Step</button>
                        </div>
                        <!-- Add this inside #steps tab -->
<div class="form-actions">
    <button type="button" id="cancel-btn" class="btn btn-secondary">Cancel</button>
    <button type="submit" id="save-btn" class="btn btn-primary">Save Recipe</button>
</div>

                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tab functionality
            const tabButtons = document.querySelectorAll('.tab-button');
           tabButtons.forEach(button => {
    button.addEventListener('click', function () {
        // Deactivate all tabs
        tabButtons.forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.remove('active');
        });

        // Activate clicked tab
        this.classList.add('active');
        const tabId = this.getAttribute('data-tab');
        document.getElementById(tabId).classList.add('active');

        // Show save + cancel only on 'steps'
        if (tabId === 'steps') {
            saveBtn.style.display = 'inline-block';
            cancelBtn.style.display = 'inline-block';
        } else {
            saveBtn.style.display = 'none';
            cancelBtn.style.display = 'none';
        }
    });
});



            // Ingredients management
            const ingredientsContainer = document.getElementById('ingredients-container');
            const addIngredientBtn = document.getElementById('add-ingredient');
            
            addIngredientBtn.addEventListener('click', addIngredient);
            
            function addIngredient() {
                const ingredientDiv = document.createElement('div');
                ingredientDiv.className = 'ingredient-entry';
                
                ingredientDiv.innerHTML = `
                    <div class="ingredient-header">
                        <span class="ingredient-number">Ingredient</span>
                        <button type="button" class="btn btn-danger remove-ingredient">× Remove</button>
                    </div>
                    <div class="ingredient-fields">
                        <input type="text" name="ingredient-name[]" placeholder="Name" required>
                        <input type="text" name="ingredient-amount[]" placeholder="Amount (e.g., 1 cup)">
                    </div>
                `;
                
                ingredientsContainer.appendChild(ingredientDiv);
                
                // Add event listener for remove button
                ingredientDiv.querySelector('.remove-ingredient').addEventListener('click', function() {
                    ingredientsContainer.removeChild(ingredientDiv);
                });
            }

            // Steps management
            const stepsContainer = document.getElementById('steps-container');
            const addStepBtn = document.getElementById('add-step');
            let stepCounter = 1;
            
            // Add first step by default
            addStep();
            
            addStepBtn.addEventListener('click', addStep);
            
           function addStep() {
    const stepDiv = document.createElement('div');
    stepDiv.className = 'step-entry';

    stepDiv.innerHTML = `
        <div class="step-header">
            <span class="step-number">Step ${stepCounter}</span>
            <button type="button" class="btn btn-danger remove-step">× Remove</button>
        </div>
        <textarea name="step-${stepCounter}" rows="3" placeholder="Enter instruction..." required></textarea>
        <div class="step-actions">
            <button type="button" class="btn btn-secondary move-up">↑ Move Up</button>
            <button type="button" class="btn btn-secondary move-down">↓ Move Down</button>
            <button type="button" class="btn btn-primary speak-btn" style="background-color: var(--secondary-color);">🎤 Speak Instruction</button>
        </div>
    `;

    stepsContainer.appendChild(stepDiv);
    stepCounter++;

    // Remove step
    stepDiv.querySelector('.remove-step').addEventListener('click', function () {
        stepsContainer.removeChild(stepDiv);
        renumberSteps();
    });

    // Move up/down logic
    const moveUp = stepDiv.querySelector('.move-up');
    const moveDown = stepDiv.querySelector('.move-down');

    moveUp.addEventListener('click', function () {
        const prev = stepDiv.previousElementSibling;
        if (prev) {
            stepsContainer.insertBefore(stepDiv, prev);
            renumberSteps();
        }
    });

    moveDown.addEventListener('click', function () {
        const next = stepDiv.nextElementSibling;
        if (next) {
            stepsContainer.insertBefore(next, stepDiv);
            renumberSteps();
        }
    });

    // 🎤 Speech recognition
    const speakBtn = stepDiv.querySelector('.speak-btn');
    const textarea = stepDiv.querySelector('textarea');

    if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        const recognition = new SpeechRecognition();
        recognition.lang = 'en-US';
        recognition.continuous = false;

        speakBtn.addEventListener('click', () => {
            recognition.start();
            speakBtn.textContent = "🎙️ Listening...";
        });

        recognition.onresult = (event) => {
            const transcript = event.results[0][0].transcript;
            textarea.value += (textarea.value ? ' ' : '') + transcript;
        };

        recognition.onerror = () => {
            speakBtn.textContent = "🎤 Speak Instruction";
            alert("❌ Voice recognition failed or blocked by browser.");
        };

        recognition.onend = () => {
            speakBtn.textContent = "🎤 Speak Instruction";
        };
    } else {
        speakBtn.disabled = true;
        speakBtn.textContent = "Not Supported";
    }
}

            
            function renumberSteps() {
                const steps = stepsContainer.querySelectorAll('.step-entry');
                steps.forEach((step, index) => {
                    step.querySelector('.step-number').textContent = `Step ${index + 1}`;
                    const textarea = step.querySelector('textarea');
                    textarea.name = `step-${index + 1}`;
                });
            }

         

            // Cancel button
          document.getElementById('cancel-btn').addEventListener('click', function() {
    const activeTab = document.querySelector('.tab-button.active').getAttribute('data-tab');

    if (activeTab === 'steps') {
        if (confirm('Are you sure you want to cancel? All unsaved changes will be lost.')) {
            window.location.href = 'INDEXX.php';
        }
    }
});

        });

        const today = new Date().toISOString().split('T')[0];
document.getElementById('date').value = today;

    </script>
</body>
</html>