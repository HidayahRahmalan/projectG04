<?php
require_once 'db_connect.php';

if (!isset($_GET['mediaID']) || !is_numeric($_GET['mediaID'])) {
    die("Invalid media ID.");
}
$mediaID = intval($_GET['mediaID']);

$stmt = $conn->prepare("SELECT * FROM MEDIA WHERE mediaID = ?");
$stmt->bind_param("i", $mediaID);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows === 0) {
    die("Media not found.");
}

$media = $result->fetch_assoc();
$meta = json_decode($media['metadata'], true);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Media Viewer</title>
</head>

<body>
    <h2>Viewing Media #<?= $mediaID ?></h2>
    <?php if ($media['media_type'] === 'image'): ?>
        <img src="<?= htmlspecialchars($media['file_path']) ?>" alt="Media" width="500">
    <?php elseif ($media['media_type'] === 'video'): ?>
        <video width="500" controls>
            <source src="<?= htmlspecialchars($media['file_path']) ?>" type="video/mp4">
            Your browser does not support video.
        </video>
    <?php endif; ?>

    <h4>Metadata:</h4>
    <pre><?= print_r($meta, true) ?></pre>

    <br><a href="<?= htmlspecialchars($media['file_path']) ?>" download>Download Media</a>
</body>

</html>