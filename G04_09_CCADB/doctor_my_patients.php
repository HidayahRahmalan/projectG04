<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Doctor') {
    header("Location: login.php");
    exit();
}
require_once 'db_conn.php';

// Get the logged-in doctor's ID
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT DoctorID FROM Doctor WHERE UserID = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$doctor_id = $stmt->get_result()->fetch_assoc()['DoctorID'];

// Fetch a unique list of patients this doctor has had appointments with
$sql = "SELECT DISTINCT p.PatientID, p.Name, p.ICNumber, p.PhoneNumber
        FROM Patient p
        JOIN Appointment a ON p.PatientID = a.PatientID
        WHERE a.DoctorID = ?
        ORDER BY p.Name ASC";
$stmt_patients = $conn->prepare($sql);
$stmt_patients->bind_param("i", $doctor_id);
$stmt_patients->execute();
$patients_result = $stmt_patients->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Patients - Doctor Portal</title>
    <link rel="stylesheet" href="css/admin_style.css"> <!-- Reusing styles -->
</head>
<body>
<div class="admin-wrapper">
    <?php 
        $active_page = 'patients'; 
        include 'doctor_sidebar.php'; 
    ?>
    <main class="admin-main-content">
        <header class="main-header">
            <h1>My Patients</h1>
            <p>Select a patient to view their history and add medical records.</p>
        </header>
        <section class="data-section">
            <div class="data-table-container">
                <table class="data-table">
                    <thead><tr><th>Name</th><th>IC Number</th><th>Phone Number</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php if ($patients_result->num_rows > 0): ?>
                            <?php while($row = $patients_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['Name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['ICNumber'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($row['PhoneNumber'] ?? 'N/A'); ?></td>
                                    <td>
                                        <a href="doctor_view_patient.php?patient_id=<?php echo $row['PatientID']; ?>" class="action-btn">View History</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4">You have not seen any patients yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>
</body>
</html>