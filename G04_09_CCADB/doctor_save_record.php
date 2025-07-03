<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Doctor') { header("Location: login.php"); exit(); }
require_once 'db_conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appointment_id = intval($_POST['appointment_id']);
    $patient_id = intval($_POST['patient_id']); // We need this for the redirect
    $diagnosis = trim($_POST['diagnosis']);
    $notes = trim($_POST['notes']);

    if (!empty($appointment_id) && !empty($diagnosis)) {
        $stmt = $conn->prepare("INSERT INTO MedicalRecord (AppointmentID, Diagnosis, Notes) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $appointment_id, $diagnosis, $notes);
        $stmt->execute();
        $stmt->close();
    }
    
    // Redirect back to the patient's history page
    header("Location: doctor_view_patient.php?patient_id=" . $patient_id);
    exit();
}