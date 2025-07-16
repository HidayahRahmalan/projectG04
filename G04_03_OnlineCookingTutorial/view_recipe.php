<?php
session_start();
require_once 'db_connect.php';

//$_SESSION['user'] = 1;
$userID = $_SESSION['userID'];

if (!isset($_GET['recipeID'])) {
    echo "Recipe ID not provided.";
    exit();
}

$recipeID = intval($_GET['recipeID']);

// Fetch recipe data
$stmt = $conn->prepare("SELECT * FROM RECIPE WHERE recipeID = ? AND userID = ?");
$stmt->bind_param("ii", $recipeID, $userID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Recipe not found or unauthorized.";
    exit();
}

$recipe = $result->fetch_assoc();

// Fetch media
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
    <title>View Recipe</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .view-wrapper {
            background: #fefefe;
            padding: 30px;
            border: 2px solid #d35400;
            border-radius: 10px;
            max-width: 1200px;
            margin: 30px auto;
        }

        .view-wrapper h2 {
            margin-top: 0;
        }

        .recipe-section {
            margin-bottom: 20px;
        }

        .recipe-label {
            font-weight: bold;
            color: #d35400;
        }

        .recipe-text {
            margin-left: 10px;
        }

        .media-gallery img,
        .media-gallery video {
            max-width: 300px;
            margin: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
        }
    </style>
</head>

<body>
    <?php include 'header.php'; ?>

    <div class="view-wrapper">
        <h2><?= htmlspecialchars($recipe['title']) ?></h2>

        <div class="recipe-section">
            <div class="recipe-label">Ingredients:</div>
            <div class="recipe-text"><?= nl2br(htmlspecialchars($recipe['ingredient'])) ?></div>
        </div>

        <div class="recipe-section">
            <div class="recipe-label">Dietary Type:</div>
            <div class="recipe-text"><?= htmlspecialchars($recipe['dietary_type']) ?></div>
        </div>

        <div class="recipe-section">
            <div class="recipe-label">Cuisine Type:</div>
            <div class="recipe-text"><?= htmlspecialchars($recipe['cuisine_type']) ?></div>
        </div>

        <div class="recipe-section">
            <div class="recipe-label">Instructions:</div>
            <div class="recipe-text" style="white-space: pre-line;"><?= htmlspecialchars($recipe['step']) ?></div>
        </div>

        <?php if (!empty($media_files)): ?>
            <div class="recipe-section media-gallery">
                <div class="recipe-label">Media:</div>
                <?php foreach ($media_files as $media): ?>
                    <?php if ($media['media_type'] === 'image'): ?>
                        <img src="<?= htmlspecialchars($media['file_path']) ?>" alt="Recipe Image">
                    <?php elseif ($media['media_type'] === 'video'): ?>
                        <video id="recipeVideo" controls>
                            <source src="<?= htmlspecialchars($media['file_path']) ?>" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                    <?php endif; ?>

                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <a href="recipe_list.php" class="btn btn-secondary" style="margin-top: 20px; display: inline-block;">Back</a>
    </div>

    <?php include 'footer.php'; ?>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const video = document.getElementById("recipeVideo");

            if (!video) return;

            let recognition;
            let recognizing = false;

            if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
                const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
                recognition = new SpeechRecognition();
                recognition.continuous = true;
                recognition.interimResults = false;
                recognition.lang = 'en-US';

                recognition.onstart = () => {
                    recognizing = true;
                    console.log("Voice control started...");
                };

                recognition.onend = () => {
                    recognizing = false;
                    console.log("Voice control stopped...");
                };

                recognition.onerror = (event) => {
                    console.error("Speech recognition error:", event.error);
                };

                recognition.onresult = (event) => {
                    for (let i = event.resultIndex; i < event.results.length; i++) {
                        if (event.results[i].isFinal) {
                            const transcript = event.results[i][0].transcript.trim().toLowerCase();
                            console.log("Heard:", transcript);

                            if (transcript.includes("start") || transcript.includes("on")) {
                                video.play();
                            } else if (transcript.includes("stop") || transcript.includes("off")) {
                                video.pause();
                            }
                        }
                    }
                };

                // Start listening automatically
                recognition.start();
            } else {
                alert("Voice recognition not supported in this browser.");
            }
        });
    </script>
</body>

</html>