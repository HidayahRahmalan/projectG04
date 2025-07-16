<?php
include ('header.php');
$UserID = $_SESSION['UserID'];
$sql = "
    SELECT r.*, i.Image_Path
    FROM recipe r
    LEFT JOIN image i ON i.Image_ID = (
        SELECT MIN(Image_ID)
        FROM image
        WHERE Recipe_ID = r.Recipe_ID
    )
    WHERE r.User_ID = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $UserID);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Recipe </title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="../G04_01_Gastroverse/toastr.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="../G04_01_Gastroverse/toastr.min.js"></script>
     <script src="snap-dialog.min.js"></script>
    <link rel="stylesheet" href="snap-dialog.min.css">
  <style>
    .description-cell {
      max-width: 200px;
      overflow-wrap: break-word;
      word-wrap: break-word;
      white-space: normal;
    }

    html, body {
      height: 100%;
    }
    
    body {
      display: flex;
      flex-direction: column;
    }
    
    .main-content {
      flex: 1;
    }
    
    .footer {
      background-color: #1f2937;
      color: white;
      padding: 1rem;
      text-align: center;
      margin-top: auto;
    }

    .recipe-form-container {
            max-width: 800px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .form-header {
            background: linear-gradient(135deg, #ff6b6b, #ffa500);
            color: white;
            padding: 25px;
            text-align: center;
        }

        .form-header h2 {
            font-size: 2.2em;
            margin: 0 0 8px 0;
            font-weight: 300;
        }

        .form-header p {
            margin: 0;
            font-size: 1.1em;
            opacity: 0.9;
        }

        .form-content {
            padding: 35px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 25px;
        }

        .form-row .form-group {
            flex: 1;
            margin-bottom: 0;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 1.1em;
        }

        .required {
            color: #e74c3c;
        }

        .form-input {
            width: 100%;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1em;
            transition: all 0.3s ease;
            background: #fafafa;
        }

        .form-input:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-textarea {
            resize: vertical;
            min-height: 120px;
            font-family: inherit;
        }

        .form-select {
            cursor: pointer;
        }

        .image-upload-area {
            border: 3px dashed #ddd;
            border-radius: 12px;
            padding: 35px;
            text-align: center;
            background: #f8f9fa;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
        }

        .image-upload-area:hover {
            border-color: #667eea;
            background: #f0f2ff;
        }

        .image-upload-area.dragover {
            border-color: #667eea;
            background: #e8ecff;
            transform: scale(1.01);
        }

        .upload-icon {
            width: 70px;
            height: 70px;
            margin: 0 auto 15px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.8em;
        }

        .upload-text {
            font-size: 1.2em;
            color: #666;
            margin-bottom: 8px;
        }

        .upload-subtext {
            color: #999;
            font-size: 0.9em;
        }

        .hidden-input {
            display: none;
        }

        .image-previews {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .image-preview {
            position: relative;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .preview-image {
            width: 100%;
            height: 120px;
            object-fit: cover;
        }

        .remove-image {
            position: absolute;
            top: 5px;
            right: 5px;
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            cursor: pointer;
            font-size: 12px;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }

        .remove-image:hover {
            background: #c0392b;
            transform: scale(1.1);
        }

        .image-counter {
            text-align: center;
            margin-top: 15px;
            font-size: 0.9em;
            color: #666;
            padding: 8px 15px;
            background: #f0f0f0;
            border-radius: 20px;
            display: inline-block;
        }

        .form-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 35px;
            padding-top: 25px;
            border-top: 2px solid #f0f0f0;
        }

        .form-btn {
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-cancel {
            background: #95a5a6;
            color: white;
        }

        .btn-cancel:hover {
            background: #7f8c8d;
            transform: translateY(-1px);
        }

        .btn-submit {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }

        .btn-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 15px rgba(102, 126, 234, 0.3);
        }

        .upload-disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .upload-disabled:hover {
            border-color: #ddd;
            background: #f8f9fa;
            transform: none;
        }

      .formouter {
            margin-top: 20px;
            max-height: 80vh;
            overflow-y: auto;
            background-color: transparent;
        }

        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
                gap: 0;
            }

            .form-content {
                padding: 25px;
            }

            .form-header {
                padding: 20px;
            }

            .form-header h2 {
                font-size: 1.8em;
            }

            .image-previews {
                grid-template-columns: repeat(2, 1fr);
            }

            .form-buttons {
                flex-direction: column;
            }

            .form-btn {
                width: 100%;
            }
        }
  </style>
</head>
<body class="bg-gray-100 text-gray-800">

<main>
<div class="max-w-7xl mx-auto mt-10 p-6 bg-white shadow rounded-lg">
  <div class="flex justify-between items-center mb-4">
    <h1 class="text-2xl font-bold text-orange-600">🍽️ Recipe </h1>
    <button onclick="openModal()" class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">➕ Add Recipe</button>
  </div>

  <table class="min-w-full bg-white border border-gray-300 rounded overflow-hidden">
    <thead class="bg-gray-100 text-sm font-semibold text-gray-700">
      <tr>
        <th class="px-4 py-2 border">ID</th>
        <th class="px-4 py-2 border">Title</th>
        <th class="px-4 py-2 border">Description</th>
        <th class="px-4 py-2 border">Cuisine</th>
        <th class="px-4 py-2 border">Dietary</th>
        <th class="px-4 py-2 border">Image</th>
        <th class="px-4 py-2 border">Upload Date</th>
        <th class="px-4 py-2 border">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
          <td class="px-4 py-2 border"><?php echo $row['Recipe_ID']; ?></td>
          <td class="px-4 py-2 border"><?php echo htmlspecialchars($row['Recipe_Title']); ?></td>
          <td class="px-4 py-2 border description-cell"><?php echo htmlspecialchars($row['Recipe_Description']); ?></td>
          <td class="px-4 py-2 border"><?php echo htmlspecialchars($row['Recipe_CuisineType']); ?></td>
          <td class="px-4 py-2 border"><?php echo htmlspecialchars($row['Recipe_DietaryType']); ?></td>
          <td class="px-4 py-2 border">
            <?php if (!empty($row['Image_Path'])): ?>
              <img src="<?php echo htmlspecialchars($row['Image_Path']); ?>" alt="Recipe Image" class="w-16 h-16 object-cover cursor-pointer clickable-image">
            <?php else: ?>
              <img src="https://via.placeholder.com/64x64" alt="No Image" class="w-16 h-16 object-cover">
            <?php endif; ?>
          </td>
          <td class="px-4 py-2 border">
  <?php 
    $date = DateTime::createFromFormat('Y-m-d', $row['Recipe_UploadDate']);
    echo $date ? $date->format('d-m-Y') : htmlspecialchars($row['Recipe_UploadDate']);
  ?>
</td>
          <td class="px-4 py-2 border space-x-2">
           <a href="edit_recipe.php?id=<?php echo $row['Recipe_ID']; ?>" class="bg-yellow-400 hover:bg-yellow-500 text-white px-2 py-1 rounded inline-block">✏️</a>
           <a href="print_recipe_pdf.php?id=<?php echo $row['Recipe_ID']; ?>" class="bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded inline-block export-btn" data-id="<?php echo $row['Recipe_ID']; ?>" target="_blank">📄</a>
            <form action="delete_recipe.php" method="post" style="display:inline;">
                <input type="hidden" name="recipe_ID" value="<?php echo $row['Recipe_ID']; ?>">
                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded delete-btn">🗑️</button>
            </form>
          </td>
        </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr><td colspan="8" class="text-center py-4 text-gray-500">No recipes found.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
</main>

<!-- MODAL -->
<div id="recipeModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
  <div class="p-6 rounded shadow-lg w-full max-w-6xl formouter">
    <h2 id="modalTitle" class="text-xl font-bold mb-4">Add Recipe</h2>
    <div class="recipe-form-container">
        <div class="form-header">
            <h2>Create Recipe</h2>  
            <p>Share your delicious creation with the world</p>
        </div>
        
        <div class="form-content">
                  <form id="recipeForm" action="submit_recipe.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="recipe_id" id="recipe_id">
                
             <!-- Recipe Title -->
            <div class="form-group">
                <label for="recipe_title">
                    Recipe Title <span class="required">*</span>
                </label>
                
                <div style="display: flex; align-items: center;">
                    <input 
                        type="text" 
                        name="recipe_title" 
                        id="recipe_title" 
                        class="form-input"
                        required 
                        placeholder="Enter your amazing recipe title"
                        style="flex: 1;"
                    >

                    <select id="languageSelectTitle" style="margin-left: 10px; height: 38px;">
                        <option value="en-US">English (US)</option>
                        <option value="ms-MY">Bahasa Melayu (Malaysia)</option>
                    </select>

                    <button type="button" id="voiceBtnTitle" onclick="startVoiceRecognition('recipe_title','languageSelectTitle','voiceBtnTitle')" style="margin-left: 10px; height: 38px;">
                        🎤
                    </button>
                </div>
            </div>
                
                <!-- Recipe Description -->
                <div class="form-group">
                    <label for="recipe_description">
                        Recipe Description <span class="required">*</span>
                    </label>

                    <div style="display: flex; align-items: flex-start;">
                        <textarea 
                            name="recipe_description" 
                            id="recipe_description" 
                            class="form-input form-textarea"
                            required 
                            placeholder="Describe your recipe, ingredients, cooking instructions, and any special tips..."
                            style="flex: 1; height: 150px;"
                        ></textarea>

                        <select id="languageSelectDesc" style="margin-left: 10px; height: 38px;">
                            <option value="en-US">English (US)</option>
                            <option value="ms-MY">Bahasa Melayu (Malaysia)</option>
                        </select>

                        <button type="button" id="voiceBtnDesc" onclick="startVoiceRecognition('recipe_description','languageSelectDesc','voiceBtnDesc')" style="margin-left: 10px; height: 38px;">
                            🎤
                        </button>
                    </div>
                </div>
                
                <!-- Cuisine and Dietary Type Row -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="recipe_cuisine">
                            Cuisine Type <span class="required">*</span>
                        </label>
                        <select name="recipe_cuisine" id="recipe_cuisine" class="form-input form-select" required>
                            <option value="">-- Select Cuisine --</option>
                            <option value="Italian">🇮🇹 Italian</option>
                            <option value="Asian">🥢 Asian</option>
                            <option value="Mexican">🌮 Mexican</option>
                            <option value="Indian">🇮🇳 Indian</option>
                            <option value="American">🇺🇸 American</option>
                            <option value="French">🇫🇷 French</option>
                            <option value="Mediterranean">🫒 Mediterranean</option>
                            <option value="Thai">🇹🇭 Thai</option>
                            <option value="Chinese">🇨🇳 Chinese</option>
                            <option value="Japanese">🇯🇵 Japanese</option>
                            <option value="Other">🌍 Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="recipe_dietary">
                            Dietary Type <span class="required">*</span>
                        </label>
                        <select name="recipe_dietary" id="recipe_dietary" class="form-input form-select" required>
                            <option value="">-- Select Dietary Type --</option>
                            <option value="Vegan">🌱 Vegan</option>
                            <option value="Vegetarian">🥕 Vegetarian</option>
                            <option value="Gluten-Free">🌾 Gluten-Free</option>
                            <option value="Non-Vegetarian">🥩 Non-Vegetarian</option>
                            <option value="Keto">🥑 Keto</option>
                            <option value="Paleo">🦴 Paleo</option>
                            <option value="Dairy-Free">🥛 Dairy-Free</option>
                            <option value="Low-Carb">⚡ Low-Carb</option>
                        </select>
                    </div>
                </div>
                
                <!-- Image Upload Section -->
                <div class="form-group">
                    <label>Recipe Images (Up to 4 images, PNG/JPEG) <span class="required">*</span></label>

                    <div id="imageUploadArea" class="image-upload-area">
                        <div class="upload-icon">📷</div>
                        <div class="upload-text">Select up to 4 images below</div>
                        <div class="upload-subtext">PNG, JPEG up to 10MB each</div>
                        <input 
                            type="file" 
                            id="recipe_images" 
                            name="recipe_images[]" 
                            class="form-input"
                            multiple 
                            accept=".png,.jpg,.jpeg,image/png,image/jpeg"
                            required
                        >
                    <!-- Image Previews -->
                    <div id="imagePreview" class="flex flex-wrap mt-4 gap-4"></div>
                    </div>
                </div>

                <!-- Magnify Modal -->
                <div id="magnifyModal" class="fixed inset-0 bg-black bg-opacity-80 hidden items-center justify-center z-50">
                <img id="magnifyImage" src="" class="max-w-full max-h-full rounded-lg shadow-lg" alt="Magnified Image">
                </div>

                <div class="form-group">
                    <label for="step_count">
                        How many steps do you want for your recipe? <span class="required">*</span>
                    </label>
                    <input 
                        type="number" 
                        id="step_count" 
                        name="step_count" 
                        class="form-input" 
                        min="1" 
                        max="25" 
                        required 
                        placeholder="Enter number of steps (1–25)"
                    >
                </div>

                <div class="form-group">
                  <label for="steps">Recipe Steps <span class="required">*</span></label>
                  <div 
                      id="stepsContainer" 
                      style="max-height: 300px; overflow-y: auto; border: 1px solid #ccc; padding: 10px; border-radius: 8px;">
                      <!-- JS will inject steps here -->
                  </div>
              </div>
                
                <!-- Form Buttons -->
                <div class="form-buttons">
                    <button type="reset" onclick="closeModal()" class="form-btn btn-cancel">
                        Cancel
                    </button>
                    <button type="submit" class="form-btn btn-submit">
                        Save Recipe
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</div>

<div id="imageModal" class="fixed inset-0 bg-black bg-opacity-70 flex justify-center items-center z-50 hidden">
  <img id="modalImage" src="" class="max-w-full max-h-full rounded-lg shadow-lg" alt="Enlarged Recipe Image">
</div>

<footer class="footer">
        <p>&copy; 2025 GastroVerse. All rights reserved.</p>
</footer>

<script>

// Image preview with magnify feature
document.getElementById('recipe_images').addEventListener('change', function(event) {
    const previewContainer = document.getElementById('imagePreview');
    previewContainer.innerHTML = '';  
    const files = event.target.files;

    if (files.length > 4) {
         toastr.error('You can only add up to 4 image.');
        event.target.value = '';  
        return;
    }

    Array.from(files).forEach(file => {
        if (!file.type.startsWith('image/')) return;

        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.classList.add('w-32', 'h-32', 'object-cover', 'rounded', 'shadow', 'cursor-pointer', 'hover:scale-105', 'transition-transform', 'duration-200');
            
            img.addEventListener('click', function() {
                document.getElementById('magnifyImage').src = e.target.result;
                document.getElementById('magnifyModal').classList.remove('hidden');
                document.getElementById('magnifyModal').classList.add('flex');
            });

            previewContainer.appendChild(img);
        }
        reader.readAsDataURL(file);
    });
});

document.getElementById('magnifyModal').addEventListener('click', function() {
    this.classList.add('hidden');
    this.classList.remove('flex');
    document.getElementById('magnifyImage').src = '';
});


document.querySelectorAll('.delete-btn').forEach(function (btn) {
    btn.addEventListener('click', function (event) {
      event.preventDefault(); 

      const form = btn.closest('form'); 

      SnapDialog().alert('Confirm Deletion', 'Are you sure you want to delete this recipe?', {
        enableConfirm: true,
        onConfirm: function () {
          form.submit(); 
        },
        enableCancel: true
      });
    });
  });

    document.querySelectorAll('.export-btn').forEach(function (btn) {
    btn.addEventListener('click', function (event) {
        event.preventDefault(); 

        const pdfUrl = btn.getAttribute('href');

        SnapDialog().alert('Export Recipe to PDF', 'Are you sure you want to generate the PDF for this recipe?', {
        enableConfirm: true,
        onConfirm: function () {
            window.open(pdfUrl, '_blank');
        },
        enableCancel: true
        });
    });
    });

function openModal() {
  document.getElementById('recipeModal').classList.remove('hidden');
  document.getElementById('recipeModal').classList.add('flex');
}

function closeModal() {
  document.getElementById('recipeModal').classList.add('hidden');
  document.getElementById('recipeModal').classList.remove('flex');
}

function editRecipe(data) {
  openModal();
  document.getElementById('modalTitle').textContent = "Edit Recipe";
  document.getElementById('recipe_id').value = data.Recipe_ID;
  document.getElementById('recipe_title').value = data.Recipe_Title;
  document.getElementById('recipe_description').value = data.Recipe_Description;
  document.getElementById('recipe_cuisine').value = data.Recipe_CuisineType;
  document.getElementById('recipe_dietary').value = data.Recipe_DietaryType;
}

// step part
const stepCountInput = document.getElementById('step_count');
const stepsContainer = document.getElementById('stepsContainer');

stepCountInput.addEventListener('input', () => {
    const count = parseInt(stepCountInput.value);
    const stepTotal = Math.max(1, Math.min(25, count || 0));
    stepsContainer.innerHTML = '';

    for (let i = 1; i <= stepTotal; i++) {
        const stepDiv = document.createElement('div');
        stepDiv.className = 'form-group';
        stepDiv.style.border = '1px dashed #aaa';
        stepDiv.style.padding = '10px';
        stepDiv.style.marginBottom = '15px';
        stepDiv.style.borderRadius = '10px';

        // 👇 INSTRUCTIONS/EXAMPLES ADDED BELOW EACH FILE INPUT
        stepDiv.innerHTML = `
            <label for="step_instruction_${i}">Step ${i} Instruction <span class="required">*</span></label>
            <div style="display: flex; align-items: flex-start; margin-bottom: 10px;">
                <textarea 
                    name="step_instruction_${i}" 
                    id="step_instruction_${i}" 
                    class="form-input form-textarea" 
                    required
                    placeholder="Describe what to do in step ${i}..."
                    style="flex: 1; height: 100px;"></textarea>
                <select id="languageSelect_step_${i}" style="margin-left: 10px; height: 38px;">
                    <option value="en-US">English (US)</option>
                    <option value="ms-MY">Bahasa Melayu (Malaysia)</option>
                </select>
                <button type="button" id="voiceBtn_step_${i}" 
                    onclick="startVoiceRecognition('step_instruction_${i}','languageSelect_step_${i}','voiceBtn_step_${i}')" 
                    style="margin-left: 10px; height: 38px;">🎤</button>
            </div>
            <label for="step_image_${i}">Step ${i} Image (JPEG/PNG only)</label>
            <input type="file" name="step_image_${i}" id="step_image_${i}" class="form-input" accept=".jpg,.jpeg,.png,image/jpeg,image/png">
            <div style="font-size:0.93em; color:#f97316; margin-bottom:6px;">E.g. Show the batter texture or how the dish looks at this step. <b>(Optional)</b></div>
            <div id="step_image_preview_${i}" class="mt-2"></div>
            <label for="step_video_${i}">Step ${i} Video (MP4 only)</label>
            <input type="file" name="step_video_${i}" id="step_video_${i}" class="form-input" accept="video/mp4">
            <div style="font-size:0.93em; color:#38bdf8; margin-bottom:6px;">E.g. Record yourself stirring, frying, or doing a tricky step. <b>(Optional)</b></div>
            <div id="step_video_preview_${i}" class="mt-2"></div>
            <label for="step_audio_${i}">Step ${i} Audio (MP3 only)</label>
            <input type="file" name="step_audio_${i}" id="step_audio_${i}" class="form-input" accept="audio/mpeg,audio/mp3">
            <div style="font-size:0.93em; color:#6366f1; margin-bottom:6px;">E.g. Read out the step instructions, or share a tip. <b>(Optional)</b></div>
            <div id="step_audio_preview_${i}" class="mt-2"></div>
        `;
        stepsContainer.appendChild(stepDiv);

        // Add event listeners for preview
        addPreviewListener(i);
    }
});

function addPreviewListener(stepNumber) {
    // Image preview
    document.getElementById(`step_image_${stepNumber}`).addEventListener('change', function(e) {
        const file = e.target.files[0];
        const previewDiv = document.getElementById(`step_image_preview_${stepNumber}`);
        previewDiv.innerHTML = '';

        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(ev) {
                const img = document.createElement('img');
                img.src = ev.target.result;
                img.classList.add('w-32', 'h-32', 'object-cover', 'rounded', 'cursor-pointer', 'hover:scale-105', 'transition-transform', 'duration-200');
                img.style.border = "2px solid #f97316"; // orange border
                img.style.padding = "3px";
                img.style.background = "#fff7ec";

                img.addEventListener('click', function() {
                    document.getElementById('magnifyImage').src = ev.target.result;
                    document.getElementById('magnifyModal').classList.remove('hidden');
                    document.getElementById('magnifyModal').classList.add('flex');
                });

                previewDiv.appendChild(img);
            }
            reader.readAsDataURL(file);
        }
    });

    // Video preview
    document.getElementById(`step_video_${stepNumber}`).addEventListener('change', function(e) {
        const file = e.target.files[0];
        const previewDiv = document.getElementById(`step_video_preview_${stepNumber}`);
        previewDiv.innerHTML = '';

        if (file && file.type.startsWith('video/')) {
            const video = document.createElement('video');
            video.controls = true;
            video.src = URL.createObjectURL(file);
            video.style.maxWidth = '420px';
            video.style.border = "2px solid #38bdf8"; // blue border
            video.style.borderRadius = "9px";
            video.style.background = "#e0f5fd";
            video.style.padding = "3px";
            previewDiv.appendChild(video);
        }
    });

    // Audio preview
    document.getElementById(`step_audio_${stepNumber}`).addEventListener('change', function(e) {
        const file = e.target.files[0];
        const previewDiv = document.getElementById(`step_audio_preview_${stepNumber}`);
        previewDiv.innerHTML = '';

        if (file && file.type.startsWith('audio/')) {
            const audio = document.createElement('audio');
            audio.controls = true;
            audio.src = URL.createObjectURL(file);
            audio.style.display = "block";
            audio.style.width = "100%";
            audio.style.maxWidth = "320px";
            audio.style.border = "2px solid #6366f1"; // indigo border
            audio.style.borderRadius = "9px";
            audio.style.background = "#ede9fe";
            audio.style.padding = "3px";
            previewDiv.appendChild(audio);
        }
    });
}
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

                var success_insert_recipe = sessionStorage.getItem('success_insert_recipe');  
                console.log("Message from sessionStorage:", success_insert_recipe); 
                if (success_insert_recipe) {
                    toastr.success(success_insert_recipe); 
                    sessionStorage.removeItem('success_insert_recipe');
                }

                var unsuccess_insert_recipe = sessionStorage.getItem('unsuccess_insert_recipe');  
                console.log("Message from sessionStorage:", unsuccess_insert_recipe); 
                if (unsuccess_insert_recipe) {
                    toastr.error(unsuccess_insert_recipe); 
                    sessionStorage.removeItem('unsuccess_insert_recipe');
                }

                 var success_edit_recipe = sessionStorage.getItem('success_edit_recipe');  
                console.log("Message from sessionStorage:", success_edit_recipe); 
                if (success_edit_recipe) {
                    toastr.success(success_edit_recipe); 
                    sessionStorage.removeItem('success_edit_recipe');
                }

                var unsuccess_edit_recipe = sessionStorage.getItem('unsuccess_edit_recipe');  
                console.log("Message from sessionStorage:", unsuccess_edit_recipe); 
                if (unsuccess_edit_recipe) {
                    toastr.error(unsuccess_edit_recipe); 
                    sessionStorage.removeItem('unsuccess_edit_recipe');
                }

                 var deletee = sessionStorage.getItem('delete');  
                console.log("Message from sessionStorage:", deletee); 
                if (deletee) {
                    toastr.success(deletee); 
                    sessionStorage.removeItem('delete');
                }

                var image_upload = sessionStorage.getItem('image_upload');  
                console.log("Message from sessionStorage:", image_upload); 
                if (image_upload) {
                    toastr.error(image_upload); 
                    sessionStorage.removeItem('image_upload');
                }
            });

    
      document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('imageModal');
        const modalImg = document.getElementById('modalImage');

        // bigger size image when clicked
        document.querySelectorAll('.clickable-image').forEach(img => {
        img.addEventListener('click', () => {
            modalImg.src = img.src;
            modal.classList.remove('hidden');
        });
        });

        // outside click image
        modal.addEventListener('click', () => {
        modal.classList.add('hidden');
        modalImg.src = '';
        });
    });


let recognition;
let recognizing = false;

function startVoiceRecognition(fieldId, langSelectId, buttonId) {
    if (!('webkitSpeechRecognition' in window)) {
        alert("Your browser doesn't support Speech Recognition. Please use Google Chrome.");
        return;
    }

    if (recognizing) {
        recognition.stop();
        return;
    }

    recognition = new webkitSpeechRecognition();
    recognition.continuous = false;
    recognition.interimResults = false;

    const selectedLang = document.getElementById(langSelectId).value;
    recognition.lang = selectedLang;

    recognition.start();
    recognizing = true;
    document.getElementById(buttonId).innerText = "🛑";

    recognition.onresult = function(event) {
        const transcript = event.results[0][0].transcript;
        document.getElementById(fieldId).value = transcript;
    };

    recognition.onerror = function(event) {
        console.error("Recognition error: ", event.error);
        alert("Error occurred: " + event.error);
    };

    recognition.onend = function() {
        recognizing = false;
        document.getElementById(buttonId).innerText = "🎤";
    };
}

</script>

</body>
</html>