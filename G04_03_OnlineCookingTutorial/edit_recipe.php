<?php
session_start();
require_once 'db_connect.php';

$userID = $_SESSION['userID'];

if (!isset($_GET['recipeID'])) {
    echo "Recipe ID not provided.";
    exit();
}

$recipeID = intval($_GET['recipeID']);
$message = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $ingredient = $_POST['ingredient'];
    $dietary_type = $_POST['dietary_type'];
    $cuisine_type = $_POST['cuisine_type'];
    $step = $_POST['step'];

    $stmt = $conn->prepare("UPDATE RECIPE SET title = ?, ingredient = ?, dietary_type = ?, cuisine_type = ?, step = ? WHERE recipeID = ? AND userID = ?");
    $stmt->bind_param("sssssii", $title, $ingredient, $dietary_type, $cuisine_type, $step, $recipeID, $userID);

    if ($stmt->execute()) {
        $message = "Recipe updated successfully. Redirecting...";
        echo "<script>
            setTimeout(function() {
                window.location.href = 'recipe_list.php?updated=1';
            }, 2000);
        </script>";

        // Log the update
        $action = "Update";
        $details = "User $user updated recipe ID $recipeID";
        $log_stmt = $conn->prepare("INSERT INTO LOG (userID, action, details) VALUES (?, ?, ?)");
        $log_stmt->bind_param("iss", $userID, $action, $details);
        $log_stmt->execute();
        $log_stmt->close();
    } else {
        $message = "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch existing recipe data
$stmt = $conn->prepare("SELECT * FROM RECIPE WHERE recipeID = ? AND userID = ?");
$stmt->bind_param("ii", $recipeID, $userID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Recipe not found or unauthorized.";
    exit();
}

$recipe = $result->fetch_assoc();

$media_stmt = $conn->prepare("SELECT media_type, file_path FROM media WHERE recipeID = ?");
$media_stmt->bind_param("i", $recipeID);
$media_stmt->execute();
$media_result = $media_stmt->get_result();

$media_files = [];
while ($row = $media_result->fetch_assoc()) {
    $media_files[] = $row;
}
$media_stmt->close();

$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Recipe</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Override global form style */
        form.custom-recipe-form {
            all: unset;
        }

        /* Custom form styling */
        .custom-form-wrapper {
            background: #fefefe;
            padding: 30px;
            border: 2px dashed #d35400;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            max-width: 1300px;
            margin: 30px auto;
        }
        .mb-3 label.form-label {
            display: block;
            margin-bottom: 5px; /* some spacing */
        }
        .custom-recipe-form .form-control {
            display: block;
            width: 100%;
            padding: 0.375rem 0.75rem; /* Bootstrap default padding */
            font-size: 1rem;
            line-height: 1.5;
            color: #212529;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;
            box-sizing: border-box;
            margin-bottom: 5px; /* some spacing */  
        }


    </style>
</head>
<body>
<?php include 'header.php'; ?>

<h2>Edit Recipe</h2>
<?php if ($message): ?>
    <div class="alert alert-danger"><?= $message ?></div>
<?php endif; ?>

<form method="POST" action="" class="custom-recipe-form">
    <div class="custom-form-wrapper">
        <div class="mb-3">
            <label class="form-label"><strong>Recipe Title</strong></label>
            <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($recipe['title']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label"><strong>Ingredients</strong></label>
            <textarea name="ingredient" id="ingredientTextarea" class="form-control" rows="3"><?= htmlspecialchars($recipe['ingredient']) ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label"><strong>Dietary Type</strong></label>
            <select name="dietary_type" class="form-select">
                <option value="">-- Select --</option>
                <?php
                $dietOptions = ['Vegan', 'Gluten-Free', 'Vegetarian', 'Keto'];
                foreach ($dietOptions as $opt) {
                    $selected = ($recipe['dietary_type'] == $opt) ? 'selected' : '';
                    echo "<option value=\"$opt\" $selected>$opt</option>";
                }
                ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label"><strong>Cuisine Type</strong></label>
            <select name="cuisine_type" class="form-select">
                <option value="">-- Select --</option>
                <?php
                $cuisineOptions = ['Italian', 'Mexican', 'Indian', 'Chinese'];
                foreach ($cuisineOptions as $opt) {
                    $selected = ($recipe['cuisine_type'] == $opt) ? 'selected' : '';
                    echo "<option value=\"$opt\" $selected>$opt</option>";
                }
                ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label"><strong>Step-by-Step Instructions</strong></label>
            <textarea name="step" id="stepTextarea" class="form-control" rows="5" required><?= htmlspecialchars($recipe['step']) ?></textarea>
        </div>

        <?php if (!empty($media_files)): ?>
            <div class="mb-3">
                <label class="form-label"><strong>Media Files</strong></label>
                <div style="display: flex; flex-wrap: wrap; gap: 15px;">
                    <?php foreach ($media_files as $media): ?>
                        <?php if ($media['media_type'] === 'image'): ?>
                            <img src="<?= htmlspecialchars($media['file_path']) ?>" alt="Recipe Image" style="max-width: 200px; height: auto; border: 1px solid #ccc; border-radius: 5px;">
                        <?php elseif ($media['media_type'] === 'video'): ?>
                            <video controls style="max-width: 300px; border-radius: 5px;">
                                <source src="<?= htmlspecialchars($media['file_path']) ?>" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>


        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary" style="margin-top: 10px;">Update Recipe</button>
            <a href="recipe_list.php" class="btn btn-secondary" style="margin-left: 25px;">Back</a>
        </div>

    </div>
</form>

<?php include 'footer.php'; ?>
<script>
    const textarea = document.getElementById('stepTextarea');

    textarea.addEventListener('input', () => {
        let cursorPosition = textarea.selectionStart;
        let value = textarea.value;
        
        let lines = value.split('\n');
        let charsBeforeCursor = 0;
        for (let i = 0; i < lines.length; i++) {
            if (charsBeforeCursor + lines[i].length + 1 > cursorPosition) {
                break;
            }
            charsBeforeCursor += lines[i].length + 1;
        }

        let newLines = lines.map((line, index) => {
            return (index + 1) + '. ' + line.replace(/^\d+\.\s*/, '');
        });

        let newValue = newLines.join('\n');
        let diff = newValue.length - value.length;

        if (newValue !== value) {
            textarea.value = newValue;

            let currentLineIndex = 0;
            let totalChars = 0;
            for (let i = 0; i < newLines.length; i++) {
                totalChars += newLines[i].length + 1;
                if (totalChars > cursorPosition + diff) {
                    currentLineIndex = i;
                    break;
                }
            }

            let prefixLength = (currentLineIndex + 1).toString().length + 2;

            let lineStartPos = 0;
            for (let i = 0; i < currentLineIndex; i++) {
                lineStartPos += newLines[i].length + 1;
            }

            let cursorPosInLine = cursorPosition - (lineStartPos - prefixLength);
            let finalCursorPos = lineStartPos + cursorPosInLine;
            if(finalCursorPos < 0) finalCursorPos = 0;

            textarea.selectionStart = textarea.selectionEnd = finalCursorPos;
        }
    });

    const ingredientTextarea = document.getElementById('ingredientTextarea');

    ingredientTextarea.addEventListener('input', () => {
        const cursorPos = ingredientTextarea.selectionStart;
        const originalValue = ingredientTextarea.value;

        const lines = originalValue.split('\n');
        const newLines = lines.map(line => {
            line = line.trim();
            if (line === '') return '';
            return line.startsWith('•') ? line : '• ' + line.replace(/^•?\s*/, '');
        });

        const newValue = newLines.join('\n');

        if (newValue !== originalValue) {
            ingredientTextarea.value = newValue;

            // Try to keep cursor near where it was
            const adjustment = newValue.length - originalValue.length;
            ingredientTextarea.selectionStart = ingredientTextarea.selectionEnd = cursorPos + adjustment;
        }
    });

</script>

</body>
</html>
