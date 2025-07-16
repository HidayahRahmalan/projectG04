<?php
session_start();
require 'db.php';
require 'functions.php';

// Security: Must be a logged-in user making a POST request
if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$report_id = filter_input(INPUT_POST, 'report_id', FILTER_SANITIZE_NUMBER_INT);
$details = filter_input(INPUT_POST, 'details', FILTER_SANITIZE_STRING);

// Validate input
if (empty($report_id) || empty($_FILES['evidence']['name'][0])) {
    $_SESSION['message'] = "You must select a report and at least one file to upload.";
    $_SESSION['message_type'] = "danger";
    header("Location: view_report.php?id=" . $report_id);
    exit();
}

// Security: Verify the user is assigned to this report and it's 'in_progress'
$check_stmt = $conn->prepare("SELECT Assigned_To, Status FROM report WHERE Report_ID = ?");
$check_stmt->bind_param("i", $report_id);
$check_stmt->execute();
$check_stmt->bind_result($assigned_to_id, $current_status);
$check_stmt->fetch();
$check_stmt->close();

if ($assigned_to_id != $user_id || $current_status !== 'in_progress') {
    error_log("Authorization Error: User {$user_id} tried to add evidence to report {$report_id} with status '{$current_status}'.");
    die("Authorization Error: You can only add evidence to 'in progress' reports assigned to you.");
}

// --- Start Transaction ---
$conn->begin_transaction();

try {
    // 1. Create a log entry for this action
    $log_details = "Added new evidence." . (!empty($details) ? " Note: " . $details : "");
    $log_id = recordLog($conn, $report_id, $user_id, 'Added Evidence', $details);

    if (!$log_id) {
        throw new Exception("Failed to create log entry.");
    }

    // 2. Process and insert each uploaded file, linking it to the new log
    $upload_success = false;
    foreach ($_FILES['evidence']['tmp_name'] as $index => $tmpName) {
        if ($_FILES['evidence']['error'][$index] !== UPLOAD_ERR_OK) continue;

        $originalName = basename($_FILES['evidence']['name'][$index]);
        $fileType = mime_content_type($tmpName);
        $typeCategory = explode('/', $fileType)[0];
        
        if (!in_array($typeCategory, ['image', 'video'])) continue;

        $uploadDir = "uploads/{$typeCategory}/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = "{$report_id}_{$log_id}_" . time() . "_{$originalName}";
        $targetFile = $uploadDir . $fileName;

        if (move_uploaded_file($tmpName, $targetFile)) {
            $media_stmt = $conn->prepare("INSERT INTO media (Report_ID, Log_ID, Type, File_path) VALUES (?, ?, ?, ?)");
            $media_stmt->bind_param("iiss", $report_id, $log_id, $typeCategory, $targetFile);
            $media_stmt->execute();
            $media_stmt->close();
            $upload_success = true;
        }
    }

    if (!$upload_success) {
        throw new Exception("File move operation failed. No valid files were uploaded.");
    }
    
    // If all good, commit the transaction
    $conn->commit();
    $_SESSION['message'] = "New evidence added successfully!";
    $_SESSION['message_type'] = "success";

} catch (Exception $e) {
    // If something went wrong, roll back
    $conn->rollback();
    $_SESSION['message'] = "Failed to add evidence: " . $e->getMessage();
    $_SESSION['message_type'] = "danger";
    error_log("Add Evidence Failed for Report ID {$report_id}: " . $e->getMessage());
}

header("Location: view_report.php?id=" . $report_id);
exit();
?>