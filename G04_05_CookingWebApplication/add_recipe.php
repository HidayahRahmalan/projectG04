<?php
session_start();
include('connect.php');
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $error = '';

    try {
        $conn->beginTransaction();

        // Validate file uploads
        if (!isset($_FILES['recipe_image']) || $_FILES['recipe_image']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Recipe image is required.");
        }
        if (!isset($_FILES['recipe_doc']) || $_FILES['recipe_doc']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Recipe document is required.");
        }

        $image_file = $_FILES['recipe_image'];
        $doc_file = $_FILES['recipe_doc'];

        $image_type = strtoupper(pathinfo($image_file['name'], PATHINFO_EXTENSION));
        $doc_type = strtoupper(pathinfo($doc_file['name'], PATHINFO_EXTENSION));

        $allowed_image_types = ['JPEG', 'JPG', 'PNG', 'GIF', 'WEBP'];
        $allowed_doc_types = ['PDF', 'DOCX', 'TXT'];

        if (!in_array($image_type, $allowed_image_types)) {
            throw new Exception("Invalid image type. Allowed: " . implode(', ', $allowed_image_types));
        }
        if ($image_file['size'] > 5 * 1024 * 1024) {
            throw new Exception("Image file too large. Max 5MB allowed.");
        }
        if (!in_array($doc_type, $allowed_doc_types)) {
            throw new Exception("Invalid document type. Allowed: " . implode(', ', $allowed_doc_types));
        }
        if ($doc_file['size'] > 10 * 1024 * 1024) {
            throw new Exception("Document file too large. Max 10MB allowed.");
        }

        // Validate preparation time format
        if (!preg_match('/^[0-9]+(\s*-\s*[0-9]+)?\s*(mins?|hours?|days?)$/i', $_POST['preparation_time'])) {
            throw new Exception("Preparation time must be in format like '30 mins', '1-2 hours', or '1 day'");
        }

        // IMAGE - Insert
        $stmt = $conn->prepare("INSERT INTO image (image_data, image_name, image_type) VALUES (?, ?, ?)");
        $img_data = file_get_contents($image_file['tmp_name']);
        $stmt->execute([$img_data, $image_file['name'], $image_type]);
        $image_id = $conn->lastInsertId();

        // DOCUMENT - Insert
        $stmt = $conn->prepare("INSERT INTO document (doc_data, doc_name, doc_type) VALUES (?, ?, ?)");
        $doc_data = file_get_contents($doc_file['tmp_name']);
        $stmt->execute([$doc_data, $doc_file['name'], $doc_type]);
        $doc_id = $conn->lastInsertId();

        // RECIPE - Insert
        $stmt = $conn->prepare("INSERT INTO recipe (
            title, description, cuisine, dietary_type, user_id, doc_id, image_id,
            difficulty, preparation_time, meal_type
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->execute([
            $_POST['title'],
            $_POST['description'],
            $_POST['cuisine'],
            $_POST['dietary_type'],
            $_SESSION['user_id'],
            $doc_id,
            $image_id,
            $_POST['difficulty'],
            $_POST['preparation_time'],
            $_POST['meal_type']
        ]);

        $recipe_id = $conn->lastInsertId();

        // TAGS - Optional
        if (!empty($_POST['tags'])) {
            $tags = array_map('trim', explode(',', $_POST['tags']));
            $tags = array_filter(array_unique($tags));

            foreach ($tags as $tag) {
                $stmt = $conn->prepare("INSERT INTO tag (tag_text, recipe_id) VALUES (?, ?)");
                $stmt->execute([$tag, $recipe_id]);
            }
        }

        $conn->commit();
        header("Location: recipe_detail.php?id=" . $recipe_id);
        exit();
    } catch (Exception $e) {
        $conn->rollBack();
        $error = "Error adding recipe: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Recipe - CookingApp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .form-container { background-color: #f8f9fa; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);}
        .file-upload-info { font-size: 0.8rem; color: #6c757d; margin-top: 0.25rem; }
        .required-field::after {
            content: " *";
            color: red;
        }
        .image-preview {
            max-width: 100%;
            max-height: 200px;
            margin-top: 10px;
            display: none;
        }
        .upload-area {
            border: 2px dashed #dee2e6;
            border-radius: 5px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        .upload-area:hover {
            border-color: #0d6efd;
            background-color: #f8f9fa;
        }
        .upload-content i {
            font-size: 2.5rem;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="INDEX.php">CookingApp</a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="recipes.php">Recipes</a></li>
                    <li class="nav-item"><a class="nav-link" href="team.php">Our Team</a></li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="add_recipe.php">Add Recipe</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="form-container">
                    <h1 class="mb-4 text-center">Add New Recipe</h1>
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    <form method="POST" enctype="multipart/form-data" id="recipeForm" novalidate>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="title" class="form-label required-field">Recipe Title</label>
                                    <input type="text" class="form-control" id="title" name="title" required
                                           value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="description" class="form-label required-field">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="5" required><?php 
                                        echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '';
                                    ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="cuisine" class="form-label required-field">Cuisine</label>
                                    <input type="text" class="form-control" id="cuisine" name="cuisine" required
                                           value="<?php echo isset($_POST['cuisine']) ? htmlspecialchars($_POST['cuisine']) : ''; ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="dietary_type" class="form-label required-field">Dietary Type</label>
                                    <input type="text" class="form-control" id="dietary_type" name="dietary_type" required
                                           value="<?php echo isset($_POST['dietary_type']) ? htmlspecialchars($_POST['dietary_type']) : ''; ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="difficulty" class="form-label required-field">Difficulty</label>
                                    <select class="form-select" id="difficulty" name="difficulty" required>
                                        <option value="">Select difficulty...</option>
                                        <option value="EASY" <?php echo (isset($_POST['difficulty']) && $_POST['difficulty'] == 'EASY') ? 'selected' : ''; ?>>Easy</option>
                                        <option value="INTERMEDIATE" <?php echo (isset($_POST['difficulty']) && $_POST['difficulty'] == 'INTERMEDIATE') ? 'selected' : ''; ?>>Intermediate</option>
                                        <option value="HARD" <?php echo (isset($_POST['difficulty']) && $_POST['difficulty'] == 'HARD') ? 'selected' : ''; ?>>Hard</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="preparation_time" class="form-label required-field">Preparation Time</label>
                                    <input type="text" class="form-control" id="preparation_time" name="preparation_time" 
                                           placeholder="e.g., 30 mins, 1-2 hours, 1 day" required
                                           value="<?php echo isset($_POST['preparation_time']) ? htmlspecialchars($_POST['preparation_time']) : ''; ?>">
                                    <small class="text-muted">Format: 30 mins, 1-2 hours, 1 day</small>
                                </div>
                                <div class="mb-3">
                                    <label for="meal_type" class="form-label required-field">Meal Type</label>
                                    <select class="form-select" id="meal_type" name="meal_type" required>
                                        <option value="">Select meal type...</option>
                                        <option value="BREAKFAST" <?php echo (isset($_POST['meal_type']) && $_POST['meal_type'] == 'BREAKFAST') ? 'selected' : ''; ?>>Breakfast</option>
                                        <option value="LUNCH" <?php echo (isset($_POST['meal_type']) && $_POST['meal_type'] == 'LUNCH') ? 'selected' : ''; ?>>Lunch</option>
                                        <option value="DINNER" <?php echo (isset($_POST['meal_type']) && $_POST['meal_type'] == 'DINNER') ? 'selected' : ''; ?>>Dinner</option>
                                        <option value="SNACK" <?php echo (isset($_POST['meal_type']) && $_POST['meal_type'] == 'SNACK') ? 'selected' : ''; ?>>Snack</option>
                                        <option value="DESSERT" <?php echo (isset($_POST['meal_type']) && $_POST['meal_type'] == 'DESSERT') ? 'selected' : ''; ?>>Dessert</option>
                                        <option value="APPETIZER" <?php echo (isset($_POST['meal_type']) && $_POST['meal_type'] == 'APPETIZER') ? 'selected' : ''; ?>>Appetizer</option>
                                        <option value="DRINK" <?php echo (isset($_POST['meal_type']) && $_POST['meal_type'] == 'DRINK') ? 'selected' : ''; ?>>Drink</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label required-field">Recipe Image</label>
                                    <div class="upload-area" id="imageUploadArea">
                                        <div class="upload-content" id="imageUploadContent">
                                            <i class="bi bi-cloud-arrow-up text-muted"></i>
                                            <p class="mt-2 mb-1">Click to upload or drag and drop</p>
                                            <p class="small text-muted">JPEG, JPG, PNG, GIF, WEBP (Max 5MB)</p>
                                        </div>
                                        <input type="file" id="recipe_image" name="recipe_image" accept="image/*" class="d-none" required>
                                        <img id="imagePreview" class="image-preview" alt="Preview">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label required-field">Recipe Document</label>
                                    <div class="upload-area" id="docUploadArea">
                                        <div class="upload-content" id="docUploadContent">
                                            <i class="bi bi-file-earmark-arrow-up text-muted"></i>
                                            <p class="mt-2 mb-1">Click to upload or drag and drop</p>
                                            <p class="small text-muted">PDF, DOCX, TXT (Max 10MB)</p>
                                        </div>
                                        <input type="file" id="recipe_doc" name="recipe_doc" accept=".pdf,.docx,.txt" class="d-none" required>
                                        <div id="docPreview" class="small mt-2" style="display:none;">
                                            <i class="bi bi-file-earmark-text"></i> <span id="docFileName"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="tags" class="form-label">Tags (comma separated)</label>
                                    <input type="text" class="form-control" id="tags" name="tags"
                                           placeholder="e.g., vegetarian, quick, spicy"
                                           value="<?php echo isset($_POST['tags']) ? htmlspecialchars($_POST['tags']) : ''; ?>">
                                </div>
                            </div>
                        </div>
                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-primary btn-lg px-5">
                                <i class="bi bi-plus-circle"></i> Add Recipe
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Client-side validation for preparation time
        document.getElementById('recipeForm').addEventListener('submit', function(e) {
            const prepTime = document.getElementById('preparation_time').value;
            const regex = /^[0-9]+(\s*-\s*[0-9]+)?\s*(mins?|hours?|days?)$/i;
            
            if (!regex.test(prepTime)) {
                e.preventDefault();
                alert('Preparation time must be in format like "30 mins", "1-2 hours", or "1 day"');
                document.getElementById('preparation_time').focus();
            }
        });

        // Image upload handling
        const imageUploadArea = document.getElementById('imageUploadArea');
        const imageFileInput = document.getElementById('recipe_image');
        const imagePreview = document.getElementById('imagePreview');
        const imageUploadContent = document.getElementById('imageUploadContent');

        // Document upload handling
        const docUploadArea = document.getElementById('docUploadArea');
        const docFileInput = document.getElementById('recipe_doc');
        const docPreview = document.getElementById('docPreview');
        const docFileName = document.getElementById('docFileName');
        const docUploadContent = document.getElementById('docUploadContent');

        // Setup for image upload
        setupFileUpload(imageUploadArea, imageFileInput, imagePreview, imageUploadContent, true);
        // Setup for document upload
        setupFileUpload(docUploadArea, docFileInput, docPreview, docUploadContent, false);

        function setupFileUpload(uploadArea, fileInput, previewElement, uploadContent, isImage) {
            // Click on upload area
            uploadArea.addEventListener('click', () => fileInput.click());

            // Handle file selection
            fileInput.addEventListener('change', function(e) {
                if (this.files && this.files[0]) {
                    const file = this.files[0];
                    
                    if (isImage) {
                        // Validate image file
                        const imageInfo = new Image();
                        imageInfo.onload = function() {
                            previewElement.src = URL.createObjectURL(file);
                            previewElement.style.display = 'block';
                            uploadContent.style.display = 'none';
                        };
                        imageInfo.onerror = function() {
                            alert('Please select a valid image file (JPEG, PNG, GIF, WEBP)');
                            fileInput.value = '';
                            return;
                        };
                        imageInfo.src = URL.createObjectURL(file);
                    } else {
                        // Handle document file
                        docFileName.textContent = file.name;
                        docPreview.style.display = 'block';
                        uploadContent.style.display = 'none';
                    }
                }
            });

            // Handle drag and drop
            uploadArea.addEventListener('dragover', (e) => {
                e.preventDefault();
                uploadArea.classList.add('border-primary');
                uploadArea.style.backgroundColor = '#f8f9fa';
            });

            uploadArea.addEventListener('dragleave', () => {
                uploadArea.classList.remove('border-primary');
                uploadArea.style.backgroundColor = '';
            });

            uploadArea.addEventListener('drop', (e) => {
                e.preventDefault();
                uploadArea.classList.remove('border-primary');
                uploadArea.style.backgroundColor = '';
                
                if (e.dataTransfer.files.length) {
                    fileInput.files = e.dataTransfer.files;
                    const event = new Event('change');
                    fileInput.dispatchEvent(event);
                }
            });
        }

        // Reset form handling
        document.querySelector('form').addEventListener('reset', function() {
            imagePreview.src = '';
            imagePreview.style.display = 'none';
            imageUploadContent.style.display = 'block';
            
            docPreview.style.display = 'none';
            docUploadContent.style.display = 'block';
        });
    </script>
</body>
</html>