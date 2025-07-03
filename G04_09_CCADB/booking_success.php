<?php
session_start();
require_once 'db_conn.php';

// Check if an appointment ID was passed
if (!isset($_GET['appointment_id'])) {
    die("No booking information found.");
}

$appointment_id = intval($_GET['appointment_id']);

// Fetch all details of the confirmed appointment using JOINs
$sql = "SELECT 
            a.AppointmentDate,
            p.Name as PatientName,
            d.Name as DoctorName,
            c.ClinicName
        FROM Appointment a
        JOIN Patient p ON a.PatientID = p.PatientID
        JOIN Doctor d ON a.DoctorID = d.DoctorID
        JOIN Clinic c ON a.ClinicID = c.ClinicID
        WHERE a.AppointmentNo = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $appointment_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Could not find booking details.");
}
$booking = $result->fetch_assoc();
$date_obj = new DateTime($booking['AppointmentDate']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Booking Confirmed!</title>
    <link rel="stylesheet" href="css/style.css" />
</head>
<body>
    <div class="main-content">
        <div class="content-card">
            <div class="success-icon" style="text-align:center; font-size: 50px; color: green;">✔</div>
            <h2>Booking Confirmed!</h2>
            <p>Your appointment has been successfully scheduled. Here are the details:</p>
            
            <div class="booking-summary" style="text-align:left; margin-top:20px; padding:20px; background-color:#f4f7f6; border-radius:8px;">
                <p><strong>Patient:</strong> <?php echo htmlspecialchars($booking['PatientName']); ?></p>
                <p><strong>Doctor:</strong> Dr. <?php echo htmlspecialchars($booking['DoctorName']); ?></p>
                <p><strong>Clinic:</strong> <?php echo htmlspecialchars($booking['ClinicName']); ?></p>
                <p><strong>Date & Time:</strong> <?php echo $date_obj->format('l, F j, Y \a\t g:i A'); ?></p>
            </div>

            <?php
            // Display the new user message if it exists
            if (isset($_SESSION['new_user_message'])) {
                echo '<p class="info-message" style="margin-top:20px; padding:15px; background-color:#e0f2f1; border-left: 5px solid #00796b; color:#004d40;">' . $_SESSION['new_user_message'] . '</p>';
                unset($_SESSION['new_user_message']); // Clear the message
            }
            ?>

            <a href="index.php" class="hero-button" style="margin-top:20px;">Return to Homepage</a>
        </div>
    </div>
</body>
</html>