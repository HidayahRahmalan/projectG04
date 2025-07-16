<?php
$page_title = "Admin Dashboard";
$css_files = ['dashboard.css'];
include 'templates/header.php';

// Check if user is an admin
if ($_SESSION['role'] != 5) { header("Location: login.php"); exit(); }

$pending_count = $conn->query("SELECT COUNT(*) as count FROM report WHERE Status = 'pending'")->fetch_assoc()['count'];
$progress_count = $conn->query("SELECT COUNT(*) as count FROM report WHERE Status = 'in_progress'")->fetch_assoc()['count'];
$resolved_count = $conn->query("SELECT COUNT(*) as count FROM report WHERE Status = 'resolved'")->fetch_assoc()['count'];
$total_staff = $conn->query("SELECT COUNT(*) as count FROM users WHERE Role != 5")->fetch_assoc()['count'];
?>

<h1 class="h2 mb-4">System Overview</h1>
<div class="row g-4">
    <div class="col-md-6 col-xl-3">
        <div class="card stat-card bg-c-yellow shadow-sm">
            <div class="card-body">
                <div class="stat-info">
                    <h5 class="card-title">Pending Reports</h5>
                    <p class="stat-number"><?= $pending_count ?></p>
                </div>
                <i class="fas fa-hourglass-half stat-icon"></i>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card stat-card bg-c-blue shadow-sm">
            <div class="card-body">
                <div class="stat-info">
                    <h5 class="card-title">In Progress</h5>
                    <p class="stat-number"><?= $progress_count ?></p>
                </div>
                <i class="fas fa-cogs stat-icon"></i>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card stat-card bg-c-green shadow-sm">
            <div class="card-body">
                <div class="stat-info">
                    <h5 class="card-title">Resolved Reports</h5>
                    <p class="stat-number"><?= $resolved_count ?></p>
                </div>
                <i class="fas fa-check-circle stat-icon"></i>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card stat-card bg-c-purple shadow-sm">
            <div class="card-body">
                <div class="stat-info">
                    <h5 class="card-title">Total Staff</h5>
                    <p class="stat-number"><?= $total_staff ?></p>
                </div>
                <i class="fas fa-users stat-icon"></i>
            </div>
        </div>
    </div>
</div>

<h2 class="h3 mt-5 mb-4">Quick Actions</h2>
<div class="row g-4">
    <div class="col-md-4">
        <a href="admin_all_reports.php" class="action-card">
            <div class="action-icon"><i class="fas fa-list-check"></i></div>
            <h5>View All Reports</h5>
        </a>
    </div>
    <div class="col-md-4">
        <a href="create_report.php" class="action-card">
            <div class="action-icon"><i class="fas fa-plus-circle"></i></div>
            <h5>Create New Report</h5>
        </a>
    </div>
    <div class="col-md-4">
        <a href="register.php" class="action-card">
            <div class="action-icon"><i class="fas fa-user-plus"></i></div>
            <h5>Register New User</h5>
        </a>
    </div>
</div>

<?php include 'templates/footer.php'; ?>