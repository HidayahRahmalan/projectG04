<?php
session_name("staff_session");
session_start();
require_once __DIR__ . '/../connection.php'; // Ensure $pdo is defined

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
  header("Location: login.php");
  exit();
}

if (!isset($_GET['id'])) {
  die("Report ID not specified.");
}

$report_id = $_GET['id'];

// Fetch report info
$stmt = $pdo->prepare("
    SELECT r.*, u.name AS reporter_name 
    FROM Report r
    JOIN User u ON r.user_id = u.user_id
    WHERE r.report_id = ?
");
$stmt->execute([$report_id]);
$report = $stmt->fetch();

$technicianName = 'Not assigned yet';
$technicianContact = 'Not available';

if (!empty($report['technician_id'])) {
    $techStmt = $pdo->prepare("
        SELECT u.name, t.phone_number 
        FROM user u 
        JOIN technician t ON u.user_id = t.technician_id 
        WHERE u.user_id = ?
    ");
    $techStmt->execute([$report['technician_id']]);
    $technician = $techStmt->fetch();

    if ($technician) {
        $technicianName = $technician['name'];
        $technicianContact = $technician['phone_number'];
    }
}


if (!$report) {
  die("Report not found.");
}

// Fetch status
$statusStmt = $pdo->prepare("SELECT status FROM StatusLog WHERE report_id = :id ORDER BY timestamp DESC LIMIT 1");
$statusStmt->execute(['id' => $report_id]);
$status = $statusStmt->fetchColumn() ?? 'Pending';

// Fetch media
$mediaStmt = $pdo->prepare("SELECT file_path, media_type, uploaded_by_role FROM Media WHERE report_id = :id");
$mediaStmt->execute(['id' => $report_id]);
$mediaFiles = $mediaStmt->fetchAll();

$latestUpdateStmt = $pdo->prepare("
    SELECT timestamp 
    FROM statuslog 
    WHERE report_id = ? 
    ORDER BY timestamp DESC 
    LIMIT 1
");
$latestUpdateStmt->execute([$report_id]);
$latestUpdate = $latestUpdateStmt->fetchColumn();

$historyStmt = $pdo->prepare("
    SELECT sl.status, sl.notes, sl.timestamp, u.name AS changed_by 
    FROM StatusLog sl
    LEFT JOIN User u ON u.user_id = sl.changed_by
    WHERE sl.report_id = ?
    ORDER BY sl.timestamp DESC
");
$historyStmt->execute([$report_id]);
$statusHistory = $historyStmt->fetchAll();

// Add the initial report submission as the FIRST update
$initialLog = [
    'status' => 'submitted',
    'notes' => 'Initial report created',
    'timestamp' => $report['created_at'], // assuming 'created_at' column exists
    'changed_by' => $report['reporter_name']
];

$statusHistory[] = $initialLog; // Add it to the end since we reverse in the loop
usort($statusHistory, fn($a, $b) => strtotime($a['timestamp']) <=> strtotime($b['timestamp'])); // Sort ascending



?>