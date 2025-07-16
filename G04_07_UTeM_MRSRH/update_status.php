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
$new_status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
$details = filter_input(INPUT_POST, 'details', FILTER_SANITIZE_STRING);

// Validate that all required data is present
if (!$report_id || !$new_status || !in_array($new_status, ['in_progress', 'resolved'])) {
    $_SESSION['message'] = 'Invalid request data.';
    $_SESSION['message_type'] = 'danger';
    header("Location: my_tasks.php");
    exit();
}

// Security: Verify that the current user is actually assigned to this report
$check_stmt = $conn->prepare("SELECT Assigned_To FROM report WHERE Report_ID = ?");
$check_stmt->bind_param("i", $report_id);
$check_stmt->execute();
$check_stmt->bind_result($assigned_to_id);
$check_stmt->fetch();
$check_stmt->close();

if ($assigned_to_id != $user_id) {
    // This is a security error. A user is trying to modify a report not assigned to them.
    error_log("Authorization Error: User {$user_id} tried to update report {$report_id} assigned to {$assigned_to_id}.");
    die("Authorization Error. You are not assigned to this report.");
}

// Special validation for 'resolved' status
if ($new_status == 'resolved' && (empty($details) || empty($_FILES['evidence']['name'][0]))) {
    $_SESSION['message'] = "To resolve a task, you must provide resolution details and upload at least one evidence photo.";
    $_SESSION['message_type'] = 'danger';
    header("Location: my_tasks.php");
    exit();
}

// --- Start Transaction ---
$conn->begin_transaction();

try {
    // 1. Update the report status and description
    $update_stmt = $conn->prepare("UPDATE report SET Status = ?, Status_Description = ? WHERE Report_ID = ?");
    $update_stmt->bind_param("ssi", $new_status, $details, $report_id);
    $update_stmt->execute();
    $update_stmt->close();

    // 2. Create a log entry for the status change
    $action_text = "Status changed to " . ucwords(str_replace('_', ' ', $new_status));
    $log_id = recordLog($conn, $report_id, $user_id, $action_text, $details);
    if (!$log_id) {
        throw new Exception("Failed to create log entry.");
    }

    // 3. Handle file uploads if the status is 'resolved'
    if ($new_status == 'resolved' && !empty($_FILES['evidence']['name'][0])) {
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
            
            // Create a unique filename
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
            throw new Exception("File move operation failed. At least one file must be uploaded successfully.");
        }
    }

    // If everything is successful, commit the transaction
    $conn->commit();
    $_SESSION['message'] = "Task status updated successfully!";
    $_SESSION['message_type'] = 'success';

} catch (Exception $e) {
    // If any step fails, roll back all changes
    $conn->rollback();
    $_SESSION['message'] = "Failed to update task: " . $e->getMessage();
    $_SESSION['message_type'] = 'danger';
    error_log("Task Update Failed for Report ID {$report_id}: " . $e->getMessage());
}

header("Location: my_tasks.php");
exit();
?>