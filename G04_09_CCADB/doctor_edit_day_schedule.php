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
if (!isset($_GET['day']) || !array_key_exists($_GET['day'], [1,2,3,4,5,6,7])) {
    header("Location: doctor_schedule.php");
    exit();
}
$day_of_week_num = intval($_GET['day']);
$days_of_week = [1=>'Monday', 2=>'Tuesday', 3=>'Wednesday', 4=>'Thursday', 5=>'Friday', 6=>'Saturday', 7=>'Sunday'];
$day_name = $days_of_week[$day_of_week_num];

// Fetch existing schedule for this day, if it exists
$current_schedule = ['StartTime' => '', 'EndTime' => ''];
$stmt = $conn->prepare("SELECT StartTime, EndTime FROM DoctorSchedule WHERE DoctorID = ? AND DayOfWeek = ?");
$stmt->bind_param("ii", $doctor_id, $day_of_week_num);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $current_schedule = $result->fetch_assoc();
}
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
    <main class="admin-main-content">
        <header class="main-header">
            <h1>Edit Schedule: <?php echo $day_name; ?></h1>
        </header>
        <section class="data-section">
            <div class="data-table-container">
                <h2>Set Your Working Hours</h2>
                <p>Select a start and end time for this day. To mark a day as off, leave the fields blank and click "Update Schedule".</p>
                <form action="doctor_update_day_schedule.php" method="POST" class="booking-form">
                    <input type="hidden" name="doctor_id" value="<?php echo $doctor_id; ?>">
                    <input type="hidden" name="day_of_week" value="<?php echo $day_of_week_num; ?>">
                    
                    <div class="form-group">
                        <label for="start_time">Start Time:</label>
                        <input type="time" id="start_time" name="start_time" value="<?php echo $current_schedule['StartTime']; ?>">
                    </div>
                    <div class="form-group">
                        <label for="end_time">End Time:</label>
                        <input type="time" id="end_time" name="end_time" value="<?php echo $current_schedule['EndTime']; ?>">
                    </div>
                    <div class="form-group">
                        <button type">
        <header class="main-header">
            <h1>Edit Schedule for <?php echo $day_name; ?></h1>
        </header>
        <section class="data-section">
            <div class="data-table-container">
                <h2>Set Working Hours</h2>
                <form action="doctor_update_schedule.php" method="POST" class="booking-form">
                    <input type="hidden" name="doctor_id" value="<?php echo $doctor_id; ?>">
                    <input type="hidden" name="day_of_week" value="<?php echo $day_of_week_num; ?>">

                    <div class="form-group">
                        <label for="start_time">Start Time:</label>
                        <input type="time" id="start_time" name="start_time" value="<?php echo $start_time; ?>">
                    </div>

                    <div class="form-group">
                        <label for="end_time">End Time:</label>
                        <input type="time" id="end_time" name="end_time" value="<?php echo $end_time; ?>">
                    </div>

                    <div class="form-group">
                        <button type="submit" class="action-btn">Save Changes</button>
                        <a href="doctor_schedule.php" class="back-button" style="margin-left: 15px;">Cancel</a>
                    </div>
                     <small style="display: block; margin-top: 15px; color: #777;">
                        Note: To mark a day as "Not Scheduled", simply leave both time fields blank and save.
                    </small>
                </form>
            </div>
        </section>
    </main>
</div>
</body>
</html>