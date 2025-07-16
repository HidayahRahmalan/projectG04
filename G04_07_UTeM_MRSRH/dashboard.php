<?php
session_start();

// Ensure the user is logged in before proceeding
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Route the user based on their role ID
$role = $_SESSION['role'] ?? 0;

if ($role == 5) {
    // Role 5 is Admin
    header("Location: admin_all_reports.php"); // Admin's main view is all reports
} else {
    // All other roles are considered Staff
    header("Location: staff_dashboard.php");
}
exit();
?>