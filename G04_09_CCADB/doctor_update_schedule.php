<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Doctor') {
    header("Location: login.php");
    exit();
}
require_once 'db_conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $doctor_id = intval($_POST['doctor_id']);
    $day_of_week = intval($_POST['day_of_week']);
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    // Security check: ensure the logged-in doctor is editing their own schedule
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT DoctorID FROM Doctor WHERE UserID = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $logged_in_doctor_id = $stmt->get_result()->fetch_assoc()['DoctorID'];

    if ($doctor_id !== $logged_in_doctor_id) {
        // Mismatch, potential security issue. Redirect.
        header("Location: doctor_schedule.php");
        exit();
    }
    
    // Check if a schedule for this day already exists
    $stmt_check = $conn->prepare("SELECT ScheduleID FROM DoctorSchedule WHERE DoctorID = ? AND DayOfWeek = ?");
    $stmt_check->bind_param("ii", $doctor_id, $day_of_week);
    $stmt_check->execute();
    $existing = $stmt_check->get_result()->fetch_assoc();
    
    // Case 1: Both times are empty -> Delete the schedule for this day
    if (empty($start_time) && empty($end_time)) {
        if ($existing) {
            $stmt_delete = $conn->prepare("DELETE FROM DoctorSchedule WHERE DoctorID = ? AND DayOfWeek = ?");
            $stmt_delete->bind_param("ii", $doctor_id, $day_of_week);
            $stmt_delete->execute();
        }
    } 
    // Case 2: Both times are provided -> Update or Insert
    elseif (!empty($start_time) && !empty($end_time)) {
        if ($existing) {
            // Update existing record
            $stmt_update = $conn->prepare("UPDATE DoctorSchedule SET StartTime = ?, EndTime = ? WHERE DoctorID = ? AND DayOfWeek = ?");
            $stmt_update->bind_param("ssii", $start_time, $end_time, $doctor_id, $day_of_week);
            $stmt_update->execute();
        } else {
            // Insert new record
            $stmt_insert = $conn->prepare("INSERT INTO DoctorSchedule (DoctorID, DayOfWeek, StartTime, EndTime) VALUES (?, ?, ?, ?)");
            $stmt_insert->bind_param("iiss", $doctor_id, $day_of_week, $start_time, $end_time);
            $stmt_insert->execute();
        }
    }
}

// Redirect back to the main schedule page
header("Location: doctor_schedule.php");
exit();