<?php
session_start();
require_once 'db_conn.php';

// If the form is submitted, process it
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // This logic is very similar to confirm_booking.php
    $clinic_id = $_POST['clinic_id'];
    $patient_name = $_POST['patient_name'];
    $patient_ic = $_POST['patient_ic'];
    $patient_phone = $_POST['patient_phone'];
    $patient_email = $_POST['patient_email'];
    $notes = $_POST['notes']; // This is the emergency reason

    // In a real system, you'd find an 'on-call' doctor. For simulation, we'll assign to Dr. Everyday (DoctorID=3)
    $doctor_id = 3; 

    $conn->begin_transaction();
    try {
        // Find or create the patient/user
        // (This logic can be copied from confirm_booking.php)
        $stmt_user = $conn->prepare("SELECT UserID FROM User WHERE Username = ?");
        $stmt_user->bind_param("s", $patient_email);
        $stmt_user->execute();
        $user_result = $stmt_user->get_result();
        $patient_id = null;

        if ($user_result->num_rows > 0) {
             $user = $user_result->fetch_assoc();
             $stmt_patient = $conn->prepare("SELECT PatientID FROM Patient WHERE UserID = ?");
             $stmt_patient->bind_param("i", $user['UserID']);
             $stmt_patient->execute();
             $patient_id = $stmt_patient->get_result()->fetch_assoc()['PatientID'];
        } else {
            $temp_password = password_hash($patient_ic, PASSWORD_DEFAULT); 
            $stmt_new_user = $conn->prepare("INSERT INTO User (Username, Password, Role) VALUES (?, ?, 'Patient')");
            $stmt_new_user->bind_param("ss", $patient_email, $temp_password);
            $stmt_new_user->execute();
            $user_id = $conn->insert_id;

            $stmt_new_patient = $conn->prepare("INSERT INTO Patient (UserID, Name, ICNumber, PhoneNumber) VALUES (?, ?, ?, ?)");
            $stmt_new_patient->bind_param("isss", $user_id, $patient_name, $patient_ic, $patient_phone);
            $stmt_new_patient->execute();
            $patient_id = $conn->insert_id;
        }

        // Insert the appointment with 'Pending Approval' status and today's date
        $stmt_appt = $conn->prepare("INSERT INTO Appointment (PatientID, DoctorID, ClinicID, AppointmentDate, Status, Notes) VALUES (?, ?, ?, CURDATE(), 'Pending Approval', ?)");
        $stmt_appt->bind_param("iiis", $patient_id, $doctor_id, $clinic_id, $notes);
        $stmt_appt->execute();
        
        $conn->commit();
        $_SESSION['request_success'] = "Your emergency request has been sent. The clinic will contact you shortly if a slot is available.";
        header("Location: index.php"); // Redirect to homepage with a success message
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        // Handle error
    }
}

// Get the clinic ID from the URL to display the form
if (!isset($_GET['clinic_id'])) { die("Clinic not specified."); }
$clinic_id = intval($_GET['clinic_id']);
$stmt_clinic = $conn->prepare("SELECT ClinicName FROM Clinic WHERE ClinicID = ?");
$stmt_clinic->bind_param("i", $clinic_id);
$stmt_clinic->execute();
$clinic_name = $stmt_clinic->get_result()->fetch_assoc()['ClinicName'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Request Emergency Slot</title>
    <link rel="stylesheet" href="css/style.css" />
</head>
<body>
    <div class="main-content" style="padding-top: 60px;">
        <div class="content-card booking-form-container">
            <a href="emergency.php" class="home-icon-link" title="Back to Clinic List">❮</a>
            <h2>Emergency Request for <?php echo htmlspecialchars($clinic_name); ?></h2>
            <p>Please provide your details and the reason for your visit. This does not guarantee an appointment.</p>
            <form action="request_emergency.php" method="POST" class="booking-form">
                <input type="hidden" name="clinic_id" value="<?php echo $clinic_id; ?>">
                <div class="form-group">
                    <label for="patient_name">Full Name:</label>
                    <input type="text" id="patient_name" name="patient_name" required>
                </div>
                <div class="form-group">
                    <label for="patient_ic">IC Number:</label>
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
                    <label for="notes">Reason for Visit (Symptoms):</label>
                    <textarea id="notes" name="notes" rows="3" required></textarea>
                </div>
                <div class="form-group">
                    <button type="submit" class="hero-button">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>