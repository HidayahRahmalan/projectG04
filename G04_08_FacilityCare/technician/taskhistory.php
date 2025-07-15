<?php
session_name("technician_session");
session_start();
require_once '../connection.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['technician_id'])) {
    header("Location: ../login.php");
    exit();
}

$technicianId = $_SESSION['technician_id'];

// Filters
$statusFilter = $_GET['status'] ?? '';
$categoryFilter = $_GET['category'] ?? '';
$dateRange = $_GET['date'] ?? '';

// Pagination
$limit = 5;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Main query
$query = "
    SELECT r.*, s.status, s.timestamp,
        (
            SELECT timestamp FROM statuslog 
            WHERE report_id = r.report_id AND status = 'in_progress' 
            ORDER BY timestamp ASC LIMIT 1
        ) AS start_time
    FROM report r
    JOIN (
        SELECT report_id, MAX(timestamp) AS latest_time
        FROM statuslog
        GROUP BY report_id
    ) latest ON r.report_id = latest.report_id
    JOIN statuslog s ON s.report_id = latest.report_id AND s.timestamp = latest.latest_time
    WHERE r.technician_id = ? AND s.status = 'resolved'
";
$params = [$technicianId];

// Apply filters
if (!empty($categoryFilter)) {
    $query .= " AND r.category = ?";
    $params[] = $categoryFilter;
}

// if ($statusFilter === 'Completed') {
//     $query .= " AND s.status = 'resolved'";
// } elseif ($statusFilter === 'Cancelled') {
//     $query .= " AND s.status = 'cancelled'";
// }

if ($dateRange === 'This Week') {
    $query .= " AND s.timestamp >= DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY)";
} elseif ($dateRange === 'This Month') {
    $query .= " AND MONTH(s.timestamp) = MONTH(CURDATE()) AND YEAR(s.timestamp) = YEAR(CURDATE())";
} elseif ($dateRange === 'Last Month') {
    $query .= " AND MONTH(s.timestamp) = MONTH(CURDATE() - INTERVAL 1 MONTH) AND YEAR(s.timestamp) = YEAR(CURDATE() - INTERVAL 1 MONTH)";
} elseif ($dateRange === 'Last 30 Days') {
    $query .= " AND s.timestamp >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
}

$query .= " ORDER BY s.timestamp DESC LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$resolvedTasks = $stmt->fetchAll();

// Count total for pagination
$countQuery = "
    SELECT COUNT(*) FROM report r
    JOIN (
        SELECT report_id, MAX(timestamp) AS latest_time
        FROM statuslog
        GROUP BY report_id
    ) latest ON r.report_id = latest.report_id
    JOIN statuslog s ON s.report_id = latest.report_id AND s.timestamp = latest.latest_time
    WHERE r.technician_id = ? AND s.status = 'resolved'
";
$countParams = [$technicianId];

if (!empty($categoryFilter)) {
    $countQuery .= " AND r.category = ?";
    $countParams[] = $categoryFilter;
}
if ($statusFilter === 'Completed') {
    $countQuery .= " AND s.status = 'resolved'";
} elseif ($statusFilter === 'Cancelled') {
    $countQuery .= " AND s.status = 'cancelled'";
}
if ($dateRange === 'This Week') {
    $countQuery .= " AND s.timestamp >= DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY)";
} elseif ($dateRange === 'This Month') {
    $countQuery .= " AND MONTH(s.timestamp) = MONTH(CURDATE()) AND YEAR(s.timestamp) = YEAR(CURDATE())";
} elseif ($dateRange === 'Last Month') {
    $countQuery .= " AND MONTH(s.timestamp) = MONTH(CURDATE() - INTERVAL 1 MONTH) AND YEAR(s.timestamp) = YEAR(CURDATE() - INTERVAL 1 MONTH)";
} elseif ($dateRange === 'Last 30 Days') {
    $countQuery .= " AND s.timestamp >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
}

$countStmt = $pdo->prepare($countQuery);
$countStmt->execute($countParams);
$totalTasks = $countStmt->fetchColumn();
$totalPages = ceil($totalTasks / $limit);

// Get category list from DB
$specialityStmt = $pdo->query("SELECT speciality_name FROM speciality");
$categories = $specialityStmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Work History - Technician Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
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
                            <a class="nav-link active" href="taskhistory.php">
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
                    <h1 class="h2">Work History</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form class="row g-3" method="GET">
                            <div class="col-md-3">
                                <label class="form-label">Date Range</label>
                                <select class="form-select" name="date">
                                    <option value="Last 30 Days" <?= $dateRange === 'Last 30 Days' ? 'selected' : '' ?>>Last 30 Days</option>
                                    <option value="This Week" <?= $dateRange === 'This Week' ? 'selected' : '' ?>>This Week</option>
                                    <option value="This Month" <?= $dateRange === 'This Month' ? 'selected' : '' ?>>This Month</option>
                                    <option value="Last Month" <?= $dateRange === 'Last Month' ? 'selected' : '' ?>>Last Month</option>
                                </select>
                            </div>
                            <!-- <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status">
                                    <option value="" <?= $statusFilter === '' ? 'selected' : '' ?>>All Statuses</option>
                                    <option value="Completed" <?= $statusFilter === 'Completed' ? 'selected' : '' ?>>Completed</option>
                                    <option value="Cancelled" <?= $statusFilter === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                </select>
                            </div> -->
                            <div class="col-md-3">
                                <label class="form-label">Category</label>
                                <select class="form-select" name="category">
                                    <option value="">All Categories</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= htmlspecialchars($cat) ?>" <?= $categoryFilter === $cat ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <div class="d-flex justify-content-end">
                                    <a href="taskhistory.php" class="btn btn-outline-secondary me-2">
                                        <i class="fas fa-undo me-1"></i> Reset
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-filter me-1"></i> Apply Filters
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Work History -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Completed Work Orders</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <?php foreach ($resolvedTasks as $task): ?>
                                <?php
                                // Duration calc
                                $duration = 'N/A';
                                if ($task['start_time']) {
                                    $start = new DateTime($task['start_time']);
                                    $end = new DateTime($task['timestamp']);
                                    $diff = $start->diff($end);
                                    $duration = ($diff->d * 24 + $diff->h) . 'h ' . $diff->i . 'm';
                                }
                                ?>
                                <a href="taskDetail.php?report_id=<?= $task['report_id'] ?>" class="list-group-item list-group-item-action history-card mb-3">
                                    <div class="d-flex w-100 justify-content-between">
                                        <div>
                                            <h6 class="mb-1"><?= htmlspecialchars($task['title']) ?></h6>
                                            <div class="d-flex align-items-center mt-2">
                                                <span class="badge bg-primary me-2"><?= htmlspecialchars($task['category']) ?></span>
                                                <span class="badge bg-success me-2">Resolved</span>
                                                <span class="badge bg-secondary me-2">
                                                    <i class="fas fa-clock me-1"></i> <?= $duration ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <small class="text-muted">Resolved: <?= date('F j, Y, g:i A', strtotime($task['timestamp'])) ?></small>
                                        </div>
                                    </div>
                                    <div class="d-flex mt-3">
                                        <div class="me-3">
                                            <small class="text-muted">Location:</small>
                                            <p class="mb-0"><?= htmlspecialchars($task['facilities']) ?></p>
                                        </div>
                                        <!-- <div class="ms-auto">
                                            <img src="https://via.placeholder.com/300x200?text=<?= urlencode($task['title']) ?>" class="media-preview" data-bs-toggle="modal" data-bs-target="#mediaModal">
                                        </div> -->
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>

                        <!-- Pagination -->
                        <?php if ($totalPages > 1): ?>
                            <nav class="mt-4">
                                <ul class="pagination justify-content-center">
                                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">Previous</a>
                                    </li>
                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">Next</a>
                                    </li>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Media Modal -->
    <div class="modal fade" id="mediaModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Work Documentation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalMedia" src="" class="img-fluid" alt="Work documentation" style="max-height: 70vh;">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">
                        <i class="fas fa-download me-1"></i> Download
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Media modal functionality
        const mediaModal = document.getElementById('mediaModal');
        if (mediaModal) {
            mediaModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const imgSrc = button.getAttribute('src');
                document.getElementById('modalMedia').src = imgSrc;
            });
        }
    </script>
</body>

</html>