<?php
session_name("technician_session");
session_start();
require_once '../connection.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['technician_id'])) {
    header("Location: ../login.php");
    exit();
}

$technicianId = $_SESSION['technician_id'];

// Assigned Tasks
$stmt = $pdo->prepare("SELECT COUNT(*) FROM report WHERE technician_id = ?");
$stmt->execute([$technicianId]);
$assignedTasks = $stmt->fetchColumn();

// In Progress Tasks
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM report 
    INNER JOIN statuslog ON report.report_id = statuslog.report_id 
    WHERE report.technician_id = ? 
    AND statuslog.status = 'in_progress'
    AND statuslog.timestamp = (
        SELECT MAX(s2.timestamp) 
        FROM statuslog s2 
        WHERE s2.report_id = report.report_id
    )
");
$stmt->execute([$technicianId]);
$inProgress = $stmt->fetchColumn();

// Completed Today
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM report 
    INNER JOIN statuslog ON report.report_id = statuslog.report_id 
    WHERE report.technician_id = ? 
    AND statuslog.status = 'resolved'
    AND statuslog.timestamp = (
        SELECT MAX(s2.timestamp) 
        FROM statuslog s2 
        WHERE s2.report_id = report.report_id
    )
");
$stmt->execute([$technicianId]);
$completedToday = $stmt->fetchColumn();

// Get current assignments for technician
$stmt = $pdo->prepare("
    SELECT r.report_id, r.title, r.facilities, r.priority, sl.status, sl.timestamp
    FROM report r
    JOIN (
        SELECT report_id, status, timestamp
        FROM statuslog s1
        WHERE timestamp = (
            SELECT MAX(timestamp) FROM statuslog s2 WHERE s2.report_id = s1.report_id
        )
    ) sl ON r.report_id = sl.report_id
    WHERE r.technician_id = ?
    AND sl.status IN ('assigned', 'in_progress')
    ORDER BY sl.timestamp DESC
");
$stmt->execute([$technicianId]);
$assignments = $stmt->fetchAll();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Technician Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../CSS/styleT.css">
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Technician Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block tech-sidebar p-0">
                <div class="d-flex flex-column p-3">
                    <div class="text-center mb-4">
                        <h4 class="text-white mb-0">
                            <i class="fas fa-tools me-2"></i>FacilityCare
                        </h4>
                        <small class="text-white-50">Technician Panel</small>
                    </div>
                    <hr class="text-white-50">
                    <ul class="nav nav-pills flex-column mb-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboardTech.php">
                                <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="allTasks.php">
                                <i class="fas fa-tasks me-2"></i> My Assignments
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="taskhistory.php">
                                <i class="fas fa-history me-2"></i> Work History
                            </a>
                        </li>
                    </ul>
                    <hr class="text-white-50">
                    <div class="dropdown">
                        <a href="#" class="d-flex justify-content-center align-items-center text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                            <strong> <?php echo isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : 'Technician User'; ?></strong>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
                            <li><a class="dropdown-item" href="profileTech.php"><i class="bi bi-person me-2"></i>Profile</a></li>
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
                    <h1 class="h2">Technician Dashboard</h1>
                    <!-- <div class="btn-toolbar mb-2 mb-md-0">
                        <button type="button" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-sync-alt me-1"></i> Refresh
                        </button>
                    </div> -->
                </div>

                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <div class="card tech-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="text-muted mb-2">Assigned Tasks</h6>
                                        <h3 class="mb-0"><?= $assignedTasks ?></h3>
                                    </div>
                                    <div style="color: var(--tech-secondary);">
                                        <i class="fas fa-clipboard-list fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card tech-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="text-muted mb-2">In Progress</h6>
                                        <h3 class="mb-0"><?= $inProgress ?></h3>
                                    </div>
                                    <div style="color: var(--tech-primary);">
                                        <i class="fas fa-tools fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card tech-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="text-muted mb-2">Resolved</h6>
                                        <h3 class="mb-0"><?= $completedToday ?></h3>
                                    </div>
                                    <div style="color: var(--tech-accent);">
                                        <i class="fas fa-check-circle fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Current Assignments -->
                <div class="card tech-card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Current Assignments</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <?php if (empty($assignments)): ?>
                                <p class="text-muted">No current assignments.</p>
                            <?php else: ?>
                                <?php foreach ($assignments as $task): ?>
                                    <a href="taskDetail.php?report_id=<?= $task['report_id'] ?>" class="list-group-item list-group-item-action">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1"><?= htmlspecialchars($task['title']) ?></h6>
                                            <?php if ($task['status'] === 'assigned'): ?>
                                                <span class="badge badge-tech badge-assigned">Assigned</span>
                                            <?php elseif ($task['status'] === 'in_progress'): ?>
                                                <span class="badge badge-tech badge-inprogress">In Progress</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="d-flex mt-2">
                                            <div class="me-3">
                                                <small class="text-muted">Location:</small>
                                                <p class="mb-0"><?= htmlspecialchars($task['facilities']) ?></p>
                                            </div>
                                            <div class="me-3">
                                                <small class="text-muted">Priority:</small>
                                                <p class="mb-0 text-<?= $task['priority'] === 'high' ? 'danger' : ($task['priority'] === 'medium' ? 'warning' : 'success') ?>">
                                                    <?= ucfirst($task['priority']) ?>
                                                </p>
                                            </div>
                                            <div>
                                                <small class="text-muted"><?= $task['status'] === 'assigned' ? 'Assigned:' : 'Started:' ?></small>
                                                <p class="mb-0"><?= date("F j, Y, g:i A", strtotime($task['timestamp'])) ?></p>
                                            </div>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <!-- <div class="card tech-card">
                    <div class="card-header">
                        <h5 class="mb-0">Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <button class="btn btn-outline-success w-100 py-3">
                                    <i class="fas fa-check-circle me-2"></i> Complete Task
                                </button>
                            </div>
                            <div class="col-md-4">
                                <button class="btn btn-outline-warning w-100 py-3">
                                    <i class="fas fa-exclamation-triangle me-2"></i> Need Help
                                </button>
                            </div>
                            <div class="col-md-4">
                                <button class="btn btn-outline-info w-100 py-3">
                                    <i class="fas fa-notes-medical me-2"></i> Add Note
                                </button>
                            </div>
                            <div class="col-md-4">
                                <button class="btn btn-outline-secondary w-100 py-3">
                                    <i class="fas fa-camera me-2"></i> Add Photo
                                </button>
                            </div>
                        </div>
                    </div>
                </div> -->
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>