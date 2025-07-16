<?php
session_start();
require_once 'db_connect.php';

$userID = $_SESSION['userID'];

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $ingredient = $_POST['ingredient'];
    $dietary_type = $_POST['dietary_type'];
    $cuisine_type = $_POST['cuisine_type'];
    $step = $_POST['step'];

    $stmt = $conn->prepare("INSERT INTO RECIPE (title, ingredient, dietary_type, cuisine_type, step, userID) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssi", $title, $ingredient, $dietary_type, $cuisine_type, $step, $userID);

    if ($stmt->execute()) {
        $newRecipeID = $stmt->insert_id;
        $stmt->close();

        // Insert into log table
        $action = "Insert";
        $details = "User $user created recipe ID $newRecipeID with title '$title'";
        $log_stmt = $conn->prepare("INSERT INTO LOG (userID, action, details) VALUES (?, ?, ?)");
        $log_stmt->bind_param("iss", $userID, $action, $details);
        $log_stmt->execute();
        $log_stmt->close();

        header("Location: upload_media.php?recipeID=$newRecipeID");
        exit();
    } else {
        $message = "Error: " . $stmt->error;
        $stmt->close();
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Recipe</title>
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
        h2{
            margin-top: 15px;
        }

    </style>
</head>
<body>
<?php include 'header.php'; ?>

<h2>Create New Recipe</h2>

<?php if ($message): ?>
    <div class="alert alert-danger"><?= $message; ?></div>
<?php endif; ?>

<form method="POST" action="create_recipe.php" class="custom-recipe-form">
    <div class="custom-form-wrapper">
        <div class="mb-3">
            <label class="form-label"><strong>Recipe Title</strong></label>
            <input type="text" name="title" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label"><strong>Ingredients</strong></label>
            <textarea name="ingredient" id="ingredientTextarea" class="form-control" rows="3"></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label"><strong>Dietary Type</strong></label>
            <select name="dietary_type" class="form-select">
                <option value="">-- Select --</option>
                <option value="Vegan">Vegan (No animal products)</option>
                <option value="Gluten-Free">Gluten-Free (No wheat, barley, or rye)</option>
                <option value="Vegetarian">Vegetarian (No meat or fish)</option>
                <option value="Keto">Keto (Low carb, high fat)</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label"><strong>Cuisine Type</strong></label>
            <select name="cuisine_type" class="form-select">
                <option value="">-- Select --</option>
                <option value="Italian">Italian</option>
                <option value="Mexican">Mexican</option>
                <option value="Indian">Indian</option>
                <option value="Chinese">Chinese</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label"><strong>Step-by-Step Instructions</strong></label>
            <button type="button" id="toggleMicBtn" style="margin-left: 1px; margin-bottom: 10px;">🎤 Start Speaking</button>
            <textarea id="stepTextarea" name="step" class="form-control" rows="5" required placeholder="Enter full step-by-step instructions..."></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Next: Upload Media</button>
    </div>
</form>

<?php include 'footer.php'; ?>
<script>
    const textarea = document.getElementById('stepTextarea');

    textarea.addEventListener('input', () => {
        let cursorPosition = textarea.selectionStart;
        let value = textarea.value;
        
        // Split by lines
        let lines = value.split('\n');

        // Calculate total chars before cursor
        let charsBeforeCursor = 0;
        for (let i = 0; i < lines.length; i++) {
            if (charsBeforeCursor + lines[i].length + 1 > cursorPosition) {
                break;
            }
            charsBeforeCursor += lines[i].length + 1; // +1 for \n
        }
        
        // Remove existing numbering and re-add
        let newLines = lines.map((line, index) => {
            return (index + 1) + '. ' + line.replace(/^\d+\.\s*/, '');
        });
        
        let newValue = newLines.join('\n');

        // Calculate difference in length between newValue and old value
        let diff = newValue.length - value.length;

        // Update only if changed
        if (newValue !== value) {
            textarea.value = newValue;

            // Adjust cursor position:
            // Add the length of numbering in the current line before cursor
            
            // Find current line number
            let currentLineIndex = 0;
            let totalChars = 0;
            for (let i = 0; i < newLines.length; i++) {
                totalChars += newLines[i].length + 1; // +1 for \n
                if (totalChars > cursorPosition + diff) {
                    currentLineIndex = i;
                    break;
                }
            }

            // Length of numbering prefix = "X. " where X is (index+1)
            // So calculate prefix length for current line
            let prefixLength = (currentLineIndex + 1).toString().length + 2; // e.g. "1. " is 3 chars

            // Calculate new cursor position:
            // position relative to line start + prefix length
            let lineStartPos = 0;
            for (let i = 0; i < currentLineIndex; i++) {
                lineStartPos += newLines[i].length + 1;
            }
            let cursorPosInLine = cursorPosition - (lineStartPos - prefixLength); 

            // Final cursor pos:
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

    const micBtn = document.getElementById('toggleMicBtn');

    let recognizing = false;
    let recognition;

    if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        recognition = new SpeechRecognition();
        recognition.continuous = true;
        recognition.interimResults = false;
        recognition.lang = 'en-US';

        recognition.onstart = () => {
            recognizing = true;
            micBtn.textContent = '🛑 Stop Speaking';
        };

        recognition.onend = () => {
            recognizing = false;
            micBtn.textContent = '🎤 Start Speaking';
        };

        recognition.onerror = (event) => {
            recognizing = false;
            micBtn.textContent = '🎤 Start Speaking';
            alert('Error: ' + event.error);
        };

        recognition.onresult = (event) => {
            for (let i = event.resultIndex; i < event.results.length; ++i) {
                if (event.results[i].isFinal) {
                    const transcript = event.results[i][0].transcript.trim();
                    if (transcript) {
                        textarea.value += (textarea.value ? '\n' : '') + transcript;
                        textarea.dispatchEvent(new Event('input')); // trigger numbering
                    }
                }
            }
        };

        micBtn.addEventListener('click', () => {
            if (recognizing) {
                recognition.stop();
            } else {
                recognition.start();
            }
        });
    } else {
        micBtn.disabled = true;
        micBtn.textContent = 'Mic not supported';
        alert('Speech recognition not supported in this browser.');
    }


</script>