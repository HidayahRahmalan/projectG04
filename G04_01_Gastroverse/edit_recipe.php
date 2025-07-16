<?php
session_start();
include('header.php');
// Use lowercase 'user_id' for session variable
$UserID = $_SESSION['UserID'];

if (!isset($_GET['id'])) {
    echo "Recipe ID is missing.";
    exit;
}

$RecipeID = intval($_GET['id']);

// Fetch recipe details
$sql = "SELECT * FROM recipe WHERE Recipe_ID = ? AND User_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $RecipeID, $UserID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Recipe not found or unauthorized access.";
    exit;
}

$row = $result->fetch_assoc();

// Fetch images (max 4)
$sqlImages = "SELECT * FROM image WHERE Recipe_ID = ? ORDER BY Image_ID LIMIT 4";
$stmtImages = $conn->prepare($sqlImages);
$stmtImages->bind_param("i", $RecipeID);
$stmtImages->execute();
$imagesResult = $stmtImages->get_result();

$images = [];
while ($img = $imagesResult->fetch_assoc()) {
    $images[] = $img;
}

$currentImageCount = count($images);

// step
$sqlSteps = "SELECT * FROM step WHERE Recipe_ID = ? ORDER BY Step_Number ASC";
$stmtSteps = $conn->prepare($sqlSteps);
$stmtSteps->bind_param("i", $RecipeID);
$stmtSteps->execute();
$stepsResult = $stmtSteps->get_result();

$steps = [];
while ($step = $stepsResult->fetch_assoc()) {
    $steps[] = $step;
}
$stepCount = count($steps);
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
     html, body {
      height: 100%;
    }
    
    body {
      display: flex;
      flex-direction: column;
    }

    .footer {
      background-color: #1f2937;
      color: white;
      padding: 1rem;
      text-align: center;
      margin-top: 40px;
    }

  /* Image card container  */
  .image-block {
    border: 1px solid #ddd;
    padding: 10px;
    margin-bottom: 1rem;
    border-radius: 8px;
    box-shadow: 0 2px 6px rgb(0 0 0 / 0.1);
    transition: box-shadow 0.3s ease;
    background: white;
    display: flex;
    align-items: center;
    gap: 15px;
  }
  .image-block:hover {
    box-shadow: 0 6px 12px rgb(0 0 0 / 0.15);
  }
  .image-block img {
    width: 120px;
    height: 120px;
    object-fit: cover;
    border-radius: 8px;
    flex-shrink: 0;
    border: 1px solid #ccc;
  }
  .image-controls {
    flex-grow: 1;
  }
  .image-controls label {
    font-weight: 600;
    display: block;
    margin-bottom: 6px;
  }
  .image-controls input[type="file"] {
    border: 1px solid #ccc;
    border-radius: 6px;
    padding: 5px;
    width: 100%;
  }
  .delete-checkbox {
    margin-top: 8px;
    display: flex;
    align-items: center;
  }
  .delete-checkbox input[type="checkbox"] {
    margin-right: 6px;
    cursor: pointer;
  }
  .delete-checkbox label {
    color: #dc2626;
    cursor: pointer;
    font-weight: 600;
  }
  /* Add new images container style */
  #newImagesContainer > div {
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 10px;
  }
  #newImagesContainer input[type="file"] {
    flex-grow: 1;
    border: 1px solid #ccc;
    border-radius: 6px;
    padding: 6px;
  }
  .removeNewImageBtn {
    background-color: #dc2626; 
    padding: 5px 10px;
    border-radius: 6px;
    color: white;
    border: none;
    cursor: pointer;
    transition: background-color 0.2s ease;
  }
  .removeNewImageBtn:hover {
    background-color: #b91c1c; 
  }

  </style>
    
</head>
<body class="bg-gray-100 text-gray-800">

<main>
<div class="max-w-7xl mx-auto mt-10 bg-white p-6 rounded shadow" class="outer_box">
  <h2 class="text-2xl font-bold mb-4 text-orange-600">✏️ Edit Recipe</h2>

  <form action="update_recipe.php" method="post" enctype="multipart/form-data" id="editRecipeForm">
    <input type="hidden" name="recipe_id" value="<?php echo $row['Recipe_ID']; ?>">

    <label class="block mb-2 font-semibold">Title</label>
    <div style="display: flex; align-items: center;">
    <input type="text" id="recipe_title" name="title" class="w-full p-2 border rounded mb-4"
          value="<?php echo htmlspecialchars($row['Recipe_Title']); ?>" required>

    <select id="languageSelectTitle" style="margin-left: 10px; height: 38px;">
        <option value="en-US">English (US)</option>     
        <option value="ms-MY">Bahasa Melayu (Malaysia)</option>                  
    </select>               

    <button type="button" id="voiceBtnTitle"
            onclick="startVoiceRecognition('recipe_title','languageSelectTitle','voiceBtnTitle')"
            style="margin-left: 10px; height: 38px;">
        🎤
    </button>
    </div>                                                                             

    <label class="block mb-2 font-semibold">Description</label>
    <div style="display: flex; align-items: center;">
    <textarea id="recipe_description" name="description" class="w-full p-2 border rounded mb-4" rows="4" required>
    <?php echo htmlspecialchars($row['Recipe_Description']); ?>
    </textarea>

    <div class="flex items-center mb-4">
        <select id="languageSelectDescription" style="margin-right: 10px; height: 38px;">
            <option value="en-US">English (US)</option>     
            <option value="ms-MY">Bahasa Melayu (Malaysia)</option>                  
        </select>

        <button type="button" id="voiceBtnDescription"
            onclick="startVoiceRecognition('recipe_description','languageSelectDescription','voiceBtnDescription')"
            style="height: 38px;">
            🎤
        </button>
    </div>
    </div>

    <div class="flex gap-4 mb-4">
      <div class="flex-1">
          <label class="block mb-2 font-semibold" for="cuisine">Cuisine Type</label>
          <select id="cuisine" name="cuisine" class="w-full p-2 border rounded" required>
          <option value="">-- Select Cuisine --</option>
          <?php
          $cuisines = [
              "Italian" => "🇮🇹 Italian",
              "Asian" => "🥢 Asian",
              "Mexican" => "🌮 Mexican",
              "Indian" => "🇮🇳 Indian",
              "American" => "🇺🇸 American",
              "French" => "🇫🇷 French",
              "Mediterranean" => "🫒 Mediterranean",
              "Thai" => "🇹🇭 Thai",
              "Chinese" => "🇨🇳 Chinese",
              "Japanese" => "🇯🇵 Japanese",
              "Other" => "🌍 Other"
          ];
          $selectedCuisine = $row['Recipe_CuisineType'];

          foreach ($cuisines as $value => $label) {
              $selected = ($value == $selectedCuisine) ? 'selected' : '';
              echo "<option value=\"$value\" $selected>$label</option>";
          }
          ?>
          </select>
      </div>


      <div class="flex-1">
          <label class="block mb-2 font-semibold" for="dietary">Dietary Type <span class="text-red-500">*</span></label>
          <select id="dietary" name="dietary" class="w-full p-2 border rounded" required>
          <option value="">-- Select Dietary Type --</option>
          <?php
          $dietaryTypes = [
              "Vegan" => "🌱 Vegan",
              "Vegetarian" => "🥕 Vegetarian",
              "Gluten-Free" => "🌾 Gluten-Free",
              "Non-Vegetarian" => "🥩 Non-Vegetarian",
              "Keto" => "🥑 Keto",
              "Paleo" => "🦴 Paleo",
              "Dairy-Free" => "🥛 Dairy-Free",
              "Low-Carb" => "⚡ Low-Carb"
          ];
          $selectedDietary = $row['Recipe_DietaryType'];

          foreach ($dietaryTypes as $value => $label) {
              $selected = ($value == $selectedDietary) ? 'selected' : '';
              echo "<option value=\"$value\" $selected>$label</option>";
          }
          ?>
          </select>
      </div>
    </div>


    <label class="block mb-2 font-semibold">Existing Images</label>
    <div id="existingImages" class="mb-6">
    <?php if ($currentImageCount === 0) {
        echo "<p class='text-gray-600'>No images uploaded yet.</p>";
    } else {
        foreach ($images as $index => $img) {
            echo '<div class="image-block">';
            echo '<img src="' . htmlspecialchars($img['Image_Path']) . '" alt="Recipe Image">';
            echo '<div class="image-controls">';
            echo '<input type="hidden" name="existing_image_id[]" value="' . $img['Image_ID'] . '">';
            echo '<label for="replace_image_' . $index . '">Replace Image</label>';
            echo '<input type="file" name="replace_image_' . $img['Image_ID'] . '" id="replace_image_' . $index . '" accept="image/*">';
            
 
            if ($currentImageCount > 1) {
                echo '<a href="delete_image.php?image_id=' . $img['Image_ID'] . '&recipe_id=' . $RecipeID . '" ';
                echo 'class="delete-image-link mt-3 inline-block px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700" ';
                echo 'data-href="delete_image.php?image_id=' . $img['Image_ID'] . '&recipe_id=' . $RecipeID . '">';
                echo 'Delete Image</a>';
            } else {
                echo '<button class="mt-3 inline-block px-4 py-2 bg-gray-400 text-white rounded cursor-not-allowed" disabled>Delete Image</button>';
            }

            echo '</div>'; 
            echo '</div>'; 
        }
    }
    ?>
    </div>


   <label class="block mb-2 font-semibold">Add New Images (Max 4 total images)</label>
    <div id="newImagesContainer" class="mb-4"></div>

    <button type="button" id="addImageBtn" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 mb-6">+ Add Image</button>



<label class="block mb-2 font-semibold mt-8">Steps</label>
<div id="stepsContainer">
    <?php if ($stepCount === 0): ?>
        <p class="text-gray-600 mb-4">No steps added yet.</p>
    <?php else: ?>
        <?php foreach ($steps as $index => $step): ?>
            <div class="step-block mb-6 p-4 border rounded shadow bg-white" data-step-number="<?= $step['Step_Number'] ?>">
                <input type="hidden" name="step_id_<?= $step['Step_Number'] ?>" value="<?= $step['Step_ID'] ?>">
                <label class="font-semibold mb-1 block">Step <?= $step['Step_Number'] ?></label>
                
                <!-- Step Instruction -->
                <textarea name="step_instruction_<?= $step['Step_Number'] ?>" rows="3" class="w-full p-2 border rounded mb-4" required><?= htmlspecialchars($step['Step_Instruction']) ?></textarea>


                <div class="flex flex-wrap justify-center gap-8 mb-4">
                    <!-- Image -->
                    <div class="flex flex-col items-center border p-3 rounded shadow">
                        <label class="block text-sm font-semibold mb-2">Image</label>
                        <?php if (!empty($step['Step_ImagePath'])): ?>
                            <img src="<?= htmlspecialchars($step['Step_ImagePath']) ?>" alt="Step Image" style="max-width: 160px; border-radius: 6px; border: 1px solid #ccc;" class="mb-2">
                            <div class="flex items-center gap-2">
                                <input type="file" name="step_image_<?= $step['Step_Number'] ?>" accept="image/*">
                                <a href="delete_step_media.php?step_id=<?= $step['Step_ID'] ?>&media_type=image&recipe_id=<?= $RecipeID ?>"
                                  class="delete-step-media-link mt-3 inline-block px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700"
                                  data-href="delete_step_media.php?step_id=<?= $step['Step_ID'] ?>&media_type=image&recipe_id=<?= $RecipeID ?>">
                                    Delete Image
                                </a>
                            </div>
                        <?php else: ?>
                            <p class="text-gray-500 mb-2">No image uploaded.</p>
                            <input type="file" name="step_image_<?= $step['Step_Number'] ?>" accept="image/*">
                        <?php endif; ?>
                    </div>

                    <!-- Video -->
                    <div class="flex flex-col items-center border p-3 rounded shadow">
                        <label class="block text-sm font-semibold mb-2">Video</label>
                        <?php if (!empty($step['Step_VideoPath'])): ?>
                            <video width="320" controls class="mb-2 rounded border">
                                <source src="<?= htmlspecialchars($step['Step_VideoPath']) ?>" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                            <div class="flex items-center gap-2">
                                <input type="file" name="step_video_<?= $step['Step_Number'] ?>" accept="video/*">
                                <a href="delete_step_media.php?step_id=<?= $step['Step_ID'] ?>&media_type=video&recipe_id=<?= $RecipeID ?>"
                                  class="delete-step-video-link mt-3 inline-block px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700"
                                  data-href="delete_step_media.php?step_id=<?= $step['Step_ID'] ?>&media_type=video&recipe_id=<?= $RecipeID ?>">
                                    Delete Video
                                </a>
                            </div>
                        <?php else: ?>
                            <p class="text-gray-500 mb-2">No video uploaded.</p>
                            <input type="file" name="step_video_<?= $step['Step_Number'] ?>" accept="video/*">
                        <?php endif; ?>
                    </div>

                    <!-- Audio -->
                    <div class="flex flex-col items-center border p-3 rounded shadow">
                        <label class="block text-sm font-semibold mb-2">Audio</label>
                        <?php if (!empty($step['Step_AudioPath'])): ?>
                            <audio controls class="mb-2 rounded border">
                                <source src="<?= htmlspecialchars($step['Step_AudioPath']) ?>" type="audio/mpeg">
                                Your browser does not support the audio element.
                            </audio>
                            <div class="flex items-center gap-2">
                                <input type="file" name="step_audio_<?= $step['Step_Number'] ?>" accept="audio/*">
                                <a href="delete_step_media.php?step_id=<?= $step['Step_ID'] ?>&media_type=audio&recipe_id=<?= $RecipeID ?>"
                                  class="delete-step-audio-link mt-3 inline-block px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700"
                                  data-href="delete_step_media.php?step_id=<?= $step['Step_ID'] ?>&media_type=audio&recipe_id=<?= $RecipeID ?>">
                                    Delete Audio
                                </a>
                            </div>
                        <?php else: ?>
                            <p class="text-gray-500 mb-2">No audio uploaded.</p>
                            <input type="file" name="step_audio_<?= $step['Step_Number'] ?>" accept="audio/*">
                        <?php endif; ?>
                    </div>
                </div>


              <?php if ($stepCount > 1 && $index === $stepCount - 1): ?>
                  <div class="mt-3 text-center">
                      <?php 
                          echo '<a href="delete_step.php?step_id=' . $step['Step_ID'] . '&recipe_id=' . $RecipeID . '" ';
                          echo 'class="delete-step-link px-3 py-1 text-white bg-red-600 rounded hover:bg-red-700" ';
                          echo 'data-href="delete_step.php?step_id=' . $step['Step_ID'] . '&recipe_id=' . $RecipeID . '">';
                          echo 'Delete Step ' . $step['Step_Number'] . '</a>';
                      ?>
                  </div>
              <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<button type="button" id="addStepBtn" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">+ Add Step</button>

    <div class="flex justify-between mt-6">
      <a href="recipe.php" class="px-4 py-2 bg-gray-400 text-white rounded hover:bg-gray-500">← Cancel</a>
      <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">💾 Update</button>
    </div>
  </form>
</div>
</main>


<footer class="footer">
        <p>&copy; 2025 GastroVerse. All rights reserved.</p>
</footer>

<script>
  let recognition;
  let recognizing = false;

  function startVoiceRecognition(fieldId, langSelectId, buttonId) {
      if (!('webkitSpeechRecognition' in window)) {
          alert("Your browser doesn't support Speech Recognition. Please use Google Chrome.");
          return;
      }

      // Stop if already recognizing
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
      document.getElementById(buttonId).innerText = "🛑 Listening...";

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


  const maxImages = 4;
  let currentTotalImages = <?php echo $currentImageCount; ?>; // current existing images count
  const newImagesContainer = document.getElementById('newImagesContainer');
  const addImageBtn = document.getElementById('addImageBtn');

  function createNewImageInput(index) {
    const div = document.createElement('div');
    div.className = 'image-block';

    // Image preview
    const img = document.createElement('img');
    img.src = '';
    img.alt = 'New Image Preview';
    img.style.display = 'none'; // hide initially
    div.appendChild(img);

    // Controls container
    const controls = document.createElement('div');
    controls.className = 'image-controls';

    // File input
    const fileInput = document.createElement('input');
    fileInput.type = 'file';
    fileInput.name = `new_image_${index}`;
    fileInput.accept = 'image/*';
    fileInput.required = true;
    controls.appendChild(fileInput);

    // Remove button
    const removeBtn = document.createElement('button');
    removeBtn.type = 'button';
    removeBtn.className = 'removeNewImageBtn';
    removeBtn.textContent = 'Remove';
    controls.appendChild(removeBtn);

    div.appendChild(controls);

    // File input change event for preview
    fileInput.addEventListener('change', function () {
      if (fileInput.files && fileInput.files[0]) {
        const reader = new FileReader();
        reader.onload = function (e) {
          img.src = e.target.result;
          img.style.display = 'block';
        }
        reader.readAsDataURL(fileInput.files[0]);
      } else {
        img.src = '';
        img.style.display = 'none';
      }
    });

    // Remove button event
    removeBtn.addEventListener('click', () => {
      div.remove();
      currentTotalImages--;
      updateAddButtonState();
    });

    return div;
  }

  function updateAddButtonState() {
    if (currentTotalImages >= maxImages) {
      addImageBtn.disabled = true;
      addImageBtn.classList.add('opacity-50', 'cursor-not-allowed');
    } else {
      addImageBtn.disabled = false;
      addImageBtn.classList.remove('opacity-50', 'cursor-not-allowed');
    }
  }

  addImageBtn.addEventListener('click', () => {
    if (currentTotalImages < maxImages) {
      const newIndex = newImagesContainer.children.length;
      const newInput = createNewImageInput(newIndex);
      newImagesContainer.appendChild(newInput);
      currentTotalImages++;
      updateAddButtonState();
    }
  });

  // Initialize button state on page load
  updateAddButtonState();

  // step part niii
      const maxSteps = 25;
      const stepsContainer = document.getElementById('stepsContainer');
      const addStepBtn = document.getElementById('addStepBtn');

      let currentStepCount = stepsContainer.querySelectorAll('.step-block').length;

      // Function to create a new step block (for newly added steps)
      function createNewStepBlock(stepNumber) {
          const div = document.createElement('div');
          div.className = 'step-block mb-6 p-4 border rounded shadow bg-white';
          div.dataset.stepNumber = stepNumber;

          div.innerHTML = `
              <input type="hidden" name="new_step_id_${stepNumber}" value="new">
              <label class="font-semibold mb-1 block">Step ${stepNumber}</label>
              <textarea name="new_step_instruction_${stepNumber}" rows="3" class="w-full p-2 border rounded mb-4" required></textarea>
              
              <div class="flex flex-col md:flex-row gap-6 justify-center items-start">
                  
                  <div class="w-full md:w-1/3 text-center p-4 border rounded shadow bg-gray-50">
                      <label for="new_step_image_${stepNumber}" class="block font-semibold mb-2">Upload Image</label>
                      <input type="file" name="new_step_image_${stepNumber}" id="new_step_image_${stepNumber}" accept="image/*" class="mb-2">
                      <div id="preview_image_${stepNumber}" class="mt-2"></div>
                  </div>

                  <div class="w-full md:w-1/3 text-center p-4 border rounded shadow bg-gray-50">
                      <label for="new_step_video_${stepNumber}" class="block font-semibold mb-2">Upload Video</label>
                      <input type="file" name="new_step_video_${stepNumber}" id="new_step_video_${stepNumber}" accept="video/*" class="mb-2">
                      <div id="preview_video_${stepNumber}" class="mt-2"></div>
                  </div>

                  <div class="w-full md:w-1/3 text-center p-4 border rounded shadow bg-gray-50">
                      <label for="new_step_audio_${stepNumber}" class="block font-semibold mb-2">Upload Audio</label>
                      <input type="file" name="new_step_audio_${stepNumber}" id="new_step_audio_${stepNumber}" accept="audio/*" class="mb-2">
                      <div id="preview_audio_${stepNumber}" class="mt-2"></div>
                  </div>

              </div>

              <div class="text-center">
                  <button type="button" class="removeNewStepBtn mt-5 px-5 py-2 bg-red-600 text-white rounded shadow">Remove Step</button>
              </div>
          `;

          setPreviewListeners(div, stepNumber);
          return div;
      }

      // Function to attach preview listeners for multimedia
      function setPreviewListeners(stepBlock, stepNumber) {
          // Image preview
          const imgInput = stepBlock.querySelector(`#new_step_image_${stepNumber}`);
          const imgPreview = stepBlock.querySelector(`#preview_image_${stepNumber}`);
          imgInput.addEventListener('change', function () {
              imgPreview.innerHTML = "";
              if (this.files && this.files[0]) {
                  const reader = new FileReader();
                  reader.onload = function (e) {
                      imgPreview.innerHTML = `<img src="${e.target.result}" alt="Preview Image" style="max-width: 100%; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.2);">`;
                  }
                  reader.readAsDataURL(this.files[0]);
              }
          });

          // Video preview
          const videoInput = stepBlock.querySelector(`#new_step_video_${stepNumber}`);
          const videoPreview = stepBlock.querySelector(`#preview_video_${stepNumber}`);
          videoInput.addEventListener('change', function () {
              videoPreview.innerHTML = "";
              if (this.files && this.files[0]) {
                  const videoURL = URL.createObjectURL(this.files[0]);
                  videoPreview.innerHTML = `<video controls style="max-width: 100%; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.2);"><source src="${videoURL}" type="video/mp4"></video>`;
              }
          });

          // Audio preview
          const audioInput = stepBlock.querySelector(`#new_step_audio_${stepNumber}`);
          const audioPreview = stepBlock.querySelector(`#preview_audio_${stepNumber}`);
          audioInput.addEventListener('change', function () {
              audioPreview.innerHTML = "";
              if (this.files && this.files[0]) {
                  const audioURL = URL.createObjectURL(this.files[0]);
                  audioPreview.innerHTML = `<audio controls style="width: 100%"><source src="${audioURL}" type="audio/mpeg"></audio>`;
              }
          });
      }

      // Function to update the Delete button visibility for existing steps
      function updateDeleteButtons() {
          const existingSteps = stepsContainer.querySelectorAll('.step-block.existing-step');
          existingSteps.forEach(btnStep => {
              const deleteBtn = btnStep.querySelector('.deleteStepBtn');
              if (!deleteBtn) return;

              if (btnStep === existingSteps[existingSteps.length - 1]) {
                  deleteBtn.style.display = 'inline-block';
              } else {
                  deleteBtn.style.display = 'none';
              }
          });

          addStepBtn.disabled = currentStepCount >= maxSteps;
          if (addStepBtn.disabled) {
              addStepBtn.classList.add('opacity-50', 'cursor-not-allowed');
          } else {
              addStepBtn.classList.remove('opacity-50', 'cursor-not-allowed');
          }
      }

      // function to makesure that the delete button is handled properly
            function updateRemoveNewStepButtons() {
          const allNewSteps = stepsContainer.querySelectorAll('.step-block:not(.existing-step)');
          allNewSteps.forEach((stepBlock, index) => {
              const removeBtn = stepBlock.querySelector('.removeNewStepBtn');
              if (removeBtn) {
                  const isLastNew = index === allNewSteps.length - 1;
                  // Always show the button but only enable the last one
                  removeBtn.style.display = 'inline-block';
                  removeBtn.disabled = !isLastNew;
                  
                  if (!isLastNew) {
                      removeBtn.classList.add('opacity-50', 'cursor-not-allowed');
                  } else {
                      removeBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                  }
              }
          });
      }

      // Event delegation for step container (delete & remove buttons)
      stepsContainer.addEventListener('click', e => {
          if (e.target.classList.contains('deleteStepBtn')) {
              const stepBlock = e.target.closest('.step-block');
              stepBlock.remove();
              currentStepCount--;
              updateDeleteButtons();
              updateRemoveNewStepButtons();
          } else if (e.target.classList.contains('removeNewStepBtn')) {
              const stepBlock = e.target.closest('.step-block');
              stepBlock.remove();
              currentStepCount--;
              updateDeleteButtons();
              updateRemoveNewStepButtons();
          }
      });

      // Add new step button handler
      addStepBtn.addEventListener('click', () => {
          if (currentStepCount < maxSteps) {
              const newStepNumber = currentStepCount + 1;
              const newStepBlock = createNewStepBlock(newStepNumber);
              stepsContainer.appendChild(newStepBlock);
              currentStepCount++;
              updateDeleteButtons();
              updateRemoveNewStepButtons();
          }
      });

      // Initialize on page load
      updateDeleteButtons();
      updateRemoveNewStepButtons();

      //confirmation for image delete
      document.querySelectorAll('.delete-image-link').forEach(function(link) {
        link.addEventListener('click', function(event) {
          event.preventDefault();
          const targetHref = this.getAttribute('data-href');

          SnapDialog().alert('Confirm Deletion', 'Are you sure you want to delete this image?', {
            enableConfirm: true,
            onConfirm: function () {
              window.location.href = targetHref;
            },
            enableCancel: true,
          });
        });
      });

      //confirmation for step delete
      document.querySelectorAll('.delete-step-link').forEach(function(link) {
      link.addEventListener('click', function(event) {
        event.preventDefault();
        const targetHref = this.getAttribute('data-href');

        SnapDialog().alert('Confirm Deletion', 'Are you sure you want to delete this step?', {
          enableConfirm: true,
          onConfirm: function () {
            window.location.href = targetHref;
          },
          enableCancel: true,
        });
      });
    });

      //confirmation for step image delete
      document.querySelectorAll('.delete-step-media-link').forEach(function(link) {
      link.addEventListener('click', function(event) {
        event.preventDefault();
        const targetHref = this.getAttribute('data-href');

        SnapDialog().alert('Confirm Deletion', 'Are you sure you want to delete this step image?', {
          enableConfirm: true,
          onConfirm: function () {
            window.location.href = targetHref;
          },
          enableCancel: true,
        });
      });
    });

    //confirmation for step video delete
      document.querySelectorAll('.delete-step-video-link').forEach(function(link) {
      link.addEventListener('click', function(event) {
        event.preventDefault();
        const targetHref = this.getAttribute('data-href');

        SnapDialog().alert('Confirm Deletion', 'Are you sure you want to delete this step video?', {
          enableConfirm: true,
          onConfirm: function () {
            window.location.href = targetHref;
          },
          enableCancel: true,
        });
      });
    });

    // confirmation for step audio delete
      document.querySelectorAll('.delete-step-audio-link').forEach(function(link) {
      link.addEventListener('click', function(event) {
        event.preventDefault();
        const targetHref = this.getAttribute('data-href');

        SnapDialog().alert('Confirm Deletion', 'Are you sure you want to delete this step audio?', {
          enableConfirm: true,
          onConfirm: function () {
            window.location.href = targetHref;
          },
          enableCancel: true,
        });
      });
    });

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

                var success_updated_recipe = sessionStorage.getItem('success_updated_recipe');  
                console.log("Message from sessionStorage:", success_updated_recipe); 
                if (success_updated_recipe) {
                    toastr.success(success_updated_recipe); 
                    sessionStorage.removeItem('success_updated_recipe');
                }
            });

</script>

</body>
</html>