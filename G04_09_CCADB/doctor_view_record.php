<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Doctor') { header("Location: login.php"); exit(); }
require_once 'db_conn.php';

// Check if a record ID is provided
if (!isset($_GET['record_id'])) {
    // A fallback redirect might be needed here, e.g., to the doctor's dashboard
    header("Location: doctor_dashboard.php");
    exit();
}
$record_id = intval($_GET['record_id']);

// Fetch the medical record details along with patient and appointment info
$sql = "SELECT 
            mr.Diagnosis, 
            mr.Notes, 
            mr.RecordDate,
            a.AppointmentDate,
            p.PatientID,
            p.Name as PatientName
        FROM MedicalRecord mr
        JOIN Appointment a ON mr.AppointmentID = a.AppointmentNo
        JOIN Patient p ON a.PatientID = p.PatientID
        WHERE mr.RecordID = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $record_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Medical record not found.");
}
$record = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Medical Record</title>
    <link rel="stylesheet" href="css/admin_style.css">
    <style>
        /* Add some specific styles for this page */
        .record-details {
            background-color: #f9f9f9;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 25px;
        }
        .record-details h3 {
            margin-top: 0;
            border-bottom: 2px solid #e0f2f1;
            padding-bottom: 10px;
        }
        .record-details p {
            line-height: 1.6;
            white-space: pre-wrap; /* This preserves line breaks in the notes */
        }
    </style>
</head>
<body>
<div class="admin-wrapper">
    <?php $active_page = 'patients'; include 'doctor_sidebar.php'; ?>
    <main class="admin-main-content">
        <header class="main-header">
            <h1>Medical Record Details</h1>
            <!-- This link takes the doctor back to the specific patient's history page -->
            <a href="doctor_view_patient.php?patient_id=<?php echo $record['PatientID']; ?>" class="back-button" style="text-decoration:none;">← Back to <?php echo htmlspecialchars($record['PatientName']); ?>'s History</a>
        </header>

        <section class="data-section">
            <div class="data-table-container">
                <div class="record-details">
                    <h3>Diagnosis</h3>
                    <p><?php echo nl2br(htmlspecialchars($record['Diagnosis'])); ?></p>
                </div>

                <div class="record-details" style="margin-top: 20px;">
                    <h3>Consultation Notes</h3>
                    <p><?php echo $record['Notes'] ? nl2br(htmlspecialchars($record['Notes'])) : '<em>No additional notes were recorded.</em>'; ?></p>
                </div>
                
                <div class="record-details" style="margin-top: 20px; background-color: transparent; border: none; padding: 0;">
                    <p><strong>Appointment Date:</strong> <?php echo date('d M Y, g:i A', strtotime($record['AppointmentDate'])); ?></p>
                    <p><strong>Record Created On:</strong> <?php echo date('d M Y, g:i A', strtotime($record['RecordDate'])); ?></p>
                </div>
            </div>
        </section>
    </main>
</div>
</body>
</html>