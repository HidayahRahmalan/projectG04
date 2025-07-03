<?php
session_start();
// Security check: Ensure the user is logged in and is a Doctor.
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Doctor') {
    header("Location: login.php");
    exit();
}

require_once 'db_conn.php';

// Get the logged-in doctor's information
$user_id = $_SESSION['user_id'];
$stmt_doctor = $conn->prepare("SELECT DoctorID, Name FROM Doctor WHERE UserID = ?");
$stmt_doctor->bind_param("i", $user_id);
$stmt_doctor->execute();
$doctor_info = $stmt_doctor->get_result()->fetch_assoc();
$doctor_id = $doctor_info['DoctorID'];
$doctor_name = $doctor_info['Name'];

// --- Fetch data for the dashboard ---

// 1. Today's Appointments for THIS doctor
$sql_today = "SELECT a.AppointmentDate, p.Name as PatientName, c.ClinicName, a.Status, a.Notes
              FROM Appointment a
              JOIN Patient p ON a.PatientID = p.PatientID
              JOIN Clinic c ON a.ClinicID = c.ClinicID
              WHERE a.DoctorID = ? AND DATE(a.AppointmentDate) = CURDATE()
              ORDER BY a.AppointmentDate ASC";
$stmt_today = $conn->prepare($sql_today);
$stmt_today->bind_param("i", $doctor_id);
$stmt_today->execute();
$appointments_today = $stmt_today->get_result();

// 2. Upcoming Appointments for THIS doctor (next 7 days)
$sql_upcoming = "SELECT a.AppointmentDate, p.Name as PatientName, c.ClinicName
                 FROM Appointment a
                 JOIN Patient p ON a.PatientID = p.PatientID
                 JOIN Clinic c ON a.ClinicID = c.ClinicID
                 WHERE a.DoctorID = ? AND DATE(a.AppointmentDate) > CURDATE() AND DATE(a.AppointmentDate) <= CURDATE() + INTERVAL 7 DAY
                 ORDER BY a.AppointmentDate ASC";
$stmt_upcoming = $conn->prepare($sql_upcoming);
$stmt_upcoming->bind_param("i", $doctor_id);
$stmt_upcoming->execute();
$appointments_upcoming = $stmt_upcoming->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Doctor Dashboard - Clinic System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/admin_style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<div class="admin-wrapper">
    
    <!-- ============================================================== -->
    <!-- CHANGE #1: Replaced the hard-coded sidebar with an include.   -->
    <!-- CHANGE #2: The 'My Schedule' link inside the sidebar file     -->
    <!-- will now point to doctor_schedule.php.                     -->
    <!-- ============================================================== -->
    <?php 
        $active_page = 'dashboard'; // Set this page as active
        include 'doctor_sidebar.php'; 
    ?>

    <!-- Main Content -->
    <main class="admin-main-content">
        <header class="main-header">
            <h1>Welcome, <?php echo htmlspecialchars($doctor_name); ?>!</h1>
            <p>Here is your schedule and patient overview for today.</p>
        </header>

        <!-- Data Tables Section -->
        <section class="data-section">
            <div class="data-table-container">
                <h2>Today's Appointments</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Patient Name</th>
                            <th>Clinic</th>
                            <th>Reason for Visit</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($appointments_today->num_rows > 0): ?>
                            <?php while($row = $appointments_today->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo date('g:i A', strtotime($row['AppointmentDate'])); ?></td>
                                    <td><?php echo htmlspecialchars($row['PatientName']); ?></td>
                                    <td><?php echo htmlspecialchars($row['ClinicName']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Notes'] ? $row['Notes'] : 'N/A'); ?></td>
                                    <td><a href="#" class="action-btn">View Record</a></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5">You have no appointments scheduled for today.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="data-table-container">
                <h2>Upcoming Appointments (Next 7 Days)</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Patient Name</th>
                            <th>Clinic</th>
                        </tr>
                    </thead>
                    <tbody>
                         <?php if ($appointments_upcoming->num_rows > 0): ?>
                            <?php while($row = $appointments_upcoming->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo date('D, M j', strtotime($row['AppointmentDate'])); ?></td>
                                    <td><?php echo date('g:i A', strtotime($row['AppointmentDate'])); ?></td>
                                    <td><?php echo htmlspecialchars($row['PatientName']); ?></td>
                                    <td><?php echo htmlspecialchars($row['ClinicName']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4">No upcoming appointments in the next 7 days.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>

</body>
</html>