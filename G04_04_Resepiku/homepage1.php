<?php include 'header.php'; ?>
<?php include 'connection.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>ResepiKu - Home</title>
  <link rel="stylesheet" href="styles.css" />
  <style>
    /* styling same as before, shortened for brevity */
  </style>
</head>
<body>
  <main>
    <div class="filter-bar">
      <select id="categoryFilter" onchange="filterByCategory()">
        <option value="all">-- Pilih Kategori --</option>
        <option value="LOCAL CUISINE">Local Cuisine</option>
        <option value="WESTERN CUISINE">Western Cuisine</option>
        <option value="CHINESE CURSINE">Chinese Cuisine</option>
        <option value="JAPANESE OR KOREAN CUISINE">Japanese/Korean</option>
        <option value="DESSERT">Dessert</option>
        <option value="BEVERAGE">Beverages</option>
        <option value="OTHER">Other</option>
      </select>
    </div>

    <div class="search-bar">
      <input type="text" id="searchInput" placeholder="Cari nama resepi..." onkeyup="filterRecipes()">
    </div>

    <div class="recipe-grid" id="recipeContainer">
      <?php
        $sql = "
          SELECT 
            RECIPE.RecipeID,
            FOOD.FoodTitle,
            FOOD.FoodCategory,
            FOOD.FoodDesc,
            FOOD.FoodImage
          FROM RECIPE
          JOIN FOOD ON RECIPE.FoodID = FOOD.FoodID
          ORDER BY RECIPE.RecipeID DESC
        ";
        $result = $conn->query($sql);

        if ($result->num_rows > 0):
          while ($row = $result->fetch_assoc()):
            // Convert BLOB image to base64
            $imageData = base64_encode($row['FoodImage']);
            $imageSrc = 'data:image/jpeg;base64,' . $imageData;
      ?>
        <div class="recipe-card" data-category="<?= htmlspecialchars($row['FoodCategory']) ?>" onclick="viewRecipe('<?= $row['RecipeID'] ?>')">
          <img src="<?= $imageSrc ?>" alt="<?= htmlspecialchars($row['FoodTitle']) ?>">
          <div class="card-content">
            <h3><?= htmlspecialchars($row['FoodTitle']) ?></h3>
            <p><?= htmlspecialchars($row['FoodDesc']) ?></p>
          </div>
        </div>
      <?php
          endwhile;
        else:
          echo "<p style='text-align:center;'>Tiada resepi tersedia.</p>";
        endif;
      ?>
    </div>
  </main>

  <?php include 'footer.php'; ?>

  <script>
    function viewRecipe(recipeId) {
      window.location.href = `detail1.php?id=${recipeId}`;
    }

    function filterRecipes() {
      const input = document.getElementById("searchInput").value.toLowerCase();
      const cards = document.querySelectorAll(".recipe-card");

      cards.forEach(card => {
        const title = card.querySelector("h3").innerText.toLowerCase();
        card.style.display = title.includes(input) ? "block" : "none";
      });
    }

    function filterByCategory() {
      const selectedCategory = document.getElementById("categoryFilter").value;
      const cards = document.querySelectorAll(".recipe-card");

      cards.forEach(card => {
        const category = card.getAttribute("data-category");
        if (selectedCategory === "all" || category === selectedCategory) {
          card.style.display = "block";
        } else {
          card.style.display = "none";
        }
      });
    }

    window.addEventListener("DOMContentLoaded", () => {
      const notifCount = document.getElementById("notifCount");
      const count = 2;
      notifCount.textContent = count;
      notifCount.style.display = count > 0 ? "inline-block" : "none";
    });
  </script>
</body>
</html>
