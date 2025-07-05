<?php
// It's good practice to ensure session is started, although your pages likely do this.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect to login if not authenticated
if (!isset($_SESSION['staffID'])) {
    header("Location: login.php");
    exit();
}

// Get user details from session
$staffName = $_SESSION['staffName'] ?? 'User';
$role = $_SESSION['role'] ?? null;

// Determine the current page filename for active link highlighting
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<style>
  /* Your existing styles are great, no changes needed here */
  @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap');
  .navbar {
    background: #34495e;
    padding: 12px 30px;
    display: flex;
    align-items: center;
    color: white;
    font-family: 'Roboto', sans-serif;
    margin-bottom: 30px; /* Added margin for spacing with content */
  }
  .navbar .logo {
    font-weight: 700;
    font-size: 20px;
    color: #f39c12;
    margin-right: auto;
    user-select: none;
    text-decoration: none;
  }
  .navbar .logo:visited {
    color: #f39c12;
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
    font-weight: 700;
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
    overflow: hidden; /* Ensures child elements conform to border-radius */
  }

  /* Links inside the dropdown */
  .user-dropdown-content a {
    color: #34495e;
    padding: 10px 15px;
    text-decoration: none;
    display: block;
    font-weight: 600;
    margin-left: 0; /* Reset margin for dropdown links */
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
  <!-- The logo now links to the correct dashboard based on the user's role -->
  <a href="<?php echo ($role === 'Admin') ? 'index.php' : 'maintenance_index.php'; ?>" class="logo">
    Facilities Maintenance
  </a>

  <!-- Navigation links are now dynamically rendered based on the user's role -->
  <?php if ($role === 'Admin'): ?>
    <!-- Admin Links -->
    <a href="index.php" class="<?php echo $currentPage === 'index.php' ? 'active' : ''; ?>">Dashboard</a>
    <a href="report_analytics.php" class="<?php echo $currentPage === 'report_analytics.php' ? 'active' : ''; ?>">Analytics</a>
    <!-- ===== NEW LINK ADDED HERE ===== -->
    <a href="about_us.php" class="<?php echo $currentPage === 'about_us.php' ? 'active' : ''; ?>">About Us</a>
  
  <?php else: ?>
    <!-- Maintenance Staff / Other Links -->
    <a href="maintenance_index.php" class="<?php echo $currentPage === 'maintenance_index.php' ? 'active' : ''; ?>">My Tasks</a>
  <?php endif; ?>

  <!-- This link is common for all roles -->
  <a href="report_create.php" class="<?php echo $currentPage === 'report_create.php' ? 'active' : ''; ?>">Make a Report</a>

  <!-- User dropdown menu remains the same for all roles -->
  <div class="user-dropdown user-menu" tabindex="0">
    <div class="welcome">Welcome, <?php echo htmlspecialchars($staffName); ?></div>
    <div class="user-dropdown-content" tabindex="-1">
      <a href="logout.php">Logout</a>
    </div>
  </div>
</div>
