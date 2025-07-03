<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}
require_once 'db_conn.php';

// Check if an appointment ID is provided
if (!isset($_GET['id'])) {
    header("Location: admin_manage_appointments.php");
    exit();
}
$appointment_id = intval($_GET['id']);

// Fetch the current appointment details to display them
$sql = "SELECT a.AppointmentNo, a.AppointmentDate, p.Name as PatientName, d.Name as DoctorName
        FROM Appointment a
        JOIN Patient p ON a.PatientID = p.PatientID
        JOIN Doctor d ON a.DoctorID = d.DoctorID
        WHERE a.AppointmentNo = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $appointment_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    // No appointment found with that ID
    header("Location: admin_manage_appointments.php");
    exit();
}
$appointment = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reschedule Appointment - Admin</title>
    <link rel="stylesheet" href="css/admin_style.css">
</head>
<body>
<div class="admin-wrapper">
    <?php include 'admin_sidebar.php'; ?>

    <main class="admin-main-content">
        <header class="main-header">
            <h1>Reschedule Appointment #<?php echo $appointment_id; ?></h1>
        </header>

        <section class="data-section">
            <div class="data-table-container">
                <h2>Current Appointment Details</h2>
                <div class="booking-summary" style="background-color: #f4f7f6; border-left: 5px solid #1e88e5; padding: 20px; margin-bottom: 20px;">
                    <p><strong>Patient:</strong> <?php echo htmlspecialchars($appointment['PatientName']); ?></p>
                    <p><strong>Doctor:</strong> Dr. <?php echo htmlspecialchars($appointment['DoctorName']); ?></p>
                    <p><strong>Current Date & Time:</strong> <?php echo date('d M Y, g:i A', strtotime($appointment['AppointmentDate'])); ?></p>
                </div>

                <h2>Set New Date and Time</h2>
                <!-- The form submits to our new backend script -->
                <form action="admin_update_appointment.php" method="POST" class="booking-form">
                    <!-- Hidden input to pass the appointment ID -->
                    <input type="hidden" name="appointment_id" value="<?php echo $appointment_id; ?>">
                    
                    <div class="form-group">
                        <label for="new_datetime">New Appointment Date and Time:</label>
                        <!-- Use datetime-local for easy date and time picking -->
                        <input type="datetime-local" id="new_datetime" name="new_datetime" required>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="action-btn">Update Appointment</button>
                        <a href="admin_manage_appointments.php" class="back-button" style="margin-left: 15px;">Cancel</a>
                    </div>
                </form>
            </div>
        </section>
    </main>
</div>
</body>
</html>