<?php
session_name("admin_session");
session_start();
require_once '../connection.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Count total reports
$totalReportsStmt = $pdo->query("SELECT COUNT(*) FROM Report");
$totalReports = $totalReportsStmt->fetchColumn();

// Count reports with no status (Pending/Open)
$openReportsStmt = $pdo->query("
   SELECT COUNT(*) FROM Report r
    WHERE (
        SELECT status FROM StatusLog s
        WHERE s.report_id = r.report_id
        ORDER BY timestamp DESC LIMIT 1
    ) ='open'
");
$openReports = $openReportsStmt->fetchColumn();

// Count reports with latest status = 'inprogress'
$inProgressStmt = $pdo->query("
    SELECT COUNT(*) FROM Report r
    WHERE (
        SELECT status FROM StatusLog s
        WHERE s.report_id = r.report_id
        ORDER BY timestamp DESC LIMIT 1
    ) = 'in_progress'
");
$inProgress = $inProgressStmt->fetchColumn();

// Count reports with latest status = 'resolved'
$resolvedStmt = $pdo->query("
    SELECT COUNT(*) FROM Report r
    WHERE (
        SELECT status FROM StatusLog s
        WHERE s.report_id = r.report_id
        ORDER BY timestamp DESC LIMIT 1
    ) = 'resolved'
");
$resolved = $resolvedStmt->fetchColumn();

// Count reports with no status at all = 'pending'
$pendingStmt = $pdo->query("
    SELECT COUNT(*) FROM Report r
    WHERE NOT EXISTS (
        SELECT 1 FROM StatusLog s
        WHERE s.report_id = r.report_id
    )
");
$pending = $pendingStmt->fetchColumn();

// Fetch recent reports with their latest status
$reportsStmt = $pdo->query("
 SELECT 
        r.report_id, 
        r.title, 
        r.category, 
        r.priority, 
        u.name AS reported_by, 
        DATE_FORMAT(r.created_at, '%d-%m-%Y') AS created_date,
        (
            SELECT status 
            FROM StatusLog s
            WHERE s.report_id = r.report_id
            ORDER BY timestamp DESC LIMIT 1
        ) AS status
    FROM Report r
    JOIN User u ON r.user_id = u.user_id
    ORDER BY r.created_at DESC
    LIMIT 10
");
$reports = $reportsStmt->fetchAll();

$categoryCounts = $pdo->query("
    SELECT category, COUNT(*) as total 
    FROM report 
    GROUP BY category
")->fetchAll(PDO::FETCH_ASSOC);

$categories = [];
$totals = [];

foreach ($categoryCounts as $row) {
    $categories[] = $row['category'];
    $totals[] = $row['total'];
}

$resolutionTimeQuery = $pdo->query("
    SELECT r.category, 
           AVG(TIMESTAMPDIFF(HOUR, r.created_at, sl.timestamp)) AS avg_hours
    FROM report r
    JOIN statuslog sl ON r.report_id = sl.report_id
    WHERE sl.status = 'resolved'
    GROUP BY r.category
");

$resolutionData = $resolutionTimeQuery->fetchAll(PDO::FETCH_ASSOC);

$resolutionLabels = [];
$resolutionHours = [];

foreach ($resolutionData as $row) {
    $resolutionLabels[] = $row['category'];
    $resolutionHours[] = round($row['avg_hours'], 2); // 2 decimal places
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../CSS/styleAdmin.css">
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Admin Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block admin-sidebar p-0">
                <div class="d-flex flex-column p-3">
                    <div class="text-center mb-4">
                        <h4 class="text-white mb-0">
                            <i class="fas fa-tools me-2"></i>FacilityCare
                        </h4>
                        <small class="text-white-50">Admin Panel</small>
                    </div>
                    <hr class="text-white-50">
                    <ul class="nav nav-pills flex-column mb-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="allReports.php">
                                <i class="bi bi-list-check"></i> All Reports
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="archived.php">
                                <i class="bi bi-archive"></i> Archived
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="allTechnician.php">
                                <i class="fas fa-users me-2"></i> Technician Management
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="allStaff.php">
                                <i class="fas fa-user-tie me-2"></i> Staff Management
                            </a>
                        </li>
                    </ul>
                    <hr class="text-white-50">
                    <div class="dropdown">
                        <a href="#" class="d-flex justify-content-center align-items-center text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                            <strong> <?php echo isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : 'Staff User'; ?></strong>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
                            <li><a class="dropdown-item" href="adminProfile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="../logout.php"><i class="bi bi-box-arrow-left me-2"></i>Sign out</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Admin Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <!-- <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary">Export</button>
                        </div> -->
                        <!-- <button type="button" class="btn btn-sm btn-primary">
                            <i class="bi bi-funnel me-1"></i> Filters
                        </button> -->
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row mb-3">
                    <div class="col-md-3 mb-3">
                        <div class="card admin-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="text-muted mb-2">Total Reports</h6>
                                        <h3 class="mb-0"><?= $totalReports ?></h3>
                                    </div>
                                    <div class="text-primary">
                                        <i class="bi bi-clipboard2-pulse"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card admin-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="text-muted mb-2">Pending</h6>
                                        <h3 class="mb-0"><?= $pending ?></h3>
                                    </div>
                                    <div class="text-secondary">
                                        <i class="bi bi-clock-history"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card admin-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="text-muted mb-2">Open</h6>
                                        <h3 class="mb-0"><?= $openReports ?></h3>
                                    </div>
                                    <div class="text-danger">
                                        <i class="bi bi-exclamation-triangle"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card admin-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="text-muted mb-2">In Progress</h6>
                                        <h3 class="mb-0"><?= $inProgress ?></h3>
                                    </div>
                                    <div class="text-warning">
                                        <i class="bi bi-hourglass-split"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card admin-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="text-muted mb-2">Resolved</h6>
                                        <h3 class="mb-0"><?= $resolved ?></h3>
                                    </div>
                                    <div class="text-success">
                                        <i class="bi bi-check-circle"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <div class="card admin-card h-100">
                            <div class="card-body">
                                <h6 class="card-title">Reports by Category</h6>
                                <div class="chart-container">
                                    <canvas id="categoryChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="card admin-card h-100">
                            <div class="card-body">
                                <h6 class="card-title">Avg Resolution Time (Hours)</h6>
                                <div class="chart-container">
                                    <canvas id="resolutionChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Reports Table -->
                <div class="card admin-card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Recent Reports</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Title</th>
                                        <th>Category</th>
                                        <th>Priority</th>
                                        <th>Status</th>
                                        <th>Reported By</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reports as $report):
                                        $status = strtolower($report['status'] ?? 'pending');

                                        // Class untuk status badge
                                        $statusClass = match ($status) {
                                            'in_progress' => 'status-progress',
                                            'resolved'    => 'status-resolved',
                                            'open'        => 'status-open',
                                            default       => 'status-open'
                                        };

                                        // Nama cantik untuk paparan
                                        $statusDisplay = ucwords(str_replace('_', ' ', $status));

                                        // Priority badge class
                                        $priorityClass = match (strtolower($report['priority'])) {
                                            'high'   => 'urgency-high',
                                            'medium' => 'urgency-medium',
                                            'low'    => 'urgency-low',
                                            default  => 'bg-secondary'
                                        };
                                    ?>
                                        <tr>
                                            <td class="fw-bold">#<?= htmlspecialchars($report['report_id']) ?></td>
                                            <td><?= htmlspecialchars($report['title']) ?></td>
                                            <td><?= htmlspecialchars($report['category']) ?></td>
                                            <td>
                                                <span class="badge <?= $priorityClass ?>">
                                                    <?= ucfirst($report['priority']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="status-badge <?= $statusClass ?>">
                                                    <?= $statusDisplay ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($report['reported_by']) ?></td>
                                            <td><?= htmlspecialchars($report['created_date']) ?></td>
                                            <td>
                                                <?php if (in_array($status, ['open', 'in_progress', 'resolved', 'archive'])): ?>
                                                    <a href="viewReport.php?id=<?= $report['report_id'] ?>" class="btn btn-sm btn-outline-secondary">View</a>
                                                <?php else: ?>
                                                    <a href="viewReport.php?id=<?= $report['report_id'] ?>&open=1" class="btn btn-sm btn-outline-primary">Open</a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-end mt-3">
                            <a href="allReports.php" class="btn btn-outline-primary">
                                View All Reports <i class="bi bi-arrow-right-circle ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        const ctx = document.getElementById('categoryChart').getContext('2d');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($categories) ?>,
                datasets: [{
                    label: 'Total Reports',
                    data: <?= json_encode($totals) ?>,
                    backgroundColor: [
                        '#4e73df',
                        '#1cc88a',
                        '#36b9cc',
                        '#f6c23e',
                        '#e74a3b',
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    </script>

    <script>
        const ctxResolution = document.getElementById('resolutionChart').getContext('2d');

        new Chart(ctxResolution, {
            type: 'bar',
            data: {
                labels: <?= json_encode($resolutionLabels) ?>,
                datasets: [{
                    label: 'Avg Resolution Time (Hours)',
                    data: <?= json_encode($resolutionHours) ?>,
                    backgroundColor: '#36b9cc',
                    borderColor: '#2c9faf',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Hours'
                        }
                    }
                }
            }
        });
    </script>

</body>
</html>