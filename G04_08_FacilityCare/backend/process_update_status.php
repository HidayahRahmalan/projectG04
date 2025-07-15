<?php
// echo '<pre>';
// print_r($_POST);
// exit;
session_start();
require_once '../connection.php';

$reportId = $_POST['report_id'];
$status = $_POST['status'];
$notes = $_POST['notes'] ?? null;
$technicianId = $_POST['technician_id'];
$adminId = $_SESSION['user_id'] ?? 'A001';

try {
    // 1. Insert ke statuslog
    $stmt = $pdo->prepare("INSERT INTO statuslog (report_id, `status`, notes, changed_by) VALUES (?, ?, ?, ?)");
    $stmt->execute([$reportId, $status, $notes, $adminId]);

    // 2. Update technician_id dalam report
    $stmt2 = $pdo->prepare("UPDATE report SET technician_id = ? WHERE report_id = ?");
    $stmt2->execute([$technicianId, $reportId]);

    $_SESSION['success'] = "Status updated successfully!";
    header("Location: ../Admin/editReports.php?id=$reportId");
    exit;
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
