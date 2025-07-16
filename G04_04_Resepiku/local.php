<?php
include "connection.php";
include "header.php";
// Fixed cuisine type
$foodType = 'Local Cuisine';

// Get filter inputs from URL
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';

// SQL base
$sql = "SELECT FoodID, FoodTitle, FoodDesc, FoodImage FROM FOOD WHERE FoodType = ?";
$params = [$foodType];
$types = "s";

// If filter category exists
if (!empty($category)) {
  $sql .= " AND FoodCategory LIKE ?";
  $params[] = "%$category%";
  $types .= "s";
}

// If search term exists
if (!empty($search)) {
  $sql .= " AND FoodTitle LIKE ?";
  $params[] = "%$search%";
  $types .= "s";
}

// Prepare statement
$stmt = $conn->prepare($sql);
if (!empty($params)) {
  $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>ResepiKu - Cari Resepi</title>
  <style>
    body {
      font-family: 'Roboto', sans-serif;
      margin: 0;
      padding: 0;
      background-color: #fff8f0;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
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

    .dropdown-content {
      display: none;
      position: absolute;
      background-color: white;
      color: black;
      min-width: 160px;
      box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
      z-index: 1;
      border-radius: 5px;
    }

    .dropdown-content a {
      color: black;
      padding: 10px 15px;
      text-decoration: none;
      display: block;
    }

    .dropdown:hover .dropdown-content {
      display: block;
    }

    main {
      flex: 1;
      padding: 30px;
    }

    .page-title {
      text-align: center;
      color: #ff6347;
      margin-bottom: 20px;
      font-size: 28px;
    }

    .filter-bar {
      text-align: center;
      margin-bottom: 30px;
    }

    .filter-bar select,
    .filter-bar input[type="text"],
    .filter-bar button {
      padding: 8px;
      border: 1px solid #ccc;
      border-radius: 5px;
      margin: 5px;
    }

    .recipe-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 25px;
    }

    .recipe-card {
      background-color: white;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s ease;
      cursor: pointer;
    }

    .recipe-card:hover {
      transform: translateY(-5px);
    }

    .recipe-image {
      width: 100%;
      height: 200px;
      object-fit: cover;
    }

    .card-content {
      padding: 15px;
    }

    .card-content h3 {
      margin: 0 0 10px 0;
    }

    .card-content p {
      margin: 0;
      font-size: 14px;
      color: #555;
    }

    .no-recipes {
      text-align: center;
      grid-column: 1 / -1;
      color: #666;
      padding: 40px 0;
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
          <a href="Local.php">Local Cuisine</a>
          <a href="western.php">Western Cuisine</a>
          <a href="japanese.php">Japanese Cuisine</a>
          <a href="chinese.php">Chinese Cuisine</a>
          <a href="korean.php">Korean Cuisine</a>
          <a href="dessert.php">Dessert</a>
          <a href="beverages.php">Beverages</a>
          <a href="other.php">Other</a>
        </div>
      </div>
      <a href="notifications.html">Notifikasi</a>
      <a href="login.html">Log Masuk</a>
      <a href="register.html">Daftar</a>
      <a href="upload-recipe.html">Kongsi Resepi</a>
    </nav>


  <main>
    <h1 class="page-title">Local Cuisine</h1>

    <div class="filter-bar">
      <form method="GET">
        <select name="category">
          <option value="">-- Pilih Kategori --</option>
          <option value="Halal" <?= ($category == "Halal" ? "selected" : "") ?>>Halal</option>
          <option value="Vegetarian" <?= ($category == "Vegetarian" ? "selected" : "") ?>>Vegetarian</option>
          <option value="Vegan" <?= ($category == "Vegan" ? "selected" : "") ?>>Vegan</option>
          <option value="High-Protein" <?= ($category == "High-Protein" ? "selected" : "") ?>>High-Protein</option>
          <option value="Gluten-Free" <?= ($category == "Gluten-Free" ? "selected" : "") ?>>Gluten-Free</option>
          <option value="Dairy-Free" <?= ($category == "Dairy-Free" ? "selected" : "") ?>>Dairy-Free</option>
          <option value="Other" <?= ($category == "Other" ? "selected" : "") ?>>Other</option>
        </select>

        <input type="text" name="search" placeholder="Cari nama resepi..." value="<?= htmlspecialchars($search) ?>" />

        <button type="submit">Cari</button>
      </form>
    </div>

    <div class="recipe-grid">
      <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
          <div class="recipe-card" onclick="window.location.href='detail.php?id=<?= $row['FoodID'] ?>'">
            <img class="recipe-image" src="get-image.php?id=<?= $row['FoodID'] ?>" alt="<?= htmlspecialchars($row['FoodTitle']) ?>" />
            <div class="card-content">
              <h3><?= htmlspecialchars($row['FoodTitle']) ?></h3>
              <p><?= htmlspecialchars($row['FoodDesc']) ?></p>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="no-recipes">
          <p>Tiada resepi dijumpai untuk carian ini.</p>
          <p><a href="upload.php">Kongsi resepi anda sekarang!</a></p>
        </div>
      <?php endif; ?>
    </div>
  </main>

  <footer>
    &copy; <?= date('Y') ?> ResepiKu. Hak cipta terpelihara.
  </footer>
</body>
</html>
