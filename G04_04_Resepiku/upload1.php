<?php
session_start();
include 'connection.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Generate RecipeID
    $getLastID = $conn->query("SELECT RecipeID FROM RECIPE ORDER BY RecipeID DESC LIMIT 1");
    if ($getLastID->num_rows > 0) {
        $lastID = $getLastID->fetch_assoc()['RecipeID'];
        $num = intval(substr($lastID, 1)) + 1;
        $newID = 'R' . str_pad($num, 5, '0', STR_PAD_LEFT);
    } else {
        $newID = 'R00001';
    }

    // Get POST data
    $recInstructions = $_POST['steps'];
    $postDescription = $_POST['post'];
    $recIngredients = $_POST['ingredients'];
    $recLevel = strtoupper($_POST['level']);
    $foodType = $_POST['foodType'];

    // Get logged in user ID (replace 'U00001' with actual session value)
    $userID = $_SESSION['user_id'] ?? 'U00001';

    // Map foodType to FoodID
    $foodMap = [
        "LOCAL CUISINE" => "F00001",
        "WESTERN CUISINE" => "F00002",
        "CHINESE CUISINE" => "F00003",
        "JAPANESE CUISINE" => "F00004",
        "KOREAN CUISINE" => "F00005",
        "DESSERT" => "F00006",
        "BEVERAGE" => "F00007",
        "OTHER" => "F00008",
    ];
    $foodID = $foodMap[$foodType] ?? 'F00008';

    // Upload video
    $videoName = $_FILES['video']['name'];
    $videoTmp = $_FILES['video']['tmp_name'];
    $videoPath = "uploads/videos/" . basename($videoName);
    move_uploaded_file($videoTmp, $videoPath);

    // Insert into RECIPE table
    $stmt = $conn->prepare("INSERT INTO RECIPE (RecipeID, RecInstructions, CookVideo, RecIngredients, RecLevel, PostDescription, FoodID, UserID)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $newID, $recInstructions, $videoPath, $recIngredients, $recLevel, $postDescription, $foodID, $userID);

    if ($stmt->execute()) {
        echo "<script>alert('Resepi berjaya dimuat naik!'); window.location.href='homepage.html';</script>";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>ResepiKu - Kongsi Resepi</title>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
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
  <header>
    <a href="homepage.html" style="text-decoration: none; color: white;">
      <h1>ResepiKu</h1>
    </a>
    <nav>
      <div class="dropdown">
        <span>Jenis</span>
        <div class="dropdown-content">
          <a href="#">Local Cuisine</a>
          <a href="#">Western Cuisine</a>
          <a href="#">Japanese Cuisine</a>
          <a href="#">Chinese Cuisine</a>
          <a href="#">Korean Cuisine</a>
          <a href="#">Dessert</a>
          <a href="#">Beverages</a>
          <a href="#">Other</a>
        </div>
      </div>
      <a href="#">Notifikasi</a>
      <a href="#">Log Masuk</a>
      <a href="#">Daftar</a>
      <a href="#">Kongsi Resepi</a>
    </nav>
  </header>

  <main>
    <div class="form-container">
      <h2>Kongsi Resepi Anda</h2>
      <form action="" method="POST" enctype="multipart/form-data">
        <label for="post">Huraian Penuh / Cerita Resepi</label>
        <textarea name="post" id="post" rows="4" required></textarea>

        <label for="steps">Langkah Memasak</label>
        <textarea name="steps" id="steps" rows="5" required></textarea>

        <label for="video">Video Tutorial</label>
        <input type="file" name="video" id="video" accept="video/*" required>

        <label for="ingredients">Senarai Bahan</label>
        <textarea name="ingredients" id="ingredients" rows="4" required></textarea>

        <label for="level">Tahap Kesukaran</label>
        <select name="level" id="level" required>
          <option value="">-- Pilih Tahap --</option>
          <option value="EASY">Mudah</option>
          <option value="IMMEDIATE">Sederhana</option>
          <option value="HARD">Sukar</option>
        </select>

        <label for="foodType">Jenis Makanan</label>
        <select name="foodType" id="foodType" required>
          <option value="">-- Pilih Jenis Makanan --</option>
          <option value="LOCAL CUISINE">LOCAL CUISINE</option>
          <option value="WESTERN CUISINE">WESTERN CUISINE</option>
          <option value="CHINESE CUISINE">CHINESE CUISINE</option>
          <option value="JAPANESE CUISINE">JAPANESE CUISINE</option>
          <option value="KOREAN CUISINE">KOREAN CUISINE</option>
          <option value="DESSERT">DESSERT</option>
          <option value="BEVERAGE">BEVERAGE</option>
          <option value="OTHER">OTHER</option>
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
