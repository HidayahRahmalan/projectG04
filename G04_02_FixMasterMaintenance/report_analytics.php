<?php
session_start();
include 'connection.php';
include 'navbar.php';

// Protect page: only allow logged-in admin
if (!isset($_SESSION['staffID']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

// --- DATA FETCHING FOR ANALYTICS ---

// 1. Report Counts by Status
$statusCounts = [
    'Reported' => 0, 'Awaiting Repair' => 0, 'In Progress' => 0,
    'Pending Approval' => 0, 'Completed' => 0
];
$statusSql = "SELECT Status, COUNT(*) as count FROM report GROUP BY Status";
$statusResult = $conn->query($statusSql);
if ($statusResult) {
    while ($row = $statusResult->fetch_assoc()) {
        if (isset($statusCounts[$row['Status']])) {
            $statusCounts[$row['Status']] = $row['count'];
        }
    }
}

// 2. Report Counts by Category
$categoryLabels = [];
$categoryData = [];
$categorySql = "SELECT c.CategoryType, COUNT(r.ReportID) as count 
                FROM Report r 
                JOIN Category c ON r.CategoryID = c.CategoryID 
                GROUP BY c.CategoryType ORDER BY count DESC";
$categoryResult = $conn->query($categorySql);
if ($categoryResult) {
    while ($row = $categoryResult->fetch_assoc()) {
        $categoryLabels[] = $row['CategoryType'];
        $categoryData[] = $row['count'];
    }
}

// 3. Report Counts by Urgency
$urgencyLabels = [];
$urgencyData = [];
$urgencySql = "SELECT UrgencyLevel, COUNT(*) as count FROM report GROUP BY UrgencyLevel";
$urgencyResult = $conn->query($urgencySql);
if ($urgencyResult) {
    while ($row = $urgencyResult->fetch_assoc()) {
        $urgencyLabels[] = $row['UrgencyLevel'];
        $urgencyData[] = $row['count'];
    }
}
/*
// 4. Average Resolution Time for Completed Reports (in hours, minutes, seconds)
$avgResolutionString = "N/A"; // Default value
// MODIFIED: Calculate average time in SECONDS for more precision
$resolutionSql = "
    SELECT AVG(TIMESTAMPDIFF(SECOND, r.CreatedDate, completion_log.completion_date)) AS avg_seconds
    FROM Report r
    JOIN (
        SELECT ReportID, MAX(DateTime) AS completion_date
        FROM MaintenanceStatusLog
        WHERE CurrentStatus = 'Completed'
        GROUP BY ReportID
    ) AS completion_log ON r.ReportID = completion_log.ReportID
    WHERE r.Status = 'Completed'";
$resolutionResult = $conn->query($resolutionSql);
if ($resolutionResult && $row = $resolutionResult->fetch_assoc()) {
    $avgSeconds = (int)($row['avg_seconds'] ?? 0);
    if ($avgSeconds > 0) {
        // MODIFIED: Convert total seconds into H, M, S parts
        $h = floor($avgSeconds / 3600);
        $m = floor(($avgSeconds % 3600) / 60);
        $s = $avgSeconds % 60;
        
        $timeParts = [];
        if ($h > 0) {
            $timeParts[] = "{$h}h";
        }
        // Show minutes if hours are present or if minutes > 0
        if ($m > 0 || !empty($timeParts)) { 
            $timeParts[] = "{$m}m";
        }
        $timeParts[] = "{$s}s";
        
        $avgResolutionString = implode(' ', $timeParts);
    }
}*/


// 5. Staff Workload (Active Reports Assigned)
$staffLabels = [];
$staffData = [];
// This query finds the last assigned staff for each active report
// --- THIS IS THE CORRECTED CODE ---
// Replaces the query that uses 'WITH' with a compatible derived table
// --- THIS IS THE FINAL, MOST COMPATIBLE VERSION ---
// Replaces the query to work on very old MySQL versions without window functions.

$staffWorkloadSql = "
    SELECT
        s.StaffName,
        COUNT(msl.ReportID) as report_count
    FROM maintenancestatuslog msl
    JOIN (
        -- Subquery to find the exact time of the latest log entry for each active report
        SELECT 
            msl_inner.ReportID, 
            MAX(msl_inner.DateTime) AS MaxDateTime
        FROM maintenancestatuslog msl_inner
        JOIN Report r ON msl_inner.ReportID = r.ReportID
        WHERE r.Status IN ('Awaiting Repair', 'In Progress', 'Pending Approval')
        GROUP BY msl_inner.ReportID
    ) AS latest_logs ON msl.ReportID = latest_logs.ReportID AND msl.DateTime = latest_logs.MaxDateTime
    JOIN staff s ON msl.StaffID = s.StaffID
    GROUP BY s.StaffName
    ORDER BY report_count DESC;
";

// --- END OF CORRECTION ---
// --- END OF CORRECTION ---
$staffResult = $conn->query($staffWorkloadSql);
if ($staffResult) {
    while ($row = $staffResult->fetch_assoc()) {
        $staffLabels[] = $row['StaffName'];
        $staffData[] = $row['report_count'];
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>Admin - Report Analytics</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap');
        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #2c3e50, #4b6584);
            margin: 0;
            padding: 30px;
            color: #34495e;
        }
        .container {
            max-width: 1200px;
            margin: auto;
        }
        h1, h2 {
            text-align: center;
            color: #f39c12;
            margin-bottom: 20px;
        }
        .analytics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        .analytic-box {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 20px;
            text-align: center;
            border-top: 5px solid #f39c12;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .analytic-box .count {
            display: block;
            font-size: 2em;
            font-weight: bold;
            color: #2c3e50;
        }
        .analytic-box .label {
            font-size: 1em;
            color: #555;
            margin-top: 5px;
        }
        .chart-container {
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Maintenance Report Analytics</h1>

    <!-- Top Level Analytics -->
    <div class="analytics-grid">
        <div class="analytic-box">
            <span class="count"><?php echo htmlspecialchars(array_sum($statusCounts)); ?></span>
            <span class="label">Total Reports</span>
        </div>
        <div class="analytic-box">
            <span class="count"><?php echo htmlspecialchars($statusCounts['In Progress'] + $statusCounts['Awaiting Repair']); ?></span>
            <span class="label">Active Reports</span>
        </div>
        <div class="analytic-box">
            <span class="count"><?php echo htmlspecialchars($statusCounts['Completed']); ?></span>
            <span class="label">Completed</span>
        </div>
        <div class="analytic-box">
            <span class="count"><?php echo htmlspecialchars($statusCounts['Pending Approval']); ?></span>
            <span class="label">Pending Approval</span>
        </div>
               <!--
        <div class="analytic-box">
            <span class="count"></span>
            <span class="label">Avg. Resolution Time</span>
        </div>
        -->

    <!-- Charts Grid -->
    <div class="analytics-grid" style="grid-template-columns: 1fr 1fr; gap: 30px;">
        <div class="chart-container">
            <h2>Reports by Category</h2>
            <canvas id="categoryChart"></canvas>
        </div>
        <div class="chart-container">
            <h2>Reports by Urgency</h2>
            <canvas id="urgencyChart"></canvas>
        </div>
    </div>
    <div class="chart-container">
        <h2>Active Assignments per Staff</h2>
        <canvas id="staffWorkloadChart"></canvas>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const chartColors = {
        blue: 'rgba(54, 162, 235, 0.6)',
        yellow: 'rgba(255, 206, 86, 0.6)',
        red: 'rgba(255, 99, 132, 0.6)',
        green: 'rgba(75, 192, 192, 0.6)',
        purple: 'rgba(153, 102, 255, 0.6)',
        orange: 'rgba(255, 159, 64, 0.6)'
    };

    // 1. Category Chart (Bar)
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    new Chart(categoryCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($categoryLabels); ?>,
            datasets: [{
                label: 'Number of Reports',
                data: <?php echo json_encode($categoryData); ?>,
                backgroundColor: chartColors.blue,
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 } }
            },
            plugins: { legend: { display: false } }
        }
    });

    // 2. Urgency Chart (Pie)
    const urgencyCtx = document.getElementById('urgencyChart').getContext('2d');
    new Chart(urgencyCtx, {
        type: 'pie',
        data: {
            labels: <?php echo json_encode($urgencyLabels); ?>,
            datasets: [{
                data: <?php echo json_encode($urgencyData); ?>,
                backgroundColor: [chartColors.red, chartColors.yellow, chartColors.green],
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'top' } }
        }
    });

    // 3. Staff Workload Chart (Horizontal Bar)
    const staffCtx = document.getElementById('staffWorkloadChart').getContext('2d');
    new Chart(staffCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($staffLabels); ?>,
            datasets: [{
                label: 'Active Assigned Reports',
                data: <?php echo json_encode($staffData); ?>,
                backgroundColor: chartColors.purple,
                borderColor: 'rgba(153, 102, 255, 1)',
                borderWidth: 1
            }]
        },
        options: {
            indexAxis: 'y', // This makes the bar chart horizontal
            responsive: true,
            scales: {
                x: { beginAtZero: true, ticks: { precision: 0 } }
            },
            plugins: { legend: { display: false } }
        }
    });
});
</script>

</body>
</html>
