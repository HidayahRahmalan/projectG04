<?php
session_start();
// Security check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Doctor') { header("Location: login.php"); exit(); }
require_once 'db_conn.php';

// Get Doctor and Patient IDs from session and URL
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT DoctorID FROM Doctor WHERE UserID = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$doctor_id = $stmt->get_result()->fetch_assoc()['DoctorID'];
$patient_id = intval($_GET['patient_id']);

// Fetch patient's name for the header
$stmt_patient_info = $conn->prepare("SELECT Name FROM Patient WHERE PatientID = ?");
$stmt_patient_info->bind_param("i", $patient_id);
$stmt_patient_info->execute();
$patient_name = $stmt_patient_info->get_result()->fetch_assoc()['Name'];

// Fetch all appointments for this patient with this doctor, checking for existing medical records
$sql = "SELECT a.AppointmentNo, a.AppointmentDate, a.Status, mr.RecordID
        FROM Appointment a
        LEFT JOIN MedicalRecord mr ON a.AppointmentNo = mr.AppointmentID
        WHERE a.PatientID = ? AND a.DoctorID = ?
        ORDER BY a.AppointmentDate DESC";
$stmt_appts = $conn->prepare($sql);
$stmt_appts->bind_param("ii", $patient_id, $doctor_id);
$stmt_appts->execute();
$appointments_history = $stmt_appts->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Patient History: <?php echo htmlspecialchars($patient_name); ?></title>
    <link rel="stylesheet" href="css/admin_style.css"> <!-- Reusing admin styles -->
</head>
<body>
<div class="admin-wrapper">
    <?php 
        $active_page = 'patients'; 
        include 'doctor_sidebar.php'; 
    ?>
    <main class="admin-main-content">
        <header class="main-header">
            <h1>Patient History: <?php echo htmlspecialchars($patient_name); ?></h1>
            <a href="doctor_my_patients.php" class="back-button" style="text-decoration:none;">← Back to Patient List</a>
        </header>
        <section class="data-section">
            <div class="data-table-container">
                <h2>Appointment History</h2>
                <table class="data-table">
                    <thead><tr><th>Date</th><th>Status</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php if ($appointments_history->num_rows > 0): ?>
                            <?php while($row = $appointments_history->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo date('d M Y, g:i A', strtotime($row['AppointmentDate'])); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo strtolower($row['Status']); ?>">
                                            <?php echo htmlspecialchars($row['Status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <!-- ========================================================= -->
                                        <!-- THE LOGIC FIX IS HERE: We now check the status and if a record exists. -->
                                        <!-- ========================================================= -->
                                        <?php if ($row['Status'] == 'Completed' && is_null($row['RecordID'])): ?>
                                            <!-- Case 1: Appointment is Completed and has NO record. Show "Add Record". -->
                                            <a href="doctor_add_record.php?appt_id=<?php echo $row['AppointmentNo']; ?>" class="action-btn">Add Record</a>
                                        <?php elseif (!is_null($row['RecordID'])): ?>
                                            <!-- Case 2: A record exists. Show "View Record". -->
                                            <a href="doctor_view_record.php?record_id=<?php echo $row['RecordID']; ?>" class="action-btn-secondary">View Record</a>
                                        <?php else: ?>
                                            <!-- Case 3: For all other statuses (Scheduled, Cancelled), show no action. -->
                                            <span style="color: #999;">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="3">No appointment history found for this patient.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>
</body>
</html>