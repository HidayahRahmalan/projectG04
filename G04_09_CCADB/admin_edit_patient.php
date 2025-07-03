<?php
session_start();
// Security check: Must be an admin to access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}
require_once 'db_conn.php';

// Step 1: Check if a patient ID was provided in the URL
if (!isset($_GET['patient_id'])) {
    // If not, redirect back to the list of patients
    header("Location: admin_manage_patients.php");
    exit();
}
$patient_id = intval($_GET['patient_id']);

// Step 2: Fetch the existing details of this specific patient
$stmt = $conn->prepare("SELECT p.Name, p.ICNumber, p.PhoneNumber, u.Username as Email 
                       FROM Patient p 
                       JOIN User u ON p.UserID = u.UserID 
                       WHERE p.PatientID = ?");
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$result = $stmt->get_result();

// If no patient is found with that ID, redirect back
if ($result->num_rows === 0) {
    header("Location: admin_manage_patients.php");
    exit();
}
// Store the patient's data in a variable
$patient = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Patient - Admin</title>
    <link rel="stylesheet" href="css/admin_style.css">
</head>
<body>
<div class="admin-wrapper">
    <?php 
        // We set this so the "Manage Patients" link stays active in the sidebar
        $active_page = 'patients'; 
        include 'admin_sidebar.php'; 
    ?>

    <main class="admin-main-content">
        <header class="main-header">
            <h1>Edit Patient: <?php echo htmlspecialchars($patient['Name']); ?></h1>
        </header>

        <section class="data-section">
            <div class="data-table-container">
                <h2>Patient Information</h2>
                <!-- This form will post the updated data to our next file -->
                <form action="admin_update_patient.php" method="POST" class="booking-form">
                    <!-- A hidden input to securely pass the patient's ID -->
                    <input type="hidden" name="patient_id" value="<?php echo $patient_id; ?>">
                    
                    <div class="form-group">
                        <label for="name">Full Name:</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($patient['Name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address (Username):</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($patient['Email']); ?>" readonly>
                        <small style="display: block; margin-top: 5px; color: #777;">Note: Email/Username cannot be changed.</small>
                    </div>

                    <div class="form-group">
                        <label for="ic_number">IC Number:</label>
                        <input type="text" id="ic_number" name="ic_number" value="<?php echo htmlspecialchars($patient['ICNumber'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="phone_number">Phone Number:</label>
                        <input type="tel" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($patient['PhoneNumber'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <button type="submit" class="action-btn">Update Patient</button>
                        <a href="admin_manage_patients.php" class="back-button" style="margin-left: 15px; text-decoration: none; color: #555;">Cancel</a>
                    </div>
                </form>
            </div>
        </section>
    </main>
</div>
</body>
</html>