<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}
require_once 'db_conn.php';

if (isset($_GET['id'])) {
    $appointment_id = intval($_GET['id']);
    $stmt = $conn->prepare("UPDATE Appointment SET Status = 'Cancelled' WHERE AppointmentNo = ?");
    $stmt->bind_param("i", $appointment_id);
    $stmt->execute();
}
header("Location: admin_manage_appointments.php");
exit();