<?php
session_start();
require_once 'db_connect.php';

$userID = $_SESSION['userID'];

// Get filter values from GET
$cuisine = $_GET['cuisine'] ?? '';
$dietary = $_GET['dietary_type'] ?? '';

// Build query with filters
$sql = "SELECT * FROM RECIPE WHERE userID = ?";
$params = [$userID];
$types = "i";

if ($cuisine !== '') {
    $sql .= " AND cuisine_type = ?";
    $params[] = $cuisine;
    $types .= "s";
}

if ($dietary !== '') {
    $sql .= " AND dietary_type = ?";
    $params[] = $dietary;
    $types .= "s";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html>
<head>
    <title>Recipe List</title>
    <link rel="stylesheet" href="style.css">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        th {
            background-color: #f5d7a0;
            text-align: left;
        }
        button {
            margin-right: 5px;
            background-color: #e67e22;
            border: none;
            padding: 5px 10px;
            color: white;
            border-radius: 4px;
            cursor: pointer;
        }
        button.delete {
            background-color: #c0392b;
        }
        button:hover {
            opacity: 0.8;
        }
        form.filter-form {
            margin-bottom: 20px;
        }
        form.filter-form select {
            padding: 5px;
            margin-right: 10px;
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<h2>Your Recipes</h2>

<!-- Filter form -->
<form method="GET" action="recipe_list.php" class="filter-form">
    <label for="cuisine">Cuisine:</label>
    <select name="cuisine" id="cuisine">
        <option value="">-- All --</option>
        <option value="Italian" <?= ($cuisine == 'Italian') ? 'selected' : '' ?>>Italian</option>
        <option value="Mexican" <?= ($cuisine == 'Mexican') ? 'selected' : '' ?>>Mexican</option>
        <option value="Indian" <?= ($cuisine == 'Indian') ? 'selected' : '' ?>>Indian</option>
        <option value="Chinese" <?= ($cuisine == 'Chinese') ? 'selected' : '' ?>>Chinese</option>
    </select>

    <label for="dietary_type">Dietary Type:</label>
    <select name="dietary_type" id="dietary_type">
        <option value="">-- All --</option>
        <option value="Vegan" <?= ($dietary == 'Vegan') ? 'selected' : '' ?>>Vegan (No animal products)</option>
        <option value="Gluten-Free" <?= ($dietary == 'Gluten-Free') ? 'selected' : '' ?>>Gluten-Free (No wheat, barley, or rye)</option>
        <option value="Vegetarian" <?= ($dietary == 'Vegetarian') ? 'selected' : '' ?>>Vegetarian (No meat or fish)</option>
        <option value="Keto" <?= ($dietary == 'Keto') ? 'selected' : '' ?>>Keto (Low carb, high fat)</option>
    </select>

    <button type="submit">Filter</button>
    <a href="recipe_list.php" style="margin-left: 10px;">Clear</a>
</form>

<!-- Recipe table -->
<table>
    <thead>
        <tr>
            <th>Title</th>
            <th>Ingredients</th>
            <th>Dietary Type</th>
            <th>Cuisine Type</th>
            <th>Created At</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result->num_rows === 0): ?>
            <tr><td colspan="6" style="text-align:center;">No recipes found.</td></tr>
        <?php else: ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr data-recipe-id="<?= $row['recipeID'] ?>">
                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <td><?= htmlspecialchars($row['ingredient']) ?></td>
                    <td><?= htmlspecialchars($row['dietary_type']) ?></td>
                    <td><?= htmlspecialchars($row['cuisine_type']) ?></td>
                    <td>
                        <?php
                            $date = new DateTime($row['created_at']);
                            echo $date->format('d-m-Y H:i:s');
                        ?>
                    </td>

                    <td>
                        <a href="view_recipe.php?recipeID=<?= $row['recipeID'] ?>"><button type="button" style="background-color: #3498db;">View</button></a>
                        <a href="edit_recipe.php?recipeID=<?= $row['recipeID'] ?>"><button type="button">Edit</button></a>
                        <a href="delete_recipe.php?recipeID=<?= $row['recipeID'] ?>" onclick="return confirm('Are you sure you want to delete this recipe?');">
                            <button type="button" class="delete">Delete</button>
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php endif; ?>
    </tbody>
</table>

<?php include 'footer.php'; ?>
<script>
document.addEventListener("DOMContentLoaded", function () {
    // Loop through each row in the table body
    document.querySelectorAll('tr[data-recipe-id]').forEach(row => {
        const recipeID = row.getAttribute('data-recipe-id');
        const key = "recipe_" + recipeID;

        // If the recipe key doesn't exist in localStorage, it means it's "deleted"
        if (localStorage.getItem('deleted_recipe_' + recipeID)) {
            row.remove();
        }
    });
});
</script>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
