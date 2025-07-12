<?php
// get_image.php
include('connect.php');

// Critical PDO settings for binary data
$conn->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
$conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

$image_id = $_GET['id'] ?? null;

if (!$image_id) {
    header("HTTP/1.0 400 Bad Request");
    exit("Image ID is required");
}

try {
    // Get the image data and type
    $stmt = $conn->prepare("
        SELECT image_data, image_type 
        FROM image 
        WHERE image_id = ?
    ");
    $stmt->execute([$image_id]);
    
    // Fetch as stream resource
    $stmt->bindColumn(1, $image_data, PDO::PARAM_LOB);
    $stmt->bindColumn(2, $image_type);
    $stmt->fetch(PDO::FETCH_BOUND);
    
    if (!$image_data) {
        header("HTTP/1.0 404 Not Found");
        exit("Image not found");
    }

    // Clean all output buffers
    while (ob_get_level()) ob_end_clean();

    // Set proper content type
    $mime_types = [
        'JPEG' => 'image/jpeg',
        'JPG' => 'image/jpeg',
        'PNG' => 'image/png',
        'GIF' => 'image/gif',
        'WEBP' => 'image/webp'
    ];
    
    header("Content-Type: " . ($mime_types[$image_type] ?? 'application/octet-stream'));
    header("Content-Length: " . (is_resource($image_data) ? fstat($image_data)['size'] : strlen($image_data)));
    
    // Output the data
    if (is_resource($image_data)) {
        fpassthru($image_data);
    } else {
        echo $image_data;
    }
    exit;
    
} catch (PDOException $e) {
    header("HTTP/1.0 500 Internal Server Error");
    exit("Database error: " . $e->getMessage());
}
?>