<?php
session_start();
include "header.php";
include "connection.php";

if (!isset($_SESSION['UserID'])) {
    $_SESSION['UserID'] = 'U00001';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = new mysqli("localhost", "resepiku", "123456", "p25_resepiku");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $title = trim($_POST['title']);
    $category = isset($_POST['category']) ? implode(', ', $_POST['category']) : '';
    $description = trim($_POST['description']);
    $foodType = trim($_POST['foodType']);
    $post = trim($_POST['post']);
    $steps = trim($_POST['steps']);
    $ingredients = trim($_POST['ingredients']);
    $level = strtoupper(trim($_POST['level']));
    $userID = $_SESSION['UserID'];

    $validLevels = ['EASY', 'IMMEDIATE', 'HARD'];
    if (!in_array($level, $validLevels)) {
        die("Invalid difficulty level selected.");
    }

    $validTypes = ['LOCAL CUISINE', 'WESTERN CUISINE', 'CHINESE CUISINE', 'JAPANESE OR KOREAN CUISINE', 'DESSERT', 'BEVERAGE', 'OTHER'];
    if (!in_array($foodType, $validTypes)) {
        die("Invalid food type selected.");
    }

    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        die("Image upload failed.");
    }
    $imageData = file_get_contents($_FILES['image']['tmp_name']);
    if (strlen($imageData) > 1000000) {
        die("Image too large. Limit 1MB.");
    }

    if (!isset($_FILES['video']) || $_FILES['video']['error'] !== UPLOAD_ERR_OK) {
        die("Video upload failed.");
    }
    $videoName = basename($_FILES['video']['name']);
    $videoTmp = $_FILES['video']['tmp_name'];
    $videoPath = 'uploads/videos/' . $videoName;
    if (!move_uploaded_file($videoTmp, $videoPath)) {
        die("Failed to save video.");
    }

    $stmt1 = $conn->prepare("INSERT INTO FOOD (FoodTitle, FoodCategory, FoodDesc, FoodType, FoodImage) VALUES (?, ?, ?, ?, ?)");
    $stmt1->bind_param("sssss", $title, $category, $description, $foodType, $imageData);
    $stmt1->send_long_data(4, $imageData);
    if (!$stmt1->execute()) {
        die("Insert FOOD failed: " . $stmt1->error);
    }

    $foodIDRes = $conn->query("SELECT FoodID FROM FOOD ORDER BY FoodID DESC LIMIT 1");
    $foodID = $foodIDRes->fetch_assoc()['FoodID'] ?? null;
    if (!$foodID) {
        die("Failed to get FoodID.");
    }

    $stmt2 = $conn->prepare("INSERT INTO RECIPE (RecInstructions, CookVideo, RecIngredients, RecLevel, PostDescription, FoodID, UserID) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt2->bind_param("sssssss", $steps, $videoPath, $ingredients, $level, $post, $foodID, $userID);
    if (!$stmt2->execute()) {
        die("Insert RECIPE failed: " . $stmt2->error);
    }

    $stmt1->close();
    $stmt2->close();
    $conn->close();

    echo "<script>alert('Resepi berjaya dimuat naik!'); window.location.href='homepage.php';</script>";
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

    #voice-status {
      font-style: italic;
      color: green;
      margin-top: 5px;
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
          <option value="CHINESE CUISINE">CHINESE CUISINE</option>
          <option value="JAPANESE OR KOREAN CUISINE">JAPANESE OR KOREAN CUISINE</option>
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
        <button type="button" onclick="startVoiceInput()" style="margin-top:10px; background-color:#28a745;">🎤 Mula Rakaman Suara</button>
        <p id="voice-status"></p>

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

  <script>
    function startVoiceInput() {
      const textarea = document.getElementById('steps');
      const status = document.getElementById('voice-status');

      if (!('webkitSpeechRecognition' in window)) {
        alert("Browser anda tidak menyokong rakaman suara. Sila guna Google Chrome.");
        return;
      }

      const recognition = new webkitSpeechRecognition();
      recognition.lang = 'ms-MY';
      recognition.continuous = true;
      recognition.interimResults = true;

      recognition.onstart = () => {
        status.textContent = "🎙️ Merakam suara...";
      };

      recognition.onerror = (event) => {
        status.textContent = "❌ Ralat: " + event.error;
      };

      recognition.onend = () => {
        status.textContent = "✅ Rakaman tamat.";
      };

      recognition.onresult = (event) => {
        let final = '';
        for (let i = event.resultIndex; i < event.results.length; ++i) {
          if (event.results[i].isFinal) {
            final += event.results[i][0].transcript + "\n";
          }
        }
        textarea.value += final;
      };

      recognition.start();
    }
  </script>
</body>
</html>
