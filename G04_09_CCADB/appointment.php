<?php
require_once 'db_conn.php';
$selected_clinic_id = isset($_GET['clinic_id']) ? intval($_GET['clinic_id']) : 0;

$clinics = [];
$sql_clinics = "SELECT ClinicID, ClinicName FROM Clinic ORDER BY ClinicName";
$result_clinics = $conn->query($sql_clinics);
if ($result_clinics->num_rows > 0) {
    while($row = $result_clinics->fetch_assoc()) {
        $clinics[] = $row;
    }
}

$departments = [];
$sql_depts = "SELECT DISTINCT Department FROM Doctor WHERE Department IS NOT NULL AND Department != '' ORDER BY Department";
$result_depts = $conn->query($sql_depts);
if ($result_depts->num_rows > 0) {
    while($row = $result_depts->fetch_assoc()) {
        $departments[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Book an Appointment</title>
  
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
      <h1>Book an Appointment</h1>
    </div>
    <div class="header-right">
      <!-- FIX #1 -->
      <a href="login.php" class="login-button">Login</a>
    </div>
  </header>
  
  <div id="sidebar" class="sidebar">
    <a href="javascript:void(0)" class="closebtn" onclick="toggleSidebar()">×</a>
    <a href="index.php">Home</a>
    <a href="location.php">Find Clinic</a>
    <a href="appointment.php" class="active">Appointments</a>
    <!-- FIX #2 -->
    <a href="login.php">Medical Records</a>
    <a href="#">Contact Us</a>
  </div>

  <div class="main-content">
    <div class="content-card booking-form-container">
      <h2>Step 1: Find Your Slot</h2>
      <p>Select a clinic, service, and desired date to check for available appointments.</p>
      
      <form action="find_slots.php" method="GET" class="booking-form">
        <div class="form-group">
          <label for="clinic">Choose a Clinic:</label>
          <select name="clinic_id" id="clinic" required>
            <option value="">-- Select a Clinic --</option>
            <?php foreach ($clinics as $clinic): ?>
              <option value="<?php echo htmlspecialchars($clinic['ClinicID']); ?>" 
                      <?php if ($clinic['ClinicID'] == $selected_clinic_id) echo 'selected'; ?>>
                <?php echo htmlspecialchars($clinic['ClinicName']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label for="department">Choose a Service:</label>
          <select name="department" id="department" required>
            <option value="">-- Select a Service --</option>
            <?php foreach ($departments as $dept): ?>
              <option value="<?php echo htmlspecialchars($dept['Department']); ?>">
                <?php echo htmlspecialchars($dept['Department']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label for="appointment_date">Select a Date:</label>
          <input type="date" id="appointment_date" name="appointment_date" required>
        </div>
        <div class="form-group">
          <button type="submit" class="hero-button">Check Availability</button>
        </div>
      </form>
      <div class="navigation-links">
        <a href="index.php" class="back-button">Return to Homepage</a>
      </div>
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
    document.addEventListener('DOMContentLoaded', function() {
        var today = new Date().toISOString().split('T')[0];
        document.getElementById('appointment_date').setAttribute('min', today);
    });
  </script>

</body>
</html>