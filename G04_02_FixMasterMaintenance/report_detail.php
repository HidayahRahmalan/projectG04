<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['staffID']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['report_id'])) {
    die("Report ID is required.");
}

$reportID = $_GET['report_id'];
$successMessage = '';
$errorMessage = '';

// Handle staff assignment form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assigned_staff'])) {
    $assignedStaffID = $_POST['assigned_staff'];
    
    // Get current status before update to ensure it's 'Reported'
    $stmtCheck = $conn->prepare("SELECT Status FROM Report WHERE ReportID = ?");
    $stmtCheck->bind_param("s", $reportID);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();
    $currentStatusRow = $resultCheck->fetch_assoc();
    $stmtCheck->close();

    if (!$currentStatusRow) {
        $errorMessage = "Report not found.";
    } else {
        $currentStatus = $currentStatusRow['Status'];
        // Only allow assignment if status is 'Reported'
        if ($currentStatus === 'Reported') {
            $newStatus = 'Awaiting Repair';

            $conn->begin_transaction();
            try {
                // Update Report status
                $stmtUpdate = $conn->prepare("UPDATE Report SET Status = ?, LastUpdatedDate = NOW() WHERE ReportID = ?");
                $stmtUpdate->bind_param("ss", $newStatus, $reportID);
                $stmtUpdate->execute();
                $stmtUpdate->close();

                // Generate new LogID
                $lastLogQuery = $conn->query("SELECT LogID FROM MaintenanceStatusLog ORDER BY LogID DESC LIMIT 1");
                $lastLogNum = 0;
                if ($lastLogQuery && $lastLog = $lastLogQuery->fetch_assoc()) {
                    $lastLogNum = (int)substr($lastLog['LogID'], 3);
                }
                $newLogID = 'LOG' . str_pad($lastLogNum + 1, 3, '0', STR_PAD_LEFT);

                // Insert into MaintenanceStatusLog
                $stmtLog = $conn->prepare("INSERT INTO MaintenanceStatusLog (LogID, ReportID, StaffID, PreviousStatus, CurrentStatus, DateTime) VALUES (?, ?, ?, ?, ?, NOW())");
                $stmtLog->bind_param("sssss", $newLogID, $reportID, $assignedStaffID, $currentStatus, $newStatus);
                $stmtLog->execute();
                $stmtLog->close();

                $conn->commit();
                $successMessage = "Maintenance staff assigned and status updated to Awaiting Repair.";
                // To prevent re-submission and show the updated state, redirect
                header("Location: report_detail.php?report_id=" . urlencode($reportID) . "&status=assigned");
                exit();
            } catch (Exception $e) {
                $conn->rollback();
                $errorMessage = "Failed to assign maintenance staff: " . $e->getMessage();
            }
        } else {
            $errorMessage = "This report is not in 'Reported' status. You cannot assign maintenance staff.";
        }
    }
}

// Fetch report details with staff and category info
$stmt = $conn->prepare("
    SELECT r.ReportID, r.Title, r.Description, r.UrgencyLevel, r.Status, r.CreatedDate, r.LastUpdatedDate, 
           s.StaffName AS ReporterName, c.CategoryID, c.CategoryType
    FROM Report r
    JOIN Staff s ON r.StaffID = s.StaffID
    JOIN Category c ON r.CategoryID = c.CategoryID
    WHERE r.ReportID = ?
");
$stmt->bind_param("s", $reportID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Report not found.");
}
$report = $result->fetch_assoc();
$stmt->close();

// Fetch the assigned maintenance staff's name
$assignedStaffName = 'No staff assigned yet.';
$stmt = $conn->prepare("
    SELECT s.StaffName
    FROM MaintenanceStatusLog m
    JOIN Staff s ON m.StaffID = s.StaffID
    WHERE m.ReportID = ? AND s.Role = 'Maintenance'
    ORDER BY m.DateTime DESC LIMIT 1
");
$stmt->bind_param("s", $reportID);
$stmt->execute();
$stmt->bind_result($fetchedStaffName);
if ($stmt->fetch()) {
    $assignedStaffName = $fetchedStaffName;
}
$stmt->close();

// Fetch media files
$stmt = $conn->prepare("SELECT MediaType, FilePath FROM MediaFile WHERE ReportID = ?");
$stmt->bind_param("s", $reportID);
$stmt->execute();
$mediaResult = $stmt->get_result();
$mediaFiles = [];
while ($row = $mediaResult->fetch_assoc()) {
    $mediaFiles[] = $row;
}
$stmt->close();

// Prepare for staff assignment dropdown
$canAssignStaff = ($report['Status'] === 'Reported');
$maintenanceStaff = [];
if ($canAssignStaff) {
    $categoryToPositionMap = [
        'Electrical' => 'Electrician', 'Plumbing' => 'Plumber', 'HVAC' => 'HVAC Technician',
        'Carpentry' => 'Carpenter', 'Cleaning' => 'Cleaner', 'Painting' => 'Painter',
        'Structural Damage' => 'Structural Technician', 'Safety Hazard' => 'Safety Officer',
        'Equipment Malfunction' => 'Equipment Technician', 'IT / Network' => 'IT Technician',
        'Grounds / Landscaping' => 'Groundskeeper', 'Pest Control' => 'Pest Control Technician',
        'Security System' => 'Security Officer', 'Other' => 'General Maintenance'
    ];
    $allowedPosition = $categoryToPositionMap[$report['CategoryType']] ?? 'General Maintenance';

    $stmt = $conn->prepare("SELECT StaffID, StaffName FROM Staff WHERE Role = 'Maintenance' AND Position = ?");
    $stmt->bind_param("s", $allowedPosition);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $maintenanceStaff[] = $row;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Report Details - <?php echo htmlspecialchars($report['ReportID']); ?></title>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap');
  body { font-family: 'Roboto', sans-serif; background: linear-gradient(135deg, #2c3e50, #4b6584); margin: 0; padding: 30px; color: #34495e; }
  .container { max-width: 700px; margin: auto; background: #ecf0f1; border-radius: 10px; padding: 30px; box-shadow: 0 6px 18px rgba(0,0,0,0.3); }
  h1 { color: #f39c12; margin-bottom: 20px; text-align: center; }
  .detail-item { margin-bottom: 15px; }
  .label { font-weight: 700; margin-bottom: 5px; color: #2d3436; }
  form { margin-top: 25px; }
  select, button { font-size: 16px; padding: 10px 15px; border-radius: 6px; border: 1.5px solid #bdc3c7; margin-right: 10px; }
  button { background: #f39c12; color: white; border: none; cursor: pointer; font-weight: 700; }
  button:hover { background: #d88e0a; }
  .message { font-weight: 600; margin: 15px 0; text-align: center; }
  .success { color: #27ae60; }
  .error { color: #e74c3c; }
  .approve-btn { background-color: #27ae60; }
  .approve-btn:hover { background-color: #219150; }
</style>
</head>
<body>
  <div class="container">
    <h1>Report Details - <?php echo htmlspecialchars($report['ReportID']); ?></h1>

    <div class="detail-item">
      <div class="label">Title:</div>
      <div><?php echo htmlspecialchars($report['Title']); ?></div>
    </div>
    <div class="detail-item">
      <div class="label">Description:</div>
      <div><?php echo nl2br(htmlspecialchars($report['Description'])); ?></div>
    </div>
    <div class="detail-item">
      <div class="label">Category:</div>
      <div><?php echo htmlspecialchars($report['CategoryType']); ?></div>
    </div>
    <div class="detail-item">
      <div class="label">Urgency Level:</div>
      <div><?php echo htmlspecialchars($report['UrgencyLevel']); ?></div>
    </div>
    <div class="detail-item">
      <div class="label">Status:</div>
      <div><?php echo htmlspecialchars($report['Status']); ?></div>
    </div>
    <div class="detail-item">
      <div class="label">Reported By:</div>
      <div><?php echo htmlspecialchars($report['ReporterName']); ?></div>
    </div>
    <div class="detail-item">
      <div class="label">Assigned Maintenance Staff:</div>
      <div><?php echo htmlspecialchars($assignedStaffName); ?></div>
    </div>
    <div class="detail-item">
      <div class="label">Created Date:</div>
      <div><?php echo htmlspecialchars($report['CreatedDate']); ?></div>
    </div>
    <div class="detail-item">
      <div class="label">Last Updated:</div>
      <div><?php echo htmlspecialchars($report['LastUpdatedDate']); ?></div>
    </div>
    <div class="detail-item">
      <div class="label">Media Files:</div>
      <div>
        <?php if (!empty($mediaFiles)): ?>
            <?php foreach ($mediaFiles as $media): ?>
                <?php if ($media['MediaType'] == 'Image'): ?>
                    <img src="<?php echo htmlspecialchars($media['FilePath']); ?>" alt="Image" style="max-width: 100%; height: auto; margin-bottom: 10px;">
                <?php elseif ($media['MediaType'] == 'Video'): ?>
                    <video width="100%" controls><source src="<?php echo htmlspecialchars($media['FilePath']); ?>" type="video/mp4"></video>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No media available for this report.</p>
        <?php endif; ?>
      </div>
    </div>

    <!-- Show the Assign form only if status is 'Reported' -->
    <?php if ($canAssignStaff): ?>
      <form method="POST" action="">
        <input type="hidden" name="assign_staff" value="1">
        <div class="detail-item">
            <label for="assigned_staff" class="label">Assign Maintenance Staff (Position: <?php echo htmlspecialchars($allowedPosition); ?>):</label>
            <select name="assigned_staff" id="assigned_staff" required>
              <option value="">-- Select Staff --</option>
              <?php foreach ($maintenanceStaff as $staff): ?>
                <option value="<?php echo htmlspecialchars($staff['StaffID']); ?>">
                  <?php echo htmlspecialchars($staff['StaffName']); ?>
                </option>
              <?php endforeach; ?>
            </select>
            <button type="submit">Assign</button>
        </div>
      </form>
    <?php endif; ?>

    <!-- Show the Approve button only if Admin and status is 'Pending Approval' -->
    <?php if ($_SESSION['role'] === 'Admin' && $report['Status'] === 'Pending Approval'): ?>
        <form method="POST" action="approve_report.php" onsubmit="return confirm('Are you sure you want to mark this report as Completed? This action cannot be undone.');">
            <input type="hidden" name="report_id" value="<?php echo htmlspecialchars($report['ReportID']); ?>">
            <button type="submit" class="approve-btn">Approve & Complete Report</button>
        </form>
    <?php endif; ?>

    <!-- Display any success or error messages -->
    <?php if ($successMessage): ?>
      <div class="message success"><?php echo htmlspecialchars($successMessage); ?></div>
    <?php elseif ($errorMessage): ?>
      <div class="message error"><?php echo htmlspecialchars($errorMessage); ?></div>
    <?php endif; ?>

  </div>
</body>
</html>