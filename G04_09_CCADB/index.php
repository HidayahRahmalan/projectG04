<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db_conn.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Clinic Portal</title>
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  
  <link rel="stylesheet" href="css/style.css" />
</head>
<body>

  <header class="header">
    <div class="header-left">
      <span class="menu-icon" onclick="toggleSidebar()">☰</span>
    </div>
    <div class="header-center">
      <h1>Clinic System Portal</h1>
    </div>
    <div class="header-right">
      <a href="login.php" class="login-button">Login</a>
    </div>
  </header>

  <div id="sidebar" class="sidebar">
    <a href="javascript:void(0)" class="closebtn" onclick="toggleSidebar()">×</a>
    <a href="index.php">Home</a>
    <a href="location.php">Find Clinic</a>
    <a href="appointment.php">Appointments</a>
    <a href="login.php">Medical Records</a>
    <a href="emergency.php">Contact Us</a>
  </div>

  <section class="hero-section">
    <div class="hero-content">
      <h1 class="hero-title">Your Health, Your Control</h1>
      <p class="hero-subtitle">Book appointments and access your medical records seamlessly.</p>
      <a href="location.php" class="hero-button">Find a Clinic Near You</a>
    </div>
  </section>
  
  <div class="main-content">
    <div class="welcome-section">
        <h2>General Health Information</h2>
        <p>Explore common health topics and stay informed.</p>
    </div>

    <div class="info-cards-container">
        <?php
            $sql = "SELECT Title, Summary, ImagePath FROM HealthArticles LIMIT 3";
            $result = $conn->query($sql);
            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo '<div class="info-card">';
                    echo '  <img src="' . htmlspecialchars($row["ImagePath"]) . '" alt="' . htmlspecialchars($row["Title"]) . '">';
                    echo '  <h3>' . htmlspecialchars($row["Title"]) . '</h3>';
                    echo '  <p>' . htmlspecialchars($row["Summary"]) . '</p>';
                    echo '  <a href="#" class="read-more-btn">Read More</a>';
                    echo '</div>';
                }
            } else {
                echo "<p>No health articles found.</p>";
            }
        ?>
    </div>
  </div>

  <footer class="enhanced-footer">
    <div class="footer-container">
      <div class="footer-column">
        <h3>Clinic System</h3>
        <p>Providing seamless access to healthcare services for everyone.</p>
      </div>
      <div class="footer-column">
        <h3>Quick Links</h3>
        <ul>
          <li><a href="index.php">Home</a></li>
          <li><a href="location.php">Find Clinic</a></li>
          <!-- THE FIX IS HERE -->
          <li><a href="login.php">Patient Login</a></li>
          <li><a href="#">Contact Us</a></li>
        </ul>
      </div>
      <div class="footer-column">
        <h3>Contact Info</h3>
        <p>123 Medical Avenue,<br>Melaka, 75450, Malaysia</p>
        <p>Phone: (06) 123-4567</p>
      </div>
    </div>
    <div class="footer-bottom">
      <p>© 2025 Clinic System. All rights reserved.</p>
    </div>
  </footer>

  <script>
    function toggleSidebar() {
      document.getElementById("sidebar").classList.toggle("active");
    }
  </script>

</body>
</html>