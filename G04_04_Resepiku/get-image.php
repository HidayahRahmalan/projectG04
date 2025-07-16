<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "resepi_db";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT FoodImage FROM food WHERE FoodID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $imageContent = $row['FoodImage'];
        
        // Check if content is a filename or binary data
        if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $imageContent)) {
            // It's a filename - redirect to the file
            $imagePath = "uploads/" . $imageContent;
            if (file_exists($imagePath)) {
                header("Location: " . $imagePath);
                exit();
            }
        } else {
            // It's binary data - output as image
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->buffer($imageContent);
            header("Content-Type: " . $mimeType);
            echo $imageContent;
            exit();
        }
    }
    
    // Fallback to placeholder if no image found
    header("Location: placeholder.jpg");
    exit();
} else {
    header("Location: placeholder.jpg");
    exit();
}
?>