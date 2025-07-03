<?php
session_start();
// Security Check: Ensure the user is a logged-in doctor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Doctor') {
    header("Location: login.php");
    exit();
}
require_once 'db_conn.php';

// Get the logged-in doctor's DoctorID
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT DoctorID FROM Doctor WHERE UserID = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die("Error: Could not find a doctor profile for this user.");
}
$doctor_id = $result->fetch_assoc()['DoctorID'];

// --- Fetch Data for the Page ---

// 1. Get the doctor's default weekly schedule
$days_of_week = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'];
$weekly_schedule = [];
$sql_weekly = "SELECT DayOfWeek, StartTime, EndTime FROM DoctorSchedule WHERE DoctorID = ? ORDER BY DayOfWeek";
$stmt_weekly = $conn->prepare($sql_weekly);
$stmt_weekly->bind_param("i", $doctor_id);
$stmt_weekly->execute();
$result_weekly = $stmt_weekly->get_result();
while($row = $result_weekly->fetch_assoc()) {
    $weekly_schedule[$row['DayOfWeek']] = $row;
}

// 2. Get the doctor's upcoming appointments
$sql_appts = "SELECT a.AppointmentDate, p.Name as PatientName, c.ClinicName 
              FROM Appointment a
              JOIN Patient p ON a.PatientID = p.PatientID
              JOIN Clinic c ON a.ClinicID = c.ClinicID
              WHERE a.DoctorID = ? AND a.AppointmentDate >= CURDATE() AND a.Status = 'Scheduled'
              ORDER BY a.AppointmentDate ASC";
$stmt_appts = $conn->prepare($sql_appts);
$stmt_appts->bind_param("i", $doctor_id);
$stmt_appts->execute();
$upcoming_appointments = $stmt_appts->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Schedule - Doctor Portal</title>
    <link rel="stylesheet" href="css/admin_style.css"> <!-- Reusing admin styles -->
</head>
<body>
<div class="admin-wrapper">
    <?php 
        $active_page = 'schedule'; // Set the active page for the sidebar
        include 'doctor_sidebar.php'; 
    ?>
    <main class="admin-main-content">
        <header class="main-header">
            <h1>My Schedule</h1>
            <p>View and manage your standard working hours and upcoming appointments.</p>
        </header>

        <section class="data-section">
            <!-- Default Weekly Schedule Table -->
            <div class="data-table-container">
                <h2>Default Weekly Hours</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Day</th>
                            <th>Working Hours</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($days_of_week as $day_num => $day_name): ?>
                            <tr>
                                <td><?php echo $day_name; ?></td>
                                <td>
                                    <?php if (isset($weekly_schedule[$day_num])): 
                                        $schedule = $weekly_schedule[$day_num];
                                        echo date('g:i A', strtotime($schedule['StartTime'])) . ' - ' . date('g:i A', strtotime($schedule['EndTime']));
                                    else:
                                        echo '<span style="color: #999;">Not scheduled</span>';
                                    endif; ?>
                                </td>
                                <td>
                                    <!-- This button now correctly links to the edit page -->
                                    <a href="doctor_edit_day.php?day=<?php echo $day_num; ?>" class="action-btn">Edit</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Upcoming Appointments Table (this part is unchanged) -->
            <div class="data-table-container">
                <h2>Upcoming Appointments</h2>
                <table class="data-table">
                    <thead><tr><th>Date & Time</th><th>Patient</th><th>Clinic</th></tr></thead>
                    <tbody>
                        <?php if ($upcoming_appointments && $upcoming_appointments->num_rows > 0): ?>
                            <?php while($row = $upcoming_appointments->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo date('d M Y, g:i A', strtotime($row['AppointmentDate'])); ?></td>
                                    <td><?php echo htmlspecialchars($row['PatientName']); ?></td>
                                    <td><?php echo htmlspecialchars($row['ClinicName']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="3">You have no upcoming appointments.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>
</body>
</html>