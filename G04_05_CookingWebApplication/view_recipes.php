<?php
require 'connect.php';
session_start();

$where = [];
$params = [];

if (!empty($_GET['cuisine'])) {
    $where[] = "`CUISINE` LIKE ?";
    $params[] = '%' . $_GET['cuisine'] . '%';
}
if (!empty($_GET['dietary_type'])) {
    $where[] = "`DIETARY_TYPE` LIKE ?";
    $params[] = '%' . $_GET['dietary_type'] . '%';
}

$where_sql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$stmt = $conn->prepare("SELECT `RECIPE_ID`, `TITLE`, `CUISINE`, `DIETARY_TYPE`, `IMAGE_ID` FROM `RECIPE` $where_sql ORDER BY `DATE_TIME` DESC");
$stmt->execute($params);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>All Recipes - CookingApp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container mt-4">
    <h2>All Recipes</h2>
    <form class="row g-3 mb-4" method="get">
        <div class="col-auto">
            <input name="cuisine" class="form-control" placeholder="Cuisine" value="<?= htmlspecialchars($_GET['cuisine'] ?? '') ?>">
        </div>
        <div class="col-auto">
            <input name="dietary_type" class="form-control" placeholder="Dietary" value="<?= htmlspecialchars($_GET['dietary_type'] ?? '') ?>">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary">Filter</button>
        </div>
    </form>
    <div class="row g-4">
    <?php
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Fetch image file
        $img = 'assets/default_food.jpg';
        if ($row['IMAGE_ID']) {
            $imgstmt = $conn->prepare("SELECT `IMAGE_NAME`, `IMAGE_DATA`, `IMAGE_TYPE` FROM `IMAGE` WHERE `IMAGE_ID` = ?");
            $imgstmt->execute([$row['IMAGE_ID']]);
            $imgrow = $imgstmt->fetch(PDO::FETCH_ASSOC);
            if ($imgrow) {
                $img = 'data:image/' . strtolower($imgrow['IMAGE_TYPE']) . ';base64,' . base64_encode($imgrow['IMAGE_DATA']);
            }
        }
        ?>
        <div class="col-md-3">
            <div class="card h-100 shadow-sm">
                <img src="<?= $img ?>" class="card-img-top" style="height:150px;object-fit:cover;">
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($row['TITLE']) ?></h5>
                    <p class="card-text small mb-1"><strong>Cuisine:</strong> <?= htmlspecialchars($row['CUISINE']) ?></p>
                    <p class="card-text small"><strong>Dietary:</strong> <?= htmlspecialchars($row['DIETARY_TYPE']) ?></p>
                    <a href="recipe_detail.php?id=<?= $row['RECIPE_ID'] ?>" class="btn btn-primary btn-sm">View Recipe</a>
                </div>
            </div>
        </div>
        <?php
    }
    ?>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
