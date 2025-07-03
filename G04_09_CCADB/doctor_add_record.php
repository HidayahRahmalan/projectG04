<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Doctor') { header("Location: login.php"); exit(); }
require_once 'db_conn.php';

$appointment_id = intval($_GET['appt_id']);
// Fetch appointment info to display for context
$stmt = $conn->prepare("SELECT a.AppointmentDate, p.PatientID, p.Name as PatientName FROM Appointment a JOIN Patient p ON a.PatientID = p.PatientID WHERE a.AppointmentNo = ?");
$stmt->bind_param("i", $appointment_id);
$stmt->execute();
$appt = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><title>Add Medical Record</title><link rel="stylesheet" href="css/admin_style.css">
</head>
<body>
<div class="admin-wrapper">
    <?php $active_page = 'patients'; include 'doctor_sidebar.php'; ?>
    <main class="admin-main-content">
        <header class="main-header">
            <h1>Add Medical Record</h1>
            <p>For appointment on <?php echo date('d M Y', strtotime($appt['AppointmentDate'])); ?> for patient <?php echo htmlspecialchars($appt['PatientName']); ?></p>
        </header>
        <section class="data-section">
            <div class="data-table-container">
                <form action="doctor_save_record.php" method="POST" class="booking-form">
                    <input type="hidden" name="appointment_id" value="<?php echo $appointment_id; ?>">
                    <input type="hidden" name="patient_id" value="<?php echo $appt['PatientID']; ?>">
                    <div class="form-group">
                        <label for="diagnosis">Diagnosis:</label>
                        <textarea id="diagnosis" name="diagnosis" rows="4" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="notes">Consultation Notes:</label>
                        <textarea id="notes" name="notes" rows="6"></textarea>
                    </div>
                    <!-- We can add prescriptions here later -->
                    <div class="form-group">
                        <button type="submit" class="action-btn">Save Record</button>
                        <a href="doctor_view_patient.php?patient_id=<?php echo $appt['PatientID']; ?>" class="back-button" style="margin-left: 15px;">Cancel</a>
                    </div>
                </form>
            </div>
        </section>
    </main>
</div>
</body>
</html>