<?php
session_start();
// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Doctor') { 
    header("Location: login.php"); 
    exit(); 
}
require_once 'db_conn.php';

// Get the appointment ID from the URL
$appointment_id = intval($_GET['appt_id']);

// ===================================================================
// THE FIX IS HERE: The SQL query now also fetches `a.Notes`.
// We will use this to pre-fill the diagnosis field.
// ===================================================================
$stmt = $conn->prepare("SELECT a.AppointmentDate, a.Notes as InitialDiagnosis, p.PatientID, p.Name as PatientName 
                       FROM Appointment a 
                       JOIN Patient p ON a.PatientID = p.PatientID 
                       WHERE a.AppointmentNo = ?");
$stmt->bind_param("i", $appointment_id);
$stmt->execute();
$appt = $stmt->get_result()->fetch_assoc();

// If no appointment is found, redirect back
if (!$appt) {
    header("Location: doctor_my_patients.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Medical Record</title>
    <link rel="stylesheet" href="css/admin_style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
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
                <form action="doctor_save_record.php" method="POST" class="booking-form" enctype="multipart/form-data">
                    <input type="hidden" name="appointment_id" value="<?php echo $appointment_id; ?>">
                    <input type="hidden" name="patient_id" value="<?php echo $appt['PatientID']; ?>">
                    
                    <div class="form-group">
                        <label for="diagnosis">Diagnosis:</label>
                        <!-- THE FIX IS HERE: The textarea is now pre-filled with the InitialDiagnosis. -->
                        <textarea id="diagnosis" name="diagnosis" rows="4" ><?php echo htmlspecialchars($appt['InitialDiagnosis'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="notes">Consultation Notes (Optional):</label>
                        <textarea id="notes" name="notes" rows="6"></textarea>
                    </div>

                    <div class="form-divider"><span>Medical Certificate (MC) - Optional</span></div>
                    
                    <div class="form-group">
                        <label>Choose MC Method:</label>
                        <select id="mc_method" name="mc_method">
                            <option value="none" selected>No MC Required</option>
                            <option value="generate">Generate with AI Assistance</option>
                            <option value="upload">Upload Existing MC Image</option>
                        </select>
                    </div>

                    <!-- Fields for AI Text Generation (hidden by default) -->
                    <div id="generate_mc_fields" style="display:none;">
                        <div class="form-group">
                            <label for="mc_days">Number of Days for Medical Leave:</label>
                            <input type="number" id="mc_days" name="mc_days" min="1" max="14">
                        </div>
                    </div>

                    <!-- Field for Image Upload (hidden by default) -->
                    <div id="upload_mc_fields" style="display:none;">
                        <div class="form-group">
                            <label for="mc_image">Upload Scanned MC Image:</label>
                            <input type="file" id="mc_image" name="mc_image" accept="image/jpeg, image/png, image/webp">
                        </div>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="action-btn">Save Record</button>
                        <a href="doctor_view_patient.php?patient_id=<?php echo $appt['PatientID']; ?>" class="back-button" style="margin-left: 15px; text-decoration: none;">Cancel</a>
                    </div>
                </form>
            </div>
        </section>
    </main>

    <!-- JavaScript to show/hide the correct form fields -->
    <script>
        document.getElementById('mc_method').addEventListener('change', function() {
            var generateFields = document.getElementById('generate_mc_fields');
            var uploadFields = document.getElementById('upload_mc_fields');
            
            if (this.value === 'generate') {
                generateFields.style.display = 'block';
                uploadFields.style.display = 'none';
            } else if (this.value === 'upload') {
                generateFields.style.display = 'none';
                uploadFields.style.display = 'block';
            } else {
                generateFields.style.display = 'none';
                uploadFields.style.display = 'none';
            }
        });
    </script>
</div>
</body>
</html>