<?php
session_start();
include 'connection.php';

// Ensure an Admin is logged in
if (!isset($_SESSION['staffID']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['report_id'])) {
    $reportID = $conn->real_escape_string($_POST['report_id']);
    // The action is performed by the logged-in Admin
    $staffID = $conn->real_escape_string($_SESSION['staffID']); 

    if (empty($reportID) || empty($staffID)) {
        die("Invalid Report ID or Staff ID.");
    }

    // Begin a transaction to ensure both updates happen or neither do
    $conn->begin_transaction();

    try {
        // Lock the row for update to prevent race conditions
        $statusQuery = $conn->prepare("SELECT Status FROM Report WHERE ReportID = ? FOR UPDATE");
        $statusQuery->bind_param("s", $reportID);
        $statusQuery->execute();
        $result = $statusQuery->get_result();
        
        if ($result && $statusRow = $result->fetch_assoc()) {
            $previousStatus = $statusRow['Status'];
            $newStatus = 'Completed'; // The target status

            // Only allow approval if the current status is 'Pending Approval'
            if ($previousStatus === 'Pending Approval') {
                // 1. Update the Report status to 'Completed'
                $update = $conn->prepare("UPDATE Report SET Status = ?, LastUpdatedDate = NOW() WHERE ReportID = ?");
                $update->bind_param("ss", $newStatus, $reportID);
                $update->execute();

                // 2. Generate the next LogID
                $logIDQuery = $conn->query("SELECT LogID FROM maintenancestatuslog ORDER BY LogID DESC LIMIT 1");
                $newLogID = 'LOG001';
                if ($logIDQuery && $row = $logIDQuery->fetch_assoc()) {
                    $lastID = $row['LogID'];
                    $num = (int)substr($lastID, 3);
                    $num++;
                    $newLogID = 'LOG' . str_pad($num, 3, '0', STR_PAD_LEFT);
                }

                // 3. Insert into maintenancestatuslog
                $stmt = $conn->prepare("INSERT INTO maintenancestatuslog (LogID, ReportID, StaffID, PreviousStatus, CurrentStatus, DateTime) VALUES (?, ?, ?, ?, ?, NOW())");
                $stmt->bind_param("sssss", $newLogID, $reportID, $staffID, $previousStatus, $newStatus);
                $stmt->execute();
                
                // If all queries were successful, commit the transaction
                $conn->commit();
                
                // Redirect back to the admin dashboard
                header("Location: index.php?status=approved");
                exit();

            } else {
                throw new Exception("This report is not pending approval. Current status: " . htmlspecialchars($previousStatus));
            }
        } else {
            throw new Exception("Report not found.");
        }

    } catch (Exception $e) {
        // If any query fails, roll back all changes
        $conn->rollback();
        die("Error: " . $e->getMessage());
    }

} else {
    die("Invalid request method.");
}
?>