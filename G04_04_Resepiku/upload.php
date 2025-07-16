<?php
session_start();
if (!isset($_SESSION['UserID'])) {
  $_SESSION['UserID'] = 'U00001'; // For testing purposes
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
      text-align: center;
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

    .mic-btn {
      margin-top: 10px;
      padding: 8px 15px;
      background-color: #28a745;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }

    .mic-btn:hover {
      background-color: #218838;
    }

    #status {
      font-size: 14px;
      color: #555;
      margin-top: 5px;
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
    <h1>ResepiKu - Kongsi Resepi</h1>
  </header>

  <main>
    <div class="form-container">
      <h2>Kongsi Resepi Anda</h2>
      <form method="POST" action="upload_recipe_process.php" enctype="multipart/form-data">
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
        <button type="button" class="mic-btn" onclick="startVoiceInput()">🎤 Mulakan Input Suara</button>
        <p id="status">Status: Belum dimulakan</p>

        <label for="video">Video Tutorial</label>
        <input type="file" id="video" name="video" accept="video/*" required>

        <label for="ingredients">Senarai Bahan</label>
        <textarea id="ingredients" name="ingredients" rows="4" required></textarea>

        <label for="level">Tahap Kesukaran</label>
        <select id="level" name="level" required>
          <option value="">-- Pilih Tahap --</option>
          <option value="EASY">Mudah</option>
          <option value="IMMEDIATE">Sederhana</option>
          <option value="HARD">Sukar</option>
        </select>

        <button type="submit">Kongsi Resepi</button>
      </form>
    </div>
  </main>

  <footer>
    &copy; 2025 ResepiKu. All rights reserved.
  </footer>

  <script>
    let recognition;
    let isListening = false;

    function startVoiceInput() {
      const stepsField = document.getElementById("steps");
      const statusField = document.getElementById("status");

      if (!('webkitSpeechRecognition' in window)) {
        alert("Browser anda tidak menyokong input suara.");
        return;
      }

      if (!recognition) {
        recognition = new webkitSpeechRecognition();
        recognition.lang = 'ms-MY';
        recognition.continuous = true;
        recognition.interimResults = false;

        recognition.onresult = function (event) {
          let transcript = '';
          for (let i = event.resultIndex; i < event.results.length; i++) {
            transcript += event.results[i][0].transcript + " ";
          }
          stepsField.value += (stepsField.value ? "\n" : "") + transcript.trim();
          statusField.textContent = "Input suara berjaya dimasukkan.";
        };

        recognition.onerror = function (event) {
          statusField.textContent = "Ralat: " + event.error;
        };

        recognition.onend = function () {
          if (isListening) {
            statusField.textContent = "Sesi tamat. Klik semula untuk teruskan.";
            isListening = false;
          }
        };
      }

      if (!isListening) {
        recognition.start();
        isListening = true;
        statusField.textContent = "🎙️ Mula bercakap...";
      } else {
        recognition.stop();
        isListening = false;
        statusField.textContent = "🔇 Input suara dihentikan.";
      }
    }
  </script>
</body>
</html>
