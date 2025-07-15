<?php 
require_once '../backend/process_archived.php'; 

// Check if admin is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archived Reports - Admin Panel</title>
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
                    <h1 class="h2">Archived Reports</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <!-- <button type="button" class="btn btn-sm btn-outline-secondary me-2">
                            <i class="bi bi-download me-1"></i> Export
                        </button> -->
                        <button type="button" class="btn btn-sm btn-danger me-2">
                            <i class="bi bi-trash me-1"></i> Empty Archive
                        </button>
                    </div>
                </div>

                <!-- Filters Card -->
                <div class="card filter-card mb-4">
                    <div class="card-body">
                        <form class="row g-3" method="GET" action="">
                            <div class="col-md-4">
                                <label for="categoryFilter" class="form-label">Category</label>
                                <select id="categoryFilter" name="category" class="form-select">
                                    <option value="">All Categories</option>
                                    <option <?= isset($_GET['category']) && $_GET['category'] == 'Plumbing' ? 'selected' : '' ?>>Plumbing</option>
                                    <option <?= isset($_GET['category']) && $_GET['category'] == 'Electrical' ? 'selected' : '' ?>>Electrical</option>
                                    <option <?= isset($_GET['category']) && $_GET['category'] == 'HVAC' ? 'selected' : '' ?>>HVAC</option>
                                    <option <?= isset($_GET['category']) && $_GET['category'] == 'Structural' ? 'selected' : '' ?>>Structural</option>
                                    <option <?= isset($_GET['category']) && $_GET['category'] == 'Cleaning' ? 'selected' : '' ?>>Cleaning</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="priorityFilter" class="form-label">Priority</label>
                                <select id="priorityFilter" name="priority" class="form-select">
                                    <option value="">All Priority Levels</option>
                                    <option <?= isset($_GET['priority']) && $_GET['priority'] == 'High' ? 'selected' : '' ?>>High</option>
                                    <option <?= isset($_GET['priority']) && $_GET['priority'] == 'Medium' ? 'selected' : '' ?>>Medium</option>
                                    <option <?= isset($_GET['priority']) && $_GET['priority'] == 'Low' ? 'selected' : '' ?>>Low</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="dateFilter" class="form-label">Archived Date</label>
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
                                    <a href="archived.php" class="btn btn-outline-secondary me-2">
                                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-funnel me-1"></i> Apply Filters
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Archived Reports Table -->
                <div class="card admin-card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Archived Reports</h5>
                        <div class="d-flex">
                            <div class="input-group me-2" style="width: 250px;">
                                <input type="text" class="form-control" placeholder="Search archives...">
                                <button class="btn btn-outline-secondary" type="button">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
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
                                        <th width="150">Reported By <i class="bi bi-arrow-down-up ms-1"></i></th>
                                        <th width="120">Date <i class="bi bi-arrow-down-up ms-1"></i></th>
                                        <th width="120">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($archivedReports)): ?>
                                        <?php foreach ($archivedReports as $report): ?>
                                            <tr class="report-row">
                                                <td class="fw-bold">#<?= htmlspecialchars($report['report_id']) ?></td>
                                                <td><?= htmlspecialchars($report['title']) ?></td>
                                                <td><?= htmlspecialchars($report['category']) ?></td>
                                                <td><span class="badge urgency-<?= strtolower($report['priority']) ?>"><?= htmlspecialchars($report['priority']) ?></span></td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <img src="https://via.placeholder.com/30" class="rounded-circle me-2" width="30" height="30">
                                                        <span><?= htmlspecialchars($report['reported_by']) ?></span>
                                                    </div>
                                                </td>
                                                <td><?= date('d M Y', strtotime($report['created_at'])) ?></td>
                                                <td>
                                                    <a href="viewReport.php?id=<?= $report['report_id'] ?>" class="btn btn-sm btn-outline-primary me-1" title="View">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="../backend/process_unarchived.php?id=<?= $report['report_id'] ?>" class="btn btn-sm btn-outline-success me-1" title="Restore">
                                                        <i class="bi bi-box-arrow-up"></i>
                                                    </a>
                                                    <a href="deleteReport.php?id=<?= $report['report_id'] ?>" class="btn btn-sm btn-outline-danger me-1" title="Delete">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="9" class="text-center text-muted">No archived reports found.</td>
                                        </tr>
                                    <?php endif; ?>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

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