<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}
require_once 'db_conn.php';

if (isset($_GET['id'], $_GET['action'])) {
    $appointment_id = intval($_GET['id']);
    $action = $_GET['action'];
    $new_status = '';

    if ($action === 'approve') {
        $new_status = 'Scheduled'; // Approving changes status to 'Scheduled'
    } elseif ($action === 'deny') {
        $new_status = 'Cancelled'; // Denying changes status to 'Cancelled'
    }

    if (!empty($new_status)) {
        $stmt = $conn->prepare("UPDATE Appointment SET Status = ? WHERE AppointmentNo = ?");
        $stmt->bind_param("si", $new_status, $appointment_id);
        $stmt->execute();
    }
}

// Redirect back to the management page
header("Location: admin_manage_requests.php");
exit();