<?php
require_once 'db_connect.php';
$recipeID = isset($_GET['recipeID']) ? intval($_GET['recipeID']) : 0;

$sql = "SELECT * FROM MEDIA WHERE recipeID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $recipeID);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>

<head>
    <title>Media Preview</title>
    <style>
        .media-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .media-item {
            width: 200px;
            text-align: center;
        }

        video,
        img {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>

<body>
    <h2>Media for Recipe #<?= $recipeID ?></h2>
    <div class="media-grid">
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="media-item">
                <?php if ($row['media_type'] === 'image'): ?>
                    <img src="<?= htmlspecialchars($row['file_path']) ?>" alt="Image">
                <?php elseif ($row['media_type'] === 'video'): ?>
                    <video controls>
                        <source src="<?= htmlspecialchars($row['file_path']) ?>" type="video/mp4">
                        Your browser does not support video.
                    </video>
                <?php endif; ?>
                <p><?= htmlspecialchars(json_decode($row['metadata'], true)['original_name']) ?></p>
            </div>
        <?php endwhile; ?>
    </div>
</body>

</html>