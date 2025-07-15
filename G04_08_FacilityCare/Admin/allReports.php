<?php
require_once '../backend/process_allReports.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['archive']) && is_numeric($_GET['archive']) && isset($_SESSION['user_id'])) {
    $archiveId = (int)$_GET['archive'];

    // Check if already archived
    $stmt = $pdo->prepare("SELECT archive FROM report WHERE report_id = ?");
    $stmt->execute([$archiveId]);
    $isArchived = $stmt->fetchColumn();

    if (!$isArchived) {
        // Only update the archive column in report table
        $update = $pdo->prepare("UPDATE report SET archive = 1 WHERE report_id = ?");
        $update->execute([$archiveId]);
    }

    // Redirect to remove archive param
    header("Location: allReports.php");
    exit();
}

if (isset($_GET['archive'])) {
    require_once '../connection.php';
    $reportId = $_GET['archive'];

    $stmt = $pdo->prepare("UPDATE report SET archive = 1 WHERE report_id = ?");
    $stmt->execute([$reportId]);

    // Optional: Log
    $logStmt = $pdo->prepare("
        INSERT INTO statuslog (report_id, status, notes, timestamp, changed_by)
        VALUES (?, 'archive', 'Archived by user', NOW(), ?)
    ");
    $logStmt->execute([$reportId, $_SESSION['user_id'] ?? 'system']);

    header("Location: process_archived.php?success=1");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Reports - Admin Panel</title>
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
                    <h1 class="h2">All Maintenance Reports</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <!-- <button class="btn btn-sm btn-outline-secondary me-2">
                            <i class="bi bi-download me-1"></i> Export
                        </button> -->
                        <!-- <button class="btn btn-sm btn-primary">
                            <i class="bi bi-plus-lg me-1"></i> New Report
                        </button> -->
                    </div>
                </div>

                <!-- Filters Card -->
                <div class="card filter-card mb-4">
                    <div class="card-body">
                        <form class="row g-3" method="GET" action="">
                            <div class="col-md-3">
                                <label for="statusFilter" class="form-label">Status</label>
                                <select id="statusFilter" name="status" class="form-select">
                                    <option value="">All Statuses</option>
                                    <option value="pending" <?= (isset($_GET['status']) && $_GET['status'] == 'pending') ? 'selected' : '' ?>>Pending</option>
                                    <option value="open" <?= (isset($_GET['status']) && $_GET['status'] == 'open') ? 'selected' : '' ?>>Open</option>
                                    <option value="in_progress" <?= (isset($_GET['status']) && $_GET['status'] == 'in_progress') ? 'selected' : '' ?>>In Progress</option>
                                    <option value="resolved" <?= (isset($_GET['status']) && $_GET['status'] == 'resolved') ? 'selected' : '' ?>>Resolved</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="categoryFilter" class="form-label">Category</label>
                                <select id="categoryFilter" name="category" class="form-select">
                                    <option value="">All Categories</option>
                                    <option value="Plumbing" <?= (isset($_GET['category']) && $_GET['category'] == 'Plumbing') ? 'selected' : '' ?>>Plumbing</option>
                                    <option value="Electrical" <?= (isset($_GET['category']) && $_GET['category'] == 'Electrical') ? 'selected' : '' ?>>Electrical</option>
                                    <option value="HVAC" <?= (isset($_GET['category']) && $_GET['category'] == 'HVAC') ? 'selected' : '' ?>>HVAC</option>
                                    <option value="Structural" <?= (isset($_GET['category']) && $_GET['category'] == 'Structural') ? 'selected' : '' ?>>Structural</option>
                                    <option value="Cleaning" <?= (isset($_GET['category']) && $_GET['category'] == 'Cleaning') ? 'selected' : '' ?>>Cleaning</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="priorityFilter" class="form-label">Priority</label>
                                <select id="priorityFilter" name="priority" class="form-select">
                                    <option value="">All Priority Levels</option>
                                    <option value="High" <?= (isset($_GET['priority']) && $_GET['priority'] == 'High') ? 'selected' : '' ?>>High</option>
                                    <option value="Medium" <?= (isset($_GET['priority']) && $_GET['priority'] == 'Medium') ? 'selected' : '' ?>>Medium</option>
                                    <option value="Low" <?= (isset($_GET['priority']) && $_GET['priority'] == 'Low') ? 'selected' : '' ?>>Low</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="dateFilter" class="form-label">Date Range</label>
                                <select id="dateFilter" name="date" class="form-select" onchange="toggleCustomDateFields()">
                                    <option value="">All Time</option>
                                    <option value="today" <?= (isset($_GET['date']) && $_GET['date'] == 'today') ? 'selected' : '' ?>>Today</option>
                                    <option value="this_week" <?= (isset($_GET['date']) && $_GET['date'] == 'this_week') ? 'selected' : '' ?>>This Week</option>
                                    <option value="this_month" <?= (isset($_GET['date']) && $_GET['date'] == 'this_month') ? 'selected' : '' ?>>This Month</option>
                                    <option value="custom" <?= (isset($_GET['date']) && $_GET['date'] == 'custom') ? 'selected' : '' ?>>Custom Range</option>
                                </select>

                                <div class="row mt-2" id="customDateFields" style="display: none;">
                                    <div class="col-md-6">
                                        <label for="startDate" class="form-label">Start Date</label>
                                        <input type="date" id="startDate" name="start_date" class="form-control"
                                            value="<?= htmlspecialchars($_GET['start_date'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="endDate" class="form-label">End Date</label>
                                        <input type="date" id="endDate" name="end_date" class="form-control"
                                            value="<?= htmlspecialchars($_GET['end_date'] ?? '') ?>">
                                    </div>
                                </div>

                            </div>
                            <div class="col-12">
                                <div class="d-flex justify-content-end">
                                    <a href="allReports.php" class="btn btn-outline-secondary">
                                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                                    </a>
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="bi bi-funnel me-1"></i> Apply Filters
                                    </button>

                                </div>
                            </div>
                        </form>

                    </div>
                </div>

                <!-- Reports Table -->
                <div class="card admin-card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Reports List</h5>
                        <div class="d-flex">
                            <form method="GET" action="">
                                <div class="input-group me-2" style="width: 250px;">
                                    <input type="text" class="form-control" name="search" placeholder="Search reports..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                                    <button class="btn btn-outline-secondary" type="submit">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </form>
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-list-check me-1"></i> Columns
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <h6 class="dropdown-header">Show/Hide Columns</h6>
                                    </li>
                                    <li><a class="dropdown-item" href="#"><i class="bi bi-check2 me-2"></i>ID</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="bi bi-check2 me-2"></i>Title</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="bi bi-check2 me-2"></i>Category</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="bi bi-check2 me-2"></i>Priority</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="bi bi-check2 me-2"></i>Status</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="bi bi-check2 me-2"></i>Reported By</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="bi bi-check2 me-2"></i>Date</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="bi bi-check2 me-2"></i>Actions</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th width="80">ID <i class="bi bi-arrow-down-up ms-1"></i></th>
                                        <th>Title <i class="bi bi-arrow-down-up ms-1"></i></th>
                                        <th width="120">Category <i class="bi bi-arrow-down-up ms-1"></i></th>
                                        <th width="100">Priority <i class="bi bi-arrow-down-up ms-1"></i></th>
                                        <th width="120">Status <i class="bi bi-arrow-down-up ms-1"></i></th>
                                        <th width="150">Reported By <i class="bi bi-arrow-down-up ms-1"></i></th>
                                        <th width="120">Date <i class="bi bi-arrow-down-up ms-1"></i></th>
                                        <th width="150">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reports as $report): ?>
                                        <?php
                                        $statusClass = match (strtolower($report['status'])) {
                                            'in_progress' => 'progress',
                                            'resolved'    => 'resolved',
                                            'open'        => 'open',
                                            default       => 'open'
                                        };
                                        ?>
                                        <tr class="report-row">
                                            <td class="fw-bold">#<?= htmlspecialchars($report['report_id']) ?></td>
                                            <td><?= htmlspecialchars($report['title']) ?></td>
                                            <td><?= htmlspecialchars($report['category']) ?></td>
                                            <td>
                                                <span class="badge urgency-<?= strtolower($report['priority']) ?>">
                                                    <?= htmlspecialchars($report['priority']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="status-badge status-<?= $statusClass ?>">
                                                    <?= ucwords(str_replace('_', ' ', strtolower($report['status']))) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="https://via.placeholder.com/30" class="rounded-circle me-2" width="30" height="30">
                                                    <span><?= htmlspecialchars($report['reported_by']) ?></span>
                                                </div>
                                            </td>
                                            <td><?= date('d M Y', strtotime($report['created_at'])) ?></td>
                                            <td>
                                                <a href="viewReport.php?id=<?= $report['report_id'] ?>&open=1" class="btn btn-sm btn-outline-primary me-1" title="View">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="editReports.php?id=<?= $report['report_id'] ?>" class="btn btn-sm btn-outline-success me-1" title="Assign">
                                                    <i class="bi bi-person-plus"></i>
                                                </a>
                                                <button type="button"
                                                    class="btn btn-sm btn-outline-secondary"
                                                    title="Archive"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#confirmArchiveModal"
                                                    data-report-id="<?= $report['report_id'] ?>">
                                                    <i class="bi bi-archive"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>

                            </table>
                        </div>

                        <!-- Pagination -->
                        <nav aria-label="Reports pagination" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">Previous</a>
                                </li>

                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>

                                <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">Next</a>
                                </li>
                            </ul>
                        </nav>

                    </div>
                </div>
            </main>
        </div>
    </div>
    <div class="modal fade" id="confirmArchiveModal" tabindex="-1" aria-labelledby="confirmArchiveLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header" style="background-color: #95AEF1;">
                    <h5 class="modal-title text-white" id="confirmArchiveLabel">Archive Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <p class="mb-2">Are you sure you want to archive this report?</p>
                    <small class="text-muted">This will move the report out of the active list, but you can always view it later in the archive section.</small>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="" id="confirmArchiveBtn" class="btn text-white" style="background-color: #95AEF1;">Yes, Archive</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleCustomDateFields() {
            const dateFilter = document.getElementById('dateFilter');
            const customDateDiv = document.getElementById('customDateRange');
            if (dateFilter.value === 'custom') {
                customDateDiv.style.display = 'block';
            } else {
                customDateDiv.style.display = 'none';
            }
        }
        window.onload = toggleCustomDateFields;

        document.addEventListener('DOMContentLoaded', function() {
            var confirmModal = document.getElementById('confirmArchiveModal');
            var confirmBtn = document.getElementById('confirmArchiveBtn');

            confirmModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                var reportId = button.getAttribute('data-report-id');
                confirmBtn.href = 'allReports.php?archive=' + reportId;
            });
        });
    </script>

    <script>
        function toggleCustomDateFields() {
            const dateFilter = document.getElementById("dateFilter").value;
            const customDateFields = document.getElementById("customDateFields");

            if (dateFilter === "custom") {
                customDateFields.style.display = "flex";
            } else {
                customDateFields.style.display = "none";
            }
        }

        // Run this on page load (in case "custom" is pre-selected)
        document.addEventListener("DOMContentLoaded", toggleCustomDateFields);
    </script>

</body>

</html>