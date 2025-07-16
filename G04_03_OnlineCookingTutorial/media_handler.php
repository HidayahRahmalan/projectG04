<?php
require_once 'db_connect.php';
require_once 'process_media.php';

function handleMediaUpload($userID, $recipeID, $file, $conn)
{
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'mov', 'avi'];
    $tmpPath = $file['tmp_name'];
    $originalName = $file['name'];
    $fileType = $file['type'];
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed)) return "Unsupported file type.";

    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $newName = uniqid('media_', true) . '.' . $ext;
    $destPath = $uploadDir . $newName;

    if (!move_uploaded_file($tmpPath, $destPath)) return "Failed to move uploaded file.";

    $mediaType = (strpos($fileType, 'video') !== false) ? 'video' : 'image';

    // Generate thumbnail
    $thumbPath = $uploadDir . 'thumb_' . $newName;
    generateThumbnail($destPath, $thumbPath, $mediaType);

    // Metadata
    $metadataArr = [
        'original_name' => $originalName,
        'size' => $file['size'],
        'type' => $fileType,
        'thumbnail' => $thumbPath
    ];

    if ($mediaType === 'video') {
        $metadataArr['duration'] = extractVideoDuration($destPath);
    }

    $metadata = json_encode($metadataArr);

    // Save to DB
    $stmt = $conn->prepare("INSERT INTO MEDIA (userID, recipeID, reviewID, media_type, file_path, metadata) VALUES (?, ?, NULL, ?, ?, ?)");
    $stmt->bind_param("iisss", $userID, $recipeID, $mediaType, $destPath, $metadata);
    $stmt->execute();
    $stmt->close();

    return "Upload successful.";
}
