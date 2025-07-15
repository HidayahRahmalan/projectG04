<?php
session_name("technician_session");
session_start();
require_once '../connection.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['technician_id'])) {
    header("Location: ../login.php");
    exit();
}

$technicianId = $_SESSION['technician_id'];

// Get filters from GET
$category = $_GET['category'] ?? '';
$priority = $_GET['priority'] ?? '';
$search = $_GET['search'] ?? '';

// ✅ Pagination setup
$limit = 5;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// ✅ Count total in_progress tasks
$countQuery = "
    SELECT COUNT(*) 
    FROM report r 
    WHERE r.technician_id = ?
    AND (
        SELECT status FROM statuslog 
        WHERE report_id = r.report_id 
        ORDER BY timestamp DESC 
        LIMIT 1
    ) = 'in_progress'
";
$countParams = [$technicianId];

if (!empty($category)) {
    $countQuery .= " AND r.category = ?";
    $countParams[] = $category;
}
if (!empty($priority)) {
    $countQuery .= " AND r.priority = ?";
    $countParams[] = $priority;
}
if (!empty($search)) {
    $countQuery .= " AND (r.title LIKE ? OR r.description LIKE ?)";
    $countParams[] = "%$search%";
    $countParams[] = "%$search%";
}

$countStmt = $pdo->prepare($countQuery);
$countStmt->execute($countParams);
$totalTasks = $countStmt->fetchColumn();
$totalPages = ceil($totalTasks / $limit);

// ✅ Main query - only in_progress tasks
$query = "
    SELECT r.*, 
           (
               SELECT status FROM statuslog 
               WHERE report_id = r.report_id 
               ORDER BY timestamp DESC 
               LIMIT 1
           ) as current_status,
           (
               SELECT timestamp FROM statuslog 
               WHERE report_id = r.report_id 
               AND status IN ('assigned','in_progress') 
               ORDER BY timestamp ASC 
               LIMIT 1
           ) as assigned_time
    FROM report r
    WHERE r.technician_id = ?
    AND (
        SELECT status FROM statuslog 
        WHERE report_id = r.report_id 
        ORDER BY timestamp DESC 
        LIMIT 1
    ) = 'in_progress'
";

$params = [$technicianId];

if (!empty($category)) {
    $query .= " AND r.category = ?";
    $params[] = $category;
}
if (!empty($priority)) {
    $query .= " AND r.priority = ?";
    $params[] = $priority;
}
if (!empty($search)) {
    $query .= " AND (r.title LIKE ? OR r.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$query .= " ORDER BY r.created_at DESC LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$tasks = $stmt->fetchAll();

// ✅ Get categories from speciality table
$specialityStmt = $pdo->query("SELECT * FROM speciality");
$specialities = $specialityStmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Assignments - Technician Panel</title>
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
                    <h1 class="h2">My Assignments</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <!-- <div class="btn-group me-2">
                            <button class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-sync-alt me-1"></i> Refresh
                            </button>
                            <button class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-download me-1"></i> Export
                            </button>
                        </div> -->
                    </div>
                </div>

                <!-- Filter Form -->
                <form method="GET" class="card tech-card mb-4">
                    <div class="card-body">
                        <div class="d-flex flex-wrap align-items-center">
                            <!-- Category -->
                            <div class="me-3 mb-2">
                                <select class="form-select form-select-sm" name="category" onchange="this.form.submit()">
                                    <option value="">All Categories</option>
                                    <?php foreach ($specialities as $speciality): ?>
                                        <option value="<?= $speciality['speciality_name'] ?>" <?= $category == $speciality['speciality_name'] ? 'selected' : '' ?>>
                                            <?= $speciality['speciality_name'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Priority -->
                            <div class="me-3 mb-2">
                                <select class="form-select form-select-sm" name="priority" onchange="this.form.submit()">
                                    <option value="">All Priorities</option>
                                    <option value="high" <?= $priority == 'high' ? 'selected' : '' ?>>High</option>
                                    <option value="medium" <?= $priority == 'medium' ? 'selected' : '' ?>>Medium</option>
                                    <option value="low" <?= $priority == 'low' ? 'selected' : '' ?>>Low</option>
                                </select>
                            </div>

                            <!-- Search -->
                            <div class="mb-2">
                                <div class="input-group input-group-sm">
                                    <input type="text" name="search" class="form-control" value="<?= htmlspecialchars($search) ?>" placeholder="Search assignments...">
                                    <button class="btn btn-outline-secondary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Assignments List -->
                <div class="card tech-card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Current Assignments</h5>
                            <small class="text-muted">Showing <?= count($tasks) ?> assignments</small>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <?php if (count($tasks) > 0): ?>
                                <?php foreach ($tasks as $task): ?>
                                    <?php
                                    $status = $task['current_status'] ?? 'new';

                                    $priority = strtolower($task['priority']);
                                    $statusBadge = match ($status) {
                                        'completed' => 'badge-completed',
                                        'in_progress' => 'badge-inprogress',
                                        'overdue' => 'badge-overdue',
                                        'assigned' => 'badge-assigned',
                                        default => 'badge-assigned'
                                    };
                                    $priorityClass = match ($priority) {
                                        'high' => 'priority-high',
                                        'medium' => 'priority-medium',
                                        'low' => 'priority-low',
                                        default => ''
                                    };
                                    $icon = match ($status) {
                                        'completed' => '<i class="fas fa-check-circle text-success me-2"></i>',
                                        'in_progress' => '<i class="fas fa-tools text-primary me-2"></i>',
                                        'overdue' => '<i class="fas fa-exclamation-triangle text-danger me-2"></i>',
                                        default => '<i class="fas fa-bell text-warning me-2"></i>'
                                    };
                                    $assignedDate = $task['assigned_time']
                                        ? date("F j, Y, g:i A", strtotime($task['assigned_time']))
                                        : 'N/A';
                                    ?>
                                    <a href="taskDetail.php?report_id=<?= $task['report_id'] ?>" class="list-group-item list-group-item-action assignment-card <?= $priorityClass ?>">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1"><?= $icon ?><?= htmlspecialchars($task['title']) ?></h6>
                                            <span class="badge badge-tech <?= $statusBadge ?>"><?= ucfirst(str_replace('_', ' ', $status)) ?></span>
                                        </div>
                                        <div class="d-flex flex-wrap mt-2">
                                            <div class="me-3 mb-1">
                                                <small class="text-muted">Location:</small>
                                                <p class="mb-0"><?= htmlspecialchars($task['facilities']) ?></p>
                                            </div>
                                            <div class="me-3 mb-1">
                                                <small class="text-muted">Category:</small>
                                                <p class="mb-0"><?= htmlspecialchars($task['category']) ?></p>
                                            </div>
                                            <div class="me-3 mb-1">
                                                <small class="text-muted">Assigned:</small>
                                                <p class="mb-0"><?= $assignedDate ?></p>
                                            </div>
                                            <div class="me-3 mb-1">
                                                <small class="text-muted">Priority:</small>
                                                <p class="mb-0 text-<?= $priority === 'high' ? 'danger' : ($priority === 'medium' ? 'warning' : 'success') ?>">
                                                    <?= ucfirst($priority) ?>
                                                </p>
                                            </div>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center text-muted">No assignments found based on current filters.</div>
                            <?php endif; ?>
                        </div>

                        <!-- Pagination -->
                        <?php if ($totalPages > 1): ?>
                            <nav aria-label="Assignments pagination" class="mt-4">
                                <ul class="pagination justify-content-center">
                                    <!-- Previous -->
                                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">Previous</a>
                                    </li>

                                    <!-- Page numbers -->
                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>

                                    <!-- Next -->
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Filter button functionality
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                // Here you would add code to filter the assignments
            });
        });
    </script>
</body>

</html>