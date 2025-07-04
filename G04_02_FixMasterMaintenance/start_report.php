<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['staffID'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['report_id'])) {
    $reportID = $conn->real_escape_string($_POST['report_id']);
    $staffID = $conn->real_escape_string($_SESSION['staffID']);

    if (empty($reportID) || empty($staffID)) {
        echo "Invalid Report ID or Staff ID.";
        exit();
    }

    // Fetch current status
    $statusQuery = $conn->prepare("SELECT Status FROM Report WHERE ReportID = ?");
    $statusQuery->bind_param("s", $reportID);
    $statusQuery->execute();
    $result = $statusQuery->get_result();

    if ($result && $statusRow = $result->fetch_assoc()) {
        $previousStatus = $statusRow['Status'];

        if ($previousStatus === 'Awaiting Repair') {
            // Update the report status
            $update = $conn->prepare("UPDATE Report SET Status = 'In Progress' WHERE ReportID = ?");
            $update->bind_param("s", $reportID);

            if ($update->execute()) {
                // Generate next LogID (e.g., LOG001, LOG002)
                $logIDQuery = $conn->query("SELECT LogID FROM maintenancestatuslog ORDER BY LogID DESC LIMIT 1");
                $newLogID = 'LOG001';
                if ($logIDQuery && $row = $logIDQuery->fetch_assoc()) {
                    $lastID = $row['LogID']; // e.g., LOG0042
                    $num = (int)substr($lastID, 3); // Extract numeric part
                    $num++; // Increment
                    $newLogID = 'LOG' . str_pad($num, 3, '0', STR_PAD_LEFT); // e.g., LOG043
                }

                // Insert into maintenancestatuslog
                $stmt = $conn->prepare("INSERT INTO maintenancestatuslog (LogID, ReportID, StaffID, PreviousStatus, CurrentStatus, DateTime) VALUES (?, ?, ?, ?, ?, NOW())");
                $currentStatus = 'In Progress';
                $stmt->bind_param("sssss", $newLogID, $reportID, $staffID, $previousStatus, $currentStatus);

                if ($stmt->execute()) {
                    $stmt->close();
                    header("Location: maintenance_index.php");
                    exit();
                } else {
                    echo "Failed to insert log: " . $stmt->error;
                }
            } else {
                echo "Failed to update report: " . $update->error;
            }
        } else {
            echo "Invalid status transition.";
        }
    } else {
        echo "Report not found.";
    }
} else {
    echo "Invalid request.";
}
?>
