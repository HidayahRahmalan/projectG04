<?php
require_once 'db_conn.php';

// --- Step 1: Get data from the previous page's form ---
if (!isset($_GET['clinic_id'], $_GET['department'], $_GET['appointment_date'])) {
    die("Error: Missing required information. Please go back and fill out the form.");
}

$clinic_id = $_GET['clinic_id'];
$department = $_GET['department'];
$appointment_date_str = $_GET['appointment_date'];
$appointment_date = new DateTime($appointment_date_str);
$day_of_week = $appointment_date->format('N');

// ===================================================================
// NEW LOGIC: Check if the selected date is today.
// ===================================================================
$today = new DateTime('today'); // Gets today's date with time set to 00:00:00
$is_today = ($appointment_date == $today);

// Fetch clinic name for display (moved up so we can use it in the error message)
$clinic_name_sql = "SELECT ClinicName FROM Clinic WHERE ClinicID = ?";
$clinic_stmt = $conn->prepare($clinic_name_sql);
$clinic_stmt->bind_param("i", $clinic_id);
$clinic_stmt->execute();
$clinic_name = $clinic_stmt->get_result()->fetch_assoc()['ClinicName'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Available Slots</title>
  <link rel="stylesheet" href="css/style.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body>
  <!-- You can add a consistent Header and Sidebar here if you want -->

  <div class="main-content" style="padding-top: 60px;">
    <div class="content-card">
      <h2>Step 2: Select a Time Slot</h2>
      <p>Showing available slots for <strong><?php echo htmlspecialchars($department); ?></strong>
         at <strong><?php echo htmlspecialchars($clinic_name); ?></strong>
         on <strong><?php echo $appointment_date->format('l, F j, Y'); ?></strong>.</p>
      <hr>

      <?php if ($is_today): ?>
        <!-- =================================================================== -->
        <!-- NEW DISPLAY BLOCK: Show this message if the user selects today's date. -->
        <!-- =================================================================== -->
        <div class="slots-container">
            <p class="no-slots" style="background-color: #fff3e0; border-left: 5px solid #ff9800; color: #e65100;">
                Online bookings must be made at least one day in advance.
                <br>For same-day appointments or emergencies, please call the clinic directly at their listed phone number.
            </p>
        </div>
        <a href="appointment.php" class="back-button">Choose a Different Date</a>

      <?php else: ?>
        <!-- The original logic for finding slots runs only if the date is not today -->
        <?php
          // Step 2: Find available doctors and their schedules
          $sql = "SELECT d.DoctorID, d.Name, ds.StartTime, ds.EndTime FROM Doctor d JOIN DoctorSchedule ds ON d.DoctorID = ds.DoctorID WHERE d.Department = ? AND ds.DayOfWeek = ?";
          $stmt = $conn->prepare($sql);
          $stmt->bind_param("si", $department, $day_of_week);
          $stmt->execute();
          $result = $stmt->get_result();
          $available_doctors = $result->fetch_all(MYSQLI_ASSOC);

          // Step 3: Find already booked appointments for that day
          $sql_booked = "SELECT DoctorID, TIME(AppointmentDate) as BookedTime FROM Appointment WHERE DATE(AppointmentDate) = ?";
          $stmt_booked = $conn->prepare($sql_booked);
          $stmt_booked->bind_param("s", $appointment_date_str);
          $stmt_booked->execute();
          $result_booked = $stmt_booked->get_result();
          $booked_slots = [];
          while ($row = $result_booked->fetch_assoc()) {
              $booked_slots[$row['DoctorID']][] = $row['BookedTime'];
          }
        ?>
        <div class="slots-container">
          <?php if (empty($available_doctors)): ?>
            <p class="no-slots">Sorry, no doctors are available for this service on the selected date. Please try another date.</p>
          <?php else: ?>
            <?php foreach ($available_doctors as $doctor): ?>
              <div class="doctor-slots">
                <h3>Dr. <?php echo htmlspecialchars($doctor['Name']); ?></h3>
                <div class="time-slots">
                  <?php
                    $start = new DateTime($doctor['StartTime']);
                    $end = new DateTime($doctor['EndTime']);
                    $interval = new DateInterval('PT1H');
                    $time_period = new DatePeriod($start, $interval, $end);
                    $slots_found = 0;

                    foreach ($time_period as $time) {
                      $slot_time_str = $time->format('H:i:s');
                      $is_booked = in_array($slot_time_str, $booked_slots[$doctor['DoctorID']] ?? []);
                      
                      if (!$is_booked) {
                        $slots_found++;
                        echo '<a href="confirm_booking.php?doctor_id=' . $doctor['DoctorID'] . '&clinic_id=' . $clinic_id . '&date=' . $appointment_date_str . '&time=' . $time->format('H:i') . '" class="time-slot-btn">' . $time->format('g:i A') . '</a>';
                      }
                    }

                    if ($slots_found === 0) {
                        echo '<p class="no-slots">No available time slots for this doctor.</p>';
                    }
                  ?>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
        <a href="appointment.php" class="back-button">Go Back</a>
      <?php endif; ?>
    </div>
  </div>

</body>
</html>