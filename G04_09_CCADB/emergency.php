<?php
require_once 'db_conn.php';
$clinics = [];
$sql = "SELECT ClinicID, ClinicName, Location, Phone FROM Clinic ORDER BY ClinicName";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $clinics[] = $row;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Emergency Booking Request</title>
    <link rel="stylesheet" href="css/style.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header and Sidebar remain the same -->
    <header class="header">
        <div class="header-left"><span class="menu-icon" onclick="toggleSidebar()">☰</span></div>
        <div class="header-center"><h1>Emergency Request</h1></div>
        <div class="header-right"><a href="login.php" class="login-button">Login</a></div>
    </header>
    <div id="sidebar" class="sidebar">
        <a href="index.php">Home</a>
        <a href="location.php">Find Clinic</a>
        <a href="appointment.php">Appointments</a>
        <a href="login.php">Medical Records</a>
        <a href="emergency.php" class="active">Contact Us</a>
    </div>

    <div class="main-content">
        <div class="content-card">
            <h2>Emergency & Same-Day Appointment Request</h2>
            <p>Please select a clinic to request an emergency slot. Your request will be sent to the clinic staff for approval. You will be contacted if a slot is available.</p>
            <div class="emergency-contact-list">
                <h3>Select a Clinic to Request a Slot</h3>
                <table class="location-table">
                    <thead><tr><th>Clinic Name</th><th>Location</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php if (!empty($clinics)): ?>
                            <?php foreach ($clinics as $clinic): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($clinic['ClinicName']); ?></td>
                                    <td><?php echo htmlspecialchars($clinic['Location']); ?></td>
                                    <td>
                                        <!-- This button now links to the request form -->
                                        <a href="request_emergency.php?clinic_id=<?php echo $clinic['ClinicID']; ?>" class="select-btn">Request Slot</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="3">No clinic information available.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script>function toggleSidebar() { document.getElementById("sidebar").classList.toggle("active"); }</script>
</body>
</html>