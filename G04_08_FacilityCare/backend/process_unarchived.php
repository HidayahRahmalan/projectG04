<?php
session_name("admin_session");
session_start();
require_once '../connection.php';

$report_id = $_GET['id'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;

if ($report_id && $user_id) {
    // 1. Set archive = 0
    $stmt = $pdo->prepare("UPDATE report SET archive = 0 WHERE report_id = ?");
    $stmt->execute([$report_id]);

    // 2. Get the last non-archived status
    $stmt = $pdo->prepare("
        SELECT status FROM statuslog 
        WHERE report_id = ? AND status != 'archived' 
        ORDER BY timestamp DESC 
        LIMIT 1
    ");
    $stmt->execute([$report_id]);
    $prevStatus = $stmt->fetchColumn() ?: 'open';

    // 3. Insert new log to show it was restored
    $note = "Report restored from archive.";
    $insert = $pdo->prepare("
        INSERT INTO statuslog (report_id, status, notes, changed_by, timestamp)
        VALUES (?, ?, ?, ?, NOW())
    ");
    $insert->execute([$report_id, $prevStatus, $note, $user_id]);

    // 4. Redirect
    header("Location: ../admin/archived.php?restored=1");
    exit();
} else {
    header("Location: ../admin/archived.php?restored=0");
    exit();
}
?>
