<?php
session_start();
include "header.php";
// Mock login for testing — remove once session login is ready
if (!isset($_SESSION['UserID'])) {
    $_SESSION['UserID'] = 'U00001'; // Default user ID (for testing only)
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Database connection
    $conn = new mysqli("localhost", "root", "", "mmdb");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Collect form inputs safely
    $title = trim($_POST['title']);
    $category = isset($_POST['category']) ? implode(', ', $_POST['category']) : '';
    $description = trim($_POST['description']);
    $foodType = trim($_POST['foodType']);
    $post = trim($_POST['post']);
    $steps = trim($_POST['steps']);
    $ingredients = trim($_POST['ingredients']);
    $level = strtoupper(trim($_POST['level']));
    $userID = $_SESSION['UserID'];

    // Input validation
    if (empty($title) || empty($description) || empty($foodType) || empty($post) ||
        empty($steps) || empty($ingredients) || empty($level) || empty($category)) {
        die("Please fill in all required fields.");
    }

    // Validate level ENUM
    $validLevels = ['EASY', 'IMMEDIATE', 'HARD'];
    if (!in_array($level, $validLevels)) {
        die("Invalid difficulty level selected.");
    }

    // Validate FoodType ENUM
    $validTypes = ['LOCAL CUISINE', 'WESTERN CUISINE', 'CHINESE CUISINE', 'JAPANESE OR KOREAN CUISINE', 'DESSERT', 'BEVERAGE', 'OTHER'];
    if (!in_array($foodType, $validTypes)) {
        die("Invalid food type selected.");
    }

    // Handle image upload (check size and content)
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        die("Image upload failed.");
    }
    $imageData = file_get_contents($_FILES['image']['tmp_name']);
    if (strlen($imageData) > 1000000) { // 1MB
        die("Image too large. Limit 1MB.");
    }

    // Handle video upload
    if (!isset($_FILES['video']) || $_FILES['video']['error'] !== UPLOAD_ERR_OK) {
        die("Video upload failed.");
    }
    $videoName = basename($_FILES['video']['name']);
    $videoTmp = $_FILES['video']['tmp_name'];
    $videoPath = 'uploads/videos/' . $videoName;
    if (!move_uploaded_file($videoTmp, $videoPath)) {
        die("Failed to save video.");
    }

    // Insert FOOD (image as BLOB)
    $stmt1 = $conn->prepare("INSERT INTO FOOD (FoodTitle, FoodCategory, FoodDesc, FoodType, FoodImage) VALUES (?, ?, ?, ?, ?)");
    $stmt1->bind_param("sssss", $title, $category, $description, $foodType, $imageData);
    $stmt1->send_long_data(4, $imageData);
    if (!$stmt1->execute()) {
        die("Insert FOOD failed: " . $stmt1->error);
    }

    // Get new FoodID
    $foodIDRes = $conn->query("SELECT FoodID FROM FOOD ORDER BY FoodID DESC LIMIT 1");
    if ($foodIDRes && $foodIDRes->num_rows > 0) {
        $foodID = $foodIDRes->fetch_assoc()['FoodID'];
    } else {
        die("Failed to retrieve new FoodID.");
    }

    // Insert RECIPE
    $stmt2 = $conn->prepare("INSERT INTO RECIPE (RecInstructions, CookVideo, RecIngredients, RecLevel, PostDescription, FoodID, UserID) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt2->bind_param("sssssss", $steps, $videoPath, $ingredients, $level, $post, $foodID, $userID);
    if (!$stmt2->execute()) {
        die("Insert RECIPE failed: " . $stmt2->error);
    }

    // Close resources
    $stmt1->close();
    $stmt2->close();
    $conn->close();

    // Redirect with success message
    echo "<script>alert('Resepi berjaya dimuat naik!'); window.location.href='homepage.html';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>ResepiKu - Kongsi Resepi</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <style>
    body {
      font-family: 'Roboto', sans-serif;
      margin: 0;
      padding: 0;
      background-color: #fff8f0;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }

    header {
      background-color: #ff6347;
      color: white;
      padding: 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
    }

    header h1 {
      margin: 0;
    }

    nav {
      display: flex;
      gap: 15px;
      align-items: center;
    }

    nav a,
    nav .dropdown {
      color: white;
      text-decoration: none;
      font-weight: bold;
      position: relative;
    }

    .dropdown {
      cursor: pointer;
    }

    .dropdown-content {
      display: none;
      position: absolute;
      background-color: #fff;
      color: black;
      min-width: 160px;
      box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
      z-index: 1;
      border-radius: 5px;
      margin-top: 5px;
    }

    .dropdown-content a {
      color: black;
      padding: 10px 15px;
      text-decoration: none;
      display: block;
    }

    .dropdown-content a:hover {
      background-color: #f1f1f1;
    }

    .dropdown:hover .dropdown-content {
      display: block;
    }

    main {
      flex: 1;
      padding: 40px 20px;
      display: flex;
      justify-content: center;
    }

    .form-container {
      background: white;
      padding: 30px 40px;
      border-radius: 10px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
      width: 100%;
      max-width: 700px;
    }

    h2 {
      text-align: center;
      color: #ff6347;
      margin-bottom: 30px;
    }

    label {
      font-weight: bold;
      display: block;
      margin-top: 15px;
    }

    input[type="text"],
    input[type="file"],
    textarea,
    select {
      width: 100%;
      padding: 10px;
      margin-top: 5px;
      border: 1px solid #ccc;
      border-radius: 5px;
    }

    textarea {
      resize: vertical;
    }

    .checkbox-group {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin-top: 5px;
    }

    .checkbox-group label {
      font-weight: normal;
    }

    button {
      width: 100%;
      padding: 10px;
      background-color: #ff6347;
      color: white;
      border: none;
      border-radius: 5px;
      margin-top: 20px;
      cursor: pointer;
    }

    button:hover {
      background-color: #e5533d;
    }

    footer {
      background-color: #333;
      color: white;
      text-align: center;
      padding: 15px 0;
    }
  </style>
</head>
<body>
    <a href="homepage.html" style="text-decoration: none; color: white;">
      <h1>ResepiKu</h1>
    </a>
    <nav>
      <div class="dropdown">
        <span>Jenis</span>
        <div class="dropdown-content">
          <a href="Local.html">Local Cuisine</a>
          <a href="western.html">Western Cuisine</a>
          <a href="japanese.html">Japanese Cuisine</a>
          <a href="chinese.html">Chinese Cuisine</a>
          <a href="korean.html">Korean Cuisine</a>
          <a href="dessert.html">Dessert</a>
          <a href="beverages.html">Beverages</a>
          <a href="other.html">Other</a>
        </div>
      </div>
      <a href="notifications.html">Notifikasi</a>
      <a href="login.html">Log Masuk</a>
      <a href="register.html">Daftar</a>
      <a href="upload-recipe.html">Kongsi Resepi</a>
    </nav>
  <main>
    <div class="form-container">
      <h2>Kongsi Resepi Anda</h2>
      <form method="POST" enctype="multipart/form-data">
        <label for="title">Tajuk Resepi</label>
        <input type="text" id="title" name="title" required>

        <label>Kategori</label>
        <div class="checkbox-group">
          <label><input type="checkbox" name="category[]" value="Halal"> Halal</label>
          <label><input type="checkbox" name="category[]" value="Vegetarian"> Vegetarian</label>
          <label><input type="checkbox" name="category[]" value="Vegan"> Vegan</label>
          <label><input type="checkbox" name="category[]" value="High-Protein"> High-Protein</label>
          <label><input type="checkbox" name="category[]" value="Gluten-Free"> Gluten-Free</label>
          <label><input type="checkbox" name="category[]" value="Dairy-Free"> Dairy-Free</label>
          <label><input type="checkbox" name="category[]" value="Other"> Other</label>
        </div>

        <label for="description">Deskripsi Ringkas</label>
        <textarea id="description" name="description" rows="3" required></textarea>

        <label for="foodType">Jenis Makanan</label>
        <select id="foodType" name="foodType" required>
          <option value="">-- Pilih Jenis Makanan --</option>
          <option value="LOCAL CUISINE">LOCAL CUISINE</option>
          <option value="WESTERN CUISINE">WESTERN CUISINE</option>
          <option value="CHINESE CURSINE">CHINESE CUISINE</option>
          <option value="JAPANESE CUISINE">JAPANESE CUISINE</option>
          <option value="KOREAN CUISINE">KOREAN CUISINE</option>
          <option value="DESSERT">DESSERT</option>
          <option value="BEVERAGE">BEVERAGE</option>
          <option value="OTHER">OTHER</option>
        </select>

        <label for="image">Gambar Makanan</label>
        <input type="file" id="image" name="image" accept="image/*" required>

        <label for="post">Huraian Penuh / Cerita Resepi</label>
        <textarea id="post" name="post" rows="4" required></textarea>

        <label for="steps">Langkah Memasak</label>
        <textarea id="steps" name="steps" rows="5" required></textarea>

        <label for="video">Video Tutorial</label>
        <input type="file" id="video" name="video" accept="video/*" required>

        <label for="ingredients">Senarai Bahan</label>
        <textarea id="ingredients" name="ingredients" rows="4" required></textarea>

        <label for="level">Tahap Kesukaran</label>
        <select id="level" name="level" required>
          <option value="">-- Pilih Tahap --</option>
          <option value="easy">Mudah</option>
          <option value="immediate">Sederhana</option>
          <option value="hard">Sukar</option>
        </select>

        <button type="submit">Kongsi Resepi</button>
      </form>
    </div>
  </main>

  <footer>
    &copy; 2025 ResepiKu. All rights reserved.
  </footer>
</body>
</html>
