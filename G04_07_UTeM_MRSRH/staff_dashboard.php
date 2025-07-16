<?php
$page_title = "Staff Dashboard";
$css_files = ['dashboard.css'];
include 'templates/header.php';

// Check if user is staff
if ($_SESSION['role'] == 5) { header("Location: admin_dashboard.php"); exit(); }

$user_id = $_SESSION['user_id'];

$pending_stmt = $conn->prepare("SELECT COUNT(*) FROM report WHERE Status = 'pending' AND Assigned_To = ?");
$pending_stmt->bind_param("i", $user_id);
$pending_stmt->execute();
$pending_count = $pending_stmt->get_result()->fetch_row()[0];
$pending_stmt->close();

$progress_stmt = $conn->prepare("SELECT COUNT(*) FROM report WHERE Status = 'in_progress' AND Assigned_To = ?");
$progress_stmt->bind_param("i", $user_id);
$progress_stmt->execute();
$progress_count = $progress_stmt->get_result()->fetch_row()[0];
$progress_stmt->close();

$resolved_stmt = $conn->prepare("SELECT COUNT(*) FROM report WHERE Status = 'resolved' AND Assigned_To = ? AND MONTH(Updated_at) = MONTH(CURDATE()) AND YEAR(Updated_at) = YEAR(CURDATE())");
$resolved_stmt->bind_param("i", $user_id);
$resolved_stmt->execute();
$resolved_count_month = $resolved_stmt->get_result()->fetch_row()[0];
$resolved_stmt->close();
?>

<h1 class="h2 mb-4">My Dashboard</h1>
 <div class="row g-4">
    <div class="col-md-6 col-xl-4">
        <a href="my_tasks.php" class="text-decoration-none">
            <div class="card stat-card bg-c-yellow shadow-sm">
                <div class="card-body">
                    <div class="stat-info">
                        <h5 class="card-title">New Tasks Assigned</h5>
                        <p class="stat-number"><?= $pending_count ?></p>
                    </div>
                    <i class="fas fa-inbox stat-icon"></i>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-xl-4">
        <a href="my_tasks.php" class="text-decoration-none">
            <div class="card stat-card bg-c-blue shadow-sm">
                <div class="card-body">
                    <div class="stat-info">
                        <h5 class="card-title">Tasks In Progress</h5>
                        <p class="stat-number"><?= $progress_count ?></p>
                    </div>
                    <i class="fas fa-tasks stat-icon"></i>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-xl-4">
        <a href="my_all_reports.php" class="text-decoration-none">
            <div class="card stat-card bg-c-green shadow-sm">
                <div class="card-body">
                    <div class="stat-info">
                        <h5 class="card-title">Resolved This Month</h5>
                        <p class="stat-number"><?= $resolved_count_month ?></p>
                    </div>
                    <i class="fas fa-check-double stat-icon"></i>
                </div>
            </div>
        </a>
    </div>
</div>

<?php include 'templates/footer.php'; ?>