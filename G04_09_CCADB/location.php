<?php
require_once 'db_conn.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Clinic Locations</title>
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
  
  <link rel="stylesheet" href="css/style.css" />
</head>
<body>

  <header class="header">
    <div class="header-left">
      <span class="menu-icon" onclick="toggleSidebar()">☰</span>
    </div>
    <div class="header-center">
      <h1>Find a Registered Clinic</h1>
    </div>
    <div class="header-right">
      <!-- FIX #1 -->
      <a href="login.php" class="login-button">Login</a>
    </div>
  </header>

  <div id="sidebar" class="sidebar">
    <a href="javascript:void(0)" class="closebtn" onclick="toggleSidebar()">×</a>
    <a href="index.php">Home</a>
    <a href="location.php" class="active">Find Clinic</a>
    <a href="appointment.php">Appointments</a>
    <!-- FIX #2 -->
    <a href="login.php">Medical Records</a>
    <a href="#">Contact Us</a>
  </div>

  <div class="main-content">
    <div class="content-card">
      <h2>Registered Clinics</h2>
      <p>Select a clinic from the list below to view details or book an appointment.</p>
      
      <table class="location-table">
        <thead>
          <tr>
            <th>Clinic Name</th>
            <th>Location</th>
            <th>Phone</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php
            $sql = "SELECT ClinicID, ClinicName, Location, Phone FROM Clinic ORDER BY ClinicName";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
              while($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row["ClinicName"]) . "</td>";
                echo "<td>" . htmlspecialchars($row["Location"]) . "</td>";
                echo "<td>" . htmlspecialchars($row["Phone"]) . "</td>";
                echo '<td><a href="appointment.php?clinic_id=' . $row["ClinicID"] . '" class="select-btn">Select</a></td>';
                echo "</tr>";
              }
            } else {
              echo "<tr><td colspan='4'>No clinics found.</td></tr>";
            }
            $conn->close();
          ?>
        </tbody>
      </table>
    </div>
  </div>

  <footer class="enhanced-footer">
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