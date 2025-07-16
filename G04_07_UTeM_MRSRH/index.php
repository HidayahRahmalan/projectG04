<?php
require_once 'db.php';

// Initialize variables
$total_reports = 0;
$pending_reports = 0;
$resolved_reports = 0;
$error_message = '';

// Fetch live statistics from the database
if ($conn && !$conn->connect_error) {
    try {
        $sql_total = "SELECT COUNT(*) FROM report";
        $total_reports = $conn->query($sql_total)->fetch_row()[0] ?? 0;

        $sql_pending = "SELECT COUNT(*) FROM report WHERE Status = 'pending'";
        $pending_reports = $conn->query($sql_pending)->fetch_row()[0] ?? 0;

        $sql_resolved = "SELECT COUNT(*) FROM report WHERE Status = 'resolved'";
        $resolved_reports = $conn->query($sql_resolved)->fetch_row()[0] ?? 0;

    } catch (Exception $e) {
        $error_message = "Could not load system statistics.";
        error_log("Index page stats error: " . $e->getMessage());
    }
    $conn->close();
} else {
    $error_message = "System statistics are currently unavailable.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - UTeM Maintenance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/index_styles.css">
</head>
<body class="landing-page">

    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center" href="index.php">
                <img src="assets/img/utem_logo.jpg" alt="UTeM Logo" style="height: 30px; margin-right: 10px;">
                UTeM Maintenance
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#publicNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="publicNavbar">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a href="login.php" class="btn btn-primary fw-bold">
                            <i class="fas fa-sign-in-alt me-2"></i>Staff & Admin Login
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <img src="assets/img/utem_logo.jpg" alt="UTeM Logo" class="mb-4" style="height: 80px;">
            <h1 class="display-4">Efficient Maintenance Reporting</h1>
            <p class="lead">
                The centralized platform for UTeM staff and administrators to report, track, and resolve maintenance issues across all residential locations.
            </p>
            <a href="login.php" class="btn btn-light btn-lg btn-cta"><i class="fas fa-wrench me-2"></i>Access Your Portal</a>
            <a href="#how-it-works" class="btn btn-outline-light btn-lg btn-cta"><i class="fas fa-question-circle me-2"></i>Learn More</a>
        </div>
    </section>

    <div class="container my-5">
        <!-- Statistics Section -->
        <section id="statistics" class="text-center">
            <h2 class="section-title">System Status at a Glance</h2>
            <p class="section-subtitle">Live statistics from our maintenance system.</p>
            <?php if ($error_message): ?>
                <div class="alert alert-warning"><?php echo htmlspecialchars($error_message); ?></div>
            <?php else: ?>
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="card h-100 shadow-sm border-light">
                            <div class="card-body">
                                <i class="fas fa-file-alt fa-3x text-primary mb-3"></i>
                                <h3 class="card-title"><?php echo number_format($total_reports); ?></h3>
                                <p class="text-muted">Total Reports Filed</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100 shadow-sm border-light">
                            <div class="card-body">
                                <i class="fas fa-hourglass-half fa-3x text-warning mb-3"></i>
                                <h3 class="card-title"><?php echo number_format($pending_reports); ?></h3>
                                <p class="text-muted">Pending Action</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100 shadow-sm border-light">
                            <div class="card-body">
                                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                <h3 class="card-title"><?php echo number_format($resolved_reports); ?></h3>
                                <p class="text-muted">Issues Resolved</p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </section>

        <hr class="my-5">

        <!-- How It Works Section -->
        <section id="how-it-works" class="text-center">
            <h2 class="section-title">A Streamlined Process</h2>
            <p class="section-subtitle">Our system simplifies maintenance management from start to finish.</p>
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-plus-circle"></i></div>
                        <h5>1. Create Report</h5>
                        <p>Administrators identify an issue and create a detailed report, selecting the required expertise.</p>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-user-cog"></i></div>
                        <h5>2. Auto-Assign</h5>
                        <p>The system intelligently assigns the task to the least busy staff member with the correct skill set.</p>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-tasks"></i></div>
                        <h5>3. Track & Resolve</h5>
                        <p>Staff track progress, add evidence, and mark tasks as resolved, keeping the system updated in real-time.</p>
                    </div>
                </div>
            </div>
        </section>

    </div>

    <footer class="footer mt-auto">
        <div class="container">
            <span>© UTeM Maintenance System <?php echo date("Y"); ?> | For Internal Use</span>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>