<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}
require_once 'db_conn.php';

if (isset($_GET['id'])) {
    $appointment_id = intval($_GET['id']);

    // Permanently delete the appointment record
    // The ON DELETE CASCADE constraint in the database will handle related records
    $stmt = $conn->prepare("DELETE FROM Appointment WHERE AppointmentNo = ?");
    $stmt->bind_param("i", $appointment_id);
    $stmt->execute();
}

// Redirect back to the appointments list
header("Location: admin_manage_appointments.php");
exit();