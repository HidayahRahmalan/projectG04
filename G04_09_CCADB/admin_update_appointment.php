<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}
require_once 'db_conn.php';

// Check if the form was submitted with the required data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appointment_id'], $_POST['new_datetime'])) {
    
    $appointment_id = intval($_POST['appointment_id']);
    $new_datetime = $_POST['new_datetime'];

    // Update the appointment date and set the status back to 'Scheduled'
    $stmt = $conn->prepare("UPDATE Appointment SET AppointmentDate = ?, Status = 'Scheduled' WHERE AppointmentNo = ?");
    $stmt->bind_param("si", $new_datetime, $appointment_id);
    
    if ($stmt->execute()) {
        // Success
    } else {
        // Handle potential error
    }
}

// Redirect back to the main appointments list
header("Location: admin_manage_appointments.php");
exit();