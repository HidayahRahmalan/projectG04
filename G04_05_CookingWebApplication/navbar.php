<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<nav class="navbar navbar-expand-lg bg-primary navbar-dark">
  <div class="container">
    <a class="navbar-brand" href="INDEX.php">CookingApp</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navb">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navb">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a href="view_recipes.php" class="nav-link">Recipes</a></li>
        <li class="nav-item"><a href="add_recipe.php" class="nav-link">Add Recipe</a></li>
        <li class="nav-item"><a href="team.php" class="nav-link">Team</a></li>
      </ul>
      <ul class="navbar-nav">
        <?php if (isset($_SESSION['user_id'])): ?>
          <li class="nav-item"><span class="navbar-text me-2">👤 <?= htmlspecialchars($_SESSION['username']) ?></span></li>
          <li class="nav-item"><a href="logout.php" class="nav-link">Logout</a></li>
        <?php else: ?>
          <li class="nav-item"><a href="signup.php" class="nav-link">Sign Up</a></li>
          <li class="nav-item"><a href="login.php" class="nav-link">Login</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>