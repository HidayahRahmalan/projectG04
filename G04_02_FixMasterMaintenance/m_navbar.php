<?php


if (!isset($_SESSION['staffID'])) {
    header("Location: login.php");
    exit();
}

$staffName = $_SESSION['staffName'] ?? 'User';

// Determine the current page filename for active link highlight
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<style>
  .navbar {
    background: #34495e;
    padding: 12px 30px;
    display: flex;
    align-items: center;
    color: white;
    font-family: 'Roboto', sans-serif;
  }
  .navbar .logo {
    font-weight: 700;
    font-size: 20px;
    color: #f39c12;
    margin-right: auto;
    user-select: none;
  }
  .navbar a, .navbar .user-menu {
    color: white;
    text-decoration: none;
    margin-left: 25px;
    font-weight: 600;
    position: relative;
    cursor: pointer;
    transition: color 0.3s ease;
  }
  .navbar a:hover, .navbar .user-menu:hover {
    color: #f39c12;
  }
  .navbar a.active {
    color: #f39c12;
  }

  /* Dropdown container */
  .user-dropdown {
    position: relative;
    display: inline-block;
  }

  /* Dropdown content (hidden by default) */
  .user-dropdown-content {
    display: none;
    position: absolute;
    right: 0;
    background-color: #ecf0f1;
    min-width: 120px;
    box-shadow: 0 8px 16px rgba(0,0,0,0.2);
    border-radius: 6px;
    z-index: 1;
  }

  /* Links inside the dropdown */
  .user-dropdown-content a {
    color: #34495e;
    padding: 10px 15px;
    text-decoration: none;
    display: block;
    font-weight: 600;
  }

  .user-dropdown-content a:hover {
    background-color: #f39c12;
    color: white;
  }

  /* Show dropdown on hover */
  .user-dropdown:hover .user-dropdown-content {
    display: block;
  }
</style>

<div class="navbar">
  <div class="logo">Facilities Maintenance</div>
  <a href="maintenance_index.php" class="<?php echo $currentPage === 'maintenance_index.php' ? 'active' : ''; ?>">Dashboard</a>

  <div class="user-dropdown user-menu" tabindex="0">
    <div class="welcome">Welcome, <?php echo htmlspecialchars($staffName); ?></div>
    <div class="user-dropdown-content" tabindex="-1">
      <a href="logout.php">Logout</a>
    </div>
  </div>
</div>
