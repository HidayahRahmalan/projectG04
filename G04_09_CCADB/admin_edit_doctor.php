<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}
require_once 'db_conn.php';

// Check if a doctor ID is provided
if (!isset($_GET['doctor_id'])) {
    header("Location: admin_manage_doctors.php");
    exit();
}
$doctor_id = intval($_GET['doctor_id']);

// Fetch the current doctor details to pre-fill the form
$stmt = $conn->prepare("SELECT Name, Department, Email, ContactNumber FROM Doctor WHERE DoctorID = ?");
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    header("Location: admin_manage_doctors.php");
    exit();
}
$doctor = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Doctor - Admin</title>
    <link rel="stylesheet" href="css/admin_style.css">
</head>
<body>
<div class="admin-wrapper">
    <?php 
        $active_page = 'doctors'; // Keep the 'Manage Doctors' link active
        include 'admin_sidebar.php'; 
    ?>

    <main class="admin-main-content">
        <header class="main-header">
            <h1>Edit Doctor: <?php echo htmlspecialchars($doctor['Name']); ?></h1>
        </header>

        <section class="data-section">
            <div class="data-table-container">
                <h2>Doctor Information</h2>
                <form action="admin_update_doctor.php" method="POST" class="booking-form">
                    <!-- Hidden input to pass the doctor's ID -->
                    <input type="hidden" name="doctor_id" value="<?php echo $doctor_id; ?>">
                    
                    <div class="form-group">
                        <label for="name">Full Name:</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($doctor['Name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="department">Department:</label>
                        <input type="text" id="department" name="department" value="<?php echo htmlspecialchars($doctor['Department']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address:</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($doctor['Email']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="contact_number">Contact Number:</label>
                        <input type="tel" id="contact_number" name="contact_number" value="<?php echo htmlspecialchars($doctor['ContactNumber']); ?>">
                    </div>

                    <div class="form-group">
                        <button type="submit" class="action-btn">Update Doctor</button>
                        <a href="admin_manage_doctors.php" class="back-button" style="margin-left: 15px;">Cancel</a>
                    </div>
                </form>
            </div>
        </section>
    </main>
</div>
</body>
</html>