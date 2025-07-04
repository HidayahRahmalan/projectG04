<?php
session_start();
include 'connection.php';
include 'navbar.php';

// Protect page: only allow logged-in admin
if (!isset($_SESSION['staffID']) || $_SESSION['role'] !== 'Admin') {
  header("Location: login.php");
  exit();
}
// Fetch workload analytics
$statusCounts = [
  'Reported' => 0,
  'Awaiting Repair' => 0,
  'In Progress' => 0,
  'Pending Approval' => 0,
  'Completed' => 0
];

$analyticsSql = "SELECT Status, COUNT(*) as count FROM report GROUP BY Status";
$analyticsResult = $conn->query($analyticsSql);
if ($analyticsResult) {
  while ($row = $analyticsResult->fetch_assoc()) {
    $status = $row['Status'];
    if (isset($statusCounts[$status])) {
      $statusCounts[$status] = $row['count'];
    }
  }
}


// Fetch all reports with related staff and category info

$filter = isset($_GET['filter']) ? $_GET['filter'] : '';
$where = '';
if ($filter && isset($statusCounts[$filter])) {
  $where = "WHERE r.Status = '" . $conn->real_escape_string($filter) . "'";
}

$sql = "
    SELECT r.ReportID, r.Title, r.UrgencyLevel, r.Status, r.CreatedDate, 
           s.StaffName, c.CategoryType
    FROM Report r
    JOIN Staff s ON r.StaffID = s.StaffID
    JOIN Category c ON r.CategoryID = c.CategoryID
    $where
    ORDER BY r.CreatedDate DESC
";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin Dashboard - Reports</title>
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
      box-shadow: 0 6px 18px rgba(0, 0, 0, 0.2);
    }

    th,
    td {
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

    .analytics {
      display: flex;
      justify-content: center;
      gap: 30px;
      margin-bottom: 30px;
    }

    .analytic-box {
      background: #fff;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(44, 62, 80, 0.08);
      padding: 20px 30px;
      text-align: center;
      min-width: 120px;
    }

    .analytic-box .count {
      display: block;
      font-size: 2.2em;
      font-weight: bold;
      color: #f39c12;
    }

    .analytic-box .label {
      font-size: 1em;
      color: #34495e;
    }

    .analytic-box.pending {
      border-top: 5px solid #e74c3c;
    }

    .analytic-box.awaiting {
      border-top: 5px solid #f1c40f;
    }

    .analytic-box.inprogress {
      border-top: 5px solid #2980b9;
    }

    .analytic-box.completed {
      border-top: 5px solid #27ae60;
    }
  </style>
</head>

<body>

  <h1>Admin Dashboard - Maintenance Reports</h1>

  <div class="analytics">
    <a href="?filter=Reported" class="analytic-box reported">
      <span class="count"><?php echo $statusCounts['Reported']; ?></span>
      <span class="label">Reported</span>
    </a>
      <a href="?filter=Awaiting Repair" class="analytic-box awaiting">
      <span class="count"><?php echo $statusCounts['Awaiting Repair']; ?></span>
      <span class="label">Awaiting Repair</span>
    </a>
    <a href="?filter=In Progress" class="analytic-box inprogress">
      <span class="count"><?php echo $statusCounts['In Progress']; ?></span>
      <span class="label">In Progress</span>
    </a>
    <a href="?filter=Pending Approval" class="analytic-box pending">
      <span class="count"><?php echo $statusCounts['Pending Approval']; ?></span>
      <span class="label">Pending Approval</span>
    </a>
    <a href="?filter=Completed" class="analytic-box completed">
      <span class="count"><?php echo $statusCounts['Completed']; ?></span>
      <span class="label">Completed</span>
    </a>
  </div>



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
      </tr>
    </thead>
    <tbody>
      <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?php echo htmlspecialchars($row['ReportID']); ?></td>
            <td><a class="report-link" href="report_detail.php?report_id=<?php echo urlencode($row['ReportID']); ?>">
                <?php echo htmlspecialchars($row['Title']); ?></a></td>
            <td><?php echo htmlspecialchars($row['CategoryType']); ?></td>
            <td><?php echo htmlspecialchars($row['UrgencyLevel']); ?></td>
            <td><?php echo htmlspecialchars($row['Status']); ?></td>
            <td><?php echo htmlspecialchars($row['StaffName']); ?></td>
            <td><?php echo htmlspecialchars($row['CreatedDate']); ?></td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr>
          <td colspan="7" style="text-align:center;">No reports found.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>



</body>

</html>