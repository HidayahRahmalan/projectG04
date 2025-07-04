<?php
session_start();
include 'connection.php';
include 'm_navbar.php';

if (!isset($_SESSION['staffID'])) {
    header("Location: login.php");
    exit();
}

$staffID = $_SESSION['staffID'];
$role = $_SESSION['role'];

// Load staff position
$staffIDEscaped = $conn->real_escape_string($staffID);
$positionResult = $conn->query("SELECT Position FROM Staff WHERE StaffID = '$staffIDEscaped'");
$staffPosition = '';
if ($positionResult && $row = $positionResult->fetch_assoc()) {
    $staffPosition = trim($row['Position']);
}

// Category-position mapping
$categoryAccess = [
    'Electrical' => ['Electrician'],
    'Plumbing' => ['Plumber'],
    'HVAC' => ['HVAC Technician'],
    'Carpentry' => ['Carpenter'],
    'Cleaning' => ['Cleaner'],
    'Painting' => ['Painter'],
    'Structural Damage' => ['Structural Technician', 'General Maintenance'],
    'Safety Hazard' => ['Safety Officer', 'Maintenance Supervisor'],
    'Equipment Malfunction' => ['Equipment Technician', 'Mechanic'],
    'IT / Network' => ['IT Technician', 'Network Specialist'],
    'Grounds / Landscaping' => ['Groundskeeper', 'Landscaper'],
    'Pest Control' => ['Pest Control Technician'],
    'Security System' => ['Security Officer', 'Security Technician'],
    'Other' => ['General Maintenance', 'Multi-skilled Technician']
];

if ($role === 'Admin') {
    $sql = "
        SELECT r.ReportID, r.Title, r.UrgencyLevel, r.Status, r.CreatedDate,
               s.StaffName, c.CategoryType
        FROM Report r
        JOIN Staff s ON r.StaffID = s.StaffID
        JOIN Category c ON r.CategoryID = c.CategoryID
        ORDER BY 
            CASE r.UrgencyLevel
                WHEN 'High' THEN 1
                WHEN 'Medium' THEN 2
                WHEN 'Low' THEN 3
                ELSE 4
            END,
            r.CreatedDate DESC
    ";
    $result = $conn->query($sql);
} else {
    // Allowed statuses for maintenance staff to see
    $allowedStatuses = "'Awaiting Repair', 'In Progress', 'Pending Approval'";

    $sql = "
        SELECT r.ReportID, r.Title, r.UrgencyLevel, r.Status, r.CreatedDate,
               s.StaffName, c.CategoryType
        FROM Report r
        JOIN Staff s ON r.StaffID = s.StaffID
        JOIN Category c ON r.CategoryID = c.CategoryID
        JOIN (
            SELECT m.ReportID, m.CurrentStatus, m.StaffID
            FROM MaintenanceStatusLog m
            JOIN (
                SELECT ReportID, MAX(DateTime) AS MaxDate
                FROM MaintenanceStatusLog
                GROUP BY ReportID
            ) lm ON m.ReportID = lm.ReportID AND m.DateTime = lm.MaxDate
            WHERE m.CurrentStatus IN ($allowedStatuses)
        ) AS latestLog ON r.ReportID = latestLog.ReportID
        WHERE latestLog.StaffID = ?
        ORDER BY 
            CASE r.UrgencyLevel
                WHEN 'High' THEN 1
                WHEN 'Medium' THEN 2
                WHEN 'Low' THEN 3
                ELSE 4
            END,
            r.CreatedDate DESC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $staffID);
    $stmt->execute();
    $result = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?php echo htmlspecialchars($role); ?> Dashboard - Reports</title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap');
    body {
      font-family: 'Roboto', sans-serif;
      background: linear-gradient(135deg, #2c3e50, #4b6584);
      margin: 0;
      padding: 30px;
      color: #34495e;
    }
    h1 {
      text-align: center;
      margin-bottom: 30px;
      color: #f39c12;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      background: #ecf0f1;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 6px 18px rgba(0,0,0,0.2);
    }
    th, td {
      padding: 12px 15px;
      text-align: left;
      border-bottom: 1px solid #bdc3c7;
    }
    th {
      background: #f39c12;
      color: white;
    }
    tr:hover {
      background: #d88e0a;
      color: white;
      cursor: pointer;
    }
    a.report-link {
      color: #2980b9;
      text-decoration: none;
    }
    a.report-link:hover {
      text-decoration: underline;
    }
    .start-btn {
      background: #2980b9;
      color: white;
      border: none;
      padding: 6px 12px;
      border-radius: 4px;
      cursor: pointer;
      font-weight: bold;
    }
    .start-btn:hover {
      background: #1f6691;
    }
    .update-btn {
      background: #27ae60;
      color: white;
      border: none;
      padding: 6px 12px;
      border-radius: 4px;
      cursor: pointer;
      font-weight: bold;
    }
    .update-btn:hover {
      background: #219150;
    }
  </style>
</head>
<body>

<h1><?php echo htmlspecialchars($role); ?> Dashboard - Maintenance Reports</h1>

<table>
  <thead>
    <tr>
      <th>Report ID</th>
      <th>Title</th>
      <th>Category</th>
      <th>Urgency</th>
      <th>Status</th>
      <th>Reported By</th>
      <th>Created Date</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
    <?php if ($result && $result->num_rows > 0): ?>
      <?php while($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?php echo htmlspecialchars($row['ReportID']); ?></td>
          <td><a class="report-link" href="maintenance_report_detail.php?report_id=<?php echo urlencode($row['ReportID']); ?>">
            <?php echo htmlspecialchars($row['Title']); ?></a></td>
          <td><?php echo htmlspecialchars($row['CategoryType']); ?></td>
          <td><?php echo htmlspecialchars($row['UrgencyLevel']); ?></td>
          <td><?php echo htmlspecialchars($row['Status']); ?></td>
          <td><?php echo htmlspecialchars($row['StaffName']); ?></td>
          <td><?php echo htmlspecialchars($row['CreatedDate']); ?></td>
          <td>
            <?php if ($row['Status'] === 'Awaiting Repair'): ?>
              <form method="POST" action="start_report.php">
                <input type="hidden" name="report_id" value="<?php echo htmlspecialchars($row['ReportID']); ?>" />
                <button class="start-btn" type="submit">Start</button>
              </form>
            <?php elseif ($row['Status'] === 'In Progress'): ?>
              <form method="POST" action="report_update.php" style="margin:0;">
                <input type="hidden" name="report_id" value="<?php echo htmlspecialchars($row['ReportID']); ?>" />
                <button class="update-btn" type="submit">Update</button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endwhile; ?>
    <?php else: ?>
      <tr><td colspan="8" style="text-align:center;">No reports found.</td></tr>
    <?php endif; ?>
  </tbody>
</table>

</body>
</html>
