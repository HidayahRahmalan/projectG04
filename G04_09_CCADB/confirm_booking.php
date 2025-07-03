<?php
session_start();
require_once 'db_conn.php';

// --- Part 1: Handle the POST request (when the user submits the form) ---
// This part remains unchanged.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn->begin_transaction();
    try {
        $doctor_id = $_POST['doctor_id'];
        $clinic_id = $_POST['clinic_id'];
        $date = $_POST['date'];
        $time = $_POST['time'];
        $full_datetime = $date . ' ' . $time;

        $patient_name = $_POST['patient_name'];
        $patient_ic = $_POST['patient_ic'];
        $patient_phone = $_POST['patient_phone'];
        $patient_email = $_POST['patient_email'];
        $notes = $_POST['notes'];

        $stmt = $conn->prepare("SELECT UserID FROM User WHERE Username = ?");
        $stmt->bind_param("s", $patient_email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user_id = null;
        $patient_id = null;

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $user_id = $user['UserID'];
            $stmt_patient = $conn->prepare("SELECT PatientID FROM Patient WHERE UserID = ?");
            $stmt_patient->bind_param("i", $user_id);
            $stmt_patient->execute();
            $patient_id = $stmt_patient->get_result()->fetch_assoc()['PatientID'];
        } else {
            $temp_password = password_hash($patient_ic, PASSWORD_DEFAULT);
            $stmt_newUser = $conn->prepare("INSERT INTO User (Username, Password, Role) VALUES (?, ?, 'Patient')");
            $stmt_newUser->bind_param("ss", $patient_email, $temp_password);
            $stmt_newUser->execute();
            $user_id = $conn->insert_id;

            $stmt_newPatient = $conn->prepare("INSERT INTO Patient (UserID, Name, ICNumber, PhoneNumber) VALUES (?, ?, ?, ?)");
            $stmt_newPatient->bind_param("isss", $user_id, $patient_name, $patient_ic, $patient_phone);
            $stmt_newPatient->execute();
            $patient_id = $conn->insert_id;

            $_SESSION['new_user_message'] = "An account has been created for you. Your username is your email, and your temporary password is your IC number.";
        }

        $stmt_appt = $conn->prepare("INSERT INTO Appointment (PatientID, DoctorID, ClinicID, AppointmentDate, Notes) VALUES (?, ?, ?, ?, ?)");
        $stmt_appt->bind_param("iiiss", $patient_id, $doctor_id, $clinic_id, $full_datetime, $notes);
        $stmt_appt->execute();
        $appointment_id = $conn->insert_id;

        $conn->commit();
        header("Location: booking_success.php?appointment_id=" . $appointment_id);
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error_message'] = "Booking failed: " . $e->getMessage();
        header("Location: confirm_booking.php");
        exit();
    }
}

// --- Part 2: Handle the GET request (displaying the form) ---
// This part also remains unchanged.
if (!isset($_GET['doctor_id'], $_GET['clinic_id'], $_GET['date'], $_GET['time'])) {
    die("Error: Incomplete booking information. Please go back and select a time slot.");
}
$doctor_id = intval($_GET['doctor_id']);
$clinic_id = intval($_GET['clinic_id']);
$date_str = $_GET['date'];
$time_str = $_GET['time'];
$date_obj = new DateTime($date_str);

$stmt_doc = $conn->prepare("SELECT Name FROM Doctor WHERE DoctorID = ?");
$stmt_doc->bind_param("i", $doctor_id);
$stmt_doc->execute();
$doctor_result = $stmt_doc->get_result();
if ($doctor_row = $doctor_result->fetch_assoc()) {
    $doctor_name = $doctor_row['Name'];
} else {
    die("Error: Doctor not found.");
}

$stmt_clinic = $conn->prepare("SELECT ClinicName FROM Clinic WHERE ClinicID = ?");
$stmt_clinic->bind_param("i", $clinic_id);
$stmt_clinic->execute();
$clinic_result = $stmt_clinic->get_result();
if ($clinic_row = $clinic_result->fetch_assoc()) {
    $clinic_name = $clinic_row['ClinicName'];
} else {
    die("Error: Clinic not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Confirm Your Booking</title>
    <link rel="stylesheet" href="css/style.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body>
    <div class="main-content" style="padding-top: 40px;">
        <div class="content-card booking-form-container">
            <h2>Step 3: Confirm Your Details</h2>
            <div class="booking-summary">
                <p>You are booking an appointment with:</p>
                <p><strong>Doctor:</strong> Dr. <?php echo htmlspecialchars($doctor_name); ?></p>
                <p><strong>Clinic:</strong> <?php echo htmlspecialchars($clinic_name); ?></p>
                <p><strong>Date:</strong> <?php echo $date_obj->format('l, F j, Y'); ?></p>
                <p><strong>Time:</strong> <?php echo (new DateTime($time_str))->format('g:i A'); ?></p>
            </div>
            <form action="confirm_booking.php" method="POST" class="booking-form">
                <!-- Form fields remain unchanged -->
                <input type="hidden" name="doctor_id" value="<?php echo $doctor_id; ?>">
                <input type="hidden" name="clinic_id" value="<?php echo $clinic_id; ?>">
                <input type="hidden" name="date" value="<?php echo $date_str; ?>">
                <input type="hidden" name="time" value="<?php echo $time_str; ?>">
                <div class="form-group">
                    <label for="patient_name">Full Name (as per IC):</label>
                    <input type="text" id="patient_name" name="patient_name" required>
                </div>
                <div class="form-group">
                    <label for="patient_ic">IC Number (e.g., 900101-10-1234):</label>
                    <input type="text" id="patient_ic" name="patient_ic" required>
                </div>
                <div class="form-group">
                    <label for="patient_phone">Phone Number:</label>
                    <input type="tel" id="patient_phone" name="patient_phone" required>
                </div>
                <div class="form-group">
                    <label for="patient_email">Email Address:</label>
                    <input type="email" id="patient_email" name="patient_email" required>
                </div>
                <div class="form-group">
                    <label for="notes">Reason for Visit (Optional):</label>
                    <textarea id="notes" name="notes" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <button type="submit" class="hero-button">Confirm Booking</button>
                </div>
            </form>
            <!-- ========================================================== -->
            <!-- THE CHANGE IS HERE: Added a "Return to Home" button/link.  -->
            <!-- ========================================================== -->
            <div class="navigation-links">
                <a href="javascript:history.back()" class="back-button">Go Back</a>
                <a href="index.php" class="back-button">Return to Home</a>
            </div>
        </div>
    </div>
</body>
</html>