<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Doctor') {
    header("Location: login.php");
    exit();
}
require_once 'db_conn.php';

// Get DoctorID
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT DoctorID FROM Doctor WHERE UserID = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$doctor_id = $stmt->get_result()->fetch_assoc()['DoctorID'];

// Get the day of the week from the URL
$day_of_week_num = isset($_GET['day']) ? intval($_GET['day']) : 0;
$days_of_week = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'];

if (!array_key_exists($day_of_week_num, $days_of_week)) {
    header("Location: doctor_schedule.php"); // Invalid day, redirect
    exit();
}
$day_name = $days_of_week[$day_of_week_num];

// Fetch existing schedule for this day, if it exists
$stmt_schedule = $conn->prepare("SELECT StartTime, EndTime FROM DoctorSchedule WHERE DoctorID = ? AND DayOfWeek = ?");
$stmt_schedule->bind_param("ii", $doctor_id, $day_of_week_num);
$stmt_schedule->execute();
$result = $stmt_schedule->get_result();
$existing_schedule = $result->fetch_assoc();

$start_time = $existing_schedule['StartTime'] ?? '';
$end_time = $existing_schedule['EndTime'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Schedule for <?php echo $day_name; ?></title>
    <link rel="stylesheet" href="css/admin_style.css">
</head>
<body>
<div class="admin-wrapper">
    <?php 
        $active_page = 'schedule';
        include 'doctor_sidebar.php'; 
    ?>
    <main class="admin-main-contentinclude 'doctor_sidebar.php'; 
    ?>
    <main class="admin-main-content">
        <header class="main-header">
            <h1>My Schedule</h1>
            <p>View and manage your standard working hours and upcoming appointments.</p>
        </header>

        <section class="data-section">
            <div class="data-table-container">
                <h2>Default Weekly Hours</h2>
                <table class="data-table">
                    <!-- ADDED "ACTIONS" COLUMN HEADER -->
                    <thead><tr><th>Day</th><th>Working Hours</th><th>Actions</th></tr></thead>
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
                                    <!-- ADDED "EDIT" BUTTON -->
                                    <a href="doctor_edit_day_schedule.php?day=<?php echo $day_num; ?>" class="action-btn">Edit</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Upcoming Appointments Table (unchanged) -->
            <div class="data-table-container">
                <h2>Upcoming Appointments</h2>
                <table class="data-table">
                    <thead><tr><th>Date & Time</th><th>Patient</th><th>Clinic</th></tr></thead>
                    <tbody>
                        <?php if ($upcoming_appointments->num_rows > 0): ?>
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