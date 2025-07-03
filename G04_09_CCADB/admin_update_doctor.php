<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}
require_once 'db_conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $doctor_id = intval($_POST['doctor_id']);
    $name = trim($_POST['name']);
    $department = trim($_POST['department']);
    $email = trim($_POST['email']);
    $contact_number = trim($_POST['contact_number']);

    // Check if required fields are filled
    if (!empty($doctor_id) && !empty($name) && !empty($department) && !empty($email)) {
        
        // Prepare the UPDATE statement
        $stmt = $conn->prepare("UPDATE Doctor SET Name = ?, Department = ?, Email = ?, ContactNumber = ? WHERE DoctorID = ?");
        $stmt->bind_param("ssssi", $name, $department, $email, $contact_number, $doctor_id);
        
        // Execute the statement
        if ($stmt->execute()) {
            // Success
            // You can add a success message to the session if you want
            // $_SESSION['success_message'] = "Doctor details updated successfully!";
        } else {
            // Error
            // $_SESSION['error_message'] = "Failed to update doctor details.";
        }
        $stmt->close();
    }
}

// Redirect back to the doctors list
header("Location: admin_manage_doctors.php");
exit();