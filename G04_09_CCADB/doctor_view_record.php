<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Doctor') { header("Location: login.php"); exit(); }
require_once 'db_conn.php';

if (!isset($_GET['record_id'])) {
    header("Location: doctor_dashboard.php");
    exit();
}
$record_id = intval($_GET['record_id']);

// Updated SQL to get all details needed for the page and the PDF
$sql = "SELECT 
            mr.Diagnosis, mr.Notes, mr.RecordDate,
            a.AppointmentNo, a.AppointmentDate,
            p.PatientID, p.Name as PatientName,
            mc.StartDate as MC_StartDate, mc.NumberOfDays as MC_Days, mc.Reason as MC_Reason, mc.ImagePath as MC_ImagePath
        FROM MedicalRecord mr
        JOIN Appointment a ON mr.AppointmentID = a.AppointmentNo
        JOIN Patient p ON a.PatientID = p.PatientID
        LEFT JOIN MedicalCertificate mc ON a.AppointmentNo = mc.AppointmentID
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
    <!-- Link to the correct stylesheet -->
    <link rel="stylesheet" href="css/admin_style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        /* Specific styles for this page's content */
        .record-details {
            background-color: #f9f9f9; border: 1px solid #e0e0e0; border-radius: 8px; 
            padding: 25px; margin-bottom: 20px;
        }
        .record-details h3 {
            margin-top: 0; border-bottom: 2px solid #e0f2f1; padding-bottom: 10px;
        }
        .record-details p { line-height: 1.6; white-space: pre-wrap; }
    </style>
</head>
<body>

<!-- ============================================================== -->
<!-- THE FIX IS HERE: The page now has the correct wrapper and sidebar structure -->
<!-- ============================================================== -->
<div class="admin-wrapper">
    <?php 
        $active_page = 'patients'; // Keep "My Patients" active in the sidebar
        include 'doctor_sidebar.php'; 
    ?>
    <main class="admin-main-content">
        <header class="main-header">
            <h1>Medical Record Details</h1>
            <a href="doctor_view_patient.php?patient_id=<?php echo $record['PatientID']; ?>" class="back-button" style="text-decoration:none;">← Back to <?php echo htmlspecialchars($record['PatientName']); ?>'s History</a>
        </header>

        <section class="data-section">
            <div class="data-table-container">
                <div class="record-details">
                    <h3>Diagnosis</h3>
                    <p><?php echo nl2br(htmlspecialchars($record['Diagnosis'])); ?></p>
                </div>
                <div class="record-details">
                    <h3>Consultation Notes</h3>
                    <p><?php echo $record['Notes'] ? nl2br(htmlspecialchars($record['Notes'])) : '<em>No additional notes were recorded.</em>'; ?></p>
                </div>
                
                <?php if (!is_null($record['MC_StartDate'])): ?>
                    <div class="record-details">
                        <h3>Medical Certificate (MC) Details</h3>
                        <p><strong>Start Date:</strong> <?php echo date('d M Y', strtotime($record['MC_StartDate'])); ?></p>
                        <p><strong>Number of Days:</strong> <?php echo htmlspecialchars($record['MC_Days']); ?></p>
                        <p><strong>Reason:</strong> <?php echo htmlspecialchars($record['MC_Reason']); ?></p>
                        
                        <div style="margin-top: 20px;">
                            <?php if (!is_null($record['MC_ImagePath'])): ?>
                                <a href="<?php echo htmlspecialchars($record['MC_ImagePath']); ?>" class="action-btn" download>Download Uploaded MC</a>
                            <?php else: ?>
                                <a href="generate_mc_pdf.php?appt_id=<?php echo $record['AppointmentNo']; ?>" target="_blank" class="action-btn">Print/Download MC</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div style="margin-top: 20px; padding: 10px; color: #555;">
                    <p><strong>Appointment Date:</strong> <?php echo date('d M Y, g:i A', strtotime($record['AppointmentDate'])); ?></p>
                    <p><strong>Record Created On:</strong> <?php echo date('d M Y, g:i A', strtotime($record['RecordDate'])); ?></p>
                </div>
            </div>
        </section>
    </main>
</div>
</body>
</html>